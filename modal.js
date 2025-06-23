function initializeModals() {
    const modal = document.getElementById('formModal');
    const detailsModal = document.getElementById('detailsModal');
    const openModalBtn = document.getElementById('openFormModal');
    const closeModalBtns = document.querySelectorAll('.close');
    const cancelFormBtn = document.getElementById('cancelForm');

    // Open form modal
    if (openModalBtn) {
        openModalBtn.onclick = function() {
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }
    }

    // Close modal function
    function closeModal(modalElement) {
        modalElement.style.display = 'none';
        document.body.style.overflow = 'auto'; // Restore scrolling
    }

    // Close modals when clicking X
    closeModalBtns.forEach(btn => {
        btn.onclick = function() {
            const modalToClose = this.closest('.modal');
            closeModal(modalToClose);
        }
    });

    // Close modals when clicking outside
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            closeModal(event.target);
        }
    }
}

// Initialize modals when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeModals();
});