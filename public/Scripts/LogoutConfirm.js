document.addEventListener('DOMContentLoaded', function () {
    const logoutLink = document.getElementById('logout');

    if (logoutLink) {
        logoutLink.addEventListener('click', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Are you sure you want to sign out?',
                html: `
                    <div class="flex flex-col items-center justify-center">
                        <lord-icon
                            src="/dictproj1/public/assets/images/warning.json"
                            trigger="loop"
                            colors="primary:#eeaa66,secondary:#e83a30"
                            style="width:120px;height:120px">
                        </lord-icon>
                        <p class="mt-3 text-sm text-gray-600">
                            Your session will end and you will be logged out.
                        </p>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonColor: '#3f33e1ff',  // Custom purple
                cancelButtonColor: '#ec063cff',   // Custom red
                confirmButtonText: '<i class="fas fa-sign-out-alt"></i> Proceed',
                cancelButtonText: 'Cancel',
                focusCancel: true,
                customClass: {
                    popup: 'rounded-xl shadow-lg',
                    title: 'text-2xl font-semibold text-gray-800',
                    htmlContainer: 'text-center',
                    confirmButton: 'px-5 py-2 rounded-md text-white',
                    cancelButton: 'px-5 py-2 rounded-md'
                },
                backdrop: `
                    rgba(0, 0, 0, 0.4)
                    left top
                    no-repeat
                `,
                didOpen: () => {
                    // This triggers Lucide or other custom icon rendering if needed
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = logoutLink.href;
                }
            });
        });
    }
});
