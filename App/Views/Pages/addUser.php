<?php
session_start();
// Only allow superadmin
if (!isset($_SESSION['userAuthLevel']) || strtolower($_SESSION['userAuthLevel']) !== 'superadmin') {
    header('Location: /dictproj1/App/Views/Pages/Documents.php');
    exit();
}

include __DIR__ . '/../../Model/connect.php';

// Set current page for sidebar highlighting
$current_page = 'addUser';

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
        // Hash the password before storing
        $hashedPassword = password_hash($passWord, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (userName, passWord, usertype, name, email, contactno) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $userName, $hashedPassword, $usertype, $name, $email, $contactno);
        
        if ($stmt->execute()) {
            $message = "User added successfully!";
            // Clear form fields on success
            $_POST = array();
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Error: Please fill in all required fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/dictproj1/public/Scripts/pwa-init.js"></script>
    <link rel="manifest" href="/dictproj1/manifest.json">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/dashboard.css">
    <link rel="stylesheet" href="/dictproj1/src/input.css">
    <title>Add New User</title>
    <style>
        .app-container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }
        .main-content {
            flex: 1;
            padding: 2rem;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            background: 
            linear-gradient(90deg, #48517f 0%, #322b5f 100%);
            background-size: 100% 100%, 20px 20px;
            min-height: 100vh;
        }
        .form-container {
            width: 100%;
            max-width: 800px;
            margin: 0;
            padding: 2.5rem;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #4a5568;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            background-color: rgba(255, 255, 255, 0.9);
            transition: all 0.3s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
            background-color: #fff;
        }
        .btn-primary {
            background: linear-gradient(90deg, #4f46e5 0%, #6366f1 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .message {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
        }
        .message.success {
            background-color: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }
        .message.error {
            background-color: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }
        .form-actions {
            grid-column: 1 / -1;
            display: flex;
            justify-content: flex-end;
            margin-top: 1rem;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="app-container">
        <?php include __DIR__ . '/../components/Sidebar.php'; ?>
        
        <div class="main-content">
            <div class="form-container">
                <h1 class="text-2xl font-bold text-gray-800 mb-6">Add New User</h1>
                
                <?php if ($message): ?>
                    <div class="message <?php echo strpos($message, 'Error') !== false ? 'error' : 'success'; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="" class="form-grid">
                    <div class="form-group">
                        <label for="userName">Username</label>
                        <input type="text" id="userName" name="userName" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="passWord">Password</label>
                        <input type="password" id="passWord" name="passWord" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="usertype">User Type</label>
                        <select id="usertype" name="usertype" class="form-control" required>
                            <option value="">Select user type</option>
                            <option value="superAdmin">Super Admin</option>
                            <option value="provincial">Provincial</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="contactno">Contact Number</label>
                        <input type="text" id="contactno" name="contactno" class="form-control" required>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-user-plus mr-2"></i>Add User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
<?php $conn->close(); ?>
