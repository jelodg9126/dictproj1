<?php
// Script to add endorsement timestamp column to maindoc table
include 'App/Model/connect.php';

echo "<h2>Adding endorsement timestamp column to maindoc table</h2>";

// Check if endorsement timestamp column already exists
$check_column = $conn->query("SHOW COLUMNS FROM maindoc LIKE 'endorsementTimestamp'");
if ($check_column->num_rows > 0) {
    echo "✅ endorsementTimestamp column already exists<br>";
} else {
    // Add endorsement timestamp column
    $add_column = "ALTER TABLE maindoc ADD COLUMN endorsementTimestamp TIMESTAMP NULL DEFAULT NULL AFTER endorsedDocProof_mime_type";
    if ($conn->query($add_column) === TRUE) {
        echo "✅ endorsementTimestamp column added successfully<br>";
    } else {
        echo "❌ Error adding endorsementTimestamp column: " . $conn->error . "<br>";
    }
}

// Show updated table structure
echo "<h3>Updated Table Structure:</h3>";
$structure = $conn->query("DESCRIBE maindoc");
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

$conn->close();
?> 