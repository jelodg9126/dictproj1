<?php
// session_start();

// include_once __DIR__ . '/connect.php';

// if (isset($_SESSION['userID'])) {
//     $user_id = $_SESSION['userID'];

//     //database or model
//     $stmt = $conn->prepare("UPDATE log_history 
//                             SET logout_time = NOW() WHERE user_id = ? 
//                             AND logout_time IS NULL ORDER BY login_time 
//                             DESC LIMIT 1");
//     $stmt->bind_param("i", $user_id);
//     $stmt->execute();
//     $stmt->close();

// }

// // Unset all session variables
// $_SESSION = array();

// // Destroy the session
// session_destroy();

// // Redirect to login page
// header("Location: ../Views/Pages/Login.php");
// exit();
