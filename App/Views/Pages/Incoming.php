<?php
require HELPER_PATH .'office_helper.php';



// // Check if user is logged in
// // if (!isset($_SESSION['uNameLogin'])) {
// //     header("Location: Login.php");
// //     exit();
// // }

// // // Check user type for validation
// // if (!isset($_SESSION['userAuthLevel'])) {
// //     // Redirect to login if no auth level is set
// //     header("Location: Login.php");
// //     exit();
// // }

// // // Check if user is superadmin, redirect to Documents.php
// // if (isset($_SESSION['userAuthLevel']) && strtolower($_SESSION['userAuthLevel']) === 'superadmin') {
// //     header('Location: Documents.php');
// //     exit();
// // }

// // Include database connection
// // include CORE_PATH .'database.php';

// // Set timezone to Manila for display consistency
// date_default_timezone_set('Asia/Manila');

// // Check for success message
// $show_success = isset($_GET['success']) && $_GET['success'] == '1';

// // Get filter parameters
// $search = $_GET['search'] ?? '';
// $office_filter = $_GET['office'] ?? '';
// $delivery_filter = $_GET['delivery'] ?? '';
// $status_filter = $_GET['status'] ?? '';
// $date_from = $_GET['date_from'] ?? '';
// $date_to = $_GET['date_to'] ?? '';

// // Build the SQL query with filters - show all documents addressed to the logged-in user
// $sql = "SELECT * FROM maindoc WHERE 1";
// $count_sql = "SELECT COUNT(*) as total FROM maindoc WHERE 1";
// $params = [];
// // $types = "";
// $count_params = [];
// // $count_types = "";

// // Add session-based filtering for receiving office (addressTo)
// if (isset($_SESSION['uNameLogin'])) {
//     $username = strtolower($_SESSION['uNameLogin']);
//     $username_to_office = [
//         'dictbulacan' => 'dictbulacan',
//         'dictpampanga' => 'dictpampanga',
//         'dictaurora' => 'dictaurora',
//         'dictbataan' => 'dictbataan',
//         'dictne' => 'dictne',
//         'dicttarlac' => 'dicttarlac',
//         'dictzambales' => 'dictzambales',
//         'admin' => 'maindoc',
//         'maindoc' => 'maindoc',
//         'others' => 'others'
//     ];
//     if (isset($username_to_office[$username])) {
//         $office_match = $username_to_office[$username];
//         $sql .= " AND LOWER(SUBSTRING(addressTo, 2)) = ?";
//         $count_sql .= " AND LOWER(SUBSTRING(addressTo, 2)) = ?";
//         $params[] = $office_match;
//         // $types .= "s";
//         $count_params[] = $office_match;
//         // $count_types .= "s";
//     }
// }

// if (!empty($search)) {
//     $sql .= " AND (doctitle LIKE ? OR officeName LIKE ? OR senderName LIKE ? OR emailAdd LIKE ? OR courierName LIKE ?)";
//     $count_sql .= " AND (doctitle LIKE ? OR officeName LIKE ? OR senderName LIKE ? OR emailAdd LIKE ? OR courierName LIKE ?)";
//     $search_param = "%$search%";
//     $params[] = $search_param;
//     $params[] = $search_param;
//     $params[] = $search_param;
//     $params[] = $search_param;
//     $params[] = $search_param;
//     // $types .= "sssss";
//     $count_params[] = $search_param;
//     $count_params[] = $search_param;
//     $count_params[] = $search_param;
//     $count_params[] = $search_param;
//     $count_params[] = $search_param;
//     // $count_types .= "sssss";
// }

// if (!empty($office_filter)) {
//     $sql .= " AND officeName = ?";
//     $count_sql .= " AND officeName = ?";
//     $params[] = $office_filter;
//     // $types .= "s";
//     $count_params[] = $office_filter;
//     // $count_types .= "s";
// }

// if (!empty($delivery_filter)) {
//     $sql .= " AND modeOfDel = ?";
//     $count_sql .= " AND modeOfDel = ?";
//     $params[] = $delivery_filter;
//     // $types .= "s";
//     $count_params[] = $delivery_filter;
//     // $count_types .= "s";
// }

// if (!empty($status_filter)) {
//     $sql .= " AND status = ?";
//     $count_sql .= " AND status = ?";
//     $params[] = $status_filter;
//     // $types .= "s";
//     $count_params[] = $status_filter;
//     // $count_types .= "s";
// }

