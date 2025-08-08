<?php
require_once __DIR__ . '/../base_path.php';
require_once CORE_PATH . 'database.php';
require_once MODEL_PATH . 'AuditLogModel.php';

header('Content-Type: application/json');

$auditLogModel = new AuditLogModel($pdo);

$filters = [
    'search'    => $_GET['search']    ?? '',
    'user'      => $_GET['user']      ?? '',
    'role'      => $_GET['role']      ?? '',
    'action'    => $_GET['action']    ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to'   => $_GET['date_to']   ?? ''
];

$page      = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;
$per_page  = 10;
$offset    = ($page - 1) * $per_page;

$auditLogs     = $auditLogModel->getAuditLogs($filters, $per_page, $offset);
$total_records = $auditLogModel->countAuditLogs($filters);
$total_pages   = ceil($total_records / $per_page);

$response = [
    'logs'          => $auditLogs,
    'total_pages'   => $total_pages,
    'total_records' => $total_records,
    'current_page'  => $page
];

echo json_encode($response);
?>
