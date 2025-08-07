<?php
include __DIR__ . '/../App/Model/connect.php';

header('Content-Type: application/json');

// Get filter parameters
$search = $_GET['search'] ?? '';
$usertype = $_GET['usertype'] ?? '';

// Build the SQL query with filters
$sql = "SELECT userID, userName, usertype, name, email, contactno FROM users WHERE 1=1";
$count_sql = "SELECT COUNT(*) as total FROM users WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (userName LIKE ? OR name LIKE ? OR email LIKE ? OR contactno LIKE ?)";
    $count_sql .= " AND (userName LIKE ? OR name LIKE ? OR email LIKE ? OR contactno LIKE ?)";
    $search_param = "%{$search}%";
    array_push($params, $search_param, $search_param, $search_param, $search_param);
    $types .= 'ssss';
}

if (!empty($usertype)) {
    $sql .= " AND usertype = ?";
    $count_sql .= " AND usertype = ?";
    $params[] = $usertype;
    $types .= 's';
}

// Get total count for pagination
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_records = $count_result->fetch_assoc()['total'];
$count_stmt->close();

// Pagination setup
$page = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Add LIMIT and OFFSET to main query
$sql .= " ORDER BY name ASC LIMIT ? OFFSET ?";
$types .= 'ii';
$params[] = $per_page;
$params[] = $offset;

// Prepare and execute the main statement
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$users = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
$stmt->close();
$conn->close();

echo json_encode([
    'users' => $users,
    'total_records' => (int)$total_records,
    'per_page' => (int)$per_page,
    'page' => (int)$page
]);
