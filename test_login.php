<?php
// Test script to debug login issues
session_start();

echo "<h2>Session Test</h2>";
echo "Session ID: " . session_id() . "<br>";
echo "Session Status: " . (session_status() == PHP_SESSION_ACTIVE ? "Active" : "Inactive") . "<br>";
echo "Session Data: <pre>" . print_r($_SESSION, true) . "</pre><br>";

// Test database connection
include_once 'App/Model/connect.php';

if ($conn->connect_error) {
    echo "Database connection failed: " . $conn->connect_error;
} else {
    echo "Database connection successful<br>";
    
    // Test users table
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Number of users in database: " . $row['count'] . "<br>";
    } else {
        echo "Error querying users table: " . $conn->error . "<br>";
    }
}

// Test session configuration
echo "<h2>Session Configuration</h2>";
echo "session.cookie_httponly: " . ini_get('session.cookie_httponly') . "<br>";
echo "session.use_only_cookies: " . ini_get('session.use_only_cookies') . "<br>";
echo "session.cookie_lifetime: " . ini_get('session.cookie_lifetime') . "<br>";
echo "session.gc_maxlifetime: " . ini_get('session.gc_maxlifetime') . "<br>";

// Test file paths
echo "<h2>File Paths</h2>";
echo "Current directory: " . __DIR__ . "<br>";
echo "Login file exists: " . (file_exists('App/Views/Pages/Login.php') ? "Yes" : "No") . "<br>";
echo "Dashboard file exists: " . (file_exists('App/Views/Pages/Dashboard.php') ? "Yes" : "No") . "<br>";
echo "dbLogin file exists: " . (file_exists('App/Model/dbLogin.php') ? "Yes" : "No") . "<br>";
?> 