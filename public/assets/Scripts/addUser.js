document.addEventListener('DOMContentLoaded', function() {
    // Show/Hide Password Toggle
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('passWord');
    
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle icon
            const icon = this.querySelector('svg');
            if (type === 'password') {
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
            } else {
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />';
            }
        });
    }
    const userNameInput = document.getElementById('userName');
    if (!userNameInput) return;

    // Create feedback element for username availability
    const userNameFeedback = document.createElement('div');
    userNameFeedback.className = 'text-sm mt-1';
    userNameInput.parentNode.insertBefore(userNameFeedback, userNameInput.nextSibling);

    let checkTimeout;
    
    userNameInput.addEventListener('input', function() {
        const username = this.value.trim();
        
        // Clear previous timeout and feedback
        clearTimeout(checkTimeout);
        userNameFeedback.textContent = '';
        userNameFeedback.className = 'text-sm mt-1';
        
        if (username.length < 3) {
            return;
        }
        
        // Debounce the API call
        checkTimeout = setTimeout(() => {
            fetch(`/dictproj1/App/Controllers/checkUsername.php?username=${encodeURIComponent(username)}`)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.exists) {
                        userNameFeedback.textContent = 'Username already taken';
                        userNameFeedback.className = 'text-sm mt-1 text-red-600';
                        this.setCustomValidity('Username already exists');
                    } else {
                        userNameFeedback.textContent = 'Username is available';
                        userNameFeedback.className = 'text-sm mt-1 text-green-600';
                        this.setCustomValidity('');
                    }
                })
                .catch(error => {
                    console.error('Error checking username:', error);
                    // Don't show error to user, just log it
                });
        }, 500);
    });
});
