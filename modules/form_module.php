<?php
// Form Module - Can be included in any page
// This module provides the document submission form

// Check if this file is being accessed directly
if (!defined('FORM_MODULE_INCLUDED')) {
    define('FORM_MODULE_INCLUDED', true);
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
?>

<div class="form-container" id="documentFormContainer">
    <form action="/dictproj1/modules/process_form.php" method="post" id="documentForm" enctype="multipart/form-data">
        <div class="form-section">
            <h3>Office Information</h3>
            <div class="form-group">
                <label for="officeName" class="required">Select Office</label>
                <select name="officeName" id="officeName" required>
                    <option value="">-- Select Office --</option>
                    <option value="Provical Office 1">Provical Office 1</option>
                    <option value="Provical Office 2">Provical Office 2</option>
                    <option value="Provical Office 3">Provical Office 3</option>
                    <option value="Provical Office 4">Provical Office 4</option>
                    <option value="Provical Office 5">Provical Office 5</option>
                    <option value="Provical Office 6">Provical Office 6</option>
                    <option value="Others">Others</option>
                </select>
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
                <input type="text" name="addressTo" id="addressTo" required placeholder="Enter receiving office">
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
                <input type="file" name="podFile" id="podFile" accept="image/*,application/pdf" required>
                <small>Max file size: 5MB</small>
            </div>
        </div>
        
        <div class="submit-section">
            <button type="submit" class="btn">Submit Document</button>
            <button type="button" class="btn btn-secondary" id="cancelForm">Cancel</button>
        </div>
    </form>
</div>

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
});
    </script> 