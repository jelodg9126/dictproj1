<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['uNameLogin'])) {
    header("Location: Login.php");
    exit();
}

// Include database connection
include __DIR__ . '/../../Model/connect.php';

// Set timezone to Manila for display consistency
date_default_timezone_set('Asia/Manila');

// Check for success message
$show_success = isset($_GET['success']) && $_GET['success'] == '1';

// Get filter parameters
$search = $_GET['search'] ?? '';
$office_filter = $_GET['office'] ?? '';
$delivery_filter = $_GET['delivery'] ?? '';
$status_filter = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build the SQL query with filters - show all documents addressed to the logged-in user
$sql = "SELECT * FROM maindoc WHERE 1";
$count_sql = "SELECT COUNT(*) as total FROM maindoc WHERE 1";
$params = [];
$types = "";
$count_params = [];
$count_types = "";

// Add session-based filtering for receiving office (addressTo)
if (isset($_SESSION['uNameLogin'])) {
    $username = strtolower($_SESSION['uNameLogin']);
    // Filter where addressTo (without first character, lowercased) matches username
    $sql .= " AND LOWER(SUBSTRING(addressTo, 2)) = ?";
    $count_sql .= " AND LOWER(SUBSTRING(addressTo, 2)) = ?";
    $params[] = $username;
    $types .= "s";
    $count_params[] = $username;
    $count_types .= "s";
}

if (!empty($search)) {
    $sql .= " AND (officeName LIKE ? OR senderName LIKE ? OR emailAdd LIKE ? OR courierName LIKE ?)";
    $count_sql .= " AND (officeName LIKE ? OR senderName LIKE ? OR emailAdd LIKE ? OR courierName LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ssss";
    $count_params[] = $search_param;
    $count_params[] = $search_param;
    $count_params[] = $search_param;
    $count_params[] = $search_param;
    $count_types .= "ssss";
}

if (!empty($office_filter)) {
    $sql .= " AND officeName = ?";
    $count_sql .= " AND officeName = ?";
    $params[] = $office_filter;
    $types .= "s";
    $count_params[] = $office_filter;
    $count_types .= "s";
}

if (!empty($delivery_filter)) {
    $sql .= " AND modeOfDel = ?";
    $count_sql .= " AND modeOfDel = ?";
    $params[] = $delivery_filter;
    $types .= "s";
    $count_params[] = $delivery_filter;
    $count_types .= "s";
}

if (!empty($status_filter)) {
    $sql .= " AND status = ?";
    $count_sql .= " AND status = ?";
    $params[] = $status_filter;
    $types .= "s";
    $count_params[] = $status_filter;
    $count_types .= "s";
}

if (!empty($date_from)) {
    $sql .= " AND DATE(dateAndTime) >= ?";
    $count_sql .= " AND DATE(dateAndTime) >= ?";
    $params[] = $date_from;
    $types .= "s";
    $count_params[] = $date_from;
    $count_types .= "s";
}

if (!empty($date_to)) {
    $sql .= " AND DATE(dateAndTime) <= ?";
    $count_sql .= " AND DATE(dateAndTime) <= ?";
    $params[] = $date_to;
    $types .= "s";
    $count_params[] = $date_to;
    $count_types .= "s";
}

$sql .= " ORDER BY dateAndTime DESC";

