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
$count_sql = "SELECT COUNT(*) as total FROM maindoc WHERE 1=1";
$params = [];
$types = "";
$count_params = [];
$count_types = "";

if (!empty($search)) {
    $sql .= " AND (officeName LIKE ? OR senderName LIKE ? OR emailAdd LIKE ? OR courierName LIKE ?)";
    $count_sql .= " AND (officeName LIKE ? OR senderName LIKE ? OR emailAdd LIKE ? OR courierName LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ssss";
    $count_params[] = $search_param;
    $count_params[] = $search_param;
    $count_params[] = $search_param;
    $count_params[] = $search_param;
    $count_types .= "ssss";
}

if (!empty($office_filter)) {
    $sql .= " AND officeName = ?";
    $count_sql .= " AND officeName = ?";
    $params[] = $office_filter;
    $types .= "s";
    $count_params[] = $office_filter;
    $count_types .= "s";
}

if (!empty($delivery_filter)) {
    $sql .= " AND modeOfDel = ?";
    $count_sql .= " AND modeOfDel = ?";
    $params[] = $delivery_filter;
    $types .= "s";
    $count_params[] = $delivery_filter;
    $count_types .= "s";
}

if (!empty($status_filter)) {
    $sql .= " AND status = ?";
    $count_sql .= " AND status = ?";
    $params[] = $status_filter;
    $types .= "s";
    $count_params[] = $status_filter;
    $count_types .= "s";
}

if (!empty($date_from)) {
    $sql .= " AND DATE(dateAndTime) >= ?";
    $count_sql .= " AND DATE(dateAndTime) >= ?";
    $params[] = $date_from;
    $types .= "s";
    $count_params[] = $date_from;
    $count_types .= "s";
}

if (!empty($date_to)) {
    $sql .= " AND DATE(dateAndTime) <= ?";
    $count_sql .= " AND DATE(dateAndTime) <= ?";
    $params[] = $date_to;
    $types .= "s";
    $count_params[] = $date_to;
    $count_types .= "s";
}

$sql .= " ORDER BY dateAndTime DESC";

