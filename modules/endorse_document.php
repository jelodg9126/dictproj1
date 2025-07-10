<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('Asia/Manila');
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
        $currentTimestamp = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("UPDATE maindoc SET endorsedToName=?, endorsedToSignature=?, endorsedDocProof=?, endorsedDocProof_filename=?, endorsedDocProof_mime_type=?, endorsementTimestamp=?, status='Endorsed' WHERE transactionID=?");
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param(
            "ssbsssi",
            $endorsedToName,
            $signatureBlob,
            $null, // placeholder for blob
            $proofFilename,
            $proofMimeType,
            $currentTimestamp,
            $transactionID
        );
        // Send signature blob (index 1) and proof blob (index 2)
        $stmt->send_long_data(1, $signatureBlob);
        if ($proofBlob !== null) {
            $stmt->send_long_data(2, $proofBlob);
        }
        if ($stmt->execute()) {
            // Audit log: only for successful endorsement
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
                $action = "Endorsed a document to $endorsedToName";
                log_audit_action($conn, $user_id, $name, $office_name, $role, $action);
                if ($conn->error) error_log('Audit log insert error: ' . $conn->error);
            }
            // Trigger audit log refresh in parent window (if opened in a modal or iframe)
            // Only output JSON for AJAX requests as below.
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