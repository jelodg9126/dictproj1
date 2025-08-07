document.getElementById('resetPasswordForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    fetch('/dictproj1/public/handlers/reset-password-handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `newPassword=${encodeURIComponent(newPassword)}&confirmPassword=${encodeURIComponent(confirmPassword)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('✅ ' + data.message);
            // Optionally redirect
        } else {
            alert('❌ ' + data.message);
        }
    })
    .catch(error => {
        console.error('AJAX error:', error);
    });
});
