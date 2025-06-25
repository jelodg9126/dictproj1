<?php
include __DIR__ . '/../App/Model/connect.php';
$transactionID = $_GET['id'] ?? '';
if (!$transactionID) exit;
$stmt = $conn->prepare('SELECT endorsedDocProof, endorsedDocProof_filename, endorsedDocProof_mime_type FROM maindoc WHERE transactionID = ?');
$stmt->bind_param('i', $transactionID);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($proof, $filename, $mime);
if ($stmt->fetch() && !empty($proof)) {
    header('Content-Type: ' . ($mime ?: 'application/octet-stream'));
    if ($filename) header('Content-Disposition: inline; filename="' . $filename . '"');
    echo $proof;
}
$stmt->close();
$conn->close(); 