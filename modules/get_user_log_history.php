<?php

//superadmin log history
include __DIR__ . '/../App/Core/database.php';
$sql = "SELECT log_id, user_id, name, office, login_time, logout_time FROM log_history ORDER BY login_time DESC";
$stmt = $pdo->query($sql);
$rows = [];
if ($stmt) {
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ;
}

header('Content-Type: application/json');
echo json_encode(['data' => $rows]);