// // Exclude documents with status 'Received' or 'Endorsed' from Incoming Documents
// $sql .= " AND (status IS NULL OR (status != 'Received' AND status != 'Endorsed'))";
// $count_sql .= " AND (status IS NULL OR (status != 'Received' AND status != 'Endorsed'))";

// if (!empty($date_from)) {
//     $sql .= " AND DATE(dateAndTime) >= ?";
//     $count_sql .= " AND DATE(dateAndTime) >= ?";
//     $params[] = $date_from;
//     // $types .= "s";
//     $count_params[] = $date_from;
//     // $count_types .= "s";
// }

// if (!empty($date_to)) {
//     $sql .= " AND DATE(dateAndTime) <= ?";
//     $count_sql .= " AND DATE(dateAndTime) <= ?";
//     $params[] = $date_to;
//     // $types .= "s";
//     $count_params[] = $date_to;
//     // $count_types .= "s";
// }

// $sql .= " ORDER BY dateAndTime DESC";

// // Pagination setup
// $page = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;
// $per_page = 10;
// $offset = ($page - 1) * $per_page;

// // Get total count for pagination
// $count_stmt = $pdo->prepare($count_sql);
// $count_stmt->execute($count_params);
// $total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
// $total_pages = ceil($total_records / $per_page);

// // Add LIMIT and OFFSET to main query
// $sql .= " LIMIT ? OFFSET ?";
// // $types .= "ii"; 
// $params[] = $per_page;
// $params[] = $offset;

// // Prepare and execute the statement
// $stmt = $pdo->prepare($sql);
// $stmt->execute($params);
// $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// // Get total count for display
// // $total_records = $result ? $result->num_rows : 0;

// // Get unique offices for filter dropdown (only from user's own receiving office)
// $user_receiving_office_filter = "";
// if (isset($_SESSION['uNameLogin'])) {
//     $username = $_SESSION['uNameLogin'];
//     $username_to_receiving_office = [
//         'dictbulacan' => 'RdictBulacan',
//         'dictpampanga' => 'RdictPampanga',
//         'dictaurora' => 'RdictAurora',
//         'dictbataan' => 'RdictBataan',
//         'dictne' => 'RdictNE',
//         'dicttarlac' => 'RdictTarlac',
//         'dictzambales' => 'RdictZambales',
//         'admin' => 'Rmaindoc',
//         'maindoc' => 'Rmaindoc',
//         'others' => 'ROthers'
//     ];
    
//     $username_lower = strtolower($username);
//     if (isset($username_to_receiving_office[$username_lower])) {
//         $user_receiving_office = $username_to_receiving_office[$username_lower];
//         $user_receiving_office_filter = " AND addressTo = " . $pdo->quote($user_receiving_office);
//     }
// }

// // Fetch receiver name from users table for the current session user
// $receiverName = '';
// if (isset($_SESSION['userID'])) {
//     $userID = $_SESSION['userID'];
//     $stmtReceiver = $pdo->prepare('SELECT name FROM users WHERE userID = ?'); // ✅ prepare with PDO
//     $stmtReceiver->execute([$userID]);                                        // ✅ execute with array of params
//     $receiverName = $stmtReceiver->fetchColumn();                             // ✅ fetchColumn gets first column from first row
// }


// $offices_sql = "SELECT DISTINCT officeName FROM maindoc 
//                 WHERE filetype = 'incoming'" . $user_receiving_office_filter . " 
//                 ORDER BY officeName";

// $offices = [];
// foreach ($pdo->query($offices_sql) as $row) { // ✅ PDO query
//     $offices[] = $row['officeName'];          // ✅ $row is already associative
// }

// // Get unique statuses for filter dropdown (only from user's own receiving office)
// $statuses_sql = "SELECT DISTINCT status FROM maindoc 
//                  WHERE filetype = 'incoming' 
//                  AND status IS NOT NULL 
//                  AND status != ''" . $user_receiving_office_filter . " 
//                  ORDER BY status";

// $statuses = [];
// foreach ($pdo->query($statuses_sql) as $row) { // ✅ Change: $pdo->query(...) instead of $conn->query
//     $statuses[] = $row['status'];             // ✅ Change: fetch_assoc() replaced by associative $row
// }


