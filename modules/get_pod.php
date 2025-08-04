<?php
include __DIR__ . '/../App/Core/database.php'; // This should give you the $pdo instance

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    http_response_code(400);
    exit('Invalid ID');
}

$sql = "SELECT pod, pod_filename, pod_mime_type FROM maindoc WHERE transactionID = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row || empty($row['pod'])) {
    http_response_code(404);
    exit('No POD found');
}

$mime = $row['pod_mime_type'] ?? 'image/png';
$filename = $row['pod_filename'] ?? 'pod.png';
$pod = $row['pod'];

ob_clean();
header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . $filename . '"');
echo $pod;
exit;
