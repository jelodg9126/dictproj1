// Received Page JavaScript

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

// Add at the top of the file
let isSubmitting = false;

// Initialize all event listeners when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeViewButtons();
    initializeEndorseButtons();
    initializeModalCloseHandlers();
    initializeModalContentHandlers();
    initializeSignaturePad();
    initializeEndorseForm();
    initializeLightboxes();
    initializeCameraFunctionality();
    initializeAutoRefreshControls();
    
    // Start auto-refresh timer for the table
    startAutoRefresh();
});

// Initialize view button functionality
function initializeViewButtons() {
    console.log('Initializing view buttons...');
    // Use event delegation for dynamically added elements
    document.addEventListener('click', function(e) {
        console.log('Document click event triggered');
        const viewBtn = e.target.closest('.view-btn');
        console.log('View button found:', viewBtn);
        
        if (!viewBtn) return;
        
        e.preventDefault();
        e.stopPropagation();
        
        try {
            const rowData = viewBtn.getAttribute('data-row');
            console.log('Row data:', rowData);
            const data = rowData ? JSON.parse(rowData) : {};
            
            // Populate modal with data
            console.log('Populating modal with data...');
            populateReceivedDetailsModal(data);
            
            // Show the modal
            const modal = document.getElementById('receivedDetailsModal');
            console.log('Modal element:', modal);
            
            if (modal) {
                console.log('Showing modal...');
                modal.style.display = 'flex';
                // Force reflow to enable transition
                void modal.offsetWidth;
                modal.style.opacity = '1';
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
                console.log('Modal should now be visible');
            } else {
                console.error('Modal element not found!');
            }
        } catch (error) {
            console.error('Error showing modal:', error);
        }
    });
}

// Populate received details modal with data
function populateReceivedDetailsModal(data) {
    document.getElementById('detailsOfficeName').textContent = getOfficeDisplayName(data.officeName) || '';
    document.getElementById('detailsSenderName').textContent = data.senderName || '';
    document.getElementById('detailsDateReceived').textContent = data.dateAndTime || '';
    document.getElementById('detailsReceivedBy').textContent = data.receivedBy || '';
    document.getElementById('detailsDocumentTitle').textContent = data.doctitle
    ? data.doctitle.charAt(0).toUpperCase() + data.doctitle.slice(1)
    : '';
    
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
        // Remove any existing event listeners to prevent duplicates
        btn.removeEventListener('click', btn.endorseClickHandler);
        
        // Create new event handler
        btn.endorseClickHandler = function(e) {
            console.log('Endorse button clicked');
            e.preventDefault();
            e.stopPropagation();
            var transactionID = btn.getAttribute('data-id');
            resetEndorseForm(transactionID);
            const endorseModal = document.getElementById('endorseModal');
            if (endorseModal) {
                console.log('Opening endorse modal');
                endorseModal.style.display = 'flex';
                endorseModal.style.opacity = '1';
                endorseModal.classList.add('show');
                document.body.style.overflow = 'hidden';
                
                // After opening the modal, re-initialize the close handlers
                setTimeout(() => {
                    console.log('Re-initializing close handlers after modal open...');
                    const closeBtn = document.getElementById('closeEndorseModal');
                    const cancelBtn = document.getElementById('cancelEndorse');
                    
                    console.log('Close button after modal open:', closeBtn);
                    console.log('Cancel button after modal open:', cancelBtn);
                    
                    if (closeBtn) {
                        closeBtn.style.pointerEvents = 'auto';
                        closeBtn.style.zIndex = '1001';
                    }
                    
                    if (cancelBtn) {
                        cancelBtn.style.pointerEvents = 'auto';
                        cancelBtn.style.zIndex = '1001';
                    }
                }, 50);
            } else {
                console.error('Endorse modal not found');
            }
        };
        
        // Add the event listener
        btn.addEventListener('click', btn.endorseClickHandler);
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

// Close modal function
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        console.log('Closing modal:', modalId);
        // Immediately hide the modal without transition to prevent reopening
        modal.style.display = 'none';
        modal.style.opacity = '0';
        modal.classList.remove('show');
        document.body.style.overflow = 'auto';
        console.log('Modal closed successfully');
    } else {
        console.error('Modal not found:', modalId);
    }
}