// $officeDisplayNames = [
//     'dictbulacan' => 'Provincial Office Bulacan',
//     'dictaurora' => 'Provincial Office Aurora',
//     'dictbataan' => 'Provincial Office Bataan',
//     'dictpampanga' => 'Provincial Office Pampanga',
//     'dictPampanga' => 'Provincial Office Pampanga',
//     'dicttarlac' => 'Provincial Office Tarlac',
//     'dictzambales' => 'Provincial Office Zambales',
//     'dictothers' => 'Provincial Office Others',
//     'dictNE' => 'Provincial Office Nueva Ecija',
//     'dictne' => 'Provincial Office Nueva Ecija',
//     'dictNUEVAECIJA' => 'Provincial Office Nueva Ecija',
//     'maindoc' => 'DICT Region 3 Office',
//     'Rdictpampanga' => 'Provincial Office Pampanga',
//     'RdictPampanga' => 'Provincial Office Pampanga',
//     'RdictTarlac' => 'Provincial Office Tarlac',
//     'RdictBataan' => 'Provincial Office Bataan',
//     'RdictBulacan' => 'Provincial Office Bulacan',
//     'RdictAurora' => 'Provincial Office Aurora',
//     'RdictZambales' => 'Provincial Office Zambales',
//     'RdictNuevaEcija' => 'Provincial Office Nueva Ecija',
//     'RdictNE' => 'Provincial Office Nueva Ecija',
//     'Rmaindoc' => 'DICT Region 3 Office',
//     // Add more as you encounter new codes!
// ];
// function getOfficeDisplayNamePHP($code, $map) {
//     if (!$code) return '';
//     $lower = strtolower($code);
//     foreach ($map as $key => $val) {
//         if (strtolower($key) === $lower) return $val;
//     }
//     return $code;
// }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="/dictproj1/public/Scripts/pwa-init.js"></script>
    <link rel="manifest" href="/dictproj1/manifest.json">
    <link rel="stylesheet" href="/dictproj1/src/input.css">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/dashboard.css">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/modal.css">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/style.css">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/incoming.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Incoming Documents</title>
</head>
<body class="min-h-screen">
    <div class="app-container">
        <?php include __DIR__ . '/../components/Sidebar.php'; ?>

     
        
        <div class="flex-1 p-6 bg-linear-90 from-[#48517f] to-[#322b5f] min-h-screen overflow-y-auto  " id="docu">
          
            
            <div class="max-w-[96%] mx-auto">
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
                        <h1 class="text-3xl font-bold text-indigo-500">Incoming Documents</h1>
                        <p class="text-gray-300 mt-2">View and track all incoming documents</p>
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
                                            Sender Office
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Document Title
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Sender Name
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
                                <tbody class="bg-white divide-y divide-gray-200" id="incomingTableBody">
                                      <?php if (!empty($documents)): ?>
                                        <?php foreach ($documents as $row): ?>
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
                                                        <?php echo htmlspecialchars(getOfficeDisplayName($row['officeName'])); ?>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['doctitle'] ?? '-'); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($row['senderName']); ?>
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
                                        <?php endforeach; ?>
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
                <h2 class="text-black">Document Details</h2>
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
                        <h3>Document Information</h3>
                        <div class="form-group">
                            <label for="detailsDocumentTitle">Document Title</label>
                            <input type="text" id="detailsDocumentTitle" readonly class="input-readonly">
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
    <div id="addSignatureModal" class="modal " style="display:none;">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2 class="text-black">Add Receipt Signature</h2>
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
                            <input type="text" name="receiverName" id="receiverName" value="<?php echo htmlspecialchars($receiverName); ?>" class="input-readonly " readonly required>
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
                            <input type="file" name="podFile" id="podFile" accept="image/*,application/pdf">
                            <button type="button" id="useCameraBtn" class="btn btn-secondary" style="margin-top:8px; display:inline-flex; align-items:center; gap:6px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A2 2 0 0122 9.618V17a2 2 0 01-2 2H4a2 2 0 01-2-2V9.618a2 2 0 012.447-1.894L9 10m6 0V6a2 2 0 00-2-2H9a2 2 0 00-2 2v4m6 0H9" /></svg>
                                <span>Use Camera</span>
                            </button>
                            <img id="capturedImagePreview" src="" style="display:none; max-width:300px; margin-top:8px;"/>
                            <input type="hidden" name="podCameraImage" id="podCameraImage">
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

  
    <script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>
    <script src="/dictproj1/public/Scripts/docs/incoming.js"></script>
    <script src="/dictproj1/public/Scripts/filterToggle.js"></script>
    <script src="/dictproj1/public/Scripts/docs/addSignatureForm.js"></script>
</body>
</html>

