<?php
// Database connection (update credentials as needed)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "documents";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userName = trim($_POST['userName'] ?? '');
    $passWord = trim($_POST['passWord'] ?? '');
    $usertype = trim($_POST['usertype'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contactno = trim($_POST['contactno'] ?? '');

    // Basic validation (add more as needed)
    if ($userName && $passWord && $usertype && $name && $email && $contactno) {
        $stmt = $conn->prepare("INSERT INTO users (userName, passWord, usertype, name, email, contactno) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $userName, $passWord, $usertype, $name, $email, $contactno);
        if ($stmt->execute()) {
            $message = "User added successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add User</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .form-container { max-width: 400px; margin: auto; padding: 24px; border: 1px solid #ccc; border-radius: 8px; background: #f9f9f9; }
        label { display: block; margin-top: 12px; }
        input, select { width: 100%; padding: 8px; margin-top: 4px; box-sizing: border-box; }
        button { margin-top: 16px; padding: 10px 20px; background: #007bff; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .message { margin-top: 16px; color: green; }
        .error { color: red; }
    </style>
</head>
<body>
<div class="form-container">
    <h2>Add User</h2>
    <?php if ($message): ?>
        <div class="message<?php echo strpos($message, 'Error') !== false ? ' error' : ''; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <form method="post" action="">
        <label for="userName">Username:</label>
        <input type="text" id="userName" name="userName" required>

        <label for="passWord">Password:</label>
        <input type="password" id="passWord" name="passWord" required>

        <label for="usertype">User Type:</label>
        <select id="usertype" name="usertype" required>
            <option value="">Select type</option>
            <option value="superAdmin">SuperAdmin</option>
            <option value="provincial">provincial</option>
        </select>

        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="contactno">Contact No:</label>
        <input type="text" id="contactno" name="contactno" required>

        <button type="submit">Add User</button>
    </form>
</div>
</body>
</html>
<?php $conn->close(); ?>
