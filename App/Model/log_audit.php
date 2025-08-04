
<?php
function log_audit_action(PDO $pdo, $user_id, $name, $office_name, $role, $action) {
    $stmt = $pdo->prepare("INSERT INTO audit_log (user_id, name, office_name, role, action, timestamp) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$user_id, $name, $office_name, $role, $action]);
}

// function log_audit_action($conn, $user_id, $name, $office_name, $role, $action) {
//     $stmt = $conn->prepare("INSERT INTO audit_log (user_id, name, office_name, role, action, timestamp) VALUES (?, ?, ?, ?, ?, NOW())");
//     $stmt->bind_param("issss", $user_id, $name, $office_name, $role, $action);
//     $stmt->execute();
//     $stmt->close();
// }
?> 
