<?php
// Test script to verify user type validation
session_start();

echo "<h2>User Type Validation Test</h2>";

// Check if user is logged in
if (!isset($_SESSION['uNameLogin'])) {
    echo "<p style='color: red;'>❌ User is NOT logged in</p>";
    echo "<p><a href='App/Views/Pages/Login.php'>Go to Login</a></p>";
} else {
    echo "<p style='color: green;'>✅ User is logged in: " . htmlspecialchars($_SESSION['uNameLogin']) . "</p>";
    
    // Check if userAuthLevel is set
    if (!isset($_SESSION['userAuthLevel'])) {
        echo "<p style='color: red;'>❌ userAuthLevel is NOT SET</p>";
    } else {
        echo "<p style='color: green;'>✅ userAuthLevel is set: " . htmlspecialchars($_SESSION['userAuthLevel']) . "</p>";
        
        // Test different user types
        switch(strtolower($_SESSION['userAuthLevel'])) {
            case 'superadmin':
                echo "<p style='color: blue;'>🔵 User type: Super Admin</p>";
                echo "<p>Access level: Full access to all features</p>";
                break;
            case 'provincial':
                echo "<p style='color: blue;'>🔵 User type: Provincial</p>";
                echo "<p>Access level: Limited to provincial office features</p>";
                break;
            default:
                echo "<p style='color: orange;'>🟠 User type: " . htmlspecialchars($_SESSION['userAuthLevel']) . " (Unknown)</p>";
                echo "<p>Access level: Defaulting to Super Admin</p>";
                break;
        }
    }
}

// Show all session variables for debugging
echo "<h3>Session Variables:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Test database connection and user table
echo "<h3>Database Check:</h3>";
include_once 'App/Model/connect.php';

$result = $conn->query("SHOW TABLES LIKE 'users'");
if ($result->num_rows > 0) {
    echo "<p style='color: green;'>✅ Users table exists</p>";
    
    // Check table structure
    $structure = $conn->query("DESCRIBE users");
    echo "<h4>Table Structure:</h4>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $structure->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check current user in database
    if (isset($_SESSION['uNameLogin'])) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE userName = ?");
        $stmt->bind_param("s", $_SESSION['uNameLogin']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo "<h4>Current User in Database:</h4>";
            echo "<p>Username: " . htmlspecialchars($user['userName']) . "</p>";
            echo "<p>User ID: " . htmlspecialchars($user['userID']) . "</p>";
            echo "<p>User Type: " . htmlspecialchars($user['usertype']) . "</p>";
        } else {
            echo "<p style='color: red;'>❌ User not found in database</p>";
        }
    }
} else {
    echo "<p style='color: red;'>❌ Users table does not exist</p>";
}

$conn->close();
?> 