// Initialize modal close handlers
function initializeModalCloseHandlers() {
    console.log('Initializing modal close handlers...');
    
    // Use a more robust approach with setTimeout to ensure DOM is ready
    setTimeout(() => {
        // Add specific event listeners for close and cancel buttons
        const closeEndorseBtn = document.getElementById('closeEndorseModal');
        const cancelEndorseBtn = document.getElementById('cancelEndorse');
        
        console.log('Close button found:', closeEndorseBtn);
        console.log('Cancel button found:', cancelEndorseBtn);
        
        if (closeEndorseBtn) {
            // Remove any existing listeners to prevent duplicates
            closeEndorseBtn.removeEventListener('click', closeEndorseBtn.closeHandler);
            
            closeEndorseBtn.closeHandler = function(e) {
                console.log('Close button clicked');
                e.preventDefault();
                e.stopPropagation();
                closeModal('endorseModal');
            };
            
            closeEndorseBtn.addEventListener('click', closeEndorseBtn.closeHandler);
            console.log('Close button event listener attached');
            
            // Also add a test click to verify the button is clickable
            closeEndorseBtn.style.pointerEvents = 'auto';
            closeEndorseBtn.style.zIndex = '1000';
        } else {
            console.error('Close button not found!');
        }
        
        if (cancelEndorseBtn) {
            // Remove any existing listeners to prevent duplicates
            cancelEndorseBtn.removeEventListener('click', cancelEndorseBtn.cancelHandler);
            
            cancelEndorseBtn.cancelHandler = function(e) {
                console.log('Cancel button clicked');
                e.preventDefault();
                e.stopPropagation();
                closeModal('endorseModal');
            };
            
            cancelEndorseBtn.addEventListener('click', cancelEndorseBtn.cancelHandler);
            console.log('Cancel button event listener attached');
            
            // Also add a test click to verify the button is clickable
            cancelEndorseBtn.style.pointerEvents = 'auto';
            cancelEndorseBtn.style.zIndex = '1000';
        } else {
            console.error('Cancel button not found!');
        }
    }, 100);
    
    // Close modals when clicking outside the modal or on close/cancel buttons
    document.addEventListener('click', function(e) {
        // Close received details modal
        if (e.target.closest('#closeReceivedDetailsModal') || 
            (e.target === document.getElementById('receivedDetailsModal'))) {
            e.preventDefault();
            e.stopPropagation();
            closeModal('receivedDetailsModal');
        }
        
        // Close endorse modal when clicking close button, cancel button, or outside
        const endorseModal = document.getElementById('endorseModal');
        const endorseContent = document.getElementById('endorseModalContent');

        if (endorseModal) {
            // Check if clicking on close or cancel buttons
            if (e.target.closest('#closeEndorseModal') || e.target.closest('#cancelEndorse')) {
                console.log('Endorse modal close triggered by button click:', e.target);
                e.preventDefault();
                e.stopPropagation();
                closeModal('endorseModal');
            }
            // Check if clicking outside the modal content
            else if (!endorseContent.contains(e.target) && endorseModal.contains(e.target)) {
                console.log('Endorse modal close triggered by clicking outside');
                e.preventDefault();
                e.stopPropagation();
                closeModal('endorseModal');
            }
        }
    });
    
    // Add escape key handler
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal.show') || 
                            document.querySelector('.modal[style*="display: flex"]');
            if (openModal) {
                e.preventDefault();
                closeModal(openModal.id);
            }
        }
    });
}

// Prevent event propagation on modal content
function initializeModalContentHandlers() {
    const endorseModalContent = document.getElementById('endorseModalContent');
    if (endorseModalContent) {
        endorseModalContent.addEventListener('click', function(e) {
            e.stopPropagation();
        });
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
        // Declare isSubmitting variable if not already declared
        if (typeof window.isSubmitting === 'undefined') {
            window.isSubmitting = false;
        }
        
        endorseForm.addEventListener('submit', function(e) {
            if (window.isSubmitting) return;
            window.isSubmitting = true;
            e.preventDefault();
            var formData = new FormData(this);
            var transactionID = document.getElementById('endorseTransactionID').value;
            
            var submitBtn = endorseForm.querySelector('button[type="submit"]');
            var originalText = submitBtn.textContent;
            submitBtn.textContent = 'Processing...';
            submitBtn.disabled = true;

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
                    closeModal('endorseModal');
                    
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
                console.error('Error:', error);
                alert('An error occurred while processing your request.');
            })
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                window.isSubmitting = false;
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

// --- AJAX Table Refresh for Received Documents ---
function refreshReceivedTable() {
    fetch('/dictproj1/modules/get_received_documents.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) return;
            const tbody = document.querySelector('table.w-full tbody');
            if (!tbody) return;
            const isAdmin = document.querySelector('.endorse-btn') !== null; // crude check
            const officeDisplayNames = window.officeDisplayNames || {
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
            };
            function getOfficeDisplayName(code) {
                if (!code) return '';
                var lower = code.toLowerCase();
                for (var key in officeDisplayNames) {
                    if (key.toLowerCase() === lower) return officeDisplayNames[key];
                }
                return code;
            }
            let html = '';
            if (data.data.length === 0) {
                html = '<tr><td colspan="9" class="text-center py-4 text-gray-500">No received documents found.</td></tr>';
            } else {
                data.data.forEach(row => {
                    const rowData = {
                        officeName: row.officeName || '',
                        senderName: row.senderName || '',
                        dateAndTime: row.dateAndTime || '',
                        receivedBy: row.receivedBy || '',
                        transactionID: row.transactionID,
                        endorsedToName: row.endorsedToName || '',
                        hasEndorsedSignature: !!row.endorsedToSignature,
                        hasEndorsedDocProof: !!row.endorsedDocProof,
                        doctitle: row.doctitle || ''
                    };
                    html += `<tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${getOfficeDisplayName(row.officeName)}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.doctitle ? escapeHtml(row.doctitle) : '-'}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.senderName ? escapeHtml(row.senderName) : ''}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.modeOfDel ? escapeHtml(row.modeOfDel) : ''}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.courierName ? escapeHtml(row.courierName) : '-'}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.receivedBy ? escapeHtml(row.receivedBy) : '-'}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.status ? escapeHtml(row.status) : '-'}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.dateAndTime ? formatDateTime(row.dateAndTime) : ''}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <a href="#" class="view-btn bg-blue-500 text-white px-3 py-1 rounded" data-id="${row.transactionID}" data-row='${escapeHtml(JSON.stringify(rowData))}'>View</a>
                            ${isAdmin ? `<button class="endorse-btn bg-green-500 text-white px-3 py-1 rounded ml-2" data-id="${row.transactionID}">Endorse</button>` : ''}
                        </td>
                    </tr>`;
                });
            }
            tbody.innerHTML = html;
            // Re-initialize all event listeners
            initializeViewButtons();
            initializeEndorseButtons();
            initializeModalCloseHandlers();
            initializeModalContentHandlers();
            initializeSignaturePad();
            initializeEndorseForm();
            initializeLightboxes();
            initializeCameraFunctionality();
        })
        .catch(error => {
            console.error('Error refreshing table:', error);
            // Don't show error to user for auto-refresh, just log it
        });
}
// Helper to escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
// Helper to format date and time
function formatDateTime(dateTimeString) {
    if (!dateTimeString) return '';
    try {
        const date = new Date(dateTimeString);
        return date.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    } catch (error) {
        return dateTimeString;
    }
}
// Auto-refresh functionality
let autoRefreshInterval = null;
let isAutoRefreshEnabled = true;

function startAutoRefresh() {
    console.log('Starting auto-refresh timer...');
    
    // Clear any existing interval
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
    
    // Set up auto-refresh every 3 seconds
    autoRefreshInterval = setInterval(function() {
        if (isAutoRefreshEnabled) {
            console.log('Auto-refreshing table...');
            showRefreshStatus();
            refreshReceivedTable();
        }
    }, 3000); // 3000 milliseconds = 3 seconds
}

function stopAutoRefresh() {
    console.log('Stopping auto-refresh timer...');
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
    }
}

