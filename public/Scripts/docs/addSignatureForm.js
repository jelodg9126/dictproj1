document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('addSignatureForm');
    if (!form) return;
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(form);
        fetch('/dictproj1/modules/update_receipt_signature.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message || 'Receipt signature added successfully.',
                    timer: 2000,
                    showConfirmButton: false
                });
                // Close and reset modal
                document.getElementById('addSignatureModal').style.display = 'none';
                if (typeof resetAddSignatureModal === 'function') resetAddSignatureModal();
                setTimeout(() => window.location.reload(), 2000);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to add receipt signature.'
                });
            }
        })
        .catch(() => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while submitting the form.'
            });
        });
    });
}); 