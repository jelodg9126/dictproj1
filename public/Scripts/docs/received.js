// Received Page JavaScript
console.log("ACTIVE RECEIVED.JS!");
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

// Initialize all event listeners when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeViewButtons();
    initializeEndorseButtons();
    initializeModalCloseHandlers();
    initializeSignaturePad();
    initializeEndorseForm();
    initializeLightboxes();
    initializeCameraFunctionality();
    // Add filter toggle functionality
    const filterToggle = document.getElementById('filterToggle');
    const filterSection = document.getElementById('filterSection');
    const filterToggleText = document.getElementById('filterToggleText');
    if (filterToggle && filterSection) {
        filterToggle.addEventListener('click', function() {
            if (filterSection.style.display === 'none' || filterSection.style.display === '') {
                filterSection.style.display = 'block';
                if (filterToggleText) filterToggleText.textContent = 'Hide Filters';
            } else {
                filterSection.style.display = 'none';
                if (filterToggleText) filterToggleText.textContent = 'Show Filters';
            }
        });
    }
});

// Initialize view button functionality
function initializeViewButtons() {
    document.querySelectorAll('.view-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var rowData = btn.getAttribute('data-row');
            var data = rowData ? JSON.parse(rowData) : {};
            
            populateReceivedDetailsModal(data);
            document.getElementById('receivedDetailsModal').style.display = 'flex';
        });
    });
}

// Populate received details modal with data
function populateReceivedDetailsModal(data) {
    document.getElementById('detailsOfficeName').textContent = getOfficeDisplayName(data.officeName) || '';
    document.getElementById('detailsSenderName').textContent = data.senderName || '';
    document.getElementById('detailsDateReceived').textContent = data.dateAndTime || '';
    document.getElementById('detailsReceivedBy').textContent = data.receivedBy || '';
    document.getElementById('detailsDocumentTitle').value = data.doctitle || '';
    
    var transactionID = data.transactionID;
    document.getElementById('detailsSignature').src = '/dictproj1/modules/get_signature.php?id=' + transactionID;
    document.getElementById('detailsPod').src = '/dictproj1/modules/get_pod.php?id=' + transactionID;
    
    // Handle receiver POD
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
    
    // Handle endorsement fields (only if elements exist)
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
}

// Initialize endorse button functionality
function initializeEndorseButtons() {
    document.querySelectorAll('.endorse-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var transactionID = btn.getAttribute('data-id');
            resetEndorseForm(transactionID);
            document.getElementById('endorseModal').style.display = 'flex';
        });
    });
}

// Reset endorse form
function resetEndorseForm(transactionID) {
    document.getElementById('endorseTransactionID').value = transactionID;
    document.getElementById('endorsedToName').value = '';
    document.getElementById('endorseSignatureInput').value = '';
    
    var canvas = document.getElementById('endorseSignaturePad');
    var ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    document.getElementById('endorseDocProof').value = '';
    document.getElementById('endorseCapturedImagePreview').src = '';
    document.getElementById('endorseCapturedImagePreview').style.display = 'none';
    document.getElementById('endorseDocProof').style.display = 'inline-block';
    
    var useEndorseCameraBtn = document.getElementById('useEndorseCameraBtn');
    if (useEndorseCameraBtn) {
        useEndorseCameraBtn.style.display = 'inline-flex';
    }
    
    var removeBtn = document.getElementById('removeEndorseCapturedImageBtn');
    if (removeBtn) {
        removeBtn.style.display = 'none';
    }
}

// Initialize modal close handlers
function initializeModalCloseHandlers() {
    // Received details modal close
    var closeReceivedDetailsModal = document.getElementById('closeReceivedDetailsModal');
    if (closeReceivedDetailsModal) {
        closeReceivedDetailsModal.onclick = function() {
            document.getElementById('receivedDetailsModal').style.display = 'none';
        };
    }
    
    var receivedDetailsModal = document.getElementById('receivedDetailsModal');
    if (receivedDetailsModal) {
        receivedDetailsModal.onclick = function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        };
    }
    
    // Endorse modal close
    var closeEndorseModal = document.getElementById('closeEndorseModal');
    if (closeEndorseModal) {
        closeEndorseModal.onclick = function() {
            document.getElementById('endorseModal').style.display = 'none';
        };
    }
    
    var cancelEndorse = document.getElementById('cancelEndorse');
    if (cancelEndorse) {
        cancelEndorse.onclick = function() {
            document.getElementById('endorseModal').style.display = 'none';
        };
    }
    
    var endorseModal = document.getElementById('endorseModal');
    if (endorseModal) {
        endorseModal.onclick = function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        };
    }
}

// Initialize signature pad functionality
function initializeSignaturePad() {
    var endorseCanvas = document.getElementById('endorseSignaturePad');
    var endorseSignatureInput = document.getElementById('endorseSignatureInput');
    var clearEndorseBtn = document.getElementById('clearEndorseSignature');
    
    if (endorseCanvas && endorseSignatureInput) {
        var endorseCtx = endorseCanvas.getContext('2d');
        let drawing = false;
        
        // Mouse events
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
        
        // Clear signature button
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
}

// Initialize endorse form submission
function initializeEndorseForm() {
    var endorseForm = document.getElementById('endorseForm');
    if (endorseForm) {
        endorseForm.addEventListener('submit', function(e) {
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
                    localStorage.setItem('auditlog-refresh', Date.now());
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
                console.log('Error:', error);
                alert('An error occurred while processing your request.');
            });
        });
    }
}

