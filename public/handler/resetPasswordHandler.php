<?php
session_start();
require_once '../../App/Model/AuthModel.php';
require_once '../../App/Core/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['userID'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
    exit;
}

$userID = $_SESSION['userID'];
$newPassword = $_POST['newPassword'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

if ($newPassword !== $confirmPassword) {
    echo json_encode(['status' => 'error', 'message' => 'Passwords do not match']);
    exit;
}

if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $newPassword)) {
    echo json_encode(['status' => 'error', 'message' => 'Password does not meet complexity requirements']);
    exit;
}

try {
    $model = new AuthModel($pdo);
    $success = $model->updatePassword($userID, $newPassword);

    if ($success) {
        echo json_encode(['status' => 'success', 'message' => 'Password updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update password']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
