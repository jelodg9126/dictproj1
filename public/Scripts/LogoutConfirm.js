document.addEventListener('DOMContentLoaded', function () {
    const logoutLink = document.getElementById('logout');

    if (logoutLink) {
        logoutLink.addEventListener('click', function(e) {
            e.preventDefault(); 

            Swal.fire({
                title: 'Signing Out?',
                text: "You will be signed out.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Proceed'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = logoutLink.href; 
                }
            });
        });
    }
});
