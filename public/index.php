<?php
$page = $_GET['page'] ?? 'dashboard';
if ($page === 'dashboard') {
    include __DIR__ . '/../App/Views/Pages/Dashboard.php';
} elseif ($page === 'documents') {
    include __DIR__ . '/../App/Views/Pages/Documents.php';
} else {
    // 404 or default
}
?>