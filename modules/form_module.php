<?php
// Form Module - Can be included in any page
// This module provides the document submission form

// Check if this file is being accessed directly
if (!defined('FORM_MODULE_INCLUDED')) {
    define('FORM_MODULE_INCLUDED', true);
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current page to pre-select filetype
$current_page = $_GET['page'] ?? '';
$pre_selected_filetype = '';
$filetype_readonly = false;

if ($current_page === 'incoming') {
    $pre_selected_filetype = 'incoming';
    $filetype_readonly = true;
} elseif ($current_page === 'outgoing') {
    $pre_selected_filetype = 'outgoing';
    $filetype_readonly = true;
}

// Auto-select office based on logged-in user
$pre_selected_office = '';
$office_readonly = false;

if (isset($_SESSION['uNameLogin'])) {
    $username = $_SESSION['uNameLogin'];
    
    // Map username to office value
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
        $pre_selected_office = $username_to_office[$username_lower];
        $office_readonly = true; // Make it readonly since it's auto-selected
    }
}

// Before rendering the Receiving Office dropdown, determine the user's own receiving office code
$exclude_receiving_office = '';
if (isset($pre_selected_office)) {
    $office_to_receiving = [
        'dictBulacan' => 'RdictBulacan',
        'dictPampanga' => 'RdictPampanga',
        'dictAurora' => 'RdictAurora',
        'dictBataan' => 'RdictBataan',
        'dictNE' => 'RdictNE',
        'dictTarlac' => 'RdictTarlac',
        'dictZambales' => 'RdictZambales',
        'maindoc' => 'Rmaindoc',
        'Others' => 'ROthers'
    ];
    if (isset($office_to_receiving[$pre_selected_office])) {
        $exclude_receiving_office = $office_to_receiving[$pre_selected_office];
    }
}
?>