// Pagination setup
$page = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Get total count for pagination
$count_stmt = $conn->prepare($count_sql);
if (!empty($count_params)) {
    $count_stmt->bind_param($count_types, ...$count_params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_records = $count_result->fetch_assoc()['total'];
$count_stmt->close();
$total_pages = ceil($total_records / $per_page);

// Add LIMIT and OFFSET to main query
$sql .= " LIMIT ? OFFSET ?";
$types .= "ii";
$params[] = $per_page;
$params[] = $offset;

// Prepare and execute the statement
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Get total count for display
$total_records = $result ? $result->num_rows : 0;

// Get unique offices for filter dropdown (only from user's own receiving office)
$user_receiving_office_filter = "";
if (isset($_SESSION['uNameLogin'])) {
    $username = $_SESSION['uNameLogin'];
    $username_to_receiving_office = [
        'dictbulacan' => 'RdictBulacan',
        'dictpampanga' => 'RdictPampanga',
        'dictaurora' => 'RdictAurora',
        'dictbataan' => 'RdictBataan',
        'dictne' => 'RdictNE',
        'dicttarlac' => 'RdictTarlac',
        'dictzambales' => 'RdictZambales',
        'maindoc' => 'Rmaindoc',
        'others' => 'ROthers'
    ];
    
    $username_lower = strtolower($username);
    if (isset($username_to_receiving_office[$username_lower])) {
        $user_receiving_office = $username_to_receiving_office[$username_lower];
        $user_receiving_office_filter = " AND addressTo = '" . $user_receiving_office . "'";
    }
}

$offices_sql = "SELECT DISTINCT officeName FROM maindoc WHERE filetype = 'incoming'" . $user_receiving_office_filter . " ORDER BY officeName";
$offices_result = $conn->query($offices_sql);
$offices = [];
if ($offices_result) {
    while ($row = $offices_result->fetch_assoc()) {
        $offices[] = $row['officeName'];
    }
}

// Get unique statuses for filter dropdown (only from user's own receiving office)
$statuses_sql = "SELECT DISTINCT status FROM maindoc WHERE filetype = 'incoming' AND status IS NOT NULL AND status != ''" . $user_receiving_office_filter . " ORDER BY status";
$statuses_result = $conn->query($statuses_sql);
$statuses = [];
if ($statuses_result) {
    while ($row = $statuses_result->fetch_assoc()) {
        $statuses[] = $row['status'];
    }
}

$officeDisplayNames = [
    'dictbulacan' => 'Provincial Office Bulacan',
    'dictaurora' => 'Provincial Office Aurora',
    'dictbataan' => 'Provincial Office Bataan',
    'dictpampanga' => 'Provincial Office Pampanga',
    'dictPampanga' => 'Provincial Office Pampanga',
    'dicttarlac' => 'Provincial Office Tarlac',
    'dictzambales' => 'Provincial Office Zambales',
    'dictothers' => 'Provincial Office Others',
    'dictNE' => 'Provincial Office Nueva Ecija',
    'dictne' => 'Provincial Office Nueva Ecija',
    'dictNUEVAECIJA' => 'Provincial Office Nueva Ecija',
    'maindoc' => 'DICT Region 3 Office',
    'Rdictpampanga' => 'Provincial Office Pampanga',
    'RdictPampanga' => 'Provincial Office Pampanga',
    'RdictTarlac' => 'Provincial Office Tarlac',
    'RdictBataan' => 'Provincial Office Bataan',
    'RdictBulacan' => 'Provincial Office Bulacan',
    'RdictAurora' => 'Provincial Office Aurora',
    'RdictZambales' => 'Provincial Office Zambales',
    'RdictNuevaEcija' => 'Provincial Office Nueva Ecija',
    'RdictNE' => 'Provincial Office Nueva Ecija',
    'Rmaindoc' => 'DICT Region 3 Office',
    // Add more as you encounter new codes!
];
function getOfficeDisplayNamePHP($code, $map) {
    if (!$code) return '';
    $lower = strtolower($code);
    foreach ($map as $key => $val) {
        if (strtolower($key) === $lower) return $val;
    }
    return $code;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="/dictproj1/src/input.css">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/dashboard.css">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/modal.css">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/style.css">
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Incoming Documents</title>
</head>
<body>
    <div class="app-container">
        <?php include __DIR__ . '/../components/Sidebar.php'; ?>

        <div class="flex-1 p-6 bg-gray-50 min-h-screen overflow-y-auto  " id="docu">
        
        <div class="flex-1 p-6 bg-linear-90 from-[#48517f] to-[#322b5f] min-h-screen overflow-y-auto  " id="docu">
          
            
            <div class="max-w-7xl mx-auto">
                <!-- Success Message -->
                <?php if ($show_success): ?>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Document record has been added successfully.',
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'OK',
                                timer: 3000
                            });
                        });
                    </script>
                <?php endif; ?>

                 <p class="text-xl text-gray-300 p-3 font-bold rounded-2xl">Welcome, <?php echo htmlspecialchars($_SESSION['uNameLogin']); ?>!</p>
                <div class="flex items-center justify-between mb-6">
                    <div class="items-center">
                        <h1 class="text-3xl font-bold text-blue-800">Incoming Documents</h1>
                        <p class="text-gray-600 mt-2">View and track all incoming documents</p>
                        <p class="text-gray-300 mt-2">View and track all incoming documents (read-only)</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 flex items-center gap-2" id="filterToggle">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
                            </svg>
                            <span id="filterToggleText">Show Filters</span>
                        </button>
                    </div>
                </div>

                <!-- Search and Filter Section -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6" id="filterSection" style="display: none;">
                    <form method="GET" action="index.php">
                        <input type="hidden" name="page" value="incoming">
                        <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                            <div class="flex items-center gap-4 flex-1">
                                <div class="relative flex-1 max-w-md">
                                    <input
                                        type="text"
                                        name="search"
                                        class="filter-input pl-10 pr-4 py-2 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="Search documents, sender, or recipient..."
                                        value="<?php echo htmlspecialchars($search); ?>"
                                    />
                                </div>
                                <div class="flex items-center gap-2">
                                    <select
                                        name="delivery"
                                        class="filter-input border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">All Modes</option>
                                        <option value="Courier" <?php echo $delivery_filter === 'Courier' ? 'selected' : ''; ?>>Courier</option>
                                        <option value="In-Person" <?php echo $delivery_filter === 'In-Person' ? 'selected' : ''; ?>>In-Person</option>
                                    </select>
                                    <select
                                        name="status"
                                        class="filter-input border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">All Status</option>
                                        <?php foreach ($statuses as $status): ?>
                                            <option value="<?php echo htmlspecialchars($status); ?>" 
                                                    <?php echo $status_filter === $status ? 'selected' : ''; ?>>
                                                <?php echo ucfirst(htmlspecialchars($status)); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="?page=incoming" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">Clear</a>
                                <span class="text-sm text-gray-600">
                                    <?php echo $total_records; ?> document<?php echo $total_records != 1 ? 's' : ''; ?>
                                </span>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Incoming Documents Table (default visible) -->
                <div id="incomingTableSection" >
                    <!-- Table Section -->
                    <div class="bg-[rgba(240,240,240,0.4)] backdrop-blur rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 backdrop-blur border-b border-gray-200">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Office
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Sender Name
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Email
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Delivery Mode
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Courier Name
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Received By
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date & Time
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php if ($result && $result->num_rows > 0): ?>
                                        <?php while($row = $result->fetch_assoc()): ?>
                                            <?php 
                                                $row_for_data = $row; 
                                                $row_for_data['pod'] = (!empty($row['pod']) || !empty($row['pod_filename'])) ? true : false; 
                                                $row_for_data['hasSignature'] = !empty($row['signature']); 
                                                $row_for_data['receivedBy'] = $row['receivedBy'] ?? '';
                                                $row_for_data['pod_filename'] = $row['pod_filename'] ?? '';
                                                unset($row_for_data['signature']); 
                                            ?>
                                            <tr class="hover:bg-gray-50 transition-colors" data-transaction-id="<?php echo htmlspecialchars($row['transactionID']); ?>">
                                            <?php $row_for_data = $row; $row_for_data['pod'] = !empty($row['pod_filename']) ? true : false; $row_for_data['hasSignature'] = !empty($row['signature']); unset($row_for_data['signature']); ?>
                                            <tr class="hover:bg-[rgb(203,202,202)] transition-colors" data-transaction-id="<?php echo htmlspecialchars($row['transactionID']); ?>">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <?php echo htmlspecialchars(getOfficeDisplayNamePHP($row['officeName'], $officeDisplayNames)); ?>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($row['senderName']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($row['emailAdd']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($row['modeOfDel']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($row['courierName'] ?: '-'); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($row['receivedBy'] ?: '-'); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($row['status'] ?: '-'); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo date('M d, Y g:i A', strtotime($row['dateAndTime'])); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php if (strtolower($row['status']) !== 'received'): ?>
                                                        <button class="view-btn bg-blue-500 text-white px-3 py-1 rounded" data-row='<?php echo json_encode($row_for_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>'>View</button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center py-12">
                                                <div class="text-gray-500 text-lg">No incoming documents found</div>
                                                <div class="text-gray-400 text-sm mt-2">Try adjusting your search or filter criteria</div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Pagination Controls -->
                    <div class="flex justify-center my-4">
                        <?php if ($total_pages > 1): ?>
                            <nav class="inline-flex -space-x-px">
                                <?php
                                // Build query string for filters/search
                                $query_params = $_GET;
                                foreach(['page_num'] as $unset) unset($query_params[$unset]);
                                $base_query = http_build_query($query_params);
                                for ($i = 1; $i <= $total_pages; $i++):
                                    $link = '?' . $base_query . ($base_query ? '&' : '') . 'page_num=' . $i;
                                ?>
                                    <a href="<?php echo $link; ?>" class="px-3 py-1 border border-gray-300 <?php echo $i == $page ? 'bg-blue-500 text-white' : 'bg-white text-gray-700'; ?> hover:bg-blue-100 mx-1 rounded">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Modal for Row Preview (styled like submit modal) -->
    <div id="detailsModal" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Document Details</h2>
                <span class="close" id="closeDetailsModal">&times;</span>
            </div>
            <div class="modal-body">
                <form id="detailsForm">
                    <div class="form-section">
                        <h3>Office Information</h3>
                        <div class="form-group">
                            <label for="detailsOfficeName">Select Office</label>
                            <input type="text" id="detailsOfficeName" readonly class="input-readonly">
                        </div>
                    </div>
                    <div class="form-section">
                        <h3>Sender Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="detailsSenderName">Sender Name</label>
                                <input type="text" id="detailsSenderName" readonly class="input-readonly">
                            </div>
                            <div class="form-group">
                                <label for="detailsEmailAdd">Email Address</label>
                                <input type="text" id="detailsEmailAdd" readonly class="input-readonly">
                            </div>
                        </div>
                    </div>
                    <div class="form-section">
                        <h3>Delivery Information</h3>
                        <div class="form-group">
                            <label for="detailsAddressTo">Receiving Office</label>
                            <input type="text" id="detailsAddressTo" readonly class="input-readonly">
                        </div>
                        <div class="form-group">
                            <label for="detailsModeOfDel">Mode of Delivery</label>
                            <input type="text" id="detailsModeOfDel" readonly class="input-readonly">
                        </div>
                        <div class="form-group">
                            <label for="detailsCourierName">Courier Name</label>
                            <input type="text" id="detailsCourierName" readonly class="input-readonly">
                        </div>
                        <div class="form-group">
                            <label for="detailsReceivedBy">Received By</label>
                            <input type="text" id="detailsReceivedBy" readonly class="input-readonly">
                        </div>
                    </div>
                    <div class="form-section">
                        <h3>Status & Date</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="detailsStatus">Status</label>
                                <input type="text" id="detailsStatus" readonly class="input-readonly">
                            </div>
                            <div class="form-group">
                                <label for="detailsDateAndTime">Date & Time</label>
                                <input type="text" id="detailsDateAndTime" readonly class="input-readonly">
                            </div>
                        </div>
                    </div>
                    <div class="form-section">
                        <h3>Signature - Sender</h3>
                        <div class="form-group">
                            <a href="#" id="signatureEnlargeLink">
                                <img id="detailsSignature" src="" alt="Sender Signature" style="max-width:300px; max-height:120px; border:1px solid #ccc; background:#f9f9f9; cursor:pointer;">
                            </a>
                        </div>
                    </div>
                    <div class="form-section">
                        <h3>Signature - Receiver</h3>
                        <div class="form-group">
                            <a href="#" id="receiverSignatureEnlargeLink">
                                <img id="detailsReceiverSignature" src="" alt="Receiver Signature" style="max-width:300px; max-height:120px; border:1px solid #ccc; background:#f9f9f9; cursor:pointer; display:none;">
                            </a>
                            <span id="receiverSignatureNoImage" style="color:#aaa;">No Receiver Signature</span>
                        </div>
                    </div>
                    <div class="form-section">
                        <h3>Proof of Document (POD) - Sender</h3>
                        <div class="form-group">
                            <a href="#" id="podEnlargeLink">
                                <img id="detailsPod" src="" alt="Sender Proof of Document" style="max-width:300px; max-height:120px; border:1px solid #ccc; background:#f9f9f9; display:none; cursor:pointer;">
                            </a>
                            <span id="podNoImage" style="color:#aaa;">No POD</span>
                        </div>
                    </div>
                    <div class="form-section">
                        <h3>Proof of Document (POD) - Receiver</h3>
                        <div class="form-group">
                            <a href="#" id="receiverPodEnlargeLink">
                                <img id="detailsReceiverPod" src="" alt="Receiver Proof of Document" style="max-width:300px; max-height:120px; border:1px solid #ccc; background:#f9f9f9; display:none; cursor:pointer;">
                            </a>
                            <span id="receiverPodNoImage" style="color:#aaa;">No Receiver POD</span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Enlarged Signature Lightbox Modal -->
    <div id="signatureLightbox" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.85); z-index:99999; align-items:center; justify-content:center; cursor:pointer;">
      <img id="enlargedSignature" src="" alt="Enlarged Signature" style="max-width:90vw; max-height:90vh; border:4px solid #fff; border-radius:8px; box-shadow:0 0 20px #000; background:#fff; cursor:default;">
    </div>

    <!-- Add a new lightbox for POD -->
    <div id="podLightbox" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.85); z-index:99999; align-items:center; justify-content:center; cursor:pointer;">
      <img id="enlargedPod" src="" alt="Enlarged POD" style="max-width:90vw; max-height:90vh; border:4px solid #fff; border-radius:8px; box-shadow:0 0 20px #000; background:#fff; cursor:default;">
    </div>

    <!-- POD Preview Modal -->
    <div id="podPreviewModal" class="modal" style="display:none;">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h2>Proof of Document</h2>
                <span class="close" id="closePodPreviewModal" style="cursor:pointer;">&times;</span>
            </div>
            <div class="modal-body" style="text-align:center;">
                <img id="podPreviewImg" src="" alt="Proof of Document" style="max-width:100%; max-height:400px; border:1px solid #ccc; background:#f9f9f9;">
            </div>
        </div>
    </div>

    <!-- Add Signature Modal for Incoming Documents -->
    <div id="addSignatureModal" class="modal" style="display:none;">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2>Add Receipt Signature</h2>
                <span class="close" id="closeAddSignatureModal" style="cursor:pointer;">&times;</span>
            </div>
            <div class="modal-body">
                <form id="addSignatureForm">
                    <input type="hidden" name="transactionID" id="signatureTransactionID">
                    <div class="form-section">
                        <h3>Document Information</h3>
                        <div class="form-group">
                            <label>From Office: <span id="signatureOfficeName"></span></label>
                        </div>
                        <div class="form-group">
                            <label>Sender: <span id="signatureSenderName"></span></label>
                        </div>
                        <div class="form-group">
                            <label>Time Sent: <span id="signatureDateReceived"></span></label>
                        </div>
                        <div class="form-group">
                            <label>Sender's Proof of Document (POD)</label>
                            <a href="#" id="addSignatureSenderPodEnlargeLink">
                                <img id="addSignatureSenderPodPreview" src="" alt="Sender POD Preview" style="max-width:300px; max-height:120px; border:1px solid #ccc; background:#f9f9f9; display:none; margin-bottom:8px; cursor:pointer;">
                            </a>
                            <span id="addSignatureSenderPodNoImage" style="color:#aaa; display:none;">No POD</span>
                        </div>
                    </div>
                    <div class="form-section">
                        <h3>Receipt Information</h3>
                        <div class="form-group">
                            <label for="receiverName" class="required">Your Name (Receiver)</label>
                            <input type="text" name="receiverName" id="receiverName" required placeholder="Enter your full name">
                        </div>
                        <div class="form-group">
                            <label for="receiptSignaturePad">Please sign below to confirm receipt:</label>
                            <br>
                            <canvas id="receiptSignaturePad" width="350" height="220" style="border:1px solid #ccc; background:#fff;"></canvas>
                            <br>
                            <button type="button" class="btn btn-secondary" id="clearReceiptSignature">Clear Signature</button>
                            <input type="hidden" name="receiptSignature" id="receiptSignatureInput">
                        </div>
                        <div class="form-group">
                            <label for="podFile" class="required">Upload Your Proof of Document (POD)</label>
                            <input type="file" name="podFile" id="podFile" accept="image/*,application/pdf" required>
                        </div>
                    </div>
                    <div class="submit-section">
                        <button type="submit" class="btn">Confirm Receipt</button>
                        <button type="button" class="btn btn-secondary" id="cancelReceiptSignature">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add a new lightbox for receiver POD -->
    <div id="receiverPodLightbox" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.85); z-index:99999; align-items:center; justify-content:center; cursor:pointer;">
      <img id="enlargedReceiverPod" src="" alt="Enlarged Receiver POD" style="max-width:90vw; max-height:90vh; border:4px solid #fff; border-radius:8px; box-shadow:0 0 20px #000; background:#fff; cursor:default;">
    </div>

    <!-- Add a lightbox modal for the sender POD preview -->
    <div id="addSignatureSenderPodLightbox" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.85); z-index:99999; align-items:center; justify-content:center; cursor:pointer;">
      <img id="addSignatureSenderPodEnlarged" src="" alt="Enlarged Sender POD" style="max-width:90vw; max-height:90vh; border:4px solid #fff; border-radius:8px; box-shadow:0 0 20px #000; background:#fff; cursor:default;">
    </div>

    <style>
    .input-readonly {
        width: 100%;
        padding: 6px 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        background: #f5f5f5;
    }
    .modal {
        display: none;
        position: fixed !important;
        z-index: 99999 !important;
        left: 0; top: 0; width: 100vw; height: 100vh;
        align-items: center; justify-content: center;
        background: rgba(0,0,0,0.3);
    }
    .modal[style*="display: flex"] {
        display: flex !important;
    }
    </style>
    <script>
    // Mapping for office codes to display names
    const officeDisplayNames = {
        'dictbulacan': 'Provincial Office Bulacan',
        'dictaurora': 'Provincial Office Aurora',
        'dictbataan': 'Provincial Office Bataan',
        'dictpampanga': 'Provincial Office Pampanga',
        'dictPampanga': 'Provincial Office Pampanga',
        'dicttarlac': 'Provincial Office Tarlac',
        'dictzambales': 'Provincial Office Zambales',
        'dictothers': 'Provincial Office Others',
        'dictNE': 'Provincial Office Nueva Ecija',
        'dictne': 'Provincial Office Nueva Ecija',
        'dictNUEVAECIJA': 'Provincial Office Nueva Ecija',
        'maindoc': 'DICT Region 3 Office',
        'Rdictpampanga': 'Provincial Office Pampanga',
        'RdictPampanga': 'Provincial Office Pampanga',
        'RdictTarlac': 'Provincial Office Tarlac',
        'RdictBataan': 'Provincial Office Bataan',
        'RdictBulacan': 'Provincial Office Bulacan',
        'RdictAurora': 'Provincial Office Aurora',
        'RdictZambales': 'Provincial Office Zambales',
        'RdictNuevaEcija': 'Provincial Office Nueva Ecija',
        'RdictNE': 'Provincial Office Nueva Ecija',
        'Rmaindoc': 'DICT Region 3 Office',
        // Add more as you encounter new codes!
    };
    function getOfficeDisplayName(code) {
        if (!code) return '';
        var lower = code.toLowerCase();
        for (var key in officeDisplayNames) {
            if (key.toLowerCase() === lower) return officeDisplayNames[key];
        }
        return code;
    }
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.view-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                var rowData = btn.getAttribute('data-row');
                if (!rowData) return;
                var data = JSON.parse(rowData);
                // Prefill Received By if available, always editable
                var receiverNameInput = document.getElementById('receiverName');
                if (receiverNameInput) {
                    receiverNameInput.value = data.receivedBy || '';
                    receiverNameInput.readOnly = false;
                }
                // Always clear signature pad
                var receiptSignatureInput = document.getElementById('receiptSignatureInput');
                if (receiptSignatureInput) receiptSignatureInput.value = '';
                var receiptCanvas = document.getElementById('receiptSignaturePad');
                if (receiptCanvas && receiptCanvas.getContext) {
                    var ctx = receiptCanvas.getContext('2d');
                    ctx.clearRect(0, 0, receiptCanvas.width, receiptCanvas.height);
                }
                // Always clear POD file input
                var podFileInput = document.getElementById('podFile');
                if (podFileInput) podFileInput.value = '';
                // Do NOT show any previous signature or POD in the Add Signature modal
                // Set document info
                document.getElementById('signatureOfficeName').textContent = getOfficeDisplayName(data.officeName) || '';
                document.getElementById('signatureSenderName').textContent = data.senderName || '';
                document.getElementById('signatureDateReceived').textContent = data.dateAndTime ? new Date(data.dateAndTime).toLocaleString() : '';
                // Set transactionID in hidden input
                var transactionID = data.transactionID;
                document.getElementById('signatureTransactionID').value = transactionID;
                
                // Set sender POD preview in Add Signature modal
                var addSignatureSenderPodPreview = document.getElementById('addSignatureSenderPodPreview');
                var addSignatureSenderPodNoImage = document.getElementById('addSignatureSenderPodNoImage');
                if (data.pod && data.transactionID) {
                    addSignatureSenderPodPreview.src = '/dictproj1/modules/get_pod.php?id=' + data.transactionID;
                    addSignatureSenderPodPreview.style.display = 'inline';
                    addSignatureSenderPodNoImage.style.display = 'none';
                    addSignatureSenderPodPreview.onerror = function() {
                        addSignatureSenderPodPreview.style.display = 'none';
                        addSignatureSenderPodNoImage.style.display = 'inline';
                    };
                } else {
                    addSignatureSenderPodPreview.src = '';
                    addSignatureSenderPodPreview.style.display = 'none';
                    addSignatureSenderPodNoImage.style.display = 'inline';
                }
                // Show the modal
                document.getElementById('addSignatureModal').style.display = 'flex';
                // In the JS view-btn click handler, after setting detailsPod, add:
                var receiverPodImg = document.getElementById('detailsReceiverPod');
                var receiverPodEnlargeLink = document.getElementById('receiverPodEnlargeLink');
                if (receiverPodEnlargeLink) {
                    receiverPodEnlargeLink.onclick = function(e) {
                        e.preventDefault();
                        if (!receiverPodImg.src || receiverPodImg.style.display === 'none') return;
                        var enlarged = document.getElementById('enlargedReceiverPod');
                        enlarged.src = receiverPodImg.src;
                        var lightbox = document.getElementById('receiverPodLightbox');
                        lightbox.style.display = 'flex';
                        lightbox.style.opacity = 0;
                        setTimeout(() => { lightbox.style.opacity = 1; }, 10);
                    };
                }
                document.getElementById('receiverPodLightbox').onclick = function(e) {
                    if (e.target === this) {
                        this.style.display = 'none';
                        document.getElementById('enlargedReceiverPod').src = '';
                    }
                };
                document.getElementById('enlargedReceiverPod').onclick = function(e) {
                    e.stopPropagation();
                };
                // Signature preview
                var detailsSignature = document.getElementById('detailsSignature');
                if (data.hasSignature && data.transactionID) {
                    detailsSignature.src = '/dictproj1/modules/get_signature.php?id=' + data.transactionID;
                    detailsSignature.style.display = 'inline';
                } else {
                    detailsSignature.src = '';
                    detailsSignature.style.display = 'none';
                }
                // POD preview
                var podImg = document.getElementById('detailsPod');
                var podNoImage = document.getElementById('podNoImage');
                if (data.pod && data.transactionID) {
                    podImg.src = '/dictproj1/modules/get_pod.php?id=' + data.transactionID;
                    podImg.style.display = 'inline';
                    podNoImage.style.display = 'none';
                    podImg.onerror = function() {
                        podImg.style.display = 'none';
                        podNoImage.style.display = 'inline';
                    };
                } else {
                    podImg.src = '';
                    podImg.style.display = 'none';
                    podNoImage.style.display = 'inline';
                }
                // In JS, after setting detailsSignature, add:
                var receiverSignatureImg = document.getElementById('detailsReceiverSignature');
                var receiverSignatureNoImage = document.getElementById('receiverSignatureNoImage');
                if (data.transactionID) {
                    receiverSignatureImg.src = '/dictproj1/modules/get_signature.php?id=' + data.transactionID + '&type=receiver';
                    receiverSignatureImg.style.display = 'inline';
                    receiverSignatureNoImage.style.display = 'none';
                    receiverSignatureImg.onerror = function() {
                        receiverSignatureImg.style.display = 'none';
                        receiverSignatureNoImage.style.display = 'inline';
                    };
                } else {
                    receiverSignatureImg.src = '';
                    receiverSignatureImg.style.display = 'none';
                    receiverSignatureNoImage.style.display = 'inline';
                }
            });
        });
        // Modal close logic for POD preview
        document.getElementById('closePodPreviewModal').onclick = function() {
            document.getElementById('podPreviewModal').style.display = 'none';
            document.getElementById('podPreviewImg').src = '';
        };
        document.getElementById('podPreviewModal').onclick = function(e) {
            if (e.target === this) {
                this.style.display = 'none';
                document.getElementById('podPreviewImg').src = '';
            }
        };
        // Modal close logic for detailsModal
        document.getElementById('closeDetailsModal').onclick = function() {
            document.getElementById('detailsModal').style.display = 'none';
        };
        document.getElementById('detailsModal').onclick = function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        };
        // Signature enlarge (lightbox) feature
        document.getElementById('signatureEnlargeLink').onclick = function(e) {
            e.preventDefault();
            var sigImg = document.getElementById('detailsSignature');
            if (!sigImg.src || sigImg.src.endsWith('get_signature.php?id=')) {
                return;
            }
            var enlarged = document.getElementById('enlargedSignature');
            enlarged.src = sigImg.src;
            var lightbox = document.getElementById('signatureLightbox');
            lightbox.style.display = 'flex';
            lightbox.style.opacity = 0;
            setTimeout(() => { lightbox.style.opacity = 1; }, 10);
        };
        document.getElementById('signatureLightbox').onclick = function(e) {
            if (e.target === this) {
                this.style.display = 'none';
                document.getElementById('enlargedSignature').src = '';
            }
        };
        document.getElementById('enlargedSignature').onclick = function(e) {
            e.stopPropagation();
        };
        // POD lightbox
        var podImg = document.getElementById('detailsPod');
        podImg.onclick = function() {
            if (!podImg.src || podImg.style.display === 'none') return;
            var enlarged = document.getElementById('enlargedPod');
            enlarged.src = podImg.src;
            var lightbox = document.getElementById('podLightbox');
            lightbox.style.display = 'flex';
            lightbox.style.opacity = 0;
            setTimeout(() => { lightbox.style.opacity = 1; }, 10);
        };
        document.getElementById('podLightbox').onclick = function(e) {
            if (e.target === this) {
                this.style.display = 'none';
                document.getElementById('enlargedPod').src = '';
            }
        };
        document.getElementById('enlargedPod').onclick = function(e) {
            e.stopPropagation();
        };
        // Live filter: auto-submit on input/change
        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }
        var filterForm = document.querySelector('#filterSection form');
        if (filterForm) {
            // For text input (search)
            var searchInput = filterForm.querySelector('input[name="search"]');
            if (searchInput) {
                searchInput.addEventListener('input', debounce(function() {
                    filterForm.submit();
                }, 400));
            }
            // For dropdowns and other filter fields
            filterForm.querySelectorAll('.filter-input').forEach(function(input) {
                if (input !== searchInput) {
                    input.addEventListener('change', function() {
                        filterForm.submit();
                    });
                }
            });
        }
        // Keep filter section open if any filter is active OR if it was previously opened
        var filterSection = document.getElementById('filterSection');
        if (filterSection) {
            // Check if filter panel was previously opened (stored in sessionStorage)
            var wasFilterPanelOpen = sessionStorage.getItem('filterPanelOpen') === 'true';
            
            var hasActiveFilter = false;
            filterSection.querySelectorAll('.filter-input').forEach(function(input) {
                if (input.value && input.value !== '') {
                    hasActiveFilter = true;
                }
            });
            
            // Keep panel open if there are active filters OR if it was previously opened
            if (hasActiveFilter || wasFilterPanelOpen) {
                filterSection.style.display = 'block';
                var filterToggleText = document.getElementById('filterToggleText');
                if (filterToggleText) filterToggleText.textContent = 'Hide Filters';
            }
        }
        // Add JS for POD lightbox
        var podEnlargeLink = document.getElementById('podEnlargeLink');
        if (podEnlargeLink) {
            podEnlargeLink.onclick = function(e) {
                e.preventDefault();
                var podImg = document.getElementById('detailsPod');
                var enlarged = document.getElementById('enlargedPod');
                enlarged.src = podImg.src;
                var lightbox = document.getElementById('podLightbox');
                lightbox.style.display = 'flex';
                lightbox.style.opacity = 0;
                setTimeout(() => { lightbox.style.opacity = 1; }, 10);
            };
        }
        
        // Add Signature Modal functionality
        // Modal close logic for addSignatureModal
        document.getElementById('closeAddSignatureModal').onclick = function() {
            document.getElementById('addSignatureModal').style.display = 'none';
        };
        document.getElementById('addSignatureModal').onclick = function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        };
        
        // Cancel button for add signature modal
        document.getElementById('cancelReceiptSignature').onclick = function() {
            document.getElementById('addSignatureModal').style.display = 'none';
        };
        
        // Receipt Signature Pad
        var receiptCanvas = document.getElementById('receiptSignaturePad');
        var receiptSignatureInput = document.getElementById('receiptSignatureInput');
        var clearReceiptBtn = document.getElementById('clearReceiptSignature');
        if (receiptCanvas && receiptSignatureInput) {
            var receiptCtx = receiptCanvas.getContext('2d');
            let receiptDrawing = false;
            let receiptLastX = 0;
            let receiptLastY = 0;

            function receiptDraw(e) {
                if (!receiptDrawing) return;
                receiptCtx.lineWidth = 2;
                receiptCtx.lineCap = 'round';
                receiptCtx.strokeStyle = '#222';
                let x, y;
                if (e.touches) {
                    x = e.touches[0].clientX - receiptCanvas.getBoundingClientRect().left;
                    y = e.touches[0].clientY - receiptCanvas.getBoundingClientRect().top;
                } else {
                    x = e.offsetX;
                    y = e.offsetY;
                }
                receiptCtx.lineTo(x, y);
                receiptCtx.stroke();
                receiptCtx.beginPath();
                receiptCtx.moveTo(x, y);
            }

            receiptCanvas.addEventListener('mousedown', (e) => {
                receiptDrawing = true;
                receiptCtx.beginPath();
                receiptCtx.moveTo(e.offsetX, e.offsetY);
            });
            receiptCanvas.addEventListener('mousemove', receiptDraw);
            receiptCanvas.addEventListener('mouseup', () => {
                receiptDrawing = false;
                receiptCtx.beginPath();
            });
            receiptCanvas.addEventListener('mouseout', () => {
                receiptDrawing = false;
                receiptCtx.beginPath();
            });
            // Touch events for mobile
            receiptCanvas.addEventListener('touchstart', (e) => {
                receiptDrawing = true;
                receiptCtx.beginPath();
                receiptCtx.moveTo(e.touches[0].clientX - receiptCanvas.getBoundingClientRect().left, e.touches[0].clientY - receiptCanvas.getBoundingClientRect().top);
            });
            receiptCanvas.addEventListener('touchmove', receiptDraw);
            receiptCanvas.addEventListener('touchend', () => {
                receiptDrawing = false;
                receiptCtx.beginPath();
            });

            // Clear signature button
            if (clearReceiptBtn) {
                clearReceiptBtn.addEventListener('click', function() {
                    receiptCtx.clearRect(0, 0, receiptCanvas.width, receiptCanvas.height);
                    receiptSignatureInput.value = '';
                });
            }
            
            // Capture signature data when drawing stops
            function captureReceiptSignature() {
                if (receiptCanvas.width > 0 && receiptCanvas.height > 0) {
                    var signatureData = receiptCanvas.toDataURL('image/png');
                    receiptSignatureInput.value = signatureData;
                }
            }
            
            // Add event listeners to capture signature
            receiptCanvas.addEventListener('mouseup', captureReceiptSignature);
            receiptCanvas.addEventListener('touchend', captureReceiptSignature);
        }
        
        // Handle add signature form submission
        document.getElementById('addSignatureForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            var transactionID = document.getElementById('signatureTransactionID').value;
            var receiverName = document.getElementById('receiverName').value.trim();
            var signatureData = receiptSignatureInput.value;
            var podFileInput = document.getElementById('podFile');
            
            // Validation
            if (!receiverName) {
                alert('Please enter your name.');
                return;
            }
            
            if (!signatureData) {
                alert('Please provide your signature.');
                return;
            }
            
            if (!podFileInput || podFileInput.files.length === 0) {
                alert('Please upload a Proof of Document (POD) file.');
                return;
            }
            
            // Show loading state
            var submitBtn = this.querySelector('button[type="submit"]');
            var originalText = submitBtn.textContent;
            submitBtn.textContent = 'Processing...';
            submitBtn.disabled = true;
            
            // Prepare form data
            var formData = new FormData();
            formData.append('transactionID', transactionID);
            formData.append('receiverName', receiverName);
            formData.append('receiptSignature', signatureData);
            
            // Add podFile to form data
            if (podFileInput && podFileInput.files.length > 0) {
                formData.append('podFile', podFileInput.files[0]);
            }
            
            // Submit to server
            fetch('/dictproj1/modules/update_receipt_signature.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Success - show success message and close modal
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        confirmButtonColor: '#3085d6',
                        timer: 2000
                    });
                    document.getElementById('addSignatureModal').style.display = 'none';
                    // Remove the row from Incoming table
                    var rowToRemove = document.querySelector('tr[data-transaction-id="' + transactionID + '"]');
                    if (rowToRemove) rowToRemove.remove();
                } else {
                    // Error - show error message
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: (data.errors ? data.errors.join(', ') : 'Unknown error occurred'),
                        confirmButtonColor: '#d33'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while processing your request.');
            })
            .finally(() => {
                // Reset button state
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });
        // Add JS for sender POD lightbox
        var addSignatureSenderPodEnlargeLink = document.getElementById('addSignatureSenderPodEnlargeLink');
        var addSignatureSenderPodPreview = document.getElementById('addSignatureSenderPodPreview');
        var addSignatureSenderPodNoImage = document.getElementById('addSignatureSenderPodNoImage');
        if (addSignatureSenderPodEnlargeLink) {
            addSignatureSenderPodEnlargeLink.onclick = function(e) {
                e.preventDefault();
                if (!addSignatureSenderPodPreview.src || addSignatureSenderPodPreview.style.display === 'none') return;
                var enlarged = document.getElementById('addSignatureSenderPodEnlarged');
                enlarged.src = addSignatureSenderPodPreview.src;
                var lightbox = document.getElementById('addSignatureSenderPodLightbox');
                lightbox.style.display = 'flex';
                lightbox.style.opacity = 0;
                setTimeout(() => { lightbox.style.opacity = 1; }, 10);
            };
        }
        document.getElementById('addSignatureSenderPodLightbox').onclick = function(e) {
            if (e.target === this) {
                this.style.display = 'none';
                document.getElementById('addSignatureSenderPodEnlarged').src = '';
            }
        };
        document.getElementById('addSignatureSenderPodEnlarged').onclick = function(e) {
            e.stopPropagation();
        };
    });

    // Fallback for Show Filters button if external JS fails
    (function() {
        var filterToggle = document.getElementById('filterToggle');
        var filterSection = document.getElementById('filterSection');
        var filterToggleText = document.getElementById('filterToggleText');
            if (filterToggle && filterSection) {
                filterToggle.addEventListener('click', function() {
                var isVisible = filterSection.style.display !== 'none';
                    filterSection.style.display = isVisible ? 'none' : 'block';
                    
                // Store the panel state in sessionStorage
                    if (isVisible) {
                    // Panel is being closed
                    sessionStorage.setItem('filterPanelOpen', 'false');
                    } else {
                    // Panel is being opened
                    sessionStorage.setItem('filterPanelOpen', 'true');
                }
                
                if (filterToggleText) {
                    filterToggleText.textContent = isVisible ? 'Show Filters' : 'Hide Filters';
                    }
                });
            }
    })();
    </script>
</body>
</html>

<?php
// Close database connection
$conn->close();
?> 