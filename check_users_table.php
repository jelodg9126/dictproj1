<?php
// Script to check users table structure and data
include_once 'App/Model/connect.php';

echo "<h2>Users Table Check</h2>";

// Check if users table exists
$result = $conn->query("SHOW TABLES LIKE 'users'");
if ($result->num_rows > 0) {
    echo "Users table exists<br>";
    
    // Show table structure
    echo "<h3>Table Structure:</h3>";
    $structure = $conn->query("DESCRIBE users");
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
    
    // Show sample data (without passwords)
    echo "<h3>Sample Users (without passwords):</h3>";
    $users = $conn->query("SELECT id, userName, created_at FROM users LIMIT 5");
    if ($users->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Username</th><th>Created</th></tr>";
        while ($row = $users->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['userName'] . "</td>";
            echo "<td>" . $row['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No users found in the table<br>";
    }
    
} else {
    echo "Users table does not exist!<br>";
    
    // Create users table if it doesn't exist
    echo "<h3>Creating users table...</h3>";
    $create_table = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        userName VARCHAR(50) UNIQUE NOT NULL,
        passWord VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($create_table)) {
        echo "Users table created successfully<br>";
        
        // Insert a test user
        $test_user = "INSERT INTO users (userName, passWord) VALUES ('admin', 'admin123')";
        if ($conn->query($test_user)) {
            echo "Test user 'admin' with password 'admin123' created<br>";
        } else {
            echo "Error creating test user: " . $conn->error . "<br>";
        }
    } else {
        echo "Error creating users table: " . $conn->error . "<br>";
    }
}

$conn->close();
?> 