// Initialize lightboxes
function initializeLightboxes() {
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
    
    var receiverPodLightbox = document.getElementById('receiverPodLightbox');
    if (receiverPodLightbox) {
        receiverPodLightbox.onclick = function(e) {
            if (e.target === this) {
                this.style.display = 'none';
                document.getElementById('enlargedReceiverPod').src = '';
            }
        };
    }
    
    var enlargedReceiverPod = document.getElementById('enlargedReceiverPod');
    if (enlargedReceiverPod) {
        enlargedReceiverPod.onclick = function(e) {
            e.stopPropagation();
        };
    }
    
    // Sender POD lightbox
    var senderPodImg = document.getElementById('detailsPod');
    if (senderPodImg) {
        senderPodImg.onclick = function() {
            if (!senderPodImg.src || senderPodImg.style.display === 'none') return;
            var enlarged = document.getElementById('enlargedSenderPod');
            enlarged.src = senderPodImg.src;
            var lightbox = document.getElementById('senderPodLightbox');
            lightbox.style.display = 'flex';
            lightbox.style.opacity = 0;
            setTimeout(() => { lightbox.style.opacity = 1; }, 10);
        };
    }
    
    var senderPodLightbox = document.getElementById('senderPodLightbox');
    if (senderPodLightbox) {
        senderPodLightbox.onclick = function(e) {
            if (e.target === this) {
                this.style.display = 'none';
                document.getElementById('enlargedSenderPod').src = '';
            }
        };
    }
    
    var enlargedSenderPod = document.getElementById('enlargedSenderPod');
    if (enlargedSenderPod) {
        enlargedSenderPod.onclick = function(e) {
            e.stopPropagation();
        };
    }
    
    // Signature lightbox
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
    
    // Endorsed Signature lightbox
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
    
    // Endorsed Document Proof lightbox
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
}

// Initialize camera functionality
function initializeCameraFunctionality() {
    var useEndorseCameraBtn = document.getElementById('useEndorseCameraBtn');
    if (useEndorseCameraBtn) {
        useEndorseCameraBtn.onclick = function() {
            Swal.fire({
                title: 'Capture Endorsed Document Proof',
                html: `
                  <div style="display: flex; flex-direction: column; align-items: center;">
                    <div id="swalEndorseCamera" style="margin-bottom:12px;"></div>
                    <img id="swalEndorseCapturedPreview" src="" style="display:none; max-width:100%; margin-bottom:12px;"/>
                    <div>
                      <button type="button" id="swalEndorseCaptureBtn" class="swal2-confirm swal2-styled" style="margin-right:8px;">Capture</button>
                      <button type="button" id="swalEndorseRetakeBtn" class="swal2-cancel swal2-styled" style="display:none; margin-right:8px;">Retake</button>
                      <button type="button" id="swalEndorseAcceptBtn" class="swal2-confirm swal2-styled" style="display:none; background:#16a34a;">Accept</button>
                    </div>
                  </div>
                `,
                showCancelButton: true,
                showConfirmButton: false,
                cancelButtonText: 'Cancel',
                didOpen: () => {
                    Webcam.set({
                        width: 320,
                        height: 240,
                        image_format: 'jpeg',
                        jpeg_quality: 90
                    });
                    Webcam.attach('#swalEndorseCamera');
                    
                    const captureBtn = document.getElementById('swalEndorseCaptureBtn');
                    const retakeBtn = document.getElementById('swalEndorseRetakeBtn');
                    const acceptBtn = document.getElementById('swalEndorseAcceptBtn');
                    const previewImg = document.getElementById('swalEndorseCapturedPreview');
                    let capturedData = '';
                    
                    captureBtn.onclick = function() {
                        Webcam.snap(function(data_uri) {
                            previewImg.src = data_uri;
                            previewImg.style.display = 'block';
                            document.getElementById('swalEndorseCamera').style.display = 'none';
                            captureBtn.style.display = 'none';
                            retakeBtn.style.display = 'inline-block';
                            acceptBtn.style.display = 'inline-block';
                            capturedData = data_uri;
                        });
                    };
                    
                    retakeBtn.onclick = function() {
                        previewImg.style.display = 'none';
                        document.getElementById('swalEndorseCamera').style.display = 'block';
                        captureBtn.style.display = 'inline-block';
                        retakeBtn.style.display = 'none';
                        acceptBtn.style.display = 'none';
                        capturedData = '';
                    };
                    
                    acceptBtn.onclick = function() {
                        if (capturedData) {
                            Swal.close();
                            document.getElementById('endorseCameraImage').value = capturedData;
                            document.getElementById('endorseCapturedImagePreview').src = capturedData;
                            document.getElementById('endorseCapturedImagePreview').style.display = 'block';
                            document.getElementById('endorseDocProof').style.display = 'none';
                            useEndorseCameraBtn.style.display = 'none';
                            
                            let removeBtn = document.getElementById('removeEndorseCapturedImageBtn');
                            if (!removeBtn) {
                                removeBtn = document.createElement('button');
                                removeBtn.type = 'button';
                                removeBtn.id = 'removeEndorseCapturedImageBtn';
                                removeBtn.className = 'btn btn-secondary';
                                removeBtn.style.marginLeft = '10px';
                                removeBtn.textContent = 'Remove';
                                document.getElementById('endorseCapturedImagePreview').after(removeBtn);
                            } else {
                                removeBtn.style.display = 'inline-block';
                            }
                            
                            removeBtn.onclick = function() {
                                document.getElementById('endorseCameraImage').value = '';
                                document.getElementById('endorseCapturedImagePreview').src = '';
                                document.getElementById('endorseCapturedImagePreview').style.display = 'none';
                                document.getElementById('endorseDocProof').style.display = 'inline-block';
                                useEndorseCameraBtn.style.display = 'inline-flex';
                                removeBtn.style.display = 'none';
                            };
                        }
                    };
                },
                willClose: () => {
                    Webcam.reset();
                }
            });
        };
    }
} 