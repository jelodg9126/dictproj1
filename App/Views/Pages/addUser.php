<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Only allow superadmin
if (!isset($_SESSION['userAuthLevel']) || strtolower($_SESSION['userAuthLevel']) !== 'superadmin') {
    header('Location: /dictproj1/App/Views/Pages/Documents.php');
    exit();
}

include __DIR__ . '/../../Model/connect.php';

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
$page = max(1, $page); // Ensure page is at least 1
$offset = ($page - 1) * $records_per_page;

// Get total number of records
$count_query = "SELECT COUNT(*) as total FROM users";
$count_result = $conn->query($count_query);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Fetch users with pagination
$query = "SELECT * FROM users ORDER BY name ASC LIMIT ?, ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $offset, $records_per_page);
$stmt->execute();
$result = $stmt->get_result();
$userRows = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

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
        // Check if email already exists
        $checkEmailStmt = $conn->prepare("SELECT email FROM users WHERE BINARY email = ?");
        $checkEmailStmt->bind_param("s", $email);
        $checkEmailStmt->execute();
        $emailResult = $checkEmailStmt->get_result();

        if ($emailResult->num_rows > 0) {
            $message = "Error: Email already exists. Please use a different email address.";
            $checkEmailStmt->close();
        } else {
            $checkEmailStmt->close();
            // Check if username already exists
            $checkUserStmt = $conn->prepare("SELECT userName FROM users WHERE userName = ?");
            $checkUserStmt->bind_param("s", $userName);
            $checkUserStmt->execute();
            $userResult = $checkUserStmt->get_result();

            if ($userResult->num_rows > 0) {
                $message = "Error: Username already exists. Please choose a different username.";
                $checkUserStmt->close();
            } else {
                $checkUserStmt->close();
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
            }
        }
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
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="/dictproj1/src/input.css">
    <link rel="manifest" href="/dictproj1/manifest.json">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/dashboard.css">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/modal.css">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/style.css">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/addUser.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" href="/dictproj1/public/assets/images/mainCircle.png" type="image/png">
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
                    <div class="flex items-center gap-3">
                        <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 flex items-center gap-2" id="filterToggle">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
                            </svg>
                            <span id="filterToggleText">Show Filters</span>
                        </button>
                        <button type="button" class="btn bg-blue-600 text-white px-10 py-3 text-md rounded-lg hover:bg-blue-700 flex items-center gap-2" id="openFormModal">
                            <i class="fas fa-user-plus"></i> Add User
                        </button>
                    </div>
                </div>

                <!-- Search and Filter Section -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6 hidden" id="filterSection">
                    <form id="filterForm">
                        <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                            <div class="flex items-center gap-4 flex-1">
                                <div class="relative flex-1 max-w-md">
                                    <input
                                        type="text"
                                        name="search"
                                        id="search"
                                        class="w-full pl-4 pr-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                        placeholder="Search by name, username, email..."
                                    />
                                </div>
                                <div class="flex items-center gap-2">
                                    <select name="usertype" id="userType" class="border rounded-lg text-sm px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                        <option value="">All User Types</option>
                                        <option value="Superadmin">Superadmin</option>
                                        <option value="Admin">Admin</option>
                                        <option value="Provincial">Provincial</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" id="clearFiltersBtn" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 text-sm">Clear</button>
                                <span class="text-sm text-gray-600" id="recordCount"></span>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto ">
                        <table class="w-full ">
                            <thead class="bg-[rgba(240,240,240,0.51)] backdrop-blur border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Username</th>
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
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($user['usertype']); ?></td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($user['name']); ?></td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($user['email']); ?></td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($user['contactno']); ?></td>
      </tr>
        <?php endforeach; ?>
    </tbody>

                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination Controls -->
                    <div class="bg-white p-4 rounded-b-lg border-t border-gray-200">
                        <div class="flex justify-center">
                    <?php if ($total_pages > 1): ?>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <?php
                            // Build query string for filters/search
                            $query_params = $_GET;
                            unset($query_params['page_num']);
                            $base_query = http_build_query($query_params);
                            $base_url = '?' . $base_query . ($base_query ? '&' : '');
                            
                            // First page link
                            if ($page > 1): ?>
                                <a href="<?php echo $base_url . 'page_num=1'; ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">First</span>
                                    &laquo;
                                </a>
                                <a href="<?php echo $base_url . 'page_num=' . ($page - 1); ?>" class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Previous</span>
                                    &lsaquo;
                                </a>
                            <?php endif; ?>
                            
                            <?php 
                            // Page number links
                            $start = max(1, $page - 2);
                            $end = min($total_pages, $start + 4);
                            $start = max(1, $end - 4);
                            
                            for ($i = $start; $i <= $end; $i++):
                                $is_first = $i === 1 && $page === 1;
                                $is_last = $i === $total_pages && $page === $total_pages;
                                $is_active = $i == $page;
                                $classes = [
                                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                    $is_active ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50',
                                    $is_first ? 'rounded-l-md' : '',
                                    ($i === $total_pages && $page === $total_pages) ? 'rounded-r-md' : ''
                                ];
                                $link = $base_url . 'page_num=' . $i;
                            ?>
                                <a href="<?php echo $link; ?>" class="<?php echo implode(' ', array_filter($classes)); ?>" <?php echo $is_active ? 'aria-current="page"' : ''; ?>>
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php // Next and Last page links
                            if ($page < $total_pages): ?>
                                <a href="<?php echo $base_url . 'page_num=' . ($page + 1); ?>" class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Next</span>
                                    &rsaquo;
                                </a>
                                <a href="<?php echo $base_url . 'page_num=' . $total_pages; ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Last</span>
                                    &raquo;
                                </a>
                            <?php endif; ?>
                            </nav>
                        <?php endif; ?>
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
                                <div class="relative">
                                    <input type="password" id="passWord" name="passWord" class="form-control rounded-lg border border-gray-300 px-4 py-2 pr-10 w-full" required>
                                    <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                </div>
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
    <script src="/dictproj1/public/Scripts/superadmin/addUser.js"></script>
</body>
</html>
<?php $conn->close(); ?>