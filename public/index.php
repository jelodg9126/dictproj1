<?php
$page = $_GET['page'] ?? 'dashboard';
if ($page === 'dashboard') {
    include __DIR__ . '/../App/Views/Pages/Dashboard.php';
} elseif ($page === 'documents') {
    include __DIR__ . '/../App/Views/Pages/Documents.php';
} elseif ($page === 'logout') {
    include __DIR__ . '/../App/Views/Pages/Login.php';
} else {
    // 404 or default
}
?>