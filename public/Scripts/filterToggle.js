// Document Tracking Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Filter toggle functionality
    const filterToggle = document.getElementById('filterToggle');
    const filterContent = document.getElementById('filterSection');
    
    if (filterToggle && filterContent) {
        filterToggle.addEventListener('click', function() {
            const isVisible = filterContent.style.display !== 'none';
            filterContent.style.display = isVisible ? 'none' : 'block';
            
         
            const toggleText = filterToggle.querySelector('span:first-child');
            const arrow = filterToggle.querySelector('span:last-child');
            
            if (isVisible) {
                toggleText.textContent = 'Show Filters';
                arrow.textContent = '▼';
                   console.log("clicked!");
            } else {
                toggleText.textContent = 'Hide Filters';
                arrow.textContent = '▲';
                  console.log("clicked!");
            }
        });
    }
    
    // Clickable rows for record details
    const clickableRows = document.querySelectorAll('.clickable-row');
    const detailsModal = document.getElementById('detailsModal');
    const recordDetails = document.getElementById('recordDetails');
    
    clickableRows.forEach(row => {
        row.addEventListener('click', function() {
            const recordData = JSON.parse(this.getAttribute('data-record'));
            displayRecordDetails(recordData);
            detailsModal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        });
    });
    
    // Function to display record details
    function displayRecordDetails(record) {
        const detailsHTML = `
            <div class="record-detail-item">
                <span class="record-detail-label">Office:</span>
                <span class="record-detail-value">${escapeHtml(record.officeName || 'N/A')}</span>
            </div>
            <div class="record-detail-item">
                <span class="record-detail-label">Sender Name:</span>
                <span class="record-detail-value">${escapeHtml(record.senderName || 'N/A')}</span>
            </div>
            <div class="record-detail-item">
                <span class="record-detail-label">Email:</span>
                <span class="record-detail-value">${escapeHtml(record.emailAdd || 'N/A')}</span>
            </div>
            <div class="record-detail-item">
                <span class="record-detail-label">Receiving Office:</span>
                <span class="record-detail-value">${escapeHtml(record.addressTo || 'N/A')}</span>
            </div>
            <div class="record-detail-item">
                <span class="record-detail-label">Delivery Mode:</span>
                <span class="record-detail-value">
                    <span class="delivery-mode ${record.modeOfDel === 'Courier' ? 'delivery-courier' : 'delivery-other'}">
                        ${escapeHtml(record.modeOfDel || 'N/A')}
                    </span>
                </span>
            </div>
            <div class="record-detail-item">
                <span class="record-detail-label">Courier Name:</span>
                <span class="record-detail-value">${escapeHtml(record.courierName || 'N/A')}</span>
            </div>
            <div class="record-detail-item">
                <span class="record-detail-label">Status:</span>
                <span class="record-detail-value">
                    <span class="status-badge ${getStatusClass(record.status)}">
                        ${escapeHtml(record.status || 'Unknown')}
                    </span>
                </span>
            </div>
            <div class="record-detail-item">
                <span class="record-detail-label">Date & Time:</span>
                <span class="record-detail-value">${formatDateTime(record.dateAndTime)}</span>
            </div>
        `;
        
        recordDetails.innerHTML = detailsHTML;
    }
    
    // Helper function to escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Helper function to get status CSS class
    function getStatusClass(status) {
        if (!status) return 'status-pending';
        
        const statusLower = status.toLowerCase();
        if (['received', 'completed', 'done'].includes(statusLower)) {
            return 'status-received';
        } else if (['incoming', 'new', 'pending'].includes(statusLower)) {
            return 'status-incoming';
        } else {
            return 'status-pending';
        }
    }
    
    // Helper function to format date and time
    function formatDateTime(dateTimeString) {
        if (!dateTimeString) return 'N/A';
        
        try {
            const date = new Date(dateTimeString);
            return date.toLocaleString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
        } catch (error) {
            return dateTimeString;
        }
    }
    
    // Auto-submit form on filter changes (optional)
    const filterInputs = document.querySelectorAll('.filter-input');
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Uncomment the line below if you want auto-submit on filter changes
            // this.closest('form').submit();
        });
    });
    
    // Add loading states to buttons
    const buttons = document.querySelectorAll('.btn, .btn-filter');
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            if (this.type === 'submit') {
                this.style.opacity = '0.7';
                this.style.pointerEvents = 'none';
                
                // Re-enable after a short delay (in case of errors)
                setTimeout(() => {
                    this.style.opacity = '1';
                    this.style.pointerEvents = 'auto';
                }, 2000);
            }
        });
    });
    
    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Escape key to close modals
        if (e.key === 'Escape') {
            const openModals = document.querySelectorAll('.modal[style*="block"]');
            openModals.forEach(modal => {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            });
        }
        
        // Ctrl/Cmd + F to focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
            e.preventDefault();
            const searchInput = document.getElementById('search');
            if (searchInput) {
                searchInput.focus();
            }
        }
    });
    
    // Add smooth scrolling for better UX
    const smoothScrollElements = document.querySelectorAll('a[href^="#"]');
    smoothScrollElements.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add confirmation for clear filters
    const clearButton = document.querySelector('.btn-clear');
    if (clearButton) {
        clearButton.addEventListener('click', function(e) {
            const hasFilters = document.querySelectorAll('.filter-tag').length > 0;
            if (hasFilters) {
                const confirmed = confirm('Are you sure you want to clear all filters?');
                if (!confirmed) {
                    e.preventDefault();
                }
            }
        });
    }
    
    // Add tooltips for better UX
    const addTooltips = () => {
        const elements = document.querySelectorAll('[title]');
        elements.forEach(element => {
            element.addEventListener('mouseenter', function() {
                const tooltip = document.createElement('div');
                tooltip.className = 'tooltip';
                tooltip.textContent = this.getAttribute('title');
                tooltip.style.cssText = `
                    position: absolute;
                    background: #333;
                    color: white;
                    padding: 5px 10px;
                    border-radius: 4px;
                    font-size: 12px;
                    z-index: 1000;
                    pointer-events: none;
                    white-space: nowrap;
                `;
                
                document.body.appendChild(tooltip);
                
                const rect = this.getBoundingClientRect();
                tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
                tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
                
                this.addEventListener('mouseleave', function() {
                    if (tooltip.parentNode) {
                        tooltip.parentNode.removeChild(tooltip);
                    }
                }, { once: true });
            });
        });
    };
    
    // Initialize tooltips
    addTooltips();
    
    // Add responsive table functionality
    const tableContainer = document.querySelector('.table-container');
    if (tableContainer && window.innerWidth <= 768) {
        const table = tableContainer.querySelector('table');
        if (table) {
            table.classList.add('responsive-table');
        }
    }
    
    // Add window resize handler
    window.addEventListener('resize', function() {
        const tableContainer = document.querySelector('.table-container');
        if (tableContainer) {
            const table = tableContainer.querySelector('table');
            if (table) {
                if (window.innerWidth <= 768) {
                    table.classList.add('responsive-table');
                } else {
                    table.classList.remove('responsive-table');
                }
            }
        }
    });
}); 