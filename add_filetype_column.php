<?php
// Script to add filetype column to maindoc table
include 'App/Model/connect.php';

echo "<h2>Adding filetype column to maindoc table</h2>";

// Check if filetype column already exists
$check_column = $conn->query("SHOW COLUMNS FROM maindoc LIKE 'filetype'");
if ($check_column->num_rows > 0) {
    echo "✅ filetype column already exists<br>";
} else {
    // Add filetype column
    $add_column = "ALTER TABLE maindoc ADD COLUMN filetype VARCHAR(20) DEFAULT 'incoming' AFTER status";
    if ($conn->query($add_column) === TRUE) {
        echo "✅ filetype column added successfully<br>";
        
        // Update existing records to have 'incoming' as default
        $update_records = "UPDATE maindoc SET filetype = 'incoming' WHERE filetype IS NULL OR filetype = ''";
        if ($conn->query($update_records) === TRUE) {
            echo "✅ Updated existing records with default filetype<br>";
        } else {
            echo "❌ Error updating existing records: " . $conn->error . "<br>";
        }
    } else {
        echo "❌ Error adding filetype column: " . $conn->error . "<br>";
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

// Add endorsement fields to maindoc table
$sql = "ALTER TABLE maindoc
    ADD COLUMN endorsedToName VARCHAR(255) DEFAULT NULL,
    ADD COLUMN endorsedToSignature MEDIUMBLOB DEFAULT NULL,
    ADD COLUMN endorsedDocProof MEDIUMBLOB DEFAULT NULL,
    ADD COLUMN endorsedDocProof_filename VARCHAR(255) DEFAULT NULL,
    ADD COLUMN endorsedDocProof_mime_type VARCHAR(100) DEFAULT NULL;";
if ($conn->query($sql) === TRUE) {
    echo "Endorsement columns added successfully.";
} else {
    echo "Error adding columns: " . $conn->error;
}

$conn->close();
?> 