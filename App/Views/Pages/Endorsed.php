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
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['officeName'] ?? ''); ?></td>
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
                <h2>Document Details</h2>
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
                document.getElementById('detailsDocumentTitle').value = data.doctitle || '';
                var transactionID = data.transactionID;
                document.getElementById('detailsSignature').src = '/dictproj1/modules/get_signature.php?id=' + transactionID;
                document.getElementById('detailsPod').src = '/dictproj1/modules/get_pod.php?id=' + transactionID;
                document.getElementById('detailsEndorsedToName').textContent = data.endorsedToName || '';
                // Format and display endorsement timestamp
                var endorsementTimestamp = data.endorsementTimestamp || '';
                if (endorsementTimestamp) {
                    var date = new Date(endorsementTimestamp);
                    var formattedDate = date.toLocaleString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    });
                    document.getElementById('detailsEndorsementTimestamp').textContent = formattedDate;
                } else {
                    document.getElementById('detailsEndorsementTimestamp').textContent = 'Not available';
                }
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
        
        // Signature lightbox functionality
        var signatureImg = document.getElementById('detailsSignature');
        if (signatureImg) {
            signatureImg.onclick = function() {
                if (!signatureImg.src || signatureImg.style.display === 'none') return;
                var enlarged = document.getElementById('enlargedSignature');
                enlarged.src = signatureImg.src;
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
        
        // POD lightbox functionality
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
        
        // Endorsed Signature lightbox functionality
        var endorsedSignatureImg = document.getElementById('detailsEndorsedSignature');
        if (endorsedSignatureImg) {
            endorsedSignatureImg.onclick = function() {
                if (!endorsedSignatureImg.src || endorsedSignatureImg.style.display === 'none') return;
                var enlarged = document.getElementById('enlargedEndorsedSignature');
                enlarged.src = endorsedSignatureImg.src;
                var lightbox = document.getElementById('endorsedSignatureLightbox');
                lightbox.style.display = 'flex';
                lightbox.style.opacity = 0;
                setTimeout(() => { lightbox.style.opacity = 1; }, 10);
            };
        }
        var endorsedSignatureLightbox = document.getElementById('endorsedSignatureLightbox');
        if (endorsedSignatureLightbox) {
            endorsedSignatureLightbox.onclick = function(e) {
                if (e.target === this) {
                    this.style.display = 'none';
                    document.getElementById('enlargedEndorsedSignature').src = '';
                }
            };
        }
        var enlargedEndorsedSignature = document.getElementById('enlargedEndorsedSignature');
        if (enlargedEndorsedSignature) {
            enlargedEndorsedSignature.onclick = function(e) {
                e.stopPropagation();
            };
        }
        
        // Endorsed Document Proof lightbox functionality
        var endorsedDocProofImg = document.getElementById('detailsEndorsedDocProof');
        if (endorsedDocProofImg) {
            endorsedDocProofImg.onclick = function() {
                if (!endorsedDocProofImg.src || endorsedDocProofImg.style.display === 'none') return;
                var enlarged = document.getElementById('enlargedEndorsedDocProof');
                enlarged.src = endorsedDocProofImg.src;
                var lightbox = document.getElementById('endorsedDocProofLightbox');
                lightbox.style.display = 'flex';
                lightbox.style.opacity = 0;
                setTimeout(() => { lightbox.style.opacity = 1; }, 10);
            };
        }
        var endorsedDocProofLightbox = document.getElementById('endorsedDocProofLightbox');
        if (endorsedDocProofLightbox) {
            endorsedDocProofLightbox.onclick = function(e) {
                if (e.target === this) {
                    this.style.display = 'none';
                    document.getElementById('enlargedEndorsedDocProof').src = '';
                }
            };
        }
        var enlargedEndorsedDocProof = document.getElementById('enlargedEndorsedDocProof');
        if (enlargedEndorsedDocProof) {
            enlargedEndorsedDocProof.onclick = function(e) {
                e.stopPropagation();
            };
        }
    });
    </script>
</body>
</html>
<?php $conn->close(); ?> 