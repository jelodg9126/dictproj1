<?php
// include __DIR__ . '/../App/Model/connect.php';
// include __DIR__ . '/../App/Core/database.php';

// $sql = "SELECT a.*, u.name AS user_fullname, u.office AS user_office
//         FROM audit_log a
//         LEFT JOIN users u ON a.user_id = u.userID
//         ORDER BY a.timestamp DESC";
// $result = $conn->query($sql);

// $rows = [];
// if ($result) {
//     while ($row = $result->fetch_assoc()) {
//         $rows[] = $row;
//     }
// }
// header('Content-Type: application/json');
// echo json_encode(['data' => $rows]);
