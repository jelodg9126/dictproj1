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

// Check user type for conditional display
$isSuperAdmin = isset($_SESSION['userAuthLevel']) && strtolower($_SESSION['userAuthLevel']) === 'superadmin';

// Include database connection
include __DIR__ . '/../../Model/connect.php';

date_default_timezone_set('Asia/Manila');

// Get filter parameters
$search = $_GET['search'] ?? '';
$office_filter = $_GET['office'] ?? '';
$delivery_filter = $_GET['delivery'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build the SQL query with filters - only received documents for the logged-in user
$sql = "SELECT * FROM maindoc WHERE status = 'Received'";
$params = [];
$types = "";

// Add session-based filtering for receiving office (addressTo)
if (isset($_SESSION['uNameLogin'])) {
    $username = strtolower($_SESSION['uNameLogin']);
    // Filter where addressTo (without first character, lowercased) matches username
    $sql .= " AND LOWER(SUBSTRING(addressTo, 2)) = ?";
    $params[] = $username;
    $types .= "s";
}

// (Optional) Keep endorsement fields filter if needed
$sql .= " AND (endorsedToName IS NULL OR endorsedToName = '' OR endorsedToSignature IS NULL OR endorsedToSignature = '' OR endorsedDocProof IS NULL OR endorsedDocProof = '')";

if (!empty($search)) {
    $sql .= " AND (officeName LIKE ? OR senderName LIKE ? OR emailAdd LIKE ? OR courierName LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ssss";
}

if (!empty($office_filter)) {
    $sql .= " AND officeName = ?";
    $params[] = $office_filter;
    $types .= "s";
}

if (!empty($delivery_filter)) {
    $sql .= " AND modeOfDel = ?";
    $params[] = $delivery_filter;
    $types .= "s";
}

if (!empty($date_from)) {
    $sql .= " AND DATE(dateAndTime) >= ?";
    $params[] = $date_from;
    $types .= "s";
}

if (!empty($date_to)) {
    $sql .= " AND DATE(dateAndTime) <= ?";
    $params[] = $date_to;
    $types .= "s";
}

$sql .= " ORDER BY dateAndTime DESC";

// Pagination setup
$page = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

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

$offices_sql = "SELECT DISTINCT officeName FROM maindoc WHERE filetype = 'incoming' AND status = 'Received'" . $user_office_filter . " ORDER BY officeName";
$offices_result = $conn->query($offices_sql);
$offices = [];
if ($offices_result) {
    while ($row = $offices_result->fetch_assoc()) {
        $offices[] = $row['officeName'];
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="/dictproj1/src/input.css">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/dashboard.css">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/modal.css">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/style.css">
    <title>Received Documents</title>
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
                </div>
                <!-- Table -->
                <div class="bg-gray-200 backdrop-blur rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Office</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sender Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delivery Mode</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Courier Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received By</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo getOfficeDisplayNamePHP($row['officeName'], $officeDisplayNames); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['senderName'] ?? ''); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['emailAdd'] ?? ''); ?></td>
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
                                                ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>'>View</a>
                                                <?php if ($isSuperAdmin): ?>
                                                    <button class="endorse-btn bg-green-500 text-white px-3 py-1 rounded ml-2" data-id="<?php echo $row['transactionID']; ?>">Endorse</button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
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
                <h2>Received Document Details</h2>
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
                <?php if ($isSuperAdmin): ?>
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
                e.preventDefault();
                var rowData = btn.getAttribute('data-row');
                var data = rowData ? JSON.parse(rowData) : {};
                document.getElementById('detailsOfficeName').textContent = getOfficeDisplayName(data.officeName) || '';
                document.getElementById('detailsSenderName').textContent = data.senderName || '';
                document.getElementById('detailsDateReceived').textContent = data.dateAndTime || '';
                document.getElementById('detailsReceivedBy').textContent = data.receivedBy || '';
                var transactionID = data.transactionID;
                document.getElementById('detailsSignature').src = '/dictproj1/modules/get_signature.php?id=' + transactionID;
                document.getElementById('detailsPod').src = '/dictproj1/modules/get_pod.php?id=' + transactionID;
                var receiverPodImg = document.getElementById('detailsReceiverPod');
                if (data.transactionID) {
                    receiverPodImg.src = '/dictproj1/modules/get_receiver_pod.php?id=' + data.transactionID;
                    receiverPodImg.style.display = 'inline';
                    receiverPodImg.onerror = function() {
                        receiverPodImg.style.display = 'none';
                    };
                } else {
                    receiverPodImg.src = '';
                    receiverPodImg.style.display = 'none';
                }
                // Endorsement fields (only if elements exist)
                var endorsedToName = document.getElementById('detailsEndorsedToName');
                if (endorsedToName) {
                    endorsedToName.textContent = data.endorsedToName || '';
                }
                var endorsedSignature = document.getElementById('detailsEndorsedSignature');
                if (endorsedSignature) {
                    if (data.hasEndorsedSignature) {
                        endorsedSignature.src = '/dictproj1/modules/get_endorsed_signature.php?id=' + transactionID;
                    } else {
                        endorsedSignature.src = '';
                    }
                }
                var endorsedDocProof = document.getElementById('detailsEndorsedDocProof');
                if (endorsedDocProof) {
                    if (data.hasEndorsedDocProof) {
                        endorsedDocProof.src = '/dictproj1/modules/get_endorsed_doc_proof.php?id=' + transactionID;
                    } else {
                        endorsedDocProof.src = '';
                    }
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
        // Endorse button logic
        document.querySelectorAll('.endorse-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var transactionID = btn.getAttribute('data-id');
                document.getElementById('endorseTransactionID').value = transactionID;
                document.getElementById('endorsedToName').value = '';
                document.getElementById('endorseSignatureInput').value = '';
                var canvas = document.getElementById('endorseSignaturePad');
                var ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                document.getElementById('endorseDocProof').value = '';
                document.getElementById('endorseModal').style.display = 'flex';
            });
        });
        document.getElementById('closeEndorseModal').onclick = function() {
            document.getElementById('endorseModal').style.display = 'none';
        };
        document.getElementById('cancelEndorse').onclick = function() {
            document.getElementById('endorseModal').style.display = 'none';
        };
        document.getElementById('endorseModal').onclick = function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        };
        // Signature pad logic
        var endorseCanvas = document.getElementById('endorseSignaturePad');
        var endorseSignatureInput = document.getElementById('endorseSignatureInput');
        var clearEndorseBtn = document.getElementById('clearEndorseSignature');
        if (endorseCanvas && endorseSignatureInput) {
            var endorseCtx = endorseCanvas.getContext('2d');
            let drawing = false;
            endorseCanvas.addEventListener('mousedown', function(e) {
                drawing = true;
                endorseCtx.beginPath();
                endorseCtx.moveTo(e.offsetX, e.offsetY);
            });
            endorseCanvas.addEventListener('mousemove', function(e) {
                if (!drawing) return;
                endorseCtx.lineWidth = 2;
                endorseCtx.lineCap = 'round';
                endorseCtx.strokeStyle = '#222';
                endorseCtx.lineTo(e.offsetX, e.offsetY);
                endorseCtx.stroke();
                endorseCtx.beginPath();
                endorseCtx.moveTo(e.offsetX, e.offsetY);
            });
            endorseCanvas.addEventListener('mouseup', function() {
                drawing = false;
                endorseCtx.beginPath();
                captureEndorseSignature();
            });
            endorseCanvas.addEventListener('mouseout', function() {
                drawing = false;
                endorseCtx.beginPath();
            });
            // Touch events for mobile
            endorseCanvas.addEventListener('touchstart', function(e) {
                drawing = true;
                endorseCtx.beginPath();
                endorseCtx.moveTo(e.touches[0].clientX - endorseCanvas.getBoundingClientRect().left, e.touches[0].clientY - endorseCanvas.getBoundingClientRect().top);
            });
            endorseCanvas.addEventListener('touchmove', function(e) {
                if (!drawing) return;
                var x = e.touches[0].clientX - endorseCanvas.getBoundingClientRect().left;
                var y = e.touches[0].clientY - endorseCanvas.getBoundingClientRect().top;
                endorseCtx.lineTo(x, y);
                endorseCtx.stroke();
                endorseCtx.beginPath();
                endorseCtx.moveTo(x, y);
            });
            endorseCanvas.addEventListener('touchend', function() {
                drawing = false;
                endorseCtx.beginPath();
                captureEndorseSignature();
            });
            if (clearEndorseBtn) {
                clearEndorseBtn.addEventListener('click', function() {
                    endorseCtx.clearRect(0, 0, endorseCanvas.width, endorseCanvas.height);
                    endorseSignatureInput.value = '';
                });
            }
            function captureEndorseSignature() {
                if (endorseCanvas.width > 0 && endorseCanvas.height > 0) {
                    var signatureData = endorseCanvas.toDataURL('image/png');
                    endorseSignatureInput.value = signatureData;
                }
            }
            endorseCanvas.addEventListener('mouseup', captureEndorseSignature);
            endorseCanvas.addEventListener('touchend', captureEndorseSignature);
        }
        // Handle endorse form submission
        document.getElementById('endorseForm').addEventListener('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            var transactionID = document.getElementById('endorseTransactionID').value;
            fetch('/dictproj1/modules/endorse_document.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        confirmButtonColor: '#3085d6',
                        timer: 2000
                    });
                    document.getElementById('endorseModal').style.display = 'none';
                    // Update the data-row attribute for the corresponding row
                    var row = document.querySelector('button.endorse-btn[data-id="' + transactionID + '"]').closest('tr');
                    if (row) {
                        var viewBtn = row.querySelector('.view-btn');
                        if (viewBtn) {
                            var rowData = JSON.parse(viewBtn.getAttribute('data-row'));
                            rowData.endorsedToName = data.endorsedToName;
                            rowData.hasEndorsedSignature = data.hasEndorsedSignature;
                            rowData.hasEndorsedDocProof = data.hasEndorsedDocProof;
                            viewBtn.setAttribute('data-row', JSON.stringify(rowData));
                        }
                    }
                } else {
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
            });
        });
        // Receiver POD lightbox
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
    });
    </script>
</body>
</html> 