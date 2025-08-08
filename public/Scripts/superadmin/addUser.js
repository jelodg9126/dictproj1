document.addEventListener('DOMContentLoaded', function() {
    // Function to get the current page number from the URL hash
    function getCurrentPage() {
        const hash = window.location.hash;
        if (hash && hash.startsWith('#page=')) {
            const page = parseInt(hash.replace('#page=', ''));
            if (!isNaN(page) && page > 0) return page;
        }
        return 1;
    }

    // Function to set the current page in the URL hash
    function setCurrentPage(page) {
        window.location.hash = '#page=' + page;
    }

    // Function to render the pagination controls
    function renderPagination(total, perPage, currentPage) {
        const totalPages = Math.ceil(total / perPage);
        const container = document.createElement('div');
        container.className = 'flex items-center justify-between';

        const nav = document.createElement('nav');
        nav.className = 'relative z-0 inline-flex rounded-md shadow-sm -space-x-px';
        nav.setAttribute('aria-label', 'Pagination');

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
                        fetchUsers();
                    }
                });
            }

            if (isSpecial === 'first') a.classList.add('rounded-l-md');
            if (isSpecial === 'last') a.classList.add('rounded-r-md');

            return a;
        };

        if (currentPage > 1) {
            nav.appendChild(createLink(1, '<<', false, false, 'first'));
            nav.appendChild(createLink(currentPage - 1, '<', false, false, null));
        }

        for (let i = 1; i <= totalPages; i++) {
            const isFirstButton = currentPage === 1 && i === 1;
            nav.appendChild(createLink(i, i, i === currentPage, false, isFirstButton ? 'first' : null));
        }

        if (currentPage < totalPages) {
            nav.appendChild(createLink(currentPage + 1, '>', false, false, null));
            nav.appendChild(createLink(totalPages, '>>', false, false, 'last'));
        }

        container.appendChild(nav);
        return container;
    }

    // Function to get filter parameters
    function getFilterParams() {
        const params = {};
        const search = document.getElementById('search');
        const usertype = document.getElementById('userType');
        if (search && search.value) params.search = search.value;
        if (usertype && usertype.value) params.usertype = usertype.value;
        return params;
    }

    // Function to fetch and display users
    function fetchUsers() {
        const page = getCurrentPage();
        const filters = getFilterParams();
        const params = new URLSearchParams({ page_num: page, ...filters });

        fetch(`/dictproj1/modules/get_users.php?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                const tableBody = document.getElementById('usersTableBody');
                tableBody.innerHTML = ''; // Clear existing rows

                if (data.users && data.users.length > 0) {
                    data.users.forEach(user => {
                        const row = document.createElement('tr');
                        row.className = 'hover:bg-gray-50 transition-colors';
                        row.innerHTML = `
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${user.userName}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${user.usertype}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${user.name}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${user.email}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${user.contactno}</td>
                        `;
                        tableBody.appendChild(row);
                    });
                } else {
                    tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-12 text-gray-500">No users found.</td></tr>';
                }

                const paginationContainer = document.getElementById('pagination-container');
                paginationContainer.innerHTML = '';
                if (data.total_records > data.per_page) {
                    paginationContainer.appendChild(renderPagination(data.total_records, data.per_page, data.page));
                }

                // Update record count
                const recordCount = document.getElementById('recordCount');
                if (recordCount) {
                    recordCount.textContent = `${data.total_records} record${data.total_records !== 1 ? 's' : ''}`;
                }
            })
            .catch(error => console.error('Error fetching users:', error));
    }

    // Filter toggle functionality
    function initFilterToggle() {
        const filterSection = document.getElementById('filterSection');
        const filterToggle = document.getElementById('filterToggle');
        const filterToggleText = document.getElementById('filterToggleText');

        if (!filterSection || !filterToggle || !filterToggleText) {
            console.error('Required filter elements not found');
            return;
        }

        let filtersVisible = filterSection.classList.contains('hidden') ? false : true;
        
        // Update button text based on initial state
        filterToggleText.textContent = filtersVisible ? 'Hide Filters' : 'Show Filters';
        
        filterToggle.addEventListener('click', function() {
            filtersVisible = !filtersVisible;
            if (filtersVisible) {
                filterSection.classList.remove('hidden');
                filterToggleText.textContent = 'Hide Filters';
            } else {
                filterSection.classList.add('hidden');
                filterToggleText.textContent = 'Show Filters';
            }
        });
    }
    
    // Initialize when DOM is fully loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFilterToggle);
    } else {
        initFilterToggle();
    }

    let debounceTimeout = null;
    function handleFilterChange() {
        setCurrentPage(1);
        fetchUsers();
    }

    document.getElementById('search').addEventListener('input', function() {
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(handleFilterChange, 300);
    });

    document.getElementById('userType').addEventListener('change', handleFilterChange);

    document.getElementById('filterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        handleFilterChange();
    });

    document.getElementById('clearFiltersBtn').addEventListener('click', function() {
        document.getElementById('search').value = '';
        document.getElementById('userType').value = '';
        handleFilterChange();
    });

    // Initial fetch
    fetchUsers();

    // Listen for hash changes to update page
    window.addEventListener('hashchange', fetchUsers);

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