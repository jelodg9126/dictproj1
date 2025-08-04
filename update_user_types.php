<?php
// Script to update existing users with proper user types
include_once 'App/Model/connect.php';

echo "<h2>Update User Types</h2>";

// Check if usertype column exists
$check_column = $conn->query("SHOW COLUMNS FROM users LIKE 'usertype'");
if ($check_column->num_rows == 0) {
    echo "<p style='color: red;'>❌ usertype column does not exist</p>";
    
    // Add usertype column
    $add_column = "ALTER TABLE users ADD COLUMN usertype VARCHAR(20) DEFAULT 'Admin'";
    if ($conn->query($add_column)) {
        echo "<p style='color: green;'>✅ usertype column added successfully</p>";
    } else {
        echo "<p style='color: red;'>❌ Error adding usertype column: " . $conn->error . "</p>";
        exit();
    }
} else {
    echo "<p style='color: green;'>✅ usertype column already exists</p>";
}

// Get all users
$users = $conn->query("SELECT userID, userName, usertype FROM users");
echo "<h3>Current Users:</h3>";
echo "<table border='1'>";
echo "<tr><th>User ID</th><th>Username</th><th>Current User Type</th><th>Action</th></tr>";

while ($user = $users->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $user['userID'] . "</td>";
    echo "<td>" . htmlspecialchars($user['userName']) . "</td>";
    echo "<td>" . htmlspecialchars($user['usertype']) . "</td>";
    
    // Determine appropriate user type based on username
    $suggested_type = 'Admin'; // default
    
    $username_lower = strtolower($user['userName']);
    if (strpos($username_lower, 'dict') === 0) {
        // Provincial office users
        $suggested_type = 'provincial';
    } elseif ($username_lower === 'admin' || $username_lower === 'maindoc') {
        // Admin users
        $suggested_type = 'Admin';
    }
    
    if ($user['usertype'] === $suggested_type) {
        echo "<td style='color: green;'>✅ Correct</td>";
    } else {
        echo "<td style='color: orange;'>🔄 Should be: " . $suggested_type . "</td>";
        
        // Update the user type
        $update_stmt = $conn->prepare("UPDATE users SET usertype = ? WHERE userID = ?");
        $update_stmt->bind_param("si", $suggested_type, $user['userID']);
        if ($update_stmt->execute()) {
            echo "<td style='color: green;'>✅ Updated to " . $suggested_type . "</td>";
        } else {
            echo "<td style='color: red;'>❌ Update failed</td>";
        }
    }
    echo "</tr>";
}
echo "</table>";

// Show final user list
echo "<h3>Updated Users:</h3>";
$updated_users = $conn->query("SELECT userID, userName, usertype FROM users ORDER BY usertype, userName");
echo "<table border='1'>";
echo "<tr><th>User ID</th><th>Username</th><th>User Type</th></tr>";
while ($user = $updated_users->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $user['userID'] . "</td>";
    echo "<td>" . htmlspecialchars($user['userName']) . "</td>";
    echo "<td>" . htmlspecialchars($user['usertype']) . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<p><a href='test_user_validation.php'>Test User Validation</a></p>";
echo "<p><a href='App/Views/Pages/Login.php'>Go to Login</a></p>";

$conn->close();
?> 