<?php
// include __DIR__ . '/../App/Model/connect.php';
// $sql = "SELECT officeName, senderName, endorsedToName, endorsementTimestamp, transactionID, receivedBy, dateAndTime, endorsedToSignature, endorsedDocProof FROM maindoc WHERE endorsedToName IS NOT NULL AND endorsedToName != '' AND endorsedToSignature IS NOT NULL AND endorsedToSignature != '' AND endorsedDocProof IS NOT NULL AND endorsedDocProof != '' ORDER BY endorsementTimestamp DESC";
// $result = $conn->query($sql);
// $rows = [];
// if ($result) {
//     while ($row = $result->fetch_assoc()) {
//         $rows[] = $row;
//     }
// }
// header('Content-Type: application/json');
// echo json_encode(['data' => $rows]); 