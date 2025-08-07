// Audit Log Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
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

function getFilterParams() {
    const params = {};
    const search = document.querySelector('input[name="search"]');
    const date_from = document.querySelector('input[name="date_from"]');
    const role = document.querySelector('select[name="role"]');
    if (search && search.value) params.search = search.value;
    if (date_from && date_from.value) params.date_from = date_from.value;
    if (role && role.value) params.role = role.value;
    return params;
}

function getCurrentPage() {
    // Try to get from URL hash (e.g. #page=2)
    const hash = window.location.hash;
    if (hash && hash.startsWith('#page=')) {
        const page = parseInt(hash.replace('#page=', ''));
        if (!isNaN(page) && page > 0) return page;
    }
    return 1;
}

function setCurrentPage(page) {
    window.location.hash = '#page=' + page;
}

function renderPagination(total, perPage, currentPage) {
    const totalPages = Math.ceil(total / perPage);
    const container = document.createElement('div');
    container.className = 'flex items-center justify-between';

    const nav = document.createElement('nav');
    nav.className = 'relative z-0 inline-flex rounded-md shadow-sm -space-x-px';
    nav.setAttribute('aria-label', 'Pagination');

    // Helper to create a page link
    const createLink = (page, text, isCurrent, isDisabled, isSpecial) => {
        const a = document.createElement('a');
        a.href = 'javascript:void(0)';
        a.innerHTML = text;
        a.className = `relative inline-flex items-center px-4 py-2 border text-sm font-medium `;

        if (isCurrent) {
            a.className += 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600';
            a.setAttribute('aria-current', 'page');
        } else if (isDisabled) {
            a.className += 'bg-white border-gray-300 text-gray-500 cursor-not-allowed';
        } else {
            a.className += 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50';
            a.addEventListener('click', () => {
                if (page !== currentPage) {
                    setCurrentPage(page);
                    fetchAuditLog();
                }
            });
        }

        if (isSpecial === 'first') a.classList.add('rounded-l-md');
        if (isSpecial === 'last') a.classList.add('rounded-r-md');

        return a;
    };

    if (currentPage > 1) {
        // First Button
        nav.appendChild(createLink(1, '<<', false, false, 'first'));
        // Previous Button
        nav.appendChild(createLink(currentPage - 1, '<', false, false, null));
    }

    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        // Add rounded-l-md to the first page number if it's the very first button
        const isFirstButton = currentPage === 1 && i === 1;
        nav.appendChild(createLink(i, i, i === currentPage, false, isFirstButton ? 'first' : null));
    }

    if (currentPage < totalPages) {
        // Next Button
        nav.appendChild(createLink(currentPage + 1, '>', false, false, null));
        // Last Button
        nav.appendChild(createLink(totalPages, '>>', false, false, 'last'));
    }

    container.appendChild(nav);
    return container;
}

function fetchAuditLog() {
    const params = getFilterParams();
    params.page_num = getCurrentPage();
    const query = new URLSearchParams(params).toString();
    fetch('/dictproj1/modules/get_audit_log.php?' + query)
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
            } else {
                tbody.innerHTML = rows.map(row => `
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.name || row.user_fullname || '-'}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.office_name || row.user_office || '-'}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.role || '-'}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.action || '-'}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row.timestamp ? new Date(row.timestamp).toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true }) : '-'}</td>
                    </tr>
                `).join('');
            }
            // Update pagination
            const pagDiv = document.querySelector('.flex.justify-center.my-4');
            if (pagDiv) {
                pagDiv.innerHTML = '';
                if (data.total_records > data.per_page) {
                    pagDiv.appendChild(renderPagination(data.total_records, data.per_page, data.page));
                }
            }
            // Update record count
            const recCount = document.querySelector('.text-sm.text-gray-600');
            if (recCount) {
                recCount.textContent = `${data.total_records} record${data.total_records !== 1 ? 's' : ''}`;
            }
        })
        .catch(err => {
            // Optionally show error
        });
}

let autoRefreshInterval = null;
let debounceTimeout = null;

function isAnyFilterSet() {
    const params = getFilterParams();
    // If any filter param is set (not empty), return true
    return Object.keys(params).length > 0;
}

function startAutoRefresh() {
    if (autoRefreshInterval) clearInterval(autoRefreshInterval);
    autoRefreshInterval = setInterval(() => {
        // Only auto-refresh if no filters are set
        if (!isAnyFilterSet()) {
            fetchAuditLog();
        }
    }, 3000);
}

function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
    }
}

function handleFilterChange() {
    setCurrentPage(1);
    fetchAuditLog();
    if (isAnyFilterSet()) {
        stopAutoRefresh();
    } else {
        startAutoRefresh();
    }
}

// Listen for filter changes
['user', 'role', 'action', 'date_from'].forEach(name => {
    const el = document.querySelector(`[name="${name}"]`);
    if (el) {
        el.addEventListener('change', handleFilterChange);
    }
});

// Live filtering for search box with debounce and debug log
const searchInput = document.querySelector('input[name="search"]');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        console.log('Live filter triggered:', searchInput.value); // DEBUG
        if (debounceTimeout) clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(() => {
            handleFilterChange();
        }, 300); // 300ms debounce
    });
}

// Prevent filter form from submitting and reloading the page
const filterForm = document.querySelector('#filterSection form');
if (filterForm) {
    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        handleFilterChange();
    });
}

window.addEventListener('hashchange', fetchAuditLog);

// On page load, decide whether to start auto-refresh
if (isAnyFilterSet()) {
    stopAutoRefresh();
} else {
    startAutoRefresh();
}
fetchAuditLog();
}); 

document.getElementById('clearFiltersBtn')?.addEventListener('click', async function(e) {
    e.preventDefault();

    // Clear the input fields
    document.getElementById('search').value = '';
    document.getElementById('userType').value = '';

    // Optional: reset page to 1
    const params = new URLSearchParams();
    params.set('ajax', 'true');
    params.set('page_num', '1');

    const response = await fetch('?' + params.toString(), {
        method: 'GET',
    });

    const text = await response.text();
    const parser = new DOMParser();
    const doc = parser.parseFromString(text, 'text/html');
    const newTbody = doc.getElementById('usersTableBody');

    if (newTbody) {
        document.getElementById('usersTableBody').innerHTML = newTbody.innerHTML;
    } else {
        console.error('Failed to clear filters: tbody not found');
    }
});