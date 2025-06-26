<?php
// Only allow superadmin
if (!isset($_SESSION['userAuthLevel']) || strtolower($_SESSION['userAuthLevel']) !== 'superadmin') {
    header('Location: /dictproj1/App/Views/Pages/Documents.php');
    exit();
}

include __DIR__ . '/../../Model/connect.php';
date_default_timezone_set('Asia/Manila');

// Set current page for sidebar highlighting
$currentPage = 'endorsed';

// Query for endorsed documents
$sql = "SELECT * FROM maindoc WHERE filetype = 'incoming' AND status = 'Received' AND endorsedToName IS NOT NULL AND endorsedToName != '' AND endorsedToSignature IS NOT NULL AND endorsedToSignature != '' AND endorsedDocProof IS NOT NULL AND endorsedDocProof != '' ORDER BY dateAndTime DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="/dictproj1/src/input.css">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/dashboard.css">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/modal.css">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/style.css">
    <title>Endorsed Documents</title>
</head>
<body>
    <div class="app-container">
        <?php include __DIR__ . '/../components/Sidebar.php'; ?>
        <div class="flex-1 p-6 bg-gray-50 min-h-screen overflow-y-auto" id="docu">
            <div class="max-w-7xl mx-auto">
                <div class="flex items-center justify-between mb-6">
                    <div class="items-center">
                        <h1 class="text-3xl font-bold text-blue-800">Endorsed Documents</h1>
                        <p class="text-gray-600 mt-2">View all documents that have been endorsed.</p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Office</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sender Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Endorsed To</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['officeName'] ?? ''); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['senderName'] ?? ''); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['endorsedToName'] ?? ''); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo isset($row['dateAndTime']) ? date('M d, Y h:i A', strtotime($row['dateAndTime'])) : ''; ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <a href="#" class="view-btn bg-blue-500 text-white px-3 py-1 rounded" data-row='<?php echo json_encode([
                                                    "officeName" => $row["officeName"] ?? '',
                                                    "senderName" => $row["senderName"] ?? '',
                                                    "dateAndTime" => $row["dateAndTime"] ?? '',
                                                    "receivedBy" => $row["receivedBy"] ?? '',
                                                    "transactionID" => $row["transactionID"],
                                                    "endorsedToName" => $row["endorsedToName"] ?? '',
                                                    "hasEndorsedSignature" => !empty($row["endorsedToSignature"]),
                                                    "hasEndorsedDocProof" => !empty($row["endorsedDocProof"]),
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
                <h2>Document Details</h2>
                <span class="close" id="closeReceivedDetailsModal" style="cursor:pointer;">&times;</span>
            </div>
            <div class="modal-body">
                <div class="form-section">
                    <h3>Document Information</h3>
                    <div><b>Office:</b> <span id="detailsOfficeName"></span></div>
                    <div><b>Sender:</b> <span id="detailsSenderName"></span></div>
                    <div><b>Date Received:</b> <span id="detailsDateReceived"></span></div>
                </div>
                <div class="form-section">
                    <h3>Receipt Information</h3>
                    <div><b>Received By:</b> <span id="detailsReceivedBy"></span></div>
                    <div><b>Signature:</b><br>
                        <img id="detailsSignature" src="" alt="Signature" style="max-width:200px; max-height:100px; border:1px solid #ccc; background:#f9f9f9;">
                    </div>
                    <div><b>Proof of Document (POD):</b><br>
                        <img id="detailsPod" src="" alt="Proof of Document" style="max-width:200px; max-height:200px; border:1px solid #ccc; background:#f9f9f9;">
                    </div>
                </div>
                <div class="form-section">
                    <h3>Endorsement Information</h3>
                    <div><b>Endorsed To Name:</b> <span id="detailsEndorsedToName"></span></div>
                    <div><b>Endorsed To Signature:</b><br>
                        <img id="detailsEndorsedSignature" src="" alt="Endorsed Signature" style="max-width:200px; max-height:100px; border:1px solid #ccc; background:#f9f9f9;">
                    </div>
                    <div><b>Endorsed Document Proof:</b><br>
                        <img id="detailsEndorsedDocProof" src="" alt="Endorsed Proof" style="max-width:200px; max-height:200px; border:1px solid #ccc; background:#f9f9f9;">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.view-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var rowData = btn.getAttribute('data-row');
                var data = rowData ? JSON.parse(rowData) : {};
                document.getElementById('detailsOfficeName').textContent = data.officeName || '';
                document.getElementById('detailsSenderName').textContent = data.senderName || '';
                document.getElementById('detailsDateReceived').textContent = data.dateAndTime || '';
                document.getElementById('detailsReceivedBy').textContent = data.receivedBy || '';
                var transactionID = data.transactionID;
                document.getElementById('detailsSignature').src = '/dictproj1/modules/get_signature.php?id=' + transactionID;
                document.getElementById('detailsPod').src = '/dictproj1/modules/get_pod.php?id=' + transactionID;
                document.getElementById('detailsEndorsedToName').textContent = data.endorsedToName || '';
                if (data.hasEndorsedSignature) {
                    document.getElementById('detailsEndorsedSignature').src = '/dictproj1/modules/get_endorsed_signature.php?id=' + transactionID;
                } else {
                    document.getElementById('detailsEndorsedSignature').src = '';
                }
                if (data.hasEndorsedDocProof) {
                    document.getElementById('detailsEndorsedDocProof').src = '/dictproj1/modules/get_endorsed_doc_proof.php?id=' + transactionID;
                } else {
                    document.getElementById('detailsEndorsedDocProof').src = '';
                }
                document.getElementById('receivedDetailsModal').style.display = 'flex';
            });
        });
        document.getElementById('closeReceivedDetailsModal').onclick = function() {
            document.getElementById('receivedDetailsModal').style.display = 'none';
        };
        document.getElementById('receivedDetailsModal').onclick = function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        };
    });
    </script>
</body>
</html>
<?php $conn->close(); ?> 