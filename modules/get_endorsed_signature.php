<?php
include __DIR__ . '/../App/Model/connect.php';
$transactionID = $_GET['id'] ?? '';
if (!$transactionID) exit;
$stmt = $conn->prepare('SELECT endorsedToSignature FROM maindoc WHERE transactionID = ?');
$stmt->bind_param('i', $transactionID);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($signature);
if ($stmt->fetch() && !empty($signature)) {
    header('Content-Type: image/png');
    echo $signature;
}
$stmt->close();
$conn->close(); 