 
 document.addEventListener("DOMContentLoaded", function() {
            lucide.createIcons();
            // Hide error message after 3 seconds
            var errorMsg = document.getElementById('error-message');
            if (errorMsg) {
                setTimeout(function() {
                    errorMsg.style.display = 'none';
                }, 3000);
            }
        });