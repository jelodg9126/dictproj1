// Audit Log Page JavaScript

// Filter toggle functionality
document.getElementById('filterToggle').addEventListener('click', function() {
    const filterSection = document.getElementById('filterSection');
    const filterToggleText = document.getElementById('filterToggleText');
    
    if (filterSection.style.display === 'none') {
        filterSection.style.display = 'block';
        filterToggleText.textContent = 'Hide Filters';
    } else {
        filterSection.style.display = 'none';
        filterToggleText.textContent = 'Show Filters';
    }
});

// --- Audit Log Table Auto-Refresh ---
function fetchAuditLog() {
    fetch('/dictproj1/modules/get_audit_log.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('auditLogTableBody');
            if (!tbody) return;
            const rows = data.data || [];
            if (rows.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center py-12">
                    <div class="text-gray-500 text-lg">No audit log records found</div>
                    <div class="text-gray-400 text-sm mt-2">Try adjusting your search or filter criteria</div>
                </td></tr>`;
                return;
            }
            tbody.innerHTML = rows.map(row => `
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.name || row.user_fullname || '-'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.office_name || row.user_office || '-'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.role || '-'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.action || '-'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.timestamp ? new Date(row.timestamp).toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true }) : '-'}</td>
                </tr>
            `).join('');
        })
        .catch(err => {
            // Optionally show error
        });
}
setInterval(fetchAuditLog, 3000);
// Initial fetch
fetchAuditLog(); 