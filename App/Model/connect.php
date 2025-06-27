<?php
$servername = "localhost"; // usually 'localhost'
$username = "root";        // default username for local servers
$password = "";            // default is blank in XAMPP
$database = "documents";   // your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// This is a migration step, not PHP code. Please run the following SQL in your database:
// ALTER TABLE maindoc ADD COLUMN receiver_signature LONGBLOB NULL AFTER signature;

?>
