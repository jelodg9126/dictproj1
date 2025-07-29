document.addEventListener('DOMContentLoaded', function() {
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
