<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('Asia/Manila');

include __DIR__ . '/../App/Core/database.php';
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

        // Handle camera capture
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
            }
            if (!in_array($_FILES['endorsedDocProof']['type'], $allowedProofTypes)) {
                echo json_encode(['success' => false, 'errors' => ['Invalid file type for proof.']]);
                exit;
            }
            $proofBlob = file_get_contents($_FILES['endorsedDocProof']['tmp_name']);
            $proofFilename = $_FILES['endorsedDocProof']['name'];
            $proofMimeType = $_FILES['endorsedDocProof']['type'];
        }

        $errors = [];
        if (empty($transactionID)) $errors[] = 'Transaction ID required.';
        if (empty($endorsedToName)) $errors[] = 'Endorsed To Name required.';
        if (empty($signatureData) || strpos($signatureData, 'data:image/png;base64,') !== 0) {
            $errors[] = 'Valid signature required.';
        }

        if (!empty($errors)) {
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }

        $signatureBlob = base64_decode(str_replace('data:image/png;base64,', '', $signatureData));
        $currentTimestamp = date('Y-m-d H:i:s');

        // Fetch doctitle for logging
        $doctitle = '';
        $stmtTitle = $pdo->prepare('SELECT doctitle FROM maindoc WHERE transactionID = ?');
        $stmtTitle->execute([$transactionID]);
        $doctitle = $stmtTitle->fetchColumn();

        // Update the main document
        $stmt = $pdo->prepare("
            UPDATE maindoc SET 
                endorsedToName = ?, 
                endorsedToSignature = ?, 
                endorsedDocProof = ?, 
                endorsedDocProof_filename = ?, 
                endorsedDocProof_mime_type = ?, 
                endorsementTimestamp = ?, 
                status = 'Endorsed' 
            WHERE transactionID = ?
        ");

        $stmt->bindValue(1, $endorsedToName, PDO::PARAM_STR);
        $stmt->bindValue(2, $signatureBlob, PDO::PARAM_LOB);
        $stmt->bindValue(3, $proofBlob, PDO::PARAM_LOB);
        $stmt->bindValue(4, $proofFilename, PDO::PARAM_STR);
        $stmt->bindValue(5, $proofMimeType, PDO::PARAM_STR);
        $stmt->bindValue(6, $currentTimestamp, PDO::PARAM_STR);
        $stmt->bindValue(7, $transactionID, PDO::PARAM_STR);

        $stmt->execute();

        // Audit logging
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (isset($_SESSION['userID'], $_SESSION['userAuthLevel'])) {
            include_once __DIR__ . '/../App/Model/log_audit.php';

            $user_id = $_SESSION['userID'];
            $role = $_SESSION['userAuthLevel'];
            $name = $_SESSION['name'] ?? null;
            $office_name = $_SESSION['office'] ?? null;

            if (!$name || !$office_name) {
                $stmtUser = $pdo->prepare('SELECT name, office FROM users WHERE userID = ?');
                $stmtUser->execute([$user_id]);
                list($name, $office_name) = $stmtUser->fetch(PDO::FETCH_NUM);
            }

            $action = $doctitle
                ? "Endorsed document \"$doctitle\" to $endorsedToName"
                : "Endorsed a document to $endorsedToName";

            log_audit_action($pdo, $user_id, $name, $office_name, $role, $action);
        }

        // Fetch updated endorsement data
        $fetch = $pdo->prepare('SELECT endorsedToName, endorsedToSignature, endorsedDocProof FROM maindoc WHERE transactionID = ?');
        $fetch->execute([$transactionID]);
        $result = $fetch->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'message' => 'Document endorsed successfully!',
            'endorsedToName' => $result['endorsedToName'],
            'hasEndorsedSignature' => !empty($result['endorsedToSignature']),
            'hasEndorsedDocProof' => !empty($result['endorsedDocProof']),
        ]);
    } else {
        echo json_encode(['success' => false, 'errors' => ['Invalid request method.']]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'errors' => [$e->getMessage()]]);
}
