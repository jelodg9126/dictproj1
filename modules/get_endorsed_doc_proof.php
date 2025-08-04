<?php
// include __DIR__ . '/../App/Model/connect.php';
include __DIR__ . '/../App/Core/database.php';
$transactionID = $_GET['id'] ?? '';
if (!$transactionID) exit;
$stmt = $pdo->prepare('SELECT endorsedDocProof, endorsedDocProof_filename, endorsedDocProof_mime_type FROM maindoc WHERE transactionID = ?');
// $stmt->bind_param('i', $transactionID);
$stmt->execute([$transactionID]);
// $stmt->store_result();
// $stmt->bind_result($proof, $filename, $mime);

// if ($stmt->fetch() && !empty($proof)) {
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row && !empty($row['endorsedDocProof'])) {
    $proof = $row['endorsedDocProof'];
    $filename = $row['endorsedDocProof_filename'];
    $mime = $row['endorsedDocProof_mime_type'];

    if (ob_get_length()) ob_end_clean();
    header('Content-Type: ' . ($mime ?: 'application/octet-stream'));
    if ($filename) header('Content-Disposition: inline; filename="' . $filename . '"');
    echo $proof;
}

http_response_code(404);
echo 'Document proof not found.';
// $stmt->close();
// $conn->close(); 