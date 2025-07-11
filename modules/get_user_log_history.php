<?php
include __DIR__ . '/../App/Model/connect.php';
$sql = "SELECT log_id, user_id, name, office, login_time, logout_time FROM log_history ORDER BY login_time DESC";
$result = $conn->query($sql);
$rows = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
}
header('Content-Type: application/json');
echo json_encode(['data' => $rows]); 