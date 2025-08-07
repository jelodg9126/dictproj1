<?php
require_once HELPER_PATH . 'office_helper.php';

// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }

// // Check if user is logged in
// if (!isset($_SESSION['uNameLogin'])) {
//     header("Location: Login.php");
//     exit();
// }

// // Check user type for validation
// if (!isset($_SESSION['userAuthLevel'])) {
//     // Redirect to login if no auth level is set
//     header("Location: Login.php");
//     exit();
// }

// Check user type for conditional display
// $isAdmin = isset($_SESSION['userAuthLevel']) && strtolower($_SESSION['userAuthLevel']) === 'admin';

// Include database connection
// include __DIR__ . '/../../Model/connect.php';

date_default_timezone_set('Asia/Manila');

// Get filter parameters
// $search = $_GET['search'] ?? '';
// $office_filter = $_GET['office'] ?? '';
// $delivery_filter = $_GET['delivery'] ?? '';
// $date_from = $_GET['date_from'] ?? '';
// $date_to = $_GET['date_to'] ?? '';

// Build the SQL query with filters - only received documents for the logged-in user
// $sql = "SELECT * FROM maindoc WHERE status = 'Received' AND (endorsedToName IS NULL OR endorsedToName = '' OR endorsedToSignature IS NULL OR endorsedToSignature = '' OR endorsedDocProof IS NULL OR endorsedDocProof = '')";
// $params = [];
// $types = "";

// Add session-based filtering for receiving office (addressTo)
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
//         $params[] = $office_match;
//         // $types .= "s";
//     }
// }

// // (Optional) Keep endorsement fields filter if needed
// $sql .= " AND (endorsedToName IS NULL OR endorsedToName = '' OR endorsedToSignature IS NULL OR endorsedToSignature = '' OR endorsedDocProof IS NULL OR endorsedDocProof = '')";

// if (!empty($search)) {
//     $sql .= " AND (doctitle LIKE ? OR officeName LIKE ? OR senderName LIKE ? OR emailAdd LIKE ? OR courierName LIKE ?)";
//     $search_param = "%$search%";
//     $params[] = $search_param;
//     $params[] = $search_param;
//     $params[] = $search_param;
//     $params[] = $search_param;
//     $params[] = $search_param;
//     // $types .= "sssss";
// }

// if (!empty($office_filter)) {
//     $sql .= " AND officeName = ?";
//     $params[] = $office_filter;
//     // $types .= "s";
// }

// if (!empty($delivery_filter)) {
//     $sql .= " AND modeOfDel = ?";
//     $params[] = $delivery_filter;
//     // $types .= "s";
// }

// if (!empty($date_from)) {
//     $sql .= " AND DATE(dateAndTime) >= ?";
//     $params[] = $date_from;
//     // $types .= "s";
// }

// if (!empty($date_to)) {
//     $sql .= " AND DATE(dateAndTime) <= ?";
//     $params[] = $date_to;
//     // $types .= "s";
// }

// $sql .= " ORDER BY dateAndTime DESC";

// Pagination setup
// $page = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;
// $per_page = 10;
// $offset = ($page - 1) * $per_page;

// Add LIMIT and OFFSET to main query
// $sql .= " LIMIT ? OFFSET ?";
// // $types .= "ii";
// $params[] = $per_page;
// $params[] = $offset;

// Prepare and execute the statement
// $stmt = $pdo->prepare($sql);
// $stmt->execute($params);
// $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique offices for filter dropdown (only from user's own office)
// $user_office_filter = "";
// if (isset($_SESSION['uNameLogin'])) {
//     $username = $_SESSION['uNameLogin'];
//     $username_to_office = [
//         'dictbulacan' => 'dictBulacan',
//         'dictpampanga' => 'dictPampanga',
//         'dictaurora' => 'dictAurora',
//         'dictbataan' => 'dictBataan',
//         'dictne' => 'dictNE',
//         'dicttarlac' => 'dictTarlac',
//         'dictzambales' => 'dictZambales',
//         'admin' => 'Rmaindoc',
//         'maindoc' => 'maindoc',
//         'others' => 'Others'
//     ];
    
