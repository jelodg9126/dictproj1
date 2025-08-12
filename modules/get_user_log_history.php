<?php

//superadmin log history
include __DIR__ . '/../App/Core/database.php';

// Get page number from request, default to 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10; // Number of items per page
$offset = ($page - 1) * $per_page;

// Get total count of records
$count_sql = "SELECT COUNT(*) as total FROM log_history";
$count_stmt = $pdo->query($count_sql);
$total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_records / $per_page);

// Get paginated records
$sql = "SELECT log_id, user_id, name, office, login_time, logout_time 
        FROM log_history 
        ORDER BY login_time DESC 
        LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode([
    'data' => $rows,
    'pagination' => [
        'current_page' => $page,
        'per_page' => $per_page,
        'total_records' => (int)$total_records,
        'total_pages' => $total_pages
    ]
]);