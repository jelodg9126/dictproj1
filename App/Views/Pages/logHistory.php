<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="/dictproj1/src/input.css">
  
</head>
<body>
     <div class="app-container">
        <?php include __DIR__ . '/../components/Sidebar.php'; ?>
        <div class="flex-1 p-6 min-h-screen overflow-y-auto" id="docu">
            <div class="max-w-7xl mx-auto">
                <div class="flex items-center justify-between mb-6">
                    <div class="items-center">
                        <h1 class="text-3xl font-bold text-indigo-500">Log History</h1>
                        <p class="text-gray-300 mt-2">View all documents that have been endorsed.</p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                                                         <!-- <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Log ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User ID</th> -->
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Office</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Login Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Logout Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Table rows will be filled by JavaScript -->
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
        // --- AUTO-REFRESH LOG HISTORY TABLE EVERY 3 SECONDS ---
        function renderLogRows(data) {
            const tbody = document.querySelector('table.w-full tbody');
            if (!tbody) return;
            tbody.innerHTML = '';
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-gray-500">No log history records found.</td></tr>';
                return;
            }
            data.forEach(function(row) {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.name || '-'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.office || '-'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.login_time ? new Date(row.login_time).toLocaleString() : '-'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.logout_time ? new Date(row.logout_time).toLocaleString() : '-'}</td>
                `;
                tbody.appendChild(tr);
            });
        }

        function fetchLogHistory() {
            fetch('/dictproj1/modules/get_user_log_history.php')
                .then(response => response.json())
                .then(json => {
                    renderLogRows(json.data || []);
                })
                .catch(() => {});
        }
        fetchLogHistory();
        setInterval(fetchLogHistory, 3000);

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