<div class="form-container" id="documentFormContainer">
    <form action="/dictproj1/modules/process_form.php" method="post" id="documentForm" enctype="multipart/form-data">
        <div class="form-section">
            <h3>Office Information</h3>
            <div class="form-group">
                <label for="officeName" class="required">Select Office</label>
                <select name="officeName" id="officeName" required <?php echo $office_readonly ? 'disabled' : ''; ?>>
                    <option value="">-- Select Office --</option>
                    <option value="dictBulacan" <?php echo $pre_selected_office === 'dictBulacan' ? 'selected' : ''; ?>>Provincial Office Bulacan</option>
                    <option value="dictPampanga" <?php echo $pre_selected_office === 'dictPampanga' ? 'selected' : ''; ?>>Provincial Office Pampanga</option>
                    <option value="dictAurora" <?php echo $pre_selected_office === 'dictAurora' ? 'selected' : ''; ?>>Provincial Office Aurora</option>
                    <option value="dictBataan" <?php echo $pre_selected_office === 'dictBataan' ? 'selected' : ''; ?>>Provincial Office Bataan</option>
                    <option value="dictNE" <?php echo $pre_selected_office === 'dictNE' ? 'selected' : ''; ?>>Provincial Office Nueva Ecija</option>
                    <option value="dictTarlac" <?php echo $pre_selected_office === 'dictTarlac' ? 'selected' : ''; ?>>Provincial Office Tarlac</option>
                    <option value="dictZambales" <?php echo $pre_selected_office === 'dictZambales' ? 'selected' : ''; ?>>Provincial Office Zambales</option>
                    <option value="maindoc" <?php echo $pre_selected_office === 'maindoc' ? 'selected' : ''; ?>>DICT Region 3 Office</option>
                    <option value="Others" <?php echo $pre_selected_office === 'Others' ? 'selected' : ''; ?>>Others</option>
                </select>
                <?php if ($office_readonly): ?>
                    <input type="hidden" name="officeName" value="<?php echo htmlspecialchars($pre_selected_office); ?>">
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="filetype" class="required">Document Type</label>
                <select name="filetype" id="filetype" required <?php echo $filetype_readonly ? 'disabled' : ''; ?>>
                    <option value="">-- Select Document Type --</option>
                    <option value="incoming" <?php echo $pre_selected_filetype === 'incoming' ? 'selected' : ''; ?>>Incoming</option>
                    <option value="outgoing" <?php echo $pre_selected_filetype === 'outgoing' ? 'selected' : ''; ?>>Outgoing</option>
                </select>
                <?php if ($filetype_readonly): ?>
                    <input type="hidden" name="filetype" value="<?php echo $pre_selected_filetype; ?>">
                <?php endif; ?>
            </div>
        </div>
        
        <div class="form-section">
            <h3>Sender Information</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="senderName" class="required">Sender Name</label>
                    <input type="text" name="senderName" id="senderName" required placeholder="Enter your full name">
                </div>
                <div class="form-group">
                    <label for="emailAdd" class="required">Email Address</label>
                    <input type="email" name="emailAdd" id="emailAdd" required placeholder="Enter your email">
                </div>
            </div>
        </div>
        
        <div class="form-section">
            <h3>Delivery Information</h3>
            <div class="form-group">
                <label for="addressTo" class="required">Receiving Office</label>
                <select name="addressTo" id="addressTo" required>
                    <?php if ($exclude_receiving_office !== 'RdictBulacan'): ?><option value="RdictBulacan">Provincial Office Bulacan</option><?php endif; ?>
                    <?php if ($exclude_receiving_office !== 'RdictPampanga'): ?><option value="RdictPampanga">Provincial Office Pampanga</option><?php endif; ?>
                    <?php if ($exclude_receiving_office !== 'RdictAurora'): ?><option value="RdictAurora">Provincial Office Aurora</option><?php endif; ?>
                    <?php if ($exclude_receiving_office !== 'RdictBataan'): ?><option value="RdictBataan">Provincial Office Bataan</option><?php endif; ?>
                    <?php if ($exclude_receiving_office !== 'RdictNE'): ?><option value="RdictNE">Provincial Office Nueva Ecija</option><?php endif; ?>
                    <?php if ($exclude_receiving_office !== 'RdictTarlac'): ?><option value="RdictTarlac">Provincial Office Tarlac</option><?php endif; ?>
                    <?php if ($exclude_receiving_office !== 'RdictZambales'): ?><option value="RdictZambales">Provincial Office Zambales</option><?php endif; ?>
                    <?php if ($exclude_receiving_office !== 'Rmaindoc'): ?><option value="Rmaindoc">DICT Region 3 Office</option><?php endif; ?>
                    <?php if ($exclude_receiving_office !== 'ROthers'): ?><option value="ROthers">Others</option><?php endif; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="modeOfDel" class="required">Mode of Delivery</label>
                <select name="modeOfDel" id="modeOfDel" required>
                    <option value="">-- Select Delivery Mode --</option>
                    <option value="Courier">Courier</option>
                    <option value="In-Person">In-Person</option>
                </select>
            </div>
            
            <div class="form-group" id="courierGroup" style="display: none;">
                <label for="courierName">Courier Name</label>
                <input type="text" name="courierName" id="courierName" placeholder="Enter courier company name">
            </div>
        </div>
        
        <div class="form-section">
            <h3>Signature</h3>
            <div class="form-group">
                <label for="signaturePad">Please sign below:</label>
                <br>
                <canvas id="signaturePad" width="350" height="220" style="border:1px solid #ccc; background:#fff;"></canvas>
                <br>
                <button type="button" class="btn btn-secondary" id="clearSignature">Clear Signature</button>
                <input type="hidden" name="signature" id="signatureInput">
            </div>
        </div>
        
        <div class="form-section">
            <h3>Proof of Document (POD)</h3>
            <div class="form-group">
                <label for="podFile">Upload Proof of Document</label>
                <input type="file" name="podFile" id="podFile" accept="image/*,application/pdf">
                <button type="button" id="useCameraBtn" class="btn btn-secondary" style="margin-top:8px; display:inline-flex; align-items:center; gap:6px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A2 2 0 0122 9.618V17a2 2 0 01-2 2H4a2 2 0 01-2-2V9.618a2 2 0 012.447-1.894L9 10m6 0V6a2 2 0 00-2-2H9a2 2 0 00-2 2v4m6 0H9" /></svg>
                    <span>Use Camera</span>
                </button>
                <img id="capturedImagePreview" src="" style="display:none; max-width:300px; margin-top:8px;"/>
                <input type="hidden" name="podCameraImage" id="podCameraImage">
                <small>Max file size: 5MB</small>
            </div>
        </div>
        
        <div class="submit-section">
            <button type="submit" class="btn">Submit Document</button>
            <button type="button" class="btn btn-secondary" id="cancelForm">Cancel</button>
        </div>
    </form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>
