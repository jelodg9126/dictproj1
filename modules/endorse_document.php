<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include __DIR__ . '/../App/Model/connect.php';
header('Content-Type: application/json');
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $transactionID = $_POST['transactionID'] ?? '';
        $endorsedToName = trim($_POST['endorsedToName'] ?? '');
        $signatureData = $_POST['endorsedToSignature'] ?? '';
        $proofBlob = null;
        $proofFilename = null;
        $proofMimeType = null;
        $maxProofSize = 5 * 1024 * 1024;
        $allowedProofTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
        if (!empty($_POST['endorseCameraImage'])) {
            $data = $_POST['endorseCameraImage'];
            if (strpos($data, 'data:image') === 0) {
                $data = explode(',', $data)[1];
            }
            $proofBlob = base64_decode($data);
            $proofFilename = 'camera_capture_' . date('Ymd_His') . '.jpg';
            $proofMimeType = 'image/jpeg';
        } elseif (isset($_FILES['endorsedDocProof']) && $_FILES['endorsedDocProof']['error'] === UPLOAD_ERR_OK) {
            if ($_FILES['endorsedDocProof']['size'] > $maxProofSize) {
                echo json_encode(['success' => false, 'errors' => ['Proof file must be 5MB or less.']]);
                exit;
            } elseif (!in_array($_FILES['endorsedDocProof']['type'], $allowedProofTypes)) {
                echo json_encode(['success' => false, 'errors' => ['Invalid file type for proof.']]);
                exit;
            } else {
                $proofBlob = file_get_contents($_FILES['endorsedDocProof']['tmp_name']);
                $proofFilename = $_FILES['endorsedDocProof']['name'];
                $proofMimeType = $_FILES['endorsedDocProof']['type'];
            }
        }
        $errors = [];
        if (empty($transactionID)) $errors[] = 'Transaction ID required.';
        if (empty($endorsedToName)) $errors[] = 'Endorsed To Name required.';
        if (empty($signatureData) || strpos($signatureData, 'data:image/png;base64,') !== 0) $errors[] = 'Valid signature required.';
        if (!empty($errors)) {
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }
        $signatureBlob = base64_decode(str_replace('data:image/png;base64,', '', $signatureData));
        $null = null;
        $stmt = $conn->prepare("UPDATE maindoc SET endorsedToName=?, endorsedToSignature=?, endorsedDocProof=?, endorsedDocProof_filename=?, endorsedDocProof_mime_type=?, status='Endorsed' WHERE transactionID=?");
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param(
            "ssbssi",
            $endorsedToName,
            $signatureBlob,
            $null, // placeholder for blob
            $proofFilename,
            $proofMimeType,
            $transactionID
        );
        // Send signature blob (index 1) and proof blob (index 2)
        $stmt->send_long_data(1, $signatureBlob);
        if ($proofBlob !== null) {
            $stmt->send_long_data(2, $proofBlob);
        }
        if ($stmt->execute()) {
            // Fetch updated endorsement info
            $fetch = $conn->prepare('SELECT endorsedToName, endorsedToSignature, endorsedDocProof FROM maindoc WHERE transactionID = ?');
            $fetch->bind_param('i', $transactionID);
            $fetch->execute();
            $fetch->bind_result($endorsedToName, $endorsedToSignature, $endorsedDocProof);
            $fetch->fetch();
            $fetch->close();
            echo json_encode([
                'success' => true,
                'message' => 'Document endorsed successfully!',
                'endorsedToName' => $endorsedToName,
                'hasEndorsedSignature' => !empty($endorsedToSignature),
                'hasEndorsedDocProof' => !empty($endorsedDocProof)
            ]);
        } else {
            throw new Exception('Database error: ' . $stmt->error);
        }
        $stmt->close();
        $conn->close();
    } else {
        echo json_encode(['success' => false, 'errors' => ['Invalid request method.']]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'errors' => [$e->getMessage()]]);
} 