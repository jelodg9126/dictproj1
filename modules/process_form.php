<?php
include __DIR__ . '/../App/Core/database.php';

date_default_timezone_set('Asia/Manila');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Input data
    $office = trim($_POST['officeName'] ?? '');
    $sname = trim($_POST['senderName'] ?? '');
    $email = trim($_POST['emailAdd'] ?? '');
    $addressTo = trim($_POST['addressTo'] ?? '');
    $modeOfDel = trim($_POST['modeOfDel'] ?? '');
    $courierName = trim($_POST['courierName'] ?? '');
    $filetype = trim($_POST['filetype'] ?? '');
    $doctitle = trim($_POST['documentTitle'] ?? '');
    $dateAndTime = date("Y-m-d H:i:s");

    $signatureData = $_POST['signature'] ?? '';
    $signatureBlob = null;
    if (!empty($signatureData) && strpos($signatureData, 'data:image/png;base64,') === 0) {
        $signatureBlob = base64_decode(str_replace('data:image/png;base64,', '', $signatureData));
    }

    $status = 'Pending';

    $podBlob = null;
    $podFilename = null;
    $podMimeType = null;
    $maxPodSize = 5 * 1024 * 1024;
    $allowedPodTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];

    $errors = []; // ✅ Moved up

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

    // Validation
    if (empty($office)) $errors[] = "Office selection is required.";
    if (empty($sname)) $errors[] = "Sender name is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email address is required.";
    if (empty($addressTo)) $errors[] = "Receiving office is required.";
    if (empty($modeOfDel)) $errors[] = "Delivery mode selection is required.";
    if (empty($filetype) || !in_array($filetype, ['incoming', 'outgoing'])) $errors[] = "Document type selection is required.";
    if (empty($doctitle)) $errors[] = "Document title is required.";
    if ($modeOfDel === 'Courier' && empty($courierName)) $errors[] = "Courier name is required.";

    // Handle validation error
    if (!empty($errors)) {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        } else {
            echo "<html><body><div class='error-container'><ul class='error-list'>";
            foreach ($errors as $error) echo "<li>$error</li>";
            echo "</ul><a href='javascript:history.back()'>← Back</a></div></body></html>";
            exit;
        }
    }

    if ($modeOfDel !== 'Courier') $courierName = '';

    try {
        // ✅ CHANGED: PDO parameterized insert
        $stmt = $pdo->prepare("INSERT INTO maindoc
            (officeName, senderName, emailAdd, signature, addressTo, modeOfDel, courierName, dateAndTime, status, filetype, pod, pod_filename, pod_mime_type, doctitle)
            VALUES (:officeName, :senderName, :emailAdd, :signature, :addressTo, :modeOfDel, :courierName, :dateAndTime, :status, :filetype, :pod, :pod_filename, :pod_mime_type, :doctitle)");

        $stmt->bindParam(':officeName', $office);
        $stmt->bindParam(':senderName', $sname);
        $stmt->bindParam(':emailAdd', $email);
        $stmt->bindParam(':signature', $signatureBlob, PDO::PARAM_LOB);
        $stmt->bindParam(':addressTo', $addressTo);
        $stmt->bindParam(':modeOfDel', $modeOfDel);
        $stmt->bindParam(':courierName', $courierName);
        $stmt->bindParam(':dateAndTime', $dateAndTime);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':filetype', $filetype);
        $stmt->bindParam(':pod', $podBlob, PDO::PARAM_LOB);
        $stmt->bindParam(':pod_filename', $podFilename);
        $stmt->bindParam(':pod_mime_type', $podMimeType);
        $stmt->bindParam(':doctitle', $doctitle);

        if ($stmt->execute()) {
            if (session_status() === PHP_SESSION_NONE) session_start();
            if (isset($_SESSION['userID']) && isset($_SESSION['userAuthLevel'])) {
                include_once __DIR__ . '/../App/Model/log_audit.php';
                $user_id = $_SESSION['userID'];
                $role = $_SESSION['userAuthLevel'];
                $name = $_SESSION['name'] ?? null;
                $office_name = $_SESSION['office'] ?? null;

                // ✅ CHANGED: PDO fallback for name/office if not in session
                if (!$name || !$office_name) {
                    $stmtUser = $pdo->prepare("SELECT name, office FROM users WHERE userID = ?");
                    $stmtUser->execute([$user_id]);
                    if ($userData = $stmtUser->fetch(PDO::FETCH_ASSOC)) {
                        $name = $userData['name'];
                        $office_name = $userData['office'];
                    }
                }

                $officeDisplayNames = [/*...*/]; // (same as before)
                $officeLabel = $officeDisplayNames[$addressTo] ?? $addressTo;
                $action = "Sent document \"$doctitle\" to $officeLabel";

                // ✅ CHANGED: pass $action to audit function
                log_audit_action($pdo, $user_id, $name, $office_name, $role, $action);
            }

            $redirect_url = $filetype === 'incoming' ? '/dictproj1/index.php?page=incoming&success=1' :
                            ($filetype === 'outgoing' ? '/dictproj1/index.php?page=outgoing&success=1' :
                            '/dictproj1/index.php?page=documents&success=1');

            if (isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Record inserted successfully!', 'redirect' => $redirect_url]);
                exit;
            } else {
                header("Location: " . $redirect_url);
                exit;
            }

        } else {
            throw new Exception("Insert failed.");
        }

    } catch (Exception $e) {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'errors' => ['Database Error: ' . $e->getMessage()]]);
        } else {
            echo "<html><body><div class='error-container'>
                    <p class='error-message'>Error: " . htmlspecialchars($e->getMessage()) . "</p>
                    <a href='javascript:history.back()'>← Back</a>
                </div></body></html>";
        }
        exit;
    }

} else {
    $current_page = $_GET['page'] ?? 'documents';
    $redirect_url = $current_page === 'incoming' ? '/dictproj1/index.php?page=incoming' :
                    ($current_page === 'outgoing' ? '/dictproj1/index.php?page=outgoing' :
                    '/dictproj1/index.php?page=documents');
    header("Location: " . $redirect_url);
    exit;
}
?>
