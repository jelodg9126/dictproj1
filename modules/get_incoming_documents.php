<?php
// include __DIR__ . '/../App/Model/connect.php';
// session_start();
// header('Content-Type: application/json');

// // Office display mapping (same as in Incoming.php)
// $officeDisplayNames = [
//     'dictbulacan' => 'Provincial Office Bulacan',
//     'dictaurora' => 'Provincial Office Aurora',
//     'dictbataan' => 'Provincial Office Bataan',
//     'dictpampanga' => 'Provincial Office Pampanga',
//     'dictPampanga' => 'Provincial Office Pampanga',
//     'dicttarlac' => 'Provincial Office Tarlac',
//     'dictzambales' => 'Provincial Office Zambales',
//     'dictothers' => 'Provincial Office Others',
//     'dictNE' => 'Provincial Office Nueva Ecija',
//     'dictne' => 'Provincial Office Nueva Ecija',
//     'dictNUEVAECIJA' => 'Provincial Office Nueva Ecija',
//     'maindoc' => 'DICT Region 3 Office',
//     'Rdictpampanga' => 'Provincial Office Pampanga',
//     'RdictPampanga' => 'Provincial Office Pampanga',
//     'RdictTarlac' => 'Provincial Office Tarlac',
//     'RdictBataan' => 'Provincial Office Bataan',
//     'RdictBulacan' => 'Provincial Office Bulacan',
//     'RdictAurora' => 'Provincial Office Aurora',
//     'RdictZambales' => 'Provincial Office Zambales',
//     'RdictNuevaEcija' => 'Provincial Office Nueva Ecija',
//     'RdictNE' => 'Provincial Office Nueva Ecija',
//     'Rmaindoc' => 'DICT Region 3 Office',
// ];
// function getOfficeDisplayNameAPI($code, $map) {
//     if (!$code) return '';
//     $lower = strtolower($code);
//     foreach ($map as $key => $val) {
//         if (strtolower($key) === $lower) return $val;
//     }
//     return $code;
// }

// // Get filter parameters
// $search = $_GET['search'] ?? '';
// $office_filter = $_GET['office'] ?? '';
// $delivery_filter = $_GET['delivery'] ?? '';
// $status_filter = $_GET['status'] ?? '';
// $date_from = $_GET['date_from'] ?? '';
// $date_to = $_GET['date_to'] ?? '';
// $page = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;
// $per_page = 10;
// $offset = ($page - 1) * $per_page;

// // Build the SQL query with filters - show all documents addressed to the logged-in user
// $sql = "SELECT * FROM maindoc WHERE 1";
// $count_sql = "SELECT COUNT(*) as total FROM maindoc WHERE 1";
// $params = [];
// $types = "";
// $count_params = [];
// $count_types = "";

// // Add session-based filtering for receiving office (addressTo)
// if (isset($_SESSION['uNameLogin'])) {
//     $username = strtolower($_SESSION['uNameLogin']);
//     $username_to_office = [
//         'dictbulacan' => 'dictbulacan',
//         'dictpampanga' => 'dictpampanga',
//         'dictaurora' => 'dictaurora',
//         'dictbataan' => 'dictbataan',
//         'dictne' => 'dictne',
//         'dicttarlac' => 'dicttarlac',
//         'dictzambales' => 'dictzambales',
//         'admin' => 'maindoc',
//         'maindoc' => 'maindoc',
//         'others' => 'others'
//     ];
//     if (isset($username_to_office[$username])) {
//         $office_match = $username_to_office[$username];
//         $sql .= " AND LOWER(SUBSTRING(addressTo, 2)) = ?";
//         $count_sql .= " AND LOWER(SUBSTRING(addressTo, 2)) = ?";
//         $params[] = $office_match;
//         $types .= "s";
//         $count_params[] = $office_match;
//         $count_types .= "s";
//     }
// }

