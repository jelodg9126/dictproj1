<?php
// Form Module - Can be included in any page
// This module provides the document submission form

// Check if this file is being accessed directly
if (!defined('FORM_MODULE_INCLUDED')) {
    define('FORM_MODULE_INCLUDED', true);
}
?>

<div class="form-container" id="documentFormContainer">
    <form action="/dictproj1/modules/process_form.php" method="post" id="documentForm">
        <div class="form-section">
            <h3>Office Information</h3>
            <div class="form-group">
                <label for="officeName" class="required">Select Office</label>
                <select name="officeName" id="officeName" required>
                    <option value="">-- Select Office --</option>
                    <option value="Office A">Office A</option>
                    <option value="Office B">Office B</option>
                    <option value="Office C">Office C</option>
                </select>
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
});
</script> 