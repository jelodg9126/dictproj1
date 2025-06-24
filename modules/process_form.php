<?php
// Process Form Module - Handles form submission
// This module can be used for both AJAX and regular form submissions

// Include database connection
include __DIR__ . '/../App/Model/connect.php';

// Set timezone to Manila
date_default_timezone_set('Asia/Manila');

// Check if form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate and sanitize input data
    $office = trim($_POST['officeName'] ?? '');
    $sname = trim($_POST['senderName'] ?? '');
    $email = trim($_POST['emailAdd'] ?? '');
    $addressTo = trim($_POST['addressTo'] ?? '');
    $modeOfDel = trim($_POST['modeOfDel'] ?? '');
    $courierName = trim($_POST['courierName'] ?? '');
    $dateAndTime = date("Y-m-d H:i:s");
    
    // Signature extraction and decoding
    $signatureData = $_POST['signature'] ?? '';
    $signatureBlob = null;
    if (!empty($signatureData) && strpos($signatureData, 'data:image/png;base64,') === 0) {
        $signatureBlob = base64_decode(str_replace('data:image/png;base64,', '', $signatureData));
    }
    $status = 'Pending';
    $filetype = '';
    
    // POD file handling
    $podBlob = null;
    $podFilename = null;
    $podMimeType = null;
    $maxPodSize = 5 * 1024 * 1024; // 5MB
    $allowedPodTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!isset($_FILES['podFile']) || $_FILES['podFile']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Proof of Document (POD) file is required.";
    } else if ($_FILES['podFile']['size'] > $maxPodSize) {
        $errors[] = "Proof of Document (POD) file must be 5MB or less.";
    } else if (!in_array($_FILES['podFile']['type'], $allowedPodTypes)) {
        $errors[] = "Only image files (JPG, PNG, GIF, WEBP) are allowed for Proof of Document (POD).";
    } else {
        $podBlob = file_get_contents($_FILES['podFile']['tmp_name']);
        $podFilename = $_FILES['podFile']['name'];
        $podMimeType = $_FILES['podFile']['type'];
    }
    
    // Validation
    $errors = [];
    
    if (empty($office)) {
        $errors[] = "Office selection is required.";
    }
    
    if (empty($sname)) {
        $errors[] = "Sender name is required.";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email address is required.";
    }
    
    if (empty($addressTo)) {
        $errors[] = "Receiving office is required.";
    }
    
    if (empty($modeOfDel)) {
        $errors[] = "Delivery mode selection is required.";
    }
    
    // Check if courier name is required when courier delivery is selected
    if ($modeOfDel === 'Courier' && empty($courierName)) {
        $errors[] = "Courier name is required when selecting Courier delivery mode.";
    }
    
    // If there are validation errors
    if (!empty($errors)) {
        // Check if this is an AJAX request
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'errors' => $errors
            ]);
            exit;
        } else {
            // Regular form submission - show error page
            echo "<!DOCTYPE html>
            <html lang='en'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Validation Errors</title>
                <style>
                    body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
                    .error-container { background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; padding: 20px; }
                    .error-list { color: #721c24; }
                    .back-link { margin-top: 20px; }
                    .back-link a { color: #007bff; text-decoration: none; }
                </style>
            </head>
            <body>
                <div class='error-container'>
                    <h2>Please correct the following errors:</h2>
                    <ul class='error-list'>";
            
            foreach ($errors as $error) {
                echo "<li>$error</li>";
            }
            
            echo "</ul>
                    <div class='back-link'>
                        <a href='javascript:history.back()'>← Back to Form</a>
                    </div>
                </div>
            </body>
            </html>";
            exit;
        }
    }
    
    // If courier delivery is not selected, set courier name to empty string
    if ($modeOfDel !== 'Courier') {
        $courierName = '';
    }
    
    try {
        // Prepare the SQL statement with correct column order
        $stmt = $conn->prepare("INSERT INTO maindoc
            (officeName, senderName, emailAdd, signature, addressTo, modeOfDel, courierName, dateAndTime, status, filetype, pod, pod_filename, pod_mime_type)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        // Bind parameters (all as strings for MySQLi, including blobs)
        $stmt->bind_param("sssssssssssss", 
            $office, $sname, $email, $signatureBlob, $addressTo, $modeOfDel, $courierName, $dateAndTime, $status, $filetype, $podBlob, $podFilename, $podMimeType
        );
        
        // Execute the statement
        if ($stmt->execute()) {
            // Check if this is an AJAX request
            if (isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Record inserted successfully!',
                    'redirect' => '/dictproj1/App/Views/Pages/Documents.php'
                ]);
                exit;
            } else {
                // Regular form submission - redirect immediately
                header("Location: /dictproj1/App/Views/Pages/Documents.php?success=1");
                exit;
            }
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        // Close statement
        $stmt->close();
        
    } catch (Exception $e) {
        // Check if this is an AJAX request
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'errors' => ['Database Error: ' . $e->getMessage()]
            ]);
            exit;
        } else {
            // Regular form submission - show error page
            echo "<!DOCTYPE html>
            <html lang='en'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Database Error</title>
                <style>
                    body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
                    .error-container { background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; padding: 20px; }
                    .error-message { color: #721c24; }
                    .back-link { margin-top: 20px; }
                    .back-link a { color: #007bff; text-decoration: none; }
                </style>
            </head>
            <body>
                <div class='error-container'>
                    <h2>Database Error</h2>
                    <p class='error-message'>Error: " . htmlspecialchars($e->getMessage()) . "</p>
                    <div class='back-link'>
                        <a href='javascript:history.back()'>← Back to Form</a>
                    </div>
                </div>
            </body>
            </html>";
        }
    }
    
    // Close database connection
    $conn->close();
    
} else {
    // If accessed directly without POST data, redirect to view records
    header("Location: /dictproj1/App/Views/Pages/Documents.php");
    exit;
}
?> 