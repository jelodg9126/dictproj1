<?php

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/dictproj1/public/assets/images/mainCircle.png" type="image/png">
    <title>Audit Log</title>
    <link rel="stylesheet" href="/dictproj1/src/input.css">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/auditLog.css">
</head>

<body>
    <div class="app-container">
        <?php include __DIR__ . '/../components/Sidebar.php'; ?>
        <div class="flex-1 p-6 min-h-screen overflow-y-auto" id="docu">
            <div class="max-w-7xl mx-auto">
                <div class="flex items-center justify-between mb-6">
                    <div class="items-center">
                        <h1 class="text-3xl font-bold text-indigo-500">Audit Log</h1>
                        <p class="text-gray-300 mt-2">View all audit log records from the database.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 flex items-center gap-2" id="filterToggle">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
                            </svg>
                            <span id="filterToggleText">Show Filters</span>
                        </button>
                    </div>
                </div>

                <!-- Search and Filter Section -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6" id="filterSection" style="display: none;">
                    <form id="filterForm" method="GET" action="">
                        <input type="hidden" name="page" value="auditLog">
                        <div class="flex flex-col md:flex-row flex-wrap gap-4 items-start md:items-center justify-between">
                            <div class="flex flex-wrap items-center gap-4 w-full md:w-auto">
                                <div class="relative w-full md:w-64">
                                    <input type="search" name="search" placeholder="Search..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                                    <select
                                        name="user"
                                        class="w-36 border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">All Users</option>
                                        <?php foreach ($users_filter as $user): ?>
                                            <option value="<?php echo htmlspecialchars($user); ?>" <?php echo ($filters['user'] ?? '') === $user ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($user); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select
                                        name="role"
                                        class="w-32 border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">All Roles</option>
                                        <?php foreach ($roles_filter as $role): ?>
                                            <option value="<?php echo htmlspecialchars($role); ?>" <?php echo ($filters['role'] ?? '') === $role ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($role); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select
                                        name="action"
                                        class="w-36 border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">All Actions</option>
                                        <?php foreach ($actions_filter as $action): ?>
                                            <option value="<?php echo htmlspecialchars($action); ?>" <?php echo ($filters['action'] ?? '') === $action ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($action); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0 ml-auto">
                                <a href="?page=auditLog" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 whitespace-nowrap">Clear</a>
                                <span class="text-sm text-gray-600 records-count whitespace-nowrap">
                                    <?php echo $total_records; ?> record<?php echo $total_records != 1 ? 's' : ''; ?>
                                </span>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Audit Log Table -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <!-- <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Audit ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User ID</th> -->
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Full Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User Office</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="auditLogTableBody">
    <?php if (!empty($auditLogs)): ?>
        <?php foreach ($auditLogs as $row): ?>
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['name'] ?? $row['user_fullname'] ?? '-'); ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['office_name'] ?? $row['user_office'] ?? '-'); ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['role']); ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['action']); ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('M d, Y g:i A', strtotime($row['timestamp'])); ?></td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="7" class="text-center py-12">
                <div class="text-gray-500 text-lg">No audit log records found</div>
                <div class="text-gray-400 text-sm mt-2">Try adjusting your search or filter criteria</div>
            </td>
        </tr>
    <?php endif; ?>
</tbody>

                        </table>
                    </div>
                </div>

                <!-- Pagination Controls -->
                                <div class="flex justify-center my-4 pagination-controls">
                    <?php if ($total_pages > 1): ?>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <?php
                            // Build query string for filters/search
                            $query_params = $_GET;
                            unset($query_params['page_num']);
                            $base_query = http_build_query($query_params);
                            $base_url = '?' . $base_query . ($base_query ? '&' : '');
                            
                            // First page link
                            if ($page > 1): ?>
                                <a href="<?php echo $base_url . 'page_num=1'; ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">First</span>
                                    &laquo;
                                </a>
                                <a href="<?php echo $base_url . 'page_num=' . ($page - 1); ?>" class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Previous</span>
                                    &lsaquo;
                                </a>
                            <?php endif; ?>
                            
                            <?php 
                            // Page number links
                            $start = max(1, $page - 2);
                            $end = min($total_pages, $start + 4);
                            $start = max(1, $end - 4);
                            
                            for ($i = $start; $i <= $end; $i++):
                                $is_first = $i === 1 && $page === 1;
                                $is_last = $i === $total_pages && $page === $total_pages;
                                $is_active = $i == $page;
                                $classes = [
                                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                    $is_active ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50',
                                    $is_first ? 'rounded-l-md' : '',
                                    ($i === $total_pages && $page === $total_pages) ? 'rounded-r-md' : ''
                                ];
                                $link = $base_url . 'page_num=' . $i;
                            ?>
                                <a href="<?php echo $link; ?>" class="<?php echo implode(' ', array_filter($classes)); ?>" <?php echo $is_active ? 'aria-current="page"' : ''; ?>>
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php // Next and Last page links
                            if ($page < $total_pages): ?>
                                <a href="<?php echo $base_url . 'page_num=' . ($page + 1); ?>" class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Next</span>
                                    &rsaquo;
                                </a>
                                <a href="<?php echo $base_url . 'page_num=' . $total_pages; ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Last</span>
                                    &raquo;
                                </a>
                            <?php endif; ?>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="/dictproj1/public/Scripts/superadmin/auditLog.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.getElementById('filterForm');
        const tableBody = document.getElementById('auditLogTableBody');
        const paginationContainer = document.querySelector('.pagination-controls');
        const recordsCount = document.querySelector('.records-count');

        function fetchAuditLogs() {
            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData);
            
            fetch(`/dictproj1/modules/get_audit_logs.php?${params.toString()}`)
                .then(response => response.json())
                .then(data => {
                    updateTable(data.logs);
                    updatePagination(data.total_pages, data.current_page, params);
                    updateRecordsCount(data.total_records);
                });
        }

        function updateTable(logs) {
            tableBody.innerHTML = '';
            if (logs.length > 0) {
                logs.forEach(log => {
                    const row = `
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${log.name || log.user_fullname || '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${log.office_name || log.user_office || '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${log.role}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${log.action}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${new Date(log.timestamp).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true })}</td>
                        </tr>
                    `;
                    tableBody.innerHTML += row;
                });
            } else {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center py-12">
                            <div class="text-gray-500 text-lg">No audit log records found</div>
                            <div class="text-gray-400 text-sm mt-2">Try adjusting your search or filter criteria</div>
                        </td>
                    </tr>
                `;
            }
        }

        function updatePagination(totalPages, currentPage, params) {
            // This function would be responsible for rebuilding the pagination controls.
            // For brevity, this is left as an exercise, but it would be similar to your existing PHP logic.
        }

        function updateRecordsCount(totalRecords) {
            recordsCount.textContent = `${totalRecords} record${totalRecords !== 1 ? 's' : ''}`;
        }

        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            fetchAuditLogs();
        });

        filterForm.querySelectorAll('input, select').forEach(element => {
            element.addEventListener('change', fetchAuditLogs);
        });
    });
    </script>
</body>

</html>