//     $username_lower = strtolower($username);
//     if (isset($username_to_office[$username_lower])) {
//         $user_office_filter = " AND officeName = '" . $username_to_office[$username_lower] . "'";
//     }
// }

// $offices_sql = "SELECT DISTINCT officeName FROM maindoc 
//                 WHERE filetype = 'incoming' AND status = 'Received'" . $user_office_filter . 
//                 " ORDER BY officeName";
// $offices = [];
// foreach ($pdo->query($offices_result) as $row) {
//         $offices[] = $row['officeName'];
//     }


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

// Fetch receiver name from users table for the current session user
// $receiverName = '';
// if (isset($_SESSION['userID'])) {
//     $userID = $_SESSION['userID'];
//     $stmtReceiver = $pdo->prepare('SELECT name FROM users WHERE userID = ?');
//     $stmtReceiver->execute([$userID]);
//     $receiverName = $stmtReceiver->fetchColumn();
// }
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="/dictproj1/public/Scripts/pwa-init.js"></script>
    <link rel="manifest" href="/dictproj1/manifest.json">
    <link rel="stylesheet" href="/dictproj1/src/input.css">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/dashboard.css">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/modal.css">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/style.css">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/received.css">
    <title>Received Documents</title>
    <link rel="icon" href="/dictproj1/public/assets/images/mainCircle.png" type="image/png">
