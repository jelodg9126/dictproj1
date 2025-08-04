<?php
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }
// // Only allow superadmin
// if (!isset($_SESSION['userAuthLevel']) || strtolower($_SESSION['userAuthLevel']) !== 'superadmin') {
//     header('Location: /dictproj1/App/Views/Pages/Documents.php');
//     exit();
// }

// include __DIR__ . '/../../Model/connect.php';

// // Set current page for sidebar highlighting
// $current_page = 'addUser';

// // Handle form submission
// $message = '';
// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     $userName = trim($_POST['userName'] ?? '');
//     $passWord = trim($_POST['passWord'] ?? '');
//     $usertype = trim($_POST['usertype'] ?? '');
//     $name = trim($_POST['name'] ?? '');
//     $email = trim($_POST['email'] ?? '');
//     $contactno = trim($_POST['contactno'] ?? '');

//     // Basic validation (add more as needed)
//     if ($userName && $passWord && $usertype && $name && $email && $contactno) {
//         // Hash the password before storing
//         $hashedPassword = password_hash($passWord, PASSWORD_DEFAULT);
        
//         $stmt = $conn->prepare("INSERT INTO users (userName, passWord, usertype, name, email, contactno) VALUES (?, ?, ?, ?, ?, ?)");
//         $stmt->bind_param("ssssss", $userName, $hashedPassword, $usertype, $name, $email, $contactno);
        
//         if ($stmt->execute()) {
//             $message = "User added successfully!";
//             // Clear form fields on success
//             $_POST = array();
//         } else {
//             $message = "Error: " . $stmt->error;
//         }
//         $stmt->close();
//     } else {
//         $message = "Error: Please fill in all required fields.";
//     }
// }

// // Fetch all users for display in a table (excluding userID)
// $userRows = [];
// $result = $conn->query("SELECT userName, passWord, usertype, name, email, contactno FROM users");
// if ($result && $result->num_rows > 0) {
//     while ($row = $result->fetch_assoc()) {
//         $userRows[] = $row;
//     }
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="/dictproj1/src/input.css">
    <link rel="manifest" href="/dictproj1/manifest.json">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/dashboard.css">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/modal.css">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/style.css">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/addUser.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Add Users</title>
</head>
<body>
    <div class="app-container">
        <?php include __DIR__ . '/../components/Sidebar.php'; ?>
        <div class="flex-1 p-6 bg-linear-90 from-[#48517f] to-[#322b5f] min-h-screen overflow-y-auto" id="docu">
            <div class="max-w-7xl mx-auto">
                <div class="flex items-center justify-between mb-6">
                    <div class="items-center">
                        <h1 class="text-3xl font-bold text-indigo-500">Users</h1>
                        <p class="text-gray-300 mt-2">Manage and track all users</p>
                    </div>
                    <button type="button" class="btn bg-blue-600 text-white px-10 py-3 text-md rounded-lg hover:bg-blue-700 flex items-center gap-2" id="openFormModal">
                        <i class="fas fa-user-plus"></i> Add User
                    </button>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto ">
                        <table class="w-full ">
                            <thead class="bg-[rgba(240,240,240,0.51)] backdrop-blur border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Username</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Password (Hashed)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">User Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Full Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Contact No</th>
                                </tr>
                            </thead>
                           
    <tbody id="usersTableBody" class="bg-[rgba(197,197,197,0.1)] backdrop-blur-sm divide-y divide-gray-200">
         <?php foreach ($userRows as $user): ?>
      <tr>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($user['userName']); ?></td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900"><?php echo htmlspecialchars($user['passWord']); ?></td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($user['usertype']); ?></td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($user['name']); ?></td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($user['email']); ?></td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($user['contactno']); ?></td>
      </tr>
        <?php endforeach; ?>
    </tbody>

                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal for Add User Form -->
        <div id="formModal" class="modal">
            <div class="modal-content" style="max-width: 600px;">
                <div class="modal-header flex justify-between items-center p-6 pb-6">
                    <h2 class="text-2xl font-bold text-blue-900">Add New User</h2>
                    <span class="close cursor-pointer text-3xl">&times;</span>
                </div>
                <div class="modal-body p-6 pt-0">
                    <form id="addUserForm" method="post" action="" autocomplete="off">
                        <div class="form-row mb-2 mt-6">
                            <div class="form-group">
                                <label for="userName">Username <span class="required">*</span></label>
                                <input type="text" id="userName" name="userName" class="form-control rounded-lg border border-gray-300 px-4 py-2" required>
                            </div>
                    <div class="form-group">
                                <label for="passWord">Password <span class="required">*</span></label>
                                <input type="password" id="passWord" name="passWord" class="form-control rounded-lg border border-gray-300 px-4 py-2" required>
                            </div>
                    </div>
                        <div class="form-row mb-2">
                    <div class="form-group">
                                <label for="usertype">User Type <span class="required">*</span></label>
                                <select id="usertype" name="usertype" class="form-control rounded-lg border border-gray-300 px-4 py-2" required>
                            <option value="">Select user type</option>
                                    <option value="Admin">Admin</option>
                            <option value="provincial">Provincial</option>
                        </select>
                    </div>
                    <div class="form-group">
                                <label for="name">Full Name <span class="required">*</span></label>
                                <input type="text" id="name" name="name" class="form-control rounded-lg border border-gray-300 px-4 py-2" required>
                            </div>
                    </div>
                        <div class="form-row mb-1">
                    <div class="form-group">
                                <label for="email">Email Address <span class="required">*</span></label>
                                <input type="email" id="email" name="email" class="form-control rounded-lg border border-gray-300 px-4 py-2" required>
                    </div>
                    <div class="form-group">
                                <label for="contactno">Contact Number <span class="required">*</span></label>
                                <input type="text" id="contactno" name="contactno" class="form-control rounded-lg border border-gray-300 px-4 py-2" required>
                            </div>
                    </div>
                        <div class="flex justify-end mt-6">
                            <button type="submit" class="btn btn-primary bg-blue-600 text-white px-16 py-2 text-lg rounded-lg hover:bg-blue-700">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
        <script src="/dictproj1/modal.js"></script>
    <script src="/dictproj1/public/assets/Scripts/addUser.js"></script>
</body>
</html>

