<?php
include __DIR__ . '/../App/Core/database.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    exit('Invalid ID');
}

$type = (isset($_GET['type']) && $_GET['type'] === 'receiver') ? 'receiver_signature' : 'signature';

$sql = "SELECT $type AS signature FROM maindoc WHERE transactionID = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id]);

$signature = $stmt->fetchColumn();

if ($signature) {
    // Clear any previously buffered output
    if (ob_get_length()) ob_end_clean();
    header('Content-Type: image/png');
    // header('Content-Length: ' . strlen($signature));
    echo $signature;
} else {
    http_response_code(404);
    echo 'No signature found.';
}
