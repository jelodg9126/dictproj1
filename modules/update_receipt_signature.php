<?php
ob_start();
// Update Receipt Signature Module - Handles receipt signature submission for incoming documents
include __DIR__ . '/../App/Model/connect.php';

// Set timezone to Manila
date_default_timezone_set('Asia/Manila');

// Check if form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate and sanitize input data
    $transactionID = trim($_POST['transactionID'] ?? '');
    $receiverName = trim($_POST['receiverName'] ?? '');
    $signatureData = $_POST['receiptSignature'] ?? '';
    
    // POD file or camera image handling
    $podBlob = null;
    $podFilename = null;
    $podMimeType = null;
    $maxPodSize = 5 * 1024 * 1024; // 5MB
    $allowedPodTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
    $errors = [];

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
        // Handle camera image (base64)
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
    
    // Validation
    if (empty($transactionID)) {
        $errors[] = "Transaction ID is required.";
    }
    
    if (empty($receiverName)) {
        $errors[] = "Receiver name is required.";
    }
    
    if (empty($signatureData) || strpos($signatureData, 'data:image/png;base64,') !== 0) {
        $errors[] = "Valid signature is required.";
    }
    
    // If there are validation errors
    if (!empty($errors)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'errors' => $errors
        ]);
        exit;
    }
    
    // Signature extraction and decoding
    $signatureBlob = base64_decode(str_replace('data:image/png;base64,', '', $signatureData));
    
    try {
        // First, verify this is a valid document (no filetype check)
        $check_stmt = $conn->prepare("SELECT transactionID, filetype, doctitle, officeName FROM maindoc WHERE transactionID = ?");
        $check_stmt->bind_param("i", $transactionID);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $doctitle = '';
        $sendingOffice = '';
        if ($row = $check_result->fetch_assoc()) {
            $doctitle = $row['doctitle'] ?? '';
            $sendingOffice = $row['officeName'] ?? '';
        } else {
            throw new Exception("Document not found.");
        }
        $check_stmt->close();
        
        // Add before the update statement
        $dup_stmt = $conn->prepare("SELECT receiver_signature, receivedBy FROM maindoc WHERE transactionID = ?");
        $dup_stmt->bind_param("i", $transactionID);
        $dup_stmt->execute();
        $dup_stmt->bind_result($existing_sig, $existing_receiver);
        $dup_stmt->fetch();
        $dup_stmt->close();
        if (!empty($existing_sig) && !empty($existing_receiver)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'errors' => ['This document has already been received. Duplicate receipt is not allowed.']
            ]);
            exit;
        }

        // Update the document with receipt signature, receiver name, and receiver POD (store in new columns)
        $stmt = $conn->prepare("UPDATE maindoc SET receiver_signature = ?, receivedBy = ?, receiver_pod = ?, receiver_pod_filename = ?, receiver_pod_mime_type = ? WHERE transactionID = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        // Pass blobs directly as variables
        $sig = $signatureBlob;
        $receiverPod = $podBlob;
        $stmt->bind_param("sssssi", $sig, $receiverName, $receiverPod, $podFilename, $podMimeType, $transactionID);
        // Execute the statement
        if ($stmt->execute()) {
            // Audit log: only for successful document receipt
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            error_log('userID: ' . ($_SESSION['userID'] ?? 'not set') . ', userAuthLevel: ' . ($_SESSION['userAuthLevel'] ?? 'not set'));
            if (isset($_SESSION['userID']) && isset($_SESSION['userAuthLevel'])) {
                include_once __DIR__ . '/../App/Model/log_audit.php';
                $user_id = $_SESSION['userID'];
                $role = $_SESSION['userAuthLevel'];
                $name = $_SESSION['name'] ?? null;
                $office_name = $_SESSION['office'] ?? null;
                if (!$name || !$office_name) {
                    $stmtUser = $conn->prepare('SELECT name, office FROM users WHERE userID = ?');
                    $stmtUser->bind_param('i', $user_id);
                    $stmtUser->execute();
                    $stmtUser->bind_result($name, $office_name);
                    $stmtUser->fetch();
                    $stmtUser->close();
                }
                $officeDisplayNames = [
                    'dictbulacan' => 'Provincial Office Bulacan',
                    'dictaurora' => 'Provincial Office Aurora',
                    'dictbataan' => 'Provincial Office Bataan',
                    'dictpampanga' => 'Provincial Office Pampanga',
                    'dictPampanga' => 'Provincial Office Pampanga',
                    'dicttarlac' => 'Provincial Office Tarlac',
                    'dictzambales' => 'Provincial Office Zambales',
                    'dictothers' => 'Provincial Office Others',
                    'dictNE' => 'Provincial Office Nueva Ecija',
                    'dictne' => 'Provincial Office Nueva Ecija',
                    'dictNUEVAECIJA' => 'Provincial Office Nueva Ecija',
                    'maindoc' => 'DICT Region 3 Office',
                    'Rdictpampanga' => 'Provincial Office Pampanga',
                    'RdictPampanga' => 'Provincial Office Pampanga',
                    'RdictTarlac' => 'Provincial Office Tarlac',
                    'RdictBataan' => 'Provincial Office Bataan',
                    'RdictBulacan' => 'Provincial Office Bulacan',
                    'RdictAurora' => 'Provincial Office Aurora',
                    'RdictZambales' => 'Provincial Office Zambales',
                    'RdictNuevaEcija' => 'Provincial Office Nueva Ecija',
                    'RdictNE' => 'Provincial Office Nueva Ecija',
                    'Rmaindoc' => 'DICT Region 3 Office',
                ];
                $officeLabel = $officeDisplayNames[strtolower($sendingOffice)] ?? $sendingOffice;
                $action = "Received document \"$doctitle\" from $officeLabel";
                log_audit_action($conn, $user_id, $name, $office_name, $role, $action);
                if ($conn->error) error_log('Audit log insert error: ' . $conn->error);
            }
            // After update, check if all three fields are present in the DB
            $checkAllStmt = $conn->prepare("SELECT signature, receivedBy, pod FROM maindoc WHERE transactionID = ?");
            $checkAllStmt->bind_param("i", $transactionID);
            $checkAllStmt->execute();
            $result = $checkAllStmt->get_result();
            $row = $result->fetch_assoc();
            $hasSignature = !empty($row['signature']);
            $hasReceivedBy = !empty($row['receivedBy']);
            $hasPod = !empty($row['pod']);
            if ($hasSignature && $hasReceivedBy && $hasPod) {
                $status_stmt = $conn->prepare("UPDATE maindoc SET status = 'Received', dateReceived = NOW() WHERE transactionID = ?");
                $status_stmt->bind_param("i", $transactionID);
                $status_stmt->execute();
                $status_stmt->close();
            }
            $checkAllStmt->close();
            $currentStatus = 'Pending';
            $statusCheckStmt = $conn->prepare("SELECT status FROM maindoc WHERE transactionID = ?");
            $statusCheckStmt->bind_param("i", $transactionID);
            $statusCheckStmt->execute();
            $statusResult = $statusCheckStmt->get_result();
            if ($statusRow = $statusResult->fetch_assoc()) {
                $currentStatus = $statusRow['status'];
            }
            $statusCheckStmt->close();
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Receipt signature added successfully!',
                'receiverName' => $receiverName,
                'status' => $currentStatus
            ]);
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        // Close statement
        $stmt->close();
        
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'errors' => ['Database Error: ' . $e->getMessage()]
        ]);
    }
    
} else {
    // Not a POST request
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'errors' => ['Invalid request method.']
    ]);
}

$conn->close();
ob_end_flush();
?> 