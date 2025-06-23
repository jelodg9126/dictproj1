<?php
// Include database connection
include __DIR__ . '/../../Model/connect.php';

// Set timezone to Manila for display consistency
date_default_timezone_set('Asia/Manila');

// Check for success message
$show_success = isset($_GET['success']) && $_GET['success'] == '1';

// Get filter parameters
$search = $_GET['search'] ?? '';
$office_filter = $_GET['office'] ?? '';
$delivery_filter = $_GET['delivery'] ?? '';
$status_filter = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build the SQL query with filters
$sql = "SELECT * FROM maindoc WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (officeName LIKE ? OR senderName LIKE ? OR emailAdd LIKE ? OR courierName LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ssss";
}

if (!empty($office_filter)) {
    $sql .= " AND officeName = ?";
    $params[] = $office_filter;
    $types .= "s";
}

if (!empty($delivery_filter)) {
    $sql .= " AND modeOfDel = ?";
    $params[] = $delivery_filter;
    $types .= "s";
}

if (!empty($status_filter)) {
    $sql .= " AND status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($date_from)) {
    $sql .= " AND DATE(dateAndTime) >= ?";
    $params[] = $date_from;
    $types .= "s";
}

if (!empty($date_to)) {
    $sql .= " AND DATE(dateAndTime) <= ?";
    $params[] = $date_to;
    $types .= "s";
}

$sql .= " ORDER BY dateAndTime DESC";

// Prepare and execute the statement
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}

$result = $stmt->get_result();

// Filter by status if specified (after fetching results)
if (!empty($status_filter) && $result) {
    $filtered_rows = [];
    while ($row = $result->fetch_assoc()) {
        $submission_date = strtotime($row['dateAndTime']);
        $current_date = time();
        $days_diff = ($current_date - $submission_date) / (60 * 60 * 24);
        
        if ($days_diff > 7) {
            $status = 'received';
        } elseif ($days_diff > 0) {
            $status = 'pending';
        } else {
            $status = 'incoming';
        }
        
        if ($status === $status_filter) {
            $filtered_rows[] = $row;
        }
    }
    
    // Create a new result set with filtered data
    $result = new stdClass();
    $result->num_rows = count($filtered_rows);
    $result->data_seek = function($index) use ($filtered_rows) {
        $this->current_index = $index;
    };
    $result->fetch_assoc = function() use ($filtered_rows) {
        if (isset($this->current_index) && $this->current_index < count($filtered_rows)) {
            return $filtered_rows[$this->current_index++];
        }
        return false;
    };
    $result->data_seek(0);
}

// Get total count for display
$total_records = $result ? $result->num_rows : 0;

// Get unique offices for filter dropdown
$offices_sql = "SELECT DISTINCT officeName FROM maindoc ORDER BY officeName";
$offices_result = $conn->query($offices_sql);
$offices = [];
if ($offices_result) {
    while ($row = $offices_result->fetch_assoc()) {
        $offices[] = $row['officeName'];
    }
}

