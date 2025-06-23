<?php
include __DIR__ . '/../App/Model/connect.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    exit('Invalid ID');
}

$stmt = $conn->prepare("SELECT signature FROM maindoc WHERE transactionID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($signature);
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