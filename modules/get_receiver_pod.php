<?php
include __DIR__ . '/../App/Core/database.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    http_response_code(400);
    exit('Invalid ID');
}

$sql = "SELECT receiver_pod, receiver_pod_filename, receiver_pod_mime_type FROM maindoc WHERE transactionID = :id";
$stmt = $pdo->prepare($sql);
// $stmt->bind_param("i", $id);
$stmt->execute(['id' =>$id]);
// $stmt->store_result();

$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row || empty($row['receiver_pod'])) {
    http_response_code(404);
    exit('No Receiver POD found');
}
$mime = $row['receiver_pod_mime_type'] ?? 'image/png';
$filename = $row['receiver_pod_filename'] ?? 'pod.png';
$pod = $row['receiver_pod'];

// $stmt->bind_result($pod, $filename, $mime);
// $stmt->fetch();

// if (!$pod) {
//     http_response_code(404);
//     exit('No Receiver POD found');
// }
ob_clean();
header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . $filename . '"');
echo $pod;
exit; 