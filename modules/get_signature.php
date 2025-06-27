<?php
include __DIR__ . '/../App/Model/connect.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    exit('Invalid ID');
}

$type = isset($_GET['type']) && $_GET['type'] === 'receiver' ? 'receiver_signature' : 'signature';
$sql = "SELECT $type, transactionID FROM maindoc WHERE transactionID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($signature, $transactionID);
$stmt->fetch();
$stmt->close();
$conn->close();

if ($signature) {
    header('Content-Type: image/png');
    echo $signature;
} else {
    http_response_code(404);
    echo 'No signature found.';
} 