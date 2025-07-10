<?php
// Debug script: Check audit_log user_id to users.userID mapping
include_once __DIR__ . '/../App/Model/connect.php';

echo "<h2>Audit Log User Match Check</h2>";
$sql = "SELECT a.audit_id, a.user_id, u.userID, u.name, u.office, u.userName FROM audit_log a LEFT JOIN users u ON a.user_id = u.userID ORDER BY a.timestamp DESC LIMIT 20";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Audit ID</th><th>Log user_id</th><th>users.userID</th><th>userName</th><th>name</th><th>office</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['audit_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['userID']) . "</td>";
        echo "<td>" . htmlspecialchars($row['userName']) . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['office']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No audit log records found or join failed.";
}
$conn->close();
?> 