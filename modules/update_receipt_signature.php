<?php
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// include_once __DIR__ . '/../App/Model/connect.php';
include_once __DIR__ . '/../App/Core/database.php';
include_once __DIR__ . '/../App/Model/log_audit.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// 1. Validate and extract POST data
$transactionID = intval($_POST['transactionID'] ?? 0);
$receiverName = trim($_POST['receiverName'] ?? '');
$signatureData = $_POST['receiptSignature'] ?? '';
$podBlob = null;
$podFilename = null;
$podMimeType = null;
$maxPodSize = 5 * 1024 * 1024; // 5MB
$allowedPodTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
$errors = [];

// 2. Process POD (file or camera image)
if (isset($_FILES['podFile']) && $_FILES['podFile']['error'] === UPLOAD_ERR_OK) {
    if ($_FILES['podFile']['size'] > $maxPodSize) {
        $errors[] = "Proof of Document (POD) file must be 5MB or less.";
    } else if (!in_array($_FILES['podFile']['type'], $allowedPodTypes)) {
        $errors[] = "Only image files (JPG, PNG, GIF, WEBP) or PDF are allowed for Proof of Document (POD).";
    } else {
        $podBlob = file_get_contents($_FILES['podFile']['tmp_name']);
        $podFilename = $_FILES['podFile']['name'];
        $podMimeType = $_FILES['podFile']['type'];
    }
} else if (!empty($_POST['podCameraImage'])) {
    $data_uri = $_POST['podCameraImage'];
    if (preg_match('/^data:image\/(\w+);base64,/', $data_uri, $type)) {
        $data = substr($data_uri, strpos($data_uri, ',') + 1);
        $data = base64_decode($data);
        $ext = strtolower($type[1]);
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $errors[] = "Camera image must be JPG, PNG, GIF, or WEBP.";
        } else if (strlen($data) > $maxPodSize) {
            $errors[] = "Camera image must be 5MB or less.";
        } else {
            $podBlob = $data;
            $podFilename = 'camera_pod_' . uniqid() . '.' . $ext;
            $podMimeType = 'image/' . $ext;
        }
    } else {
        $errors[] = "Invalid camera image format.";
    }
} else {
    $errors[] = "Proof of Document (POD) file or camera image is required.";
}

if (empty($transactionID)) {
    $errors[] = "Transaction ID is required.";
}
if (empty($receiverName)) {
    $errors[] = "Receiver name is required.";
}
if (empty($signatureData) || strpos($signatureData, 'data:image/png;base64,') !== 0) {
    $errors[] = "Valid signature is required.";
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// 3. Check for duplicate receipt
$dup_stmt = $pdo->prepare("SELECT receiver_signature, receivedBy FROM maindoc WHERE transactionID = ?");
// $dup_stmt->bind_param("i", $transactionID);
$dup_stmt->execute([$transactionID]);
// $dup_stmt->bind_result($existing_sig, $existing_receiver);
// $dup_stmt->fetch();
$dup_result = $dup_stmt->fetch(PDO::FETCH_ASSOC);
$existing_sig = $dup_result['receiver_signature'] ?? null;
$existing_receiver = $dup_result['receivedBy'] ?? null;
// $dup_stmt->close();
if (!empty($existing_sig) && !empty($existing_receiver)) {
    echo json_encode(['success' => false, 'errors' => ['This document has already been received. Duplicate receipt is not allowed.']]);
    exit;
}


// 4. Decode signature
$signatureBlob = base64_decode(str_replace('data:image/png;base64,', '', $signatureData));

// 5. Update maindoc
$stmt = $pdo->prepare("UPDATE maindoc SET receiver_signature = ?, receivedBy = ?, receiver_pod = ?, receiver_pod_filename = ?, receiver_pod_mime_type = ?, status = 'Received', dateReceived = NOW() WHERE transactionID = ?");
// if (!$stmt) {
//     echo json_encode(['success' => false, 'errors' => ['Database error: ' . $pdo->error]]);
//     exit;
// }
// $stmt->send_long_data(0, $signatureBlob);
// $stmt->send_long_data(2, $podBlob);
// $stmt->bind_param("sssssi", $dummy1, $dummy2, $dummy3, $dummy4, $dummy5, $dummy6);
// $dummy1 = null; $dummy2 = null; $dummy3 = null; $dummy4 = null; $dummy5 = null; $dummy6 = null;
// $dummy1 = $signatureBlob;
// $dummy2 = $receiverName;
// $dummy3 = $podBlob;
// $dummy4 = $podFilename;
// $dummy5 = $podMimeType;
// $dummy6 = $transactionID;
$success = $stmt->execute([
    $signatureBlob,
    $receiverName,
    $podBlob,
    $podFilename,
    $podMimeType,
    $transactionID
]);
// $stmt->close();

if (!$success) {
    // echo json_encode(['success' => false, 'errors' => ['Failed to update document: ' . $pdo->error]]);
    echo json_encode(['success' => false, 'errors' => ['Failed to update document: ' . implode(' | ', $stmt->errorInfo())]]);

    exit;
}

// 6. Audit log
$user_id = $_SESSION['userID'] ?? null;
$role = $_SESSION['userAuthLevel'] ?? null;
$name = $_SESSION['name'] ?? null;
$office_name = $_SESSION['office'] ?? null;
if (!$name || !$office_name) {
    $stmtUser = $pdo->prepare('SELECT name, office FROM users WHERE userID = ?');
    // $stmtUser->bind_param('i', $user_id);
    $stmtUser->execute([$user_id]);
    // $stmtUser->bind_result($name, $office_name);
    // $stmtUser->fetch();
    $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);
    $name = $userData['name']?? null ;
    $office_name = $userData['office']?? null;
    // $stmtUser->close();
}
$action = "Received document (ID: $transactionID)";
if ($user_id && $name && $office_name && $role) {
    log_audit_action($pdo, $user_id, $name, $office_name, $role, $action);
}

// 7. Success response
echo json_encode(['success' => true, 'message' => 'Receipt signature added successfully!']);
// $conn->close();
?> 