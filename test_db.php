<?php
// Test database connection and table
include 'App/Model/connect.php';

echo "<h2>Database Connection Test</h2>";

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "✅ Database connection successful<br>";
}

// Check if table exists
$table_check = $conn->query("SHOW TABLES LIKE 'maindoc'");
if ($table_check->num_rows > 0) {
    echo "✅ Table 'maindoc' exists<br>";
    
    // Check table structure
    $structure = $conn->query("DESCRIBE maindoc");
    echo "<h3>Table Structure:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $structure->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check if there are any records
    $count = $conn->query("SELECT COUNT(*) as total FROM maindoc");
    $total = $count->fetch_assoc()['total'];
    echo "<br>📊 Total records in table: " . $total . "<br>";
    
    if ($total > 0) {
        echo "<h3>Sample Records:</h3>";
        $sample = $conn->query("SELECT * FROM maindoc LIMIT 3");
        echo "<table border='1'>";
        echo "<tr><th>Office</th><th>Sender</th><th>Email</th><th>Delivery</th><th>Date</th></tr>";
        while ($row = $sample->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['officeName']) . "</td>";
            echo "<td>" . htmlspecialchars($row['senderName']) . "</td>";
            echo "<td>" . htmlspecialchars($row['emailAdd']) . "</td>";
            echo "<td>" . htmlspecialchars($row['modeOfDel']) . "</td>";
            echo "<td>" . htmlspecialchars($row['dateAndTime']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} else {
    echo "❌ Table 'maindoc' does not exist<br>";
    
    // Create the table if it doesn't exist
    echo "<h3>Creating table 'maindoc'...</h3>";
    $create_table = "CREATE TABLE maindoc (
        id INT AUTO_INCREMENT PRIMARY KEY,
        officeName VARCHAR(255) NOT NULL,
        senderName VARCHAR(255) NOT NULL,
        emailAdd VARCHAR(255) NOT NULL,
        addressTo VARCHAR(255) NOT NULL,
        modeOfDel VARCHAR(50) NOT NULL,
        courierName VARCHAR(255),
        dateAndTime DATETIME NOT NULL,
        status VARCHAR(50) DEFAULT 'pending'
    )";
    
    if ($conn->query($create_table) === TRUE) {
        echo "✅ Table 'maindoc' created successfully<br>";
    } else {
        echo "❌ Error creating table: " . $conn->error . "<br>";
    }
}

$conn->close();
?> 