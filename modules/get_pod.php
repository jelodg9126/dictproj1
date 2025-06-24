<?php
include __DIR__ . '/../App/Model/connect.php';
$id = intval($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT pod, pod_filename, pod_mime_type FROM maindoc WHERE transactionID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($pod, $filename, $mime);
$stmt->fetch();
$stmt->close();
$conn->close();
if ($pod) {
    header('Content-Type: ' . $mime);
    header('Content-Disposition: inline; filename="' . $filename . '"');
    echo $pod;
} else {
    http_response_code(404);
    echo 'No file found.';
} 