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

// Always initialize $userRows and $total_pages before any logic
$userRows = array();
$total_pages = 1;

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

// Fetch users from the database for display and live search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$base_query = "FROM users WHERE 1=1";
$params = [];
$types = '';
if (!empty($search)) {
    $base_query .= " AND (name LIKE ? OR email LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm]);
    $types .= 'ss';
}
$page = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;
$count_sql = "SELECT COUNT(*) as total $base_query";
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_users = $count_result ? $count_result->fetch_assoc()['total'] : 0;
$total_pages = max(1, ceil($total_users / $per_page));
$userRows = [];
$data_params = $params;
$data_types = $types;
$data_sql = "SELECT userName, passWord, usertype, name, email, contactno $base_query ORDER BY name ASC LIMIT ? OFFSET ?";
$data_params[] = $per_page;
$data_params[] = $offset;
$data_types .= 'ii';
$stmt = $conn->prepare($data_sql);
$stmt->bind_param($data_types, ...$data_params);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $userRows[] = $row;
    }
}
// AJAX: Only return table body if ajax=1
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    echo '<tbody id="usersTableBody">';
    foreach ($userRows as $user) {
        echo '<tr class="hover:bg-gray-50 transition-colors">';
        echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . htmlspecialchars($user['userName']) . '</td>';
        echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . htmlspecialchars($user['usertype']) . '</td>';
        echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . htmlspecialchars($user['name']) . '</td>';
        echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . htmlspecialchars($user['email']) . '</td>';
        echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . htmlspecialchars($user['contactno']) . '</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    exit;
}

// Add new live search filter UI above the table
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
                <!-- Search and Filter Bar -->
                <div class="bg-white p-4 mb-4 rounded-lg shadow-sm border border-gray-200">
                    <div class="flex flex-wrap gap-4">
                        <div class="flex-1 min-w-[200px]">
                            <label for="liveSearch" class="block text-sm font-medium text-gray-700 mb-1">Search Users</label>
                            <input type="text" id="liveSearch" name="search" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" placeholder="Search by name or email">
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full " id="userTable">
                            <thead class="bg-[rgba(240,240,240,0.51)] backdrop-blur border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Username</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">User Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Full Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Contact No</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody" class="bg-[rgb(197,197,197,0.1)] backdrop-blur-sm divide-y divide-gray-200">
                                <?php foreach ($userRows as $user): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($user['userName']); ?></td>
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
                <!-- Pagination Controls -->
                <div class="flex justify-center my-4">
                    <?php if ($total_pages > 1): ?>
                        <nav class="inline-flex -space-x-px">
                            <?php
                            // Build query string for filters/search, preserving all except page_num
                            $query_params = $_GET;
                            foreach(['page_num'] as $unset) unset($query_params[$unset]);
                            $base_query = http_build_query($query_params);
                            for ($i = 1; $i <= $total_pages; $i++):
                                $link = ($base_query ? "?{$base_query}&" : "?") . "page_num={$i}";
                            ?>
                                <a href="<?php echo $link; ?>" class="px-3 py-1 border border-gray-300 <?php echo $i == $page ? 'bg-blue-500 text-white' : 'bg-white text-gray-700'; ?> hover:bg-blue-100 mx-1 rounded">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                        </nav>
                    <?php endif; ?>
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
    <script>
document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById("liveSearch");
    let debounceTimeout;
    searchInput.addEventListener("input", function() {
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(() => {
            const query = encodeURIComponent(searchInput.value);
            fetch(`/dictproj1/App/Views/Pages/addUser.php?ajax=1&search=${query}`)
                .then((response) => response.text())
                .then((html) => {
                    document.querySelector("#usersTableBody").outerHTML = html;
                })
                .catch((err) => console.error("AJAX load failed:", err));
        }, 300); // 300ms debounce
    });
});
</script>
</body>
</html>
<?php $conn->close(); ?>
