<?php
// include __DIR__ . '/../App/Model/connect.php';
// header('Content-Type: application/json');

// $sql = "SELECT * FROM maindoc WHERE status = 'Received' ORDER BY dateAndTime DESC";
// $result = $conn->query($sql);
// $rows = [];
// if ($result) {
//     while ($row = $result->fetch_assoc()) {
//         $row['pod'] = !empty($row['pod_filename']) ? true : false;
//         $row['hasSignature'] = !empty($row['signature']);
//         unset($row['signature']); // Don't send binary data
//         $rows[] = $row;
//     }
// }
// echo json_encode(['success' => true, 'data' => $rows]);
