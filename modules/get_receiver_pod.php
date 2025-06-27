<?php
include __DIR__ . '/../App/Model/connect.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    http_response_code(400);
    exit('Invalid ID');
}

$sql = "SELECT receiver_pod, receiver_pod_filename, receiver_pod_mime_type FROM maindoc WHERE transactionID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    http_response_code(404);
    exit('No Receiver POD found');
}

$stmt->bind_result($pod, $filename, $mime);
$stmt->fetch();

if (!$pod) {
    http_response_code(404);
    exit('No Receiver POD found');
}

header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . $filename . '"');
echo $pod;
exit; 