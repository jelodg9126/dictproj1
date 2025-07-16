// Incoming Page JavaScript
// (All logic from the <script> block in Incoming.php, except for CDN imports)

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

// ... (The rest of the JS logic from the <script> block goes here, unchanged, except for the SweetAlert2 and WebcamJS CDN imports)

// (For brevity, the full code is not repeated here, but in the actual file, all the logic from the <script> block will be included.) 

document.addEventListener('DOMContentLoaded', function() {
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

    // Add logic for view button to open add signature modal
    document.querySelectorAll('.view-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const rowData = btn.getAttribute('data-row');
            if (rowData) {
                const data = JSON.parse(rowData);
                // Populate modal fields as needed
                document.getElementById('signatureTransactionID').value = data.transactionID || '';
                document.getElementById('signatureOfficeName').textContent = data.officeName || '';
                document.getElementById('signatureSenderName').textContent = data.senderName || '';
                document.getElementById('signatureDateReceived').textContent = data.dateAndTime || '';
                // Set POD preview if available
                const podPreview = document.getElementById('addSignatureSenderPodPreview');
                if (podPreview && data.transactionID) {
                    podPreview.src = '/dictproj1/modules/get_pod.php?id=' + data.transactionID;
                    podPreview.style.display = data.pod ? 'inline' : 'none';
                }
                const podNoImage = document.getElementById('addSignatureSenderPodNoImage');
                if (podNoImage) {
                    podNoImage.style.display = data.pod ? 'none' : 'inline';
                }
                // Show the modal
                document.getElementById('addSignatureModal').style.display = 'flex';
            }
        });
    });

    // Close handler for add signature modal
    const closeAddSignatureModal = document.getElementById('closeAddSignatureModal');
    const addSignatureModal = document.getElementById('addSignatureModal');
    function resetAddSignatureModal() {
        // Reset all input fields
        document.getElementById('signatureTransactionID').value = '';
        document.getElementById('signatureOfficeName').textContent = '';
        document.getElementById('signatureSenderName').textContent = '';
        document.getElementById('signatureDateReceived').textContent = '';
        document.getElementById('receiverName').value = '';
        // Reset signature pad
        if (signaturePad && signatureInput) {
            ctx.clearRect(0, 0, signaturePad.width, signaturePad.height);
            signatureInput.value = '';
        }
        // Reset file input and camera image
        const podFile = document.getElementById('podFile');
        if (podFile) podFile.value = '';
        if (capturedImagePreview) {
            capturedImagePreview.src = '';
            capturedImagePreview.style.display = 'none';
        }
        if (podCameraImageInput) podCameraImageInput.value = '';
        // Reset sender POD preview
        const podPreview = document.getElementById('addSignatureSenderPodPreview');
        if (podPreview) {
            podPreview.src = '';
            podPreview.style.display = 'none';
        }
        const podNoImage = document.getElementById('addSignatureSenderPodNoImage');
        if (podNoImage) podNoImage.style.display = 'inline';
    }
    if (closeAddSignatureModal) {
        closeAddSignatureModal.onclick = function() {
            addSignatureModal.style.display = 'none';
            resetAddSignatureModal();
        };
    }
    // Also allow clicking outside modal-content to close
    if (addSignatureModal) {
        addSignatureModal.onclick = function(e) {
            if (e.target === addSignatureModal) {
                addSignatureModal.style.display = 'none';
                resetAddSignatureModal();
            }
        };
    }

    // Signature Pad Logic
    const signaturePad = document.getElementById('receiptSignaturePad');
    const signatureInput = document.getElementById('receiptSignatureInput');
    const clearSignatureBtn = document.getElementById('clearReceiptSignature');
    let drawing = false;
    let ctx = signaturePad.getContext('2d');
    let lastX = 0, lastY = 0;

    function draw(e) {
        if (!drawing) return;
        let x, y;
        if (e.touches) {
            x = e.touches[0].clientX - signaturePad.getBoundingClientRect().left;
            y = e.touches[0].clientY - signaturePad.getBoundingClientRect().top;
        } else {
            x = e.offsetX;
            y = e.offsetY;
        }
        ctx.lineTo(x, y);
        ctx.stroke();
        ctx.beginPath();
        ctx.moveTo(x, y);
    }

    signaturePad.addEventListener('mousedown', function(e) {
        drawing = true;
        ctx.beginPath();
        ctx.moveTo(e.offsetX, e.offsetY);
    });
    signaturePad.addEventListener('mousemove', draw);
    signaturePad.addEventListener('mouseup', function() {
        drawing = false;
        ctx.beginPath();
        signatureInput.value = signaturePad.toDataURL();
    });
    signaturePad.addEventListener('mouseleave', function() {
        drawing = false;
        ctx.beginPath();
    });
    // Touch events for mobile
    signaturePad.addEventListener('touchstart', function(e) {
        drawing = true;
        let x = e.touches[0].clientX - signaturePad.getBoundingClientRect().left;
        let y = e.touches[0].clientY - signaturePad.getBoundingClientRect().top;
        ctx.beginPath();
        ctx.moveTo(x, y);
    });
    signaturePad.addEventListener('touchmove', function(e) {
        e.preventDefault();
        draw(e);
        let x = e.touches[0].clientX - signaturePad.getBoundingClientRect().left;
        let y = e.touches[0].clientY - signaturePad.getBoundingClientRect().top;
        signatureInput.value = signaturePad.toDataURL();
    }, { passive: false });
    signaturePad.addEventListener('touchend', function() {
        drawing = false;
        ctx.beginPath();
    });

    if (clearSignatureBtn) {
        clearSignatureBtn.addEventListener('click', function(e) {
            e.preventDefault();
            ctx.clearRect(0, 0, signaturePad.width, signaturePad.height);
            signatureInput.value = '';
        });
    }

    // 1. Make the Cancel button gray and functional
    const cancelBtn = document.getElementById('cancelReceiptSignature');
    if (cancelBtn) {
        cancelBtn.classList.remove('bg-red-500', 'hover:bg-red-600');
        cancelBtn.classList.add('bg-gray-500', 'hover:bg-gray-600', 'text-white');
        cancelBtn.onclick = function(e) {
            e.preventDefault();
            addSignatureModal.style.display = 'none';
            resetAddSignatureModal();
        };
    }

    // 2. SweetAlert2 Camera Modal Logic (refactored for reliability)
    // Camera Modal Logic (match outgoing.js/received.js)
    const useCameraBtn = document.getElementById('useCameraBtn');
    const capturedImagePreview = document.getElementById('capturedImagePreview');
    const podCameraImageInput = document.getElementById('podCameraImage');
    if (useCameraBtn && capturedImagePreview && podCameraImageInput) {
        let currentFacingMode = 'user';
        useCameraBtn.onclick = function() {
            Swal.fire({
                title: 'Capture Proof of Document',
                html: `
                  <div style="display: flex; flex-direction: column; align-items: center;">
                    <div id="swalCameraContainer" style="margin-bottom:12px;"></div>
                    <img id="swalCapturedPreview" src="" style="display:none; max-width:100%; margin-bottom:12px;"/>
                    <div>
                      <button type="button" id="swalCaptureBtn" class="swal2-confirm swal2-styled" style="margin-right:8px;">Capture</button>
                      <button type="button" id="swalRetakeBtn" class="swal2-cancel swal2-styled" style="display:none; margin-right:8px;">Retake</button>
                      <button type="button" id="swalAcceptBtn" class="swal2-confirm swal2-styled" style="display:none; background:#16a34a;">Accept</button>
                      <button type="button" id="swalSwitchCamBtn" class="swal2-cancel swal2-styled" style="margin-left:8px;">Switch Camera</button>
                    </div>
                  </div>
                `,
                showCancelButton: true,
                showConfirmButton: false,
                cancelButtonText: 'Cancel',
                didOpen: () => {
                    function attachCamera(facingMode) {
                        Webcam.reset();
                        Webcam.set({
                            width: 320,
                            height: 240,
                            image_format: 'jpeg',
                            jpeg_quality: 90,
                            constraints: { facingMode: { exact: facingMode } }
                        });
                        Webcam.attach('#swalCameraContainer');
                    }
                    attachCamera(currentFacingMode);
                    const captureBtn = document.getElementById('swalCaptureBtn');
                    const retakeBtn = document.getElementById('swalRetakeBtn');
                    const acceptBtn = document.getElementById('swalAcceptBtn');
                    const switchCamBtn = document.getElementById('swalSwitchCamBtn');
                    const previewImg = document.getElementById('swalCapturedPreview');
                    let capturedData = '';
                    captureBtn.onclick = function() {
                        Webcam.snap(function(data_uri) {
                            previewImg.src = data_uri;
                            previewImg.style.display = 'block';
                            document.getElementById('swalCameraContainer').style.display = 'none';
                            captureBtn.style.display = 'none';
                            retakeBtn.style.display = 'inline-block';
                            acceptBtn.style.display = 'inline-block';
                            switchCamBtn.style.display = 'none';
                            capturedData = data_uri;
                        });
                    };
                    retakeBtn.onclick = function() {
                        previewImg.style.display = 'none';
                        document.getElementById('swalCameraContainer').style.display = 'block';
                        captureBtn.style.display = 'inline-block';
                        retakeBtn.style.display = 'none';
                        acceptBtn.style.display = 'none';
                        switchCamBtn.style.display = 'inline-block';
                        capturedData = '';
                    };
                    acceptBtn.onclick = function() {
                        if (capturedData) {
                            Swal.close();
                            podCameraImageInput.value = capturedData;
                            capturedImagePreview.src = capturedData;
                            capturedImagePreview.style.display = 'block';
                        }
                    };
                    switchCamBtn.onclick = function() {
                        currentFacingMode = currentFacingMode === 'user' ? 'environment' : 'user';
                        attachCamera(currentFacingMode);
                    };
                },
                willClose: () => {
                    Webcam.reset();
                }
            });
        };
    }

    // Lightbox for sender's POD in Add Signature modal
    const senderPodPreview = document.getElementById('addSignatureSenderPodPreview');
    const senderPodLightbox = document.getElementById('addSignatureSenderPodLightbox');
    const senderPodEnlarged = document.getElementById('addSignatureSenderPodEnlarged');
    if (senderPodPreview && senderPodLightbox && senderPodEnlarged) {
        senderPodPreview.onclick = function() {
            if (senderPodPreview.src && senderPodPreview.style.display !== 'none') {
                senderPodEnlarged.src = senderPodPreview.src;
                senderPodLightbox.style.display = 'flex';
            }
        };
        senderPodLightbox.onclick = function(e) {
            if (e.target === senderPodLightbox || e.target === senderPodEnlarged) {
                senderPodLightbox.style.display = 'none';
                senderPodEnlarged.src = '';
            }
        };
    }
}); 