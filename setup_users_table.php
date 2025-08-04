<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "documents";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create users table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Users table created successfully or already exists<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// Check if test user exists
$check_user = "SELECT * FROM users WHERE username = 'admin'";
$result = $conn->query($check_user);

if ($result->num_rows == 0) {
    // Insert test user
    $insert_user = "INSERT INTO users (username, password) VALUES ('admin', 'admin123')";
    if ($conn->query($insert_user) === TRUE) {
        echo "Test user created successfully<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
    } else {
        echo "Error creating test user: " . $conn->error . "<br>";
    }
} else {
    echo "Test user already exists<br>";
    echo "Username: admin<br>";
    echo "Password: admin123<br>";
}

$conn->close();
echo "<br><a href='App/Views/Pages/Login.php'>Go to Login Page</a>";
?> 