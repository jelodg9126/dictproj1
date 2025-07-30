<?php
// Only allow admin
if (!isset($_SESSION['userAuthLevel']) || strtolower($_SESSION['userAuthLevel']) !== 'admin') {
    header('Location: /dictproj1/App/Views/Pages/Documents.php');
    exit();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['userAuthLevel']) && strtolower($_SESSION['userAuthLevel']) === 'superadmin') {
    header('Location: Documents.php');
    exit();
}

include __DIR__ . '/../../Model/connect.php';
date_default_timezone_set('Asia/Manila');

// Set current page for sidebar highlighting
$currentPage = 'endorsed';

// Query for endorsed documents
$sql = "SELECT * FROM maindoc WHERE filetype = 'outgoing' AND status = 'Endorsed' AND endorsedToName IS NOT NULL AND endorsedToName != '' AND endorsedToSignature IS NOT NULL AND endorsedToSignature != '' AND endorsedDocProof IS NOT NULL AND endorsedDocProof != '' ORDER BY dateAndTime DESC";
$result = $conn->query($sql);


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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="/dictproj1/public/Scripts/pwa-init.js"></script>
    <link rel="manifest" href="/dictproj1/manifest.json">
    <link rel="stylesheet" href="/dictproj1/src/input.css">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/dashboard.css">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/modal.css">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/style.css">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/endorsed.css">
    <title>Endorsed Documents</title>
</head>
<body>
    <div class="app-container">
        <?php include __DIR__ . '/../components/Sidebar.php'; ?>
        <div class="flex-1 p-6 min-h-screen overflow-y-auto" id="docu">
            <div class="max-w-7xl mx-auto">
                <div class="flex items-center justify-between mb-6">
                    <div class="items-center">
                        <h1 class="text-3xl font-bold text-indigo-500">Endorsed Documents</h1>
                        <p class="text-gray-300 mt-2">View all documents that have been endorsed.</p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Office</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Document Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sender Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Endorsed To</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Endorsed Date & Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars(getOfficeDisplayNamePHP($row['officeName'], $officeDisplayNames)); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['doctitle'] ?? '-'); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['senderName'] ?? ''); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['endorsedToName'] ?? ''); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo !empty($row['endorsementTimestamp']) ? date('M d, Y g:i A', strtotime($row['endorsementTimestamp'])) : '-'; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <a href="#" class="view-btn bg-blue-500 text-white px-3 py-1 rounded" data-row='<?php echo json_encode([
                                                    "officeName" => $row["officeName"] ?? '',
                                                    "senderName" => $row["senderName"] ?? '',
                                                    "dateAndTime" => $row["dateAndTime"] ?? '',
                                                    "receivedBy" => $row["receivedBy"] ?? '',
                                                    "transactionID" => $row["transactionID"],
                                                    "endorsedToName" => $row["endorsedToName"] ?? '',
                                                    "endorsementTimestamp" => isset($row["endorsementTimestamp"]) && $row["endorsementTimestamp"] ? (string)$row["endorsementTimestamp"] : '',
                                                    "hasEndorsedSignature" => !empty($row["endorsedToSignature"]),
                                                    "hasEndorsedDocProof" => !empty($row["endorsedDocProof"]),
                                                    "doctitle" => $row["doctitle"] ?? '',
                                                ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>'>View</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center py-4 text-gray-500">No endorsed documents found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Details Modal for Endorsed Documents (identical to Received) -->
    <div id="receivedDetailsModal" class="modal" style="display:none;">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2 class="text-black">Document Details</h2>
                <span class="close" id="closeReceivedDetailsModal" style="cursor:pointer;">&times;</span>
            </div>
            <div class="modal-body">
                <div class="form-section">
                    <div><b>Document Title:</b> <span id="detailsDocumentTitle"></span></div>
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
                    <div><b>Proof of Document (POD):</b><br>
                        <img id="detailsPod" src="" alt="Proof of Document" style="max-width:200px; max-height:200px; border:1px solid #ccc; background:#f9f9f9; cursor:pointer;">
                    </div>
                </div>
                <div class="form-section">
                    <h3>Endorsement Information</h3>
                    <div><b>Endorsed To Name:</b> <span id="detailsEndorsedToName"></span></div>
                    <div><b>Endorsement Date & Time:</b> <span id="detailsEndorsementTimestamp"></span></div>
                    <div><b>Endorsed To Signature:</b><br>
                        <img id="detailsEndorsedSignature" src="" alt="Endorsed Signature" style="max-width:200px; max-height:100px; border:1px solid #ccc; background:#f9f9f9; cursor:pointer;">
                    </div>
                    <div><b>Endorsed Document Proof:</b><br>
                        <img id="detailsEndorsedDocProof" src="" alt="Endorsed Proof" style="max-width:200px; max-height:200px; border:1px solid #ccc; background:#f9f9f9; cursor:pointer;">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Add lightbox for signature -->
    <div id="signatureLightbox" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.85); z-index:99999; align-items:center; justify-content:center; cursor:pointer;">
      <img id="enlargedSignature" src="" alt="Enlarged Signature" style="max-width:90vw; max-height:90vh; border:4px solid #fff; border-radius:8px; box-shadow:0 0 20px #000; background:#fff; cursor:default;">
    </div>
    <!-- Add lightbox for POD -->
    <div id="podLightbox" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.85); z-index:99999; align-items:center; justify-content:center; cursor:pointer;">
      <img id="enlargedPod" src="" alt="Enlarged POD" style="max-width:90vw; max-height:90vh; border:4px solid #fff; border-radius:8px; box-shadow:0 0 20px #000; background:#fff; cursor:default;">
    </div>
    <!-- Add lightbox for endorsed signature -->
    <div id="endorsedSignatureLightbox" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.85); z-index:99999; align-items:center; justify-content:center; cursor:pointer;">
      <img id="enlargedEndorsedSignature" src="" alt="Enlarged Endorsed Signature" style="max-width:90vw; max-height:90vh; border:4px solid #fff; border-radius:8px; box-shadow:0 0 20px #000; background:#fff; cursor:default;">
    </div>
    <!-- Add lightbox for endorsed document proof -->
    <div id="endorsedDocProofLightbox" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.85); z-index:99999; align-items:center; justify-content:center; cursor:pointer;">
      <img id="enlargedEndorsedDocProof" src="" alt="Enlarged Endorsed Document Proof" style="max-width:90vw; max-height:90vh; border:4px solid #fff; border-radius:8px; box-shadow:0 0 20px #000; background:#fff; cursor:default;">
    </div>
    <script src="/dictproj1/public/Scripts/docs/endorsed.js"></script>
</body>
</html>
<?php $conn->close(); ?> 