<script>
// Form-specific JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const modeOfDel = document.getElementById('modeOfDel');
    const courierGroup = document.getElementById('courierGroup');
    const courierName = document.getElementById('courierName');
    const cancelBtn = document.getElementById('cancelForm');

    // Show/hide courier field based on delivery mode
    if (modeOfDel) {
        modeOfDel.addEventListener('change', function() {
            if (this.value === 'Courier') {
                courierGroup.style.display = 'block';
                courierName.required = true;
            } else {
                courierGroup.style.display = 'none';
                courierName.required = false;
                courierName.value = '';
            }
        });
    }

    // Handle cancel button
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            // If form is in a modal, close it
            const modal = document.getElementById('formModal');
            if (modal) {
                modal.style.display = 'none';
            } else {
                // Otherwise redirect to view records
                window.location.href = '/dictproj1/App/Views/Pages/Documents.php';
            }
        });
    }

    // Signature Pad
    const canvas = document.getElementById('signaturePad');
    const signatureInput = document.getElementById('signatureInput');
    const clearBtn = document.getElementById('clearSignature');
    if (canvas && signatureInput) {
        const ctx = canvas.getContext('2d');
        let drawing = false;
        let lastX = 0;
        let lastY = 0;

        function draw(e) {
            if (!drawing) return;
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.strokeStyle = '#222';
            let x, y;
            if (e.touches) {
                x = e.touches[0].clientX - canvas.getBoundingClientRect().left;
                y = e.touches[0].clientY - canvas.getBoundingClientRect().top;
            } else {
                x = e.offsetX;
                y = e.offsetY;
            }
            ctx.lineTo(x, y);
            ctx.stroke();
            ctx.beginPath();
            ctx.moveTo(x, y);
        }

        canvas.addEventListener('mousedown', (e) => {
            drawing = true;
            ctx.beginPath();
            ctx.moveTo(e.offsetX, e.offsetY);
        });
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', () => {
            drawing = false;
            ctx.beginPath();
        });
        canvas.addEventListener('mouseout', () => {
            drawing = false;
            ctx.beginPath();
        });
        // Touch events for mobile
        canvas.addEventListener('touchstart', (e) => {
            drawing = true;
            ctx.beginPath();
            let x = e.touches[0].clientX - canvas.getBoundingClientRect().left;
            let y = e.touches[0].clientY - canvas.getBoundingClientRect().top;
            ctx.moveTo(x, y);
        });
        canvas.addEventListener('touchmove', (e) => {
            e.preventDefault();
            draw(e);
        });
        canvas.addEventListener('touchend', () => {
            drawing = false;
            ctx.beginPath();
        });
        // Clear signature
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                signatureInput.value = '';
            });
        }
        // On form submit, save signature as base64
        const form = document.getElementById('documentForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                // Only save if something is drawn
                if (!ctx.getImageData(0, 0, canvas.width, canvas.height).data.some(channel => channel !== 0)) {
                    signatureInput.value = '';
                } else {
                    signatureInput.value = canvas.toDataURL('image/png');
                }
            });
        }
    }

    document.getElementById('useCameraBtn').onclick = function() {
        Swal.fire({
            title: 'Capture Proof of Document',
            html: `
              <div style="display: flex; flex-direction: column; align-items: center;">
                <div id="swalCamera" style="margin-bottom:12px;"></div>
                <img id="swalCapturedPreview" src="" style="display:none; max-width:100%; margin-bottom:12px;"/>
                <div>
                  <button type="button" id="swalCaptureBtn" class="swal2-confirm swal2-styled" style="margin-right:8px;">Capture</button>
                  <button type="button" id="swalRetakeBtn" class="swal2-cancel swal2-styled" style="display:none; margin-right:8px;">Retake</button>
                  <button type="button" id="swalAcceptBtn" class="swal2-confirm swal2-styled" style="display:none; background:#16a34a;">Accept</button>
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
                Webcam.attach('#swalCamera');
                const captureBtn = document.getElementById('swalCaptureBtn');
                const retakeBtn = document.getElementById('swalRetakeBtn');
                const acceptBtn = document.getElementById('swalAcceptBtn');
                const previewImg = document.getElementById('swalCapturedPreview');
                let capturedData = '';
                captureBtn.onclick = function() {
                    Webcam.snap(function(data_uri) {
                        previewImg.src = data_uri;
                        previewImg.style.display = 'block';
                        document.getElementById('swalCamera').style.display = 'none';
                        captureBtn.style.display = 'none';
                        retakeBtn.style.display = 'inline-block';
                        acceptBtn.style.display = 'inline-block';
                        capturedData = data_uri;
                    });
                };
                retakeBtn.onclick = function() {
                    previewImg.style.display = 'none';
                    document.getElementById('swalCamera').style.display = 'block';
                    captureBtn.style.display = 'inline-block';
                    retakeBtn.style.display = 'none';
                    acceptBtn.style.display = 'none';
                    capturedData = '';
                };
                acceptBtn.onclick = function() {
                    if (capturedData) {
                        Swal.close();
                        document.getElementById('podCameraImage').value = capturedData;
                        document.getElementById('capturedImagePreview').src = capturedData;
                        document.getElementById('capturedImagePreview').style.display = 'block';
                    }
                };
            },
            willClose: () => {
                Webcam.reset();
            }
        });
    };
});
    </script> 