function toggleAutoRefresh() {
    isAutoRefreshEnabled = !isAutoRefreshEnabled;
    
    const toggleBtn = document.getElementById('autoRefreshToggle');
    const toggleText = document.getElementById('autoRefreshText');
    
    if (isAutoRefreshEnabled) {
        toggleText.textContent = 'Auto Refresh: ON';
        toggleBtn.classList.remove('bg-red-500', 'hover:bg-red-600');
        toggleBtn.classList.add('bg-blue-500', 'hover:bg-blue-600');
        console.log('Auto-refresh enabled');
    } else {
        toggleText.textContent = 'Auto Refresh: OFF';
        toggleBtn.classList.remove('bg-blue-500', 'hover:bg-blue-600');
        toggleBtn.classList.add('bg-red-500', 'hover:bg-red-600');
        console.log('Auto-refresh disabled');
    }
}

function showRefreshStatus() {
    const statusElement = document.getElementById('refreshStatus');
    if (statusElement) {
        statusElement.classList.remove('hidden');
        
        // Hide the status after 2 seconds
        setTimeout(() => {
            statusElement.classList.add('hidden');
        }, 2000);
    }
}

function initializeAutoRefreshControls() {
    const toggleBtn = document.getElementById('autoRefreshToggle');
    const manualRefreshBtn = document.getElementById('manualRefreshBtn');
    
    if (toggleBtn) {
        toggleBtn.addEventListener('click', toggleAutoRefresh);
    }
    
    if (manualRefreshBtn) {
        manualRefreshBtn.addEventListener('click', function() {
            console.log('Manual refresh triggered');
            showRefreshStatus();
            refreshReceivedTable();
        });
    }
}

// Expose globally for use from other modules if needed
window.refreshReceivedTable = refreshReceivedTable;
window.startAutoRefresh = startAutoRefresh;
window.stopAutoRefresh = stopAutoRefresh;

// Test function to verify modal close functionality
window.testModalClose = function() {
    console.log('Testing modal close functionality...');
    
    const closeBtn = document.getElementById('closeEndorseModal');
    const cancelBtn = document.getElementById('cancelEndorse');
    const endorseModal = document.getElementById('endorseModal');
    
    console.log('Close button:', closeBtn);
    console.log('Cancel button:', cancelBtn);
    console.log('Endorse modal:', endorseModal);
    
    if (closeBtn) {
        console.log('Close button clickable:', closeBtn.style.pointerEvents);
        console.log('Close button z-index:', closeBtn.style.zIndex);
    }
    
    if (cancelBtn) {
        console.log('Cancel button clickable:', cancelBtn.style.pointerEvents);
        console.log('Cancel button z-index:', cancelBtn.style.zIndex);
    }
    
    // Try to trigger a click programmatically
    if (closeBtn) {
        console.log('Attempting to trigger close button click...');
        closeBtn.click();
    }
}; 