// if (!empty($search)) {
//     $sql .= " AND (officeName LIKE ? OR senderName LIKE ? OR emailAdd LIKE ? OR courierName LIKE ?)";
//     $count_sql .= " AND (officeName LIKE ? OR senderName LIKE ? OR emailAdd LIKE ? OR courierName LIKE ?)";
//     $search_param = "%$search%";
//     $params[] = $search_param;
//     $params[] = $search_param;
//     $params[] = $search_param;
//     $params[] = $search_param;
//     $types .= "ssss";
//     $count_params[] = $search_param;
//     $count_params[] = $search_param;
//     $count_params[] = $search_param;
//     $count_params[] = $search_param;
//     $count_types .= "ssss";
// }

// if (!empty($office_filter)) {
//     $sql .= " AND officeName = ?";
//     $count_sql .= " AND officeName = ?";
//     $params[] = $office_filter;
//     $types .= "s";
//     $count_params[] = $office_filter;
//     $count_types .= "s";
// }

// if (!empty($delivery_filter)) {
//     $sql .= " AND modeOfDel = ?";
//     $count_sql .= " AND modeOfDel = ?";
//     $params[] = $delivery_filter;
//     $types .= "s";
//     $count_params[] = $delivery_filter;
//     $count_types .= "s";
// }

// if (!empty($status_filter)) {
//     $sql .= " AND status = ?";
//     $count_sql .= " AND status = ?";
//     $params[] = $status_filter;
//     $types .= "s";
//     $count_params[] = $status_filter;
//     $count_types .= "s";
// }

// // Exclude documents with status 'Received' or 'Endorsed' from Incoming Documents
// $sql .= " AND (status IS NULL OR (status != 'Received' AND status != 'Endorsed'))";
// $count_sql .= " AND (status IS NULL OR (status != 'Received' AND status != 'Endorsed'))";

// if (!empty($date_from)) {
//     $sql .= " AND DATE(dateAndTime) >= ?";
//     $count_sql .= " AND DATE(dateAndTime) >= ?";
//     $params[] = $date_from;
//     $types .= "s";
//     $count_params[] = $date_from;
//     $count_types .= "s";
// }

// if (!empty($date_to)) {
//     $sql .= " AND DATE(dateAndTime) <= ?";
//     $count_sql .= " AND DATE(dateAndTime) <= ?";
//     $params[] = $date_to;
//     $types .= "s";
//     $count_params[] = $date_to;
//     $count_types .= "s";
// }

// $sql .= " ORDER BY dateAndTime DESC";

// // Pagination
// $count_stmt = $conn->prepare($count_sql);
// if (!empty($count_params)) {
//     $count_stmt->bind_param($count_types, ...$count_params);
// }
// $count_stmt->execute();
// $count_result = $count_stmt->get_result();
// $total_records = $count_result->fetch_assoc()['total'];
// $count_stmt->close();
// $total_pages = ceil($total_records / $per_page);

// $sql .= " LIMIT ? OFFSET ?";
// $types .= "ii";
// $params[] = $per_page;
// $params[] = $offset;

// $stmt = $conn->prepare($sql);
// if (!empty($params)) {
//     $stmt->bind_param($types, ...$params);
// }
// $stmt->execute();
// $result = $stmt->get_result();
// $rows = [];
// if ($result) {
//     while ($row = $result->fetch_assoc()) {
//         // Map officeName
//         $row['officeName'] = getOfficeDisplayNameAPI($row['officeName'], $officeDisplayNames);
//         // Format date
//         $row['dateAndTime'] = $row['dateAndTime'] ? date('M d, Y g:i A', strtotime($row['dateAndTime'])) : '';
//         // Add pod and hasSignature for JS logic
//         $row['pod'] = (!empty($row['pod']) || !empty($row['pod_filename'])) ? true : false;
//         $row['hasSignature'] = !empty($row['signature']);
//         unset($row['signature']);
//         $rows[] = $row;
//     }
// }
// $stmt->close();
// $conn->close();
// echo json_encode([
//     'success' => true,
//     'data' => $rows,
//     'total_pages' => $total_pages,
//     'total_records' => $total_records
// ]); 