// Get unique statuses for filter dropdown
$statuses_sql = "SELECT DISTINCT status FROM maindoc WHERE status IS NOT NULL AND status != '' ORDER BY status";
$statuses_result = $conn->query($statuses_sql);
$statuses = [];
if ($statuses_result) {
    while ($row = $statuses_result->fetch_assoc()) {
        $statuses[] = $row['status'];
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
    <link rel="stylesheet" href="/dictproj1/public/assets/css/dashboard.css">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/modal.css">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/style.css">

    <title>Document</title>
</head>
<body>
    <div class="app-container">
        <?php include __DIR__ . '/../components/Sidebar.php'; ?>

        <div class="flex-1 p-6 bg-gray-50 min-h-screen overflow-y-auto  " id="docu">
            <div class="max-w-7xl mx-auto">
                <!-- Success Message -->
                <?php if ($show_success): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6" id="successMessage">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <strong>Success!</strong> Document record has been added successfully.
                        </div>
                        <button type="button" class="float-right text-green-700 hover:text-green-900" onclick="this.parentElement.parentElement.remove()">
                            <span class="text-2xl">&times;</span>
                        </button>
                    </div>
                    <script>
                        // Auto-hide success message after 5 seconds
                        setTimeout(function() {
                            const successMsg = document.getElementById('successMessage');
                            if (successMsg) {
                                successMsg.remove();
                            }
                        }, 5000);
                    </script>
                <?php endif; ?>

                <div class="flex items-center justify-between mb-6">
                    <div class="items-center">
                        <h1 class="text-3xl font-bold text-blue-800">Documents</h1>
                        <p class="text-gray-600 mt-2">Manage and track all incoming documents</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 flex items-center gap-2" id="filterToggle">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
                            </svg>
                            <span id="filterToggleText">Show Filters</span>
                        </button>
                        <button type="button" class="btn" id="openFormModal">Add New Record</button>
                    </div>
                </div>

                <!-- Search and Filter Section -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6" id="filterSection" style="display: none;">
                    <form method="GET" action="">
                        <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                            <div class="flex items-center gap-4 flex-1">
                                <div class="relative flex-1 max-w-md">
                                    <input
                                        type="text"
                                        name="search"
                                        placeholder="Search documents, sender, or recipient..."
                                        class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        value="<?php echo htmlspecialchars($search); ?>"
                                    />
                                </div>
                                <div class="flex items-center gap-2">
                                    <select
                                        name="office"
                                        class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">All Offices</option>
                                        <?php foreach ($offices as $office): ?>
                                            <option value="<?php echo htmlspecialchars($office); ?>" 
                                                    <?php echo $office_filter === $office ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($office); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select
                                        name="delivery"
                                        class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">All Modes</option>
                                        <option value="Courier" <?php echo $delivery_filter === 'Courier' ? 'selected' : ''; ?>>Courier</option>
                                        <option value="In-Person" <?php echo $delivery_filter === 'In-Person' ? 'selected' : ''; ?>>In-Person</option>
                                    </select>
                                    <select
                                        name="status"
                                        class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">All Status</option>
                                        <?php foreach ($statuses as $status): ?>
                                            <option value="<?php echo htmlspecialchars($status); ?>" 
                                                    <?php echo $status_filter === $status ? 'selected' : ''; ?>>
                                                <?php echo ucfirst(htmlspecialchars($status)); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">Filter</button>
                                <a href="/dictproj1/App/Views/Pages/Documents.php" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">Clear</a>
                                <span class="text-sm text-gray-600">
                                    <?php echo $total_records; ?> document<?php echo $total_records != 1 ? 's' : ''; ?>
                                </span>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Table Section -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Office
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Sender Name
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Email
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Delivery Mode
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Courier Name
                                    </th>
                                    
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>

                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date & Time
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while($row = $result->fetch_assoc()): ?>
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($row['officeName']); ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo htmlspecialchars($row['senderName']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo htmlspecialchars($row['emailAdd']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo htmlspecialchars($row['modeOfDel']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo htmlspecialchars($row['courierName'] ?: '-'); ?>
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo htmlspecialchars($row['status'] ?: '-'); ?>
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo date('M d, Y g:i A', strtotime($row['dateAndTime'])); ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-12">
                                            <div class="text-gray-500 text-lg">No documents found</div>
                                            <div class="text-gray-400 text-sm mt-2">Try adjusting your search or filter criteria</div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Form -->
    <div id="formModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Document Record</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <?php include __DIR__ . '/../../../modules/form_module.php'; ?>
            </div>
        </div>
    </div>

    <script src="/dictproj1/modal.js"></script>
    <script>
        // Filter toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const filterToggle = document.getElementById('filterToggle');
            const filterSection = document.getElementById('filterSection');
            const filterToggleText = document.getElementById('filterToggleText');
            
            if (filterToggle && filterSection) {
                filterToggle.addEventListener('click', function() {
                    const isVisible = filterSection.style.display !== 'none';
                    filterSection.style.display = isVisible ? 'none' : 'block';
                    
                    if (isVisible) {
                        filterToggleText.textContent = 'Show Filters';
                        filterToggle.classList.remove('bg-blue-500');
                        filterToggle.classList.add('bg-gray-500');
                    } else {
                        filterToggleText.textContent = 'Hide Filters';
                        filterToggle.classList.remove('bg-gray-500');
                        filterToggle.classList.add('bg-blue-500');
                    }
                });
            }
        });
    </script>
</body>
</html>

<?php
// Close database connection
$conn->close();
?> 