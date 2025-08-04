<?php
include __DIR__ . '/../App/Core/database.php';

$transactionID = $_GET['id'] ?? '';
if (!$transactionID) exit;

$stmt = $pdo->prepare('SELECT endorsedToSignature FROM maindoc WHERE transactionID = ?');
$stmt->execute([$transactionID]);
$signature = $stmt->fetchColumn();

if (!empty($signature)) {
    if (ob_get_length()) ob_end_clean();
    header('Content-Type: image/png');
    echo $signature;
}