</head>
<body>
    <div class="app-container">
        <?php include __DIR__ . '/../components/Sidebar.php'; ?>
        <div class="flex-1 p-6 bg-linear-90 from-[#48517f] to-[#322b5f] min-h-screen overflow-y-auto" id="docu">
            <div class="max-w-7xl mx-auto">
                <div class="flex items-center justify-between mb-6">
                    <div class="items-center">
                        <p class="text-xl text-gray-300 p-3 font-bold rounded-2xl">Welcome, <?php echo htmlspecialchars($_SESSION['uNameLogin']); ?>!</p>
                        <h1 class="text-3xl font-bold text-indigo-500">Received Documents</h1>
                        <p class="text-gray-300 mt-2">View and track all documents that have been received.</p>
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
                <!-- Filter Section -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6" id="filterSection" style="display: none;">
                    <form method="GET" action="index.php">
                        <input type="hidden" name="page" value="received">
                        <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                            <div class="flex items-center gap-4 flex-1">
                                <div class="relative flex-1 max-w-md">
                                    <input
                                        type="text"
                                        name="search"
                                        class="filter-input pl-10 pr-4 py-2 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="Search document title, sender, or recipient..."
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
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="?page=received" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">Clear</a>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- Table -->
                <div class="bg-gray-200 backdrop-blur rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sender Office</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Document Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sender Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delivery Mode</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Courier Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received By</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($documents)): ?>
                                    <?php foreach ($documents as $row): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap font-medium text-sm text-gray-900"><?php echo getOfficeDisplayName($row['officeName']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['doctitle'] ?? '-'); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['senderName'] ?? ''); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['modeOfDel'] ?? ''); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['courierName'] ?? '-'); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['receivedBy'] ?? '-'); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['status'] ?? '-'); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo isset($row['dateAndTime']) ? date('M d, Y h:i A', strtotime($row['dateAndTime'])) : ''; ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <a href="#" class="view-btn bg-blue-500 text-white px-3 py-1 rounded" data-id="<?php echo $row['transactionID']; ?>" data-row='<?php echo json_encode([
                                                    "officeName" => $row["officeName"] ?? '',
                                                    "senderName" => $row["senderName"] ?? '',
                                                    "dateAndTime" => $row["dateAndTime"] ?? '',
                                                    "receivedBy" => $row["receivedBy"] ?? '',
                                                    "transactionID" => $row["transactionID"],
                                                    "endorsedToName" => $row["endorsedToName"] ?? '',
                                                    "hasEndorsedSignature" => !empty($row["endorsedToSignature"]),
                                                    "hasEndorsedDocProof" => !empty($row["endorsedDocProof"]),
                                                    "doctitle" => $row["doctitle"] ?? ''
                                                ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>'>View</a>
                                                <?php if ($isAdmin): ?>
                                                    <button class="endorse-btn bg-green-500 text-white px-3 py-1 rounded ml-2" data-id="<?php echo $row['transactionID']; ?>">Endorse</button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="9" class="text-center py-4 text-gray-500">No received documents found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Details Modal for Received Documents -->
    <div id="receivedDetailsModal" class="modal" style="display:none;">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2 class="text-black">Received Document Details</h2>
                <span class="close" id="closeReceivedDetailsModal" style="cursor:pointer;">&times;</span>
            </div>
            <div class="modal-body">
                <div class="form-section">
                    <h3>Document Information</h3>
                    <div class="form-group">
                        <label for="detailsDocumentTitle">Document Title</label>
                        <input type="text" id="detailsDocumentTitle" readonly class="input-readonly">
                    </div>
                    <div><b>Office:</b> <span id="detailsOfficeName"></span></div>
                    <div><b>Sender:</b> <span id="detailsSenderName"></span></div>
                    <div><b>Date Received:</b> <span id="detailsDateReceived"></span></div>
                </div>
                <div class="form-section">
                    <h3>Receipt Information</h3>
                    <div><b>Received By:</b> <span id="detailsReceivedBy"></span></div>
                    <div><b>Signature:</b><br>
                        <img id="detailsSignature" src="" alt="Signature" style="max-width:200px; max-height:100px; border:1px solid #ccc; background:#f9f9f9; cursor:pointer;">
                    </div>
                    <div><b>Proof of Document (POD) - Sender:</b><br>
                        <a href="#" id="podEnlargeLink">
                            <img id="detailsPod" src="" alt="Sender Proof of Document" style="max-width:200px; max-height:200px; border:1px solid #ccc; background:#f9f9f9;">
                        </a>
                    </div>
                    <div><b>Proof of Document (POD) - Receiver:</b><br>
                        <a href="#" id="receiverPodEnlargeLink">
                            <img id="detailsReceiverPod" src="" alt="Receiver Proof of Document" style="max-width:200px; max-height:200px; border:1px solid #ccc; background:#f9f9f9;">
                        </a>
                    </div>
                </div>
                <?php if ($isAdmin): ?>
                <div class="form-section">
                    <h3>Endorsement Information</h3>
                    <div><b>Endorsed To Name:</b> <span id="detailsEndorsedToName"></span></div>
                    <div><b>Endorsed To Signature:</b><br>
                        <img id="detailsEndorsedSignature" src="" alt="Endorsed Signature" style="max-width:200px; max-height:100px; border:1px solid #ccc; background:#f9f9f9; cursor:pointer;">
                    </div>
                    <div><b>Endorsed Document Proof:</b><br>
                        <img id="detailsEndorsedDocProof" src="" alt="Endorsed Proof" style="max-width:200px; max-height:200px; border:1px solid #ccc; background:#f9f9f9; cursor:pointer;">
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Endorse Modal -->
    <div id="endorseModal" class="modal" style="display:none;">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2>Endorse Document</h2>
                <span class="close" id="closeEndorseModal" style="cursor:pointer;">&times;</span>
            </div>
            <div class="modal-body">
                <form id="endorseForm">
                    <input type="hidden" id="endorseTransactionID" name="transactionID">
                    <div class="form-group">
                        <label for="endorsedToName">Endorsed To Name</label>
                        <input type="text" id="endorsedToName" name="endorsedToName" class="input-readonly" required>
                    </div>
                    <div class="form-group">
                        <label>Endorsed To Signature</label>
                        <canvas id="endorseSignaturePad" width="300" height="100" style="border:1px solid #ccc;"></canvas>
                        <input type="hidden" id="endorseSignatureInput" name="endorsedToSignature">
                        <button type="button" id="clearEndorseSignature" class="bg-gray-500 text-white px-3 py-1 rounded hover:bg-gray-600 ml-2">Clear Signature</button>
                    </div>
                    <div class="form-group">
                        <label for="endorseDocProof">Endorsed Document Proof (Image/PDF)</label>
                        <input type="file" id="endorseDocProof" name="endorsedDocProof" accept="image/*,application/pdf">
                        <button type="button" id="useEndorseCameraBtn" class="btn btn-secondary" style="margin-top:8px; display:inline-flex; align-items:center; gap:6px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A2 2 0 0122 9.618V17a2 2 0 01-2 2H4a2 2 0 01-2-2V9.618a2 2 0 012.447-1.894L9 10m6 0V6a2 2 0 00-2-2H9a2 2 0 00-2 2v4m6 0H9" /></svg>
                            <span>Use Camera</span>
                        </button>
                        <img id="endorseCapturedImagePreview" src="" style="display:none; max-width:300px; margin-top:8px;"/>
                        <input type="hidden" name="endorseCameraImage" id="endorseCameraImage">
                    </div>
                    <div class="submit-section">
                        <button type="submit" class="btn">Submit Endorsement</button>
                        <button type="button" class="btn btn-secondary" id="cancelEndorse">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Add a new lightbox for receiver POD -->
    <div id="receiverPodLightbox" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.85); z-index:99999; align-items:center; justify-content:center; cursor:pointer;">
      <img id="enlargedReceiverPod" src="" alt="Enlarged Receiver POD" style="max-width:90vw; max-height:90vh; border:4px solid #fff; border-radius:8px; box-shadow:0 0 20px #000; background:#fff; cursor:default;">
    </div>
    <!-- Add a new lightbox for sender POD -->
    <div id="senderPodLightbox" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.85); z-index:99999; align-items:center; justify-content:center; cursor:pointer;">
      <img id="enlargedSenderPod" src="" alt="Enlarged Sender POD" style="max-width:90vw; max-height:90vh; border:4px solid #fff; border-radius:8px; box-shadow:0 0 20px #000; background:#fff; cursor:default;">
    </div>
    <!-- Add lightbox for signature -->
    <div id="signatureLightbox" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.85); z-index:99999; align-items:center; justify-content:center; cursor:pointer;">
      <img id="enlargedSignature" src="" alt="Enlarged Signature" style="max-width:90vw; max-height:90vh; border:4px solid #fff; border-radius:8px; box-shadow:0 0 20px #000; background:#fff; cursor:default;">
    </div>
    <!-- Add lightbox for endorsed signature -->
    <div id="endorsedSignatureLightbox" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.85); z-index:99999; align-items:center; justify-content:center; cursor:pointer;">
      <img id="enlargedEndorsedSignature" src="" alt="Enlarged Endorsed Signature" style="max-width:90vw; max-height:90vh; border:4px solid #fff; border-radius:8px; box-shadow:0 0 20px #000; background:#fff; cursor:default;">
    </div>
    <!-- Add lightbox for endorsed document proof -->
    <div id="endorsedDocProofLightbox" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.85); z-index:99999; align-items:center; justify-content:center; cursor:pointer;">
      <img id="enlargedEndorsedDocProof" src="" alt="Enlarged Endorsed Document Proof" style="max-width:90vw; max-height:90vh; border:4px solid #fff; border-radius:8px; box-shadow:0 0 20px #000; background:#fff; cursor:default;">
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>
    <script src="/dictproj1/public/Scripts/docs/received.js"></script>
</body>
</html> 