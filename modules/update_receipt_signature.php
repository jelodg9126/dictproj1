<?php
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
    
    // POD file handling
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
    } else {
        $errors[] = "Proof of Document (POD) file is required.";
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
        $check_stmt = $conn->prepare("SELECT transactionID, filetype FROM maindoc WHERE transactionID = ?");
        $check_stmt->bind_param("i", $transactionID);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows === 0) {
            throw new Exception("Document not found.");
        }
        
        $check_stmt->close();
        
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
                $status_stmt = $conn->prepare("UPDATE maindoc SET status = 'Received' WHERE transactionID = ?");
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
?> 