// Pagination setup
$page = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Get total count for pagination
$count_stmt = $conn->prepare($count_sql);
if (!empty($count_params)) {
    $count_stmt->bind_param($count_types, ...$count_params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_records = $count_result->fetch_assoc()['total'];
$count_stmt->close();
$total_pages = ceil($total_records / $per_page);

// Add LIMIT and OFFSET to main query
$sql .= " LIMIT ? OFFSET ?";
$types .= "ii";
$params[] = $per_page;
$params[] = $offset;

// Prepare and execute the statement
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
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
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Document</title>
</head>
<body>
    <div class="app-container">
        <?php include __DIR__ . '/../components/Sidebar.php'; ?>

        <div class="flex-1 p-6 bg-gray-50 min-h-screen overflow-y-auto  " id="docu">
            <div class="max-w-7xl mx-auto">
                <!-- Success Message -->
                <?php if ($show_success): ?>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Document record has been added successfully.',
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'OK',
                                timer: 3000
                            });
                        });
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
                                        <?php $row_for_data = $row; unset($row_for_data['signature']); ?>
                                        <tr class="hover:bg-gray-50 transition-colors clickable-row" data-row='<?php echo json_encode($row_for_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>'>
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
                <!-- Pagination Controls -->
                <div class="flex justify-center my-4">
                    <?php if ($total_pages > 1): ?>
                        <nav class="inline-flex -space-x-px">
                            <?php
                            // Build query string for filters/search
                            $query_params = $_GET;
                            foreach(['page_num'] as $unset) unset($query_params[$unset]);
                            $base_query = http_build_query($query_params);
                            for ($i = 1; $i <= $total_pages; $i++):
                                $link = '?' . $base_query . ($base_query ? '&' : '') . 'page_num=' . $i;
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

    <!-- Details Modal for Row Preview (styled like submit modal) -->
    <div id="detailsModal" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Document Details</h2>
                <span class="close" id="closeDetailsModal">&times;</span>
            </div>
            <div class="modal-body">
                <form id="detailsForm">
                    <div class="form-section">
                        <h3>Office Information</h3>
                        <div class="form-group">
                            <label for="detailsOfficeName">Select Office</label>
                            <input type="text" id="detailsOfficeName" readonly class="input-readonly">
                        </div>
                    </div>
                    <div class="form-section">
                        <h3>Sender Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="detailsSenderName">Sender Name</label>
                                <input type="text" id="detailsSenderName" readonly class="input-readonly">
                            </div>
                            <div class="form-group">
                                <label for="detailsEmailAdd">Email Address</label>
                                <input type="text" id="detailsEmailAdd" readonly class="input-readonly">
                            </div>
                        </div>
                    </div>
                    <div class="form-section">
                        <h3>Delivery Information</h3>
                        <div class="form-group">
                            <label for="detailsAddressTo">Receiving Office</label>
                            <input type="text" id="detailsAddressTo" readonly class="input-readonly">
                        </div>
                        <div class="form-group">
                            <label for="detailsModeOfDel">Mode of Delivery</label>
                            <input type="text" id="detailsModeOfDel" readonly class="input-readonly">
                        </div>
                        <div class="form-group">
                            <label for="detailsCourierName">Courier Name</label>
                            <input type="text" id="detailsCourierName" readonly class="input-readonly">
                        </div>
                    </div>
                    <div class="form-section">
                        <h3>Status & Date</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="detailsStatus">Status</label>
                                <input type="text" id="detailsStatus" readonly class="input-readonly">
                            </div>
                            <div class="form-group">
                                <label for="detailsDateAndTime">Date & Time</label>
                                <input type="text" id="detailsDateAndTime" readonly class="input-readonly">
                            </div>
                        </div>
                    </div>
                    <div class="form-section">
                        <h3>Signature</h3>
                        <div class="form-group">
                            <a href="#" id="signatureEnlargeLink">
                                <img id="detailsSignature" src="" alt="Signature" style="max-width:300px; max-height:120px; border:1px solid #ccc; background:#f9f9f9; cursor:pointer;">
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Enlarged Signature Lightbox Modal -->
    <div id="signatureLightbox" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.85); z-index:99999; align-items:center; justify-content:center; cursor:pointer;">
      <img id="enlargedSignature" src="" alt="Enlarged Signature" style="max-width:90vw; max-height:90vh; border:4px solid #fff; border-radius:8px; box-shadow:0 0 20px #000; background:#fff; cursor:default;">
    </div>

    <style>
    .input-readonly {
        width: 100%;
        padding: 6px 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        background: #f5f5f5;
    }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add New Record button logic
        document.getElementById('openFormModal').onclick = function() {
            document.getElementById('formModal').style.display = 'flex';
        };
        // Modal close logic for formModal
        document.querySelectorAll('#formModal .close').forEach(function(closeBtn) {
            closeBtn.onclick = function() {
                document.getElementById('formModal').style.display = 'none';
            };
        });
        // Row click event for details modal
        document.querySelectorAll('.clickable-row').forEach(function(row) {
            row.addEventListener('click', function() {
                var rowData = this.getAttribute('data-row');
                if (!rowData) return;
                var data = JSON.parse(rowData);
                document.getElementById('detailsOfficeName').value = data.officeName || '';
                document.getElementById('detailsSenderName').value = data.senderName || '';
                document.getElementById('detailsEmailAdd').value = data.emailAdd || '';
                document.getElementById('detailsAddressTo').value = data.addressTo || '';
                document.getElementById('detailsModeOfDel').value = data.modeOfDel || '';
                document.getElementById('detailsCourierName').value = data.courierName || '';
                document.getElementById('detailsStatus').value = data.status || '';
                document.getElementById('detailsDateAndTime').value = data.dateAndTime || '';
                if (data.transactionID) {
                    document.getElementById('detailsSignature').src = '/dictproj1/modules/get_signature.php?id=' + data.transactionID;
                } else {
                    document.getElementById('detailsSignature').src = '';
                }
                document.getElementById('detailsModal').style.display = 'flex';
            });
        });
        // Modal close logic for detailsModal
        document.getElementById('closeDetailsModal').onclick = function() {
            document.getElementById('detailsModal').style.display = 'none';
        };
        document.getElementById('detailsModal').onclick = function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        };
        // Signature enlarge (lightbox) feature
        document.getElementById('signatureEnlargeLink').onclick = function(e) {
            e.preventDefault();
            var sigImg = document.getElementById('detailsSignature');
            console.log('Signature src:', sigImg.src);
            if (!sigImg.src || sigImg.src.endsWith('get_signature.php?id=')) {
                // No image to show
                return;
            }
            var enlarged = document.getElementById('enlargedSignature');
            enlarged.src = sigImg.src;
            var lightbox = document.getElementById('signatureLightbox');
            lightbox.style.display = 'flex';
            lightbox.style.opacity = 0;
            setTimeout(() => { lightbox.style.opacity = 1; }, 10);
        };
        // Only close lightbox if background (not image) is clicked
        document.getElementById('signatureLightbox').onclick = function(e) {
            if (e.target === this) {
                this.style.display = 'none';
                document.getElementById('enlargedSignature').src = '';
            }
        };
        document.getElementById('enlargedSignature').onclick = function(e) {
            e.stopPropagation();
        };
    });
    </script>
</body>
</html>

<?php
// Close database connection
$conn->close();
?> 