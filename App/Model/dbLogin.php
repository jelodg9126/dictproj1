<?php

// Set session cookie parameters
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

// Start session and configure it properly
session_start();
session_regenerate_id(true); // Regenerate session ID for security

include_once '../Model/connect.php';

// Check if form was submitted
if (!isset($_POST['uNameLogin']) || !isset($_POST['pNameLogin'])) {
    header("Location: ../Views/Pages/Login.php?error=missing_data");
    exit();
}

// Sanitize inputs to prevent SQL injection
$username = mysqli_real_escape_string($conn, $_POST['uNameLogin']);
$password = mysqli_real_escape_string($conn, $_POST['pNameLogin']);

// Prepared Statement to check database
$stmt = $conn->prepare("SELECT * FROM users WHERE userName=? AND passWord=?");
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$result = $stmt->get_result();


if (!$result) {
    header("Location: ../Views/Pages/Login.php?error=db_error");
    exit();
}

if ($result && $result->num_rows > 0) {
    // If a match is found, session variables are set
    $row = $result->fetch_assoc();
    $_SESSION['uNameLogin'] = $row["userName"];
    $_SESSION['user_id'] = $row["id"]; // Assuming there's an id column
    $_SESSION['login_time'] = time();
    $_SESSION['userAuthLevel'] = $row['userType'];
    // Clear any output buffer to ensure clean redirect
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    switch($_SESSION['userAuthLevel']){
        case 'superAdmin':
            //redirect to dashboard
            header("Location: ../Views/Pages/Dashboard.php");
            exit();
        case 'provincial':
            //redirect to dashboard
            header("Location: ../Views/RegionalPages/DashboardPO.php");
            exit();                                                                   

    }
} else {
    // Clear any output buffer
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    header("Location: ../Views/Pages/Login.php?error=invalid_credentials");
    exit();
}

// Close Connection
mysqli_close($conn);
?>
==]]