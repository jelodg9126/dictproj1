<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include __DIR__ . '/../App/Model/connect.php';

date_default_timezone_set('Asia/Manila');

// Get filter parameters
$search = $_GET['search'] ?? '';
$user_filter = $_GET['user'] ?? '';
$role_filter = $_GET['role'] ?? '';
$action_filter = $_GET['action'] ?? '';
$date_from = $_GET['date_from'] ?? '';
file_put_contents(__DIR__ . '/../my_debug_log.txt', 'date_from=' . (isset($date_from) ? $date_from : 'NOT SET') . PHP_EOL, FILE_APPEND);
$date_to = $_GET['date_to'] ?? '';
$page = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

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
    $sql .= " AND (a.name LIKE ? OR a.office_name LIKE ? OR a.action LIKE ? OR u.name LIKE ? OR u.office LIKE ? )";
    $count_sql .= " AND (a.name LIKE ? OR a.office_name LIKE ? OR a.action LIKE ? OR u.name LIKE ? OR u.office LIKE ? )";
    $search_param = "%$search%";
    for ($i = 0; $i < 5; $i++) {
        $params[] = $search_param;
        $count_params[] = $search_param;
    }
    $types .= "sssss";
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
    $sql .= " AND DATE(a.timestamp) = ?";
    $count_sql .= " AND DATE(a.timestamp) = ?";
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

$sql .= " ORDER BY a.timestamp DESC LIMIT ? OFFSET ?";
$types .= "ii";
$params[] = (int)$per_page;
$params[] = (int)$offset;

// Before executing the SQL
file_put_contents(__DIR__ . '/../my_debug_log.txt', 'SQL: ' . $sql . ' | Params: ' . json_encode($params) . PHP_EOL, FILE_APPEND);

// Get total count for pagination
$count_stmt = $conn->prepare($count_sql);
if (!empty($count_params)) {
    $count_stmt->bind_param($count_types, ...$count_params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_records = $count_result->fetch_assoc()['total'];
$count_stmt->close();

// Prepare and execute the main query
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode([
    'data' => $rows,
    'total_records' => $total_records,
    'per_page' => $per_page,
    'page' => $page
]);
?> 