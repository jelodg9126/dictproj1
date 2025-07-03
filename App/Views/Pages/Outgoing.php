<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['uNameLogin'])) {
    header("Location: Login.php");
    exit();
}

// Check user type for validation
if (!isset($_SESSION['userAuthLevel'])) {
    // Redirect to login if no auth level is set
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

// Build the SQL query with filters - only outgoing documents
$sql = "SELECT * FROM maindoc WHERE filetype = 'outgoing'";
$count_sql = "SELECT COUNT(*) as total FROM maindoc WHERE filetype = 'outgoing'";
$params = [];
$types = "";
$count_params = [];
$count_types = "";

// Add session-based filtering for office
if (isset($_SESSION['uNameLogin'])) {
    $username = $_SESSION['uNameLogin'];
    
    // Map username to office value (same mapping as in form_module.php)
    $username_to_office = [
        'dictbulacan' => 'dictBulacan',
        'dictpampanga' => 'dictPampanga',
        'dictaurora' => 'dictAurora',
        'dictbataan' => 'dictBataan',
        'dictne' => 'dictNE',
        'dicttarlac' => 'dictTarlac',
        'dictzambales' => 'dictZambales',
        'maindoc' => 'maindoc',
        'others' => 'Others'
    ];
    
    $username_lower = strtolower($username);
    if (isset($username_to_office[$username_lower])) {
        $user_office = $username_to_office[$username_lower];
        $sql .= " AND officeName = ?";
        $count_sql .= " AND officeName = ?";
        $params[] = $user_office;
        $types .= "s";
        $count_params[] = $user_office;
        $count_types .= "s";
    }
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

// Get unique offices for filter dropdown (only from user's own office)
$user_office_filter = "";
if (isset($_SESSION['uNameLogin'])) {
    $username = $_SESSION['uNameLogin'];
    $username_to_office = [
        'dictbulacan' => 'dictBulacan',
        'dictpampanga' => 'dictPampanga',
        'dictaurora' => 'dictAurora',
        'dictbataan' => 'dictBataan',
        'dictne' => 'dictNE',
        'dicttarlac' => 'dictTarlac',
        'dictzambales' => 'dictZambales',
        'maindoc' => 'maindoc',
        'others' => 'Others'
    ];
    
    $username_lower = strtolower($username);
    if (isset($username_to_office[$username_lower])) {
        $user_office_filter = " AND officeName = '" . $username_to_office[$username_lower] . "'";
    }
}

$offices_sql = "SELECT DISTINCT officeName FROM maindoc WHERE filetype = 'outgoing'" . $user_office_filter . " ORDER BY officeName";
$offices_result = $conn->query($offices_sql);
$offices = [];
if ($offices_result) {
    while ($row = $offices_result->fetch_assoc()) {
        $offices[] = $row['officeName'];
    }
}

// Get unique statuses for filter dropdown (only from user's own office)
$statuses_sql = "SELECT DISTINCT status FROM maindoc WHERE filetype = 'outgoing' AND status IS NOT NULL AND status != ''" . $user_office_filter . " ORDER BY status";
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
    <title>Outgoing Documents</title>
</head>
<body>
    <div class="app-container">
        <?php include __DIR__ . '/../components/Sidebar.php'; ?>

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

                <div class="flex items-center justify-between mb-6">
                    <div class="items-center">
                        <p class="text-xl text-gray-300 p-3 font-bold rounded-2xl">Welcome, <?php echo htmlspecialchars($_SESSION['uNameLogin']); ?>!</p>
                        <h1 class="text-3xl font-bold text-indigo-500">Outgoing Documents</h1>
                        <p class="text-gray-300 mt-2">Manage and track all outgoing documents</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 flex items-center gap-2" id="filterToggle">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
                            </svg>
                            <span id="filterToggleText">Show Filters</span>
                        </button>
                        <button type="button" class="btn" id="openFormModal">Add New Record</button>
                    </div>
                </div>

                <!-- Search and Filter Section -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6" id="filterSection" style="display: none;">
                    <form method="GET" action="index.php">
                        <input type="hidden" name="page" value="outgoing">
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
                                <a href="?page=outgoing" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">Clear</a>
                                <span class="text-sm text-gray-600">
                                    <?php echo $total_records; ?> document<?php echo $total_records != 1 ? 's' : ''; ?>
                                </span>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Table Section -->
                <div class=" bg-gray-200 rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto ">
                        <table class="w-full ">
                            <thead class="bg-[rgba(240,240,240,0.51)] backdrop-blur border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Destination Office
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Sender Name
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Email
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Delivery Mode
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Courier Name
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Date & Time
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-[rgb(197,197,197,0.1)] backdrop-blur-sm divide-y divide-gray-200">
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while($row = $result->fetch_assoc()): ?>
                                        <?php 
                                            // Always include all expected fields for the modal
                                            $row_for_data = [
                                                'officeName' => $row['officeName'] ?? '',
                                                'senderName' => $row['senderName'] ?? '',
                                                'emailAdd' => $row['emailAdd'] ?? '',
                                                'modeOfDel' => $row['modeOfDel'] ?? '',
                                                'courierName' => $row['courierName'] ?? '',
                                                'status' => $row['status'] ?? '',
                                                'dateAndTime' => $row['dateAndTime'] ?? '',
                                                'dateReceived' => $row['dateReceived'] ?? '',
                                                'transactionID' => $row['transactionID'] ?? '',
                                                'addressTo' => $row['addressTo'] ?? '',
                                                'pod' => !empty($row['pod']) ? true : false,
                                                'receivedBy' => $row['receivedBy'] ?? ''
                                            ];
                                        ?>
                                        <tr class="hover:bg-gray-50 transition-colors" data-transaction-id="<?php echo htmlspecialchars($row['transactionID']); ?>">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars(getOfficeDisplayNamePHP($row['addressTo'], $officeDisplayNames)); ?>
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
                                                <?php echo htmlspecialchars($row['status'] ?: '-'); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo date('M d, Y g:i A', strtotime($row['dateAndTime'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <button class="view-btn bg-blue-500 text-white px-3 py-1 rounded" data-row='<?php echo json_encode($row_for_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>'>View</button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-12 ">
                                            <div class="text-gray-500 text-lg">No outgoing documents found</div>
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

    <!-- Modal for Form -->
    <div id="formModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Document Record</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <?php include __DIR__ . '/../../../modules/form_module.php'; ?>
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
                            <label for="detailsOfficeName">Originating Office</label>
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
                        <div class="form-group">
                            <label>Sender Signature</label><br>
                            <img id="detailsSenderSignature" src="" alt="Sender Signature" style="max-width:300px; max-height:120px; border:1px solid #ccc; background:#f9f9f9;">
                        </div>
                        <div class="form-group">
                            <label>Sender POD</label><br>
                            <img id="detailsSenderPod" src="" alt="Sender POD" style="max-width:300px; max-height:120px; border:1px solid #ccc; background:#f9f9f9;">
                            <span id="senderPodNoImage" style="color:#aaa;">No POD</span>
                        </div>
                    </div>
                    <div class="form-section">
                        <h3>Receiver Information</h3>
                        <div class="form-group">
                            <label for="detailsReceiverName">Receiver Name</label>
                            <input type="text" id="detailsReceiverName" readonly class="input-readonly">
                        </div>
                        <div class="form-group">
                            <label for="detailsDestinationOffice">Destination Office</label>
                            <input type="text" id="detailsDestinationOffice" readonly class="input-readonly">
                        </div>
                        <div class="form-group">
                            <label>Receiver Signature</label><br>
                            <img id="detailsReceiverSignature" src="" alt="Receiver Signature" style="max-width:300px; max-height:120px; border:1px solid #ccc; background:#f9f9f9;">
                            <span id="receiverSignatureNoImage" style="color:#aaa;">No receiver signature</span>
                        </div>
                        <div class="form-group">
                            <label>Receiver POD</label><br>
                            <img id="detailsReceiverPod" src="" alt="Receiver POD" style="max-width:300px; max-height:120px; border:1px solid #ccc; background:#f9f9f9;">
                            <span id="receiverPodNoImage" style="color:#aaa;">No receiver POD</span>
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
                                <label for="detailsDateAndTime" id="detailsDateAndTimeLabel">Date & Time</label>
                                <input type="text" id="detailsDateAndTime" readonly class="input-readonly">
                            </div>
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
    <script src="/dictproj1/modal.js"></script>
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
        // Handle view button clicks
        document.querySelectorAll('.view-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                var rowData = btn.getAttribute('data-row');
                if (!rowData) return;
                var data = JSON.parse(rowData);
                var displayOffice = getOfficeDisplayName(data.officeName);
                var displayReceivingOffice = getOfficeDisplayName(data.addressTo);
                document.getElementById('detailsOfficeName').value = displayOffice;
                document.getElementById('detailsSenderName').value = data.senderName || '';
                document.getElementById('detailsEmailAdd').value = data.emailAdd || '';
                document.getElementById('detailsStatus').value = data.status || '';
                var dateTimeInput = document.getElementById('detailsDateAndTime');
                var dateTimeLabel = document.getElementById('detailsDateAndTimeLabel');
                if (data.status && data.status.toLowerCase() === 'received' && data.dateReceived) {
                    dateTimeInput.value = data.dateReceived ? new Date(data.dateReceived).toLocaleString() : '';
                    dateTimeLabel.textContent = 'Date & Time Received';
                } else {
                    dateTimeInput.value = data.dateAndTime ? new Date(data.dateAndTime).toLocaleString() : '';
                    dateTimeLabel.textContent = 'Date & Time Created';
                }
                // Sender signature
                var senderSig = document.getElementById('detailsSenderSignature');
                if (data.transactionID) {
                    senderSig.src = '/dictproj1/modules/get_signature.php?id=' + data.transactionID;
                    senderSig.style.display = 'inline';
                } else {
                    senderSig.src = '';
                    senderSig.style.display = 'none';
                }
                // Sender POD
                var senderPodImg = document.getElementById('detailsSenderPod');
                var senderPodNoImage = document.getElementById('senderPodNoImage');
                if (data.pod && data.transactionID) {
                    senderPodImg.src = '/dictproj1/modules/get_pod.php?id=' + data.transactionID;
                    senderPodImg.style.display = 'inline';
                    senderPodNoImage.style.display = 'none';
                } else {
                    senderPodImg.src = '';
                    senderPodImg.style.display = 'none';
                    senderPodNoImage.style.display = 'inline';
                }
                // Receiver name
                var receiverNameInput = document.getElementById('detailsReceiverName');
                receiverNameInput.value = data.receivedBy || '';
                if (!data.receivedBy) receiverNameInput.placeholder = 'No receiver';
                // Receiver signature
                var receiverSig = document.getElementById('detailsReceiverSignature');
                var receiverSigNoImage = document.getElementById('receiverSignatureNoImage');
                if (data.transactionID) {
                    receiverSig.src = '/dictproj1/modules/get_signature.php?id=' + data.transactionID + '&type=receiver';
                    receiverSig.style.display = 'inline';
                    receiverSigNoImage.style.display = 'none';
                    receiverSig.onerror = function() {
                        receiverSig.style.display = 'none';
                        receiverSigNoImage.style.display = 'inline';
                    };
                } else {
                    receiverSig.src = '';
                    receiverSig.style.display = 'none';
                    receiverSigNoImage.style.display = 'inline';
                }
                // Receiver POD
                var receiverPodImg = document.getElementById('detailsReceiverPod');
                var receiverPodNoImage = document.getElementById('receiverPodNoImage');
                if (data.transactionID) {
                    receiverPodImg.src = '/dictproj1/modules/get_receiver_pod.php?id=' + data.transactionID;
                    receiverPodImg.style.display = 'inline';
                    receiverPodNoImage.style.display = 'none';
                    receiverPodImg.onerror = function() {
                        receiverPodImg.style.display = 'none';
                        receiverPodNoImage.style.display = 'inline';
                    };
                } else {
                    receiverPodImg.src = '';
                    receiverPodImg.style.display = 'none';
                    receiverPodNoImage.style.display = 'inline';
                }
                document.getElementById('detailsDestinationOffice').value = getOfficeDisplayName(data.addressTo) || '';
                document.getElementById('detailsModal').style.display = 'flex';
            });
        });
        
        // Event delegation for POD preview button
        var tableBody = document.querySelector('tbody');
        if (tableBody) {
            tableBody.addEventListener('click', function(e) {
                var btn = e.target.closest('.preview-pod-btn');
                if (btn) {
                    // Eye button clicked
                    e.preventDefault();
                    e.stopPropagation();
                    var id = btn.getAttribute('data-id');
                    var modal = document.getElementById('podPreviewModal');
                    var img = document.getElementById('podPreviewImg');
                    img.src = '/dictproj1/modules/get_pod.php?id=' + id;
                    modal.style.display = 'flex';
                    return;
                }
            });
        }
        // Modal close logic for POD preview
        var closePodPreviewModal = document.getElementById('closePodPreviewModal');
        if (closePodPreviewModal) {
            closePodPreviewModal.onclick = function() {
                document.getElementById('podPreviewModal').style.display = 'none';
                document.getElementById('podPreviewImg').src = '';
            };
        }
        var podPreviewModal = document.getElementById('podPreviewModal');
        if (podPreviewModal) {
            podPreviewModal.onclick = function(e) {
                if (e.target === this) {
                    this.style.display = 'none';
                    document.getElementById('podPreviewImg').src = '';
                }
            };
        }
        // Modal close logic for detailsModal
        var closeDetailsModal = document.getElementById('closeDetailsModal');
        if (closeDetailsModal) {
            closeDetailsModal.onclick = function() {
                document.getElementById('detailsModal').style.display = 'none';
            };
        }
        var detailsModal = document.getElementById('detailsModal');
        if (detailsModal) {
            detailsModal.onclick = function(e) {
                if (e.target === this) {
                    this.style.display = 'none';
                }
            };
        }
        // Signature enlarge (lightbox) feature
        var signatureEnlargeLink = document.getElementById('signatureEnlargeLink');
        if (signatureEnlargeLink) {
            signatureEnlargeLink.onclick = function(e) {
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
        }
        var signatureLightbox = document.getElementById('signatureLightbox');
        if (signatureLightbox) {
            signatureLightbox.onclick = function(e) {
                if (e.target === this) {
                    this.style.display = 'none';
                    document.getElementById('enlargedSignature').src = '';
                }
            };
        }
        var enlargedSignature = document.getElementById('enlargedSignature');
        if (enlargedSignature) {
            enlargedSignature.onclick = function(e) {
                e.stopPropagation();
            };
        }
        // POD lightbox
        var podImg = document.getElementById('detailsPod');
        if (podImg) {
            podImg.onclick = function() {
                if (!podImg.src || podImg.style.display === 'none') return;
                var enlarged = document.getElementById('enlargedPod');
                enlarged.src = podImg.src;
                var lightbox = document.getElementById('podLightbox');
                lightbox.style.display = 'flex';
                lightbox.style.opacity = 0;
                setTimeout(() => { lightbox.style.opacity = 1; }, 10);
            };
        }
        var podLightbox = document.getElementById('podLightbox');
        if (podLightbox) {
            podLightbox.onclick = function(e) {
                if (e.target === this) {
                    this.style.display = 'none';
                    document.getElementById('enlargedPod').src = '';
                }
            };
        }
        var enlargedPod = document.getElementById('enlargedPod');
        if (enlargedPod) {
            enlargedPod.onclick = function(e) {
                e.stopPropagation();
            };
        }
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
        // Modal close logic for formModal
        document.querySelectorAll('#formModal .close').forEach(function(closeBtn) {
            closeBtn.onclick = function() {
                document.getElementById('formModal').style.display = 'none';
            };
        });
        
        // Modal close logic for POD preview
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