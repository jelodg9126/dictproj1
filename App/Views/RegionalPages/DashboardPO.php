<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['uNameLogin'])) {
    header("Location: ../Pages/Login.php");
    exit();
}

// Redirect all users to the main dashboard
header("Location: ../Pages/Dashboard.php");
exit();