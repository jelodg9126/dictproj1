<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['uNameLogin'])) {
    header("Location: Login.php");
    exit();
}

// Include database connection
include __DIR__ . '/../../Model/connect.php';

// Set timezone to Manila
date_default_timezone_set('Asia/Manila');

// Get filter parameters
$search = $_GET['search'] ?? '';
$user_filter = $_GET['user'] ?? '';
$role_filter = $_GET['role'] ?? '';
$action_filter = $_GET['action'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build the SQL query with filters
$sql = "SELECT a.*, u.name AS user_fullname, u.office AS user_office 
        FROM audit_log a 
        LEFT JOIN users u ON a.user_id = u.userID 
        WHERE 1";
$count_sql = "SELECT COUNT(*) as total 
              FROM audit_log a 
              LEFT JOIN users u ON a.user_id = u.userID 
              WHERE 1";
$params = [];
$types = "";
$count_params = [];
$count_types = "";

if (!empty($search)) {
    $sql .= " AND (a.name LIKE ? OR a.office_name LIKE ? OR a.action LIKE ? OR u.name LIKE ? OR u.office LIKE ?)";
    $count_sql .= " AND (a.name LIKE ? OR a.office_name LIKE ? OR a.action LIKE ? OR u.name LIKE ? OR u.office LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sssss";
    $count_params[] = $search_param;
    $count_params[] = $search_param;
    $count_params[] = $search_param;
    $count_params[] = $search_param;
    $count_params[] = $search_param;
    $count_types .= "sssss";
}

if (!empty($user_filter)) {
    $sql .= " AND (a.name = ? OR u.name = ?)";
    $count_sql .= " AND (a.name = ? OR u.name = ?)";
    $params[] = $user_filter;
    $params[] = $user_filter;
    $types .= "ss";
    $count_params[] = $user_filter;
    $count_params[] = $user_filter;
    $count_types .= "ss";
}

if (!empty($role_filter)) {
    $sql .= " AND a.role = ?";
    $count_sql .= " AND a.role = ?";
    $params[] = $role_filter;
    $types .= "s";
    $count_params[] = $role_filter;
    $count_types .= "s";
}

if (!empty($action_filter)) {
    $sql .= " AND a.action LIKE ?";
    $count_sql .= " AND a.action LIKE ?";
    $action_param = "%$action_filter%";
    $params[] = $action_param;
    $types .= "s";
    $count_params[] = $action_param;
    $count_types .= "s";
}

if (!empty($date_from)) {
    $sql .= " AND DATE(a.timestamp) >= ?";
    $count_sql .= " AND DATE(a.timestamp) >= ?";
    $params[] = $date_from;
    $types .= "s";
    $count_params[] = $date_from;
    $count_types .= "s";
}

if (!empty($date_to)) {
    $sql .= " AND DATE(a.timestamp) <= ?";
    $count_sql .= " AND DATE(a.timestamp) <= ?";
    $params[] = $date_to;
    $types .= "s";
    $count_params[] = $date_to;
    $count_types .= "s";
}

$sql .= " ORDER BY a.timestamp DESC";

// Pagination setup
$page = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Get total count for pagination
$count_stmt = $conn->prepare($count_sql);
if (!empty($count_params)) {
    $count_stmt->bind_param($count_types, ...$count_params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_records = $count_result->fetch_assoc()['total'];
$count_stmt->close();
$total_pages = ceil($total_records / $per_page);

// Add LIMIT and OFFSET to main query
$sql .= " LIMIT ? OFFSET ?";
$types .= "ii";
$params[] = (int)$per_page;
$params[] = (int)$offset;

// Prepare and execute the statement
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Get unique users for filter dropdown
$users_sql = "SELECT DISTINCT a.name FROM audit_log a WHERE a.name IS NOT NULL AND a.name != '' ORDER BY a.name";
$users_result = $conn->query($users_sql);
$users = [];
if ($users_result) {
    while ($row = $users_result->fetch_assoc()) {
        $users[] = $row['name'];
    }
}

// Get unique roles for filter dropdown
$roles_sql = "SELECT DISTINCT a.role FROM audit_log a WHERE a.role IS NOT NULL AND a.role != '' ORDER BY a.role";
$roles_result = $conn->query($roles_sql);
$roles = [];
if ($roles_result) {
    while ($row = $roles_result->fetch_assoc()) {
        $roles[] = $row['role'];
    }
}

// Get unique actions for filter dropdown
$actions_sql = "SELECT DISTINCT a.action FROM audit_log a WHERE a.action IS NOT NULL AND a.action != '' ORDER BY a.action";
$actions_result = $conn->query($actions_sql);
$actions = [];
if ($actions_result) {
    while ($row = $actions_result->fetch_assoc()) {
        $actions[] = $row['action'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6" id="filterSection">
                    <form method="GET" action="auditLog.php">
                        <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                            <div class="flex items-center gap-4 flex-1">
                                <div class="relative flex-1 max-w-md">
                                    <input
                                        type="text"
                                        name="search"
                                        class="filter-input pl-10 pr-4 py-2 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="Search users, offices, or actions..."
                                        value="<?php echo htmlspecialchars($search); ?>"
                                    />
                                </div>
                                <div class="flex items-center gap-2">
                                    <input
                                        type="date"
                                        name="date_from"
                                        class="filter-input border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        value="<?php echo htmlspecialchars($date_from); ?>"
                                    />
                                    <select
                                        name="role"
                                        class="filter-input border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">All Roles</option>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?php echo htmlspecialchars($role); ?>" 
                                                    <?php echo $role_filter === $role ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($role); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                            <a href="/dictproj1/App/Views/Pages/auditLog.php" class="ml-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                             Clear
                            </a>
                                <span class="text-sm text-gray-600">
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
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while($row = $result->fetch_assoc()): ?>
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <!-- <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['audit_id']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['user_id']); ?></td> -->
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['name'] ?: $row['user_fullname'] ?: '-'); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['office_name'] ?: $row['user_office'] ?: '-'); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['role']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['action']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('M d, Y g:i A', strtotime($row['timestamp'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
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
                <div class="flex justify-center my-4" id="pagination-container">
                    <!-- This will be populated by auditLog.js -->
                </div>
            </div>
        </div>
    </div>

    <script src="/dictproj1/public/Scripts/superadmin/auditLog.js"></script>
</body>
</html>