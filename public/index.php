<?php
$page = $_GET['page'] ?? 'dashboard';
if ($page === 'dashboard') {
    include __DIR__ . '/../App/Views/Pages/Dashboard.php';
} elseif ($page === 'documents') {
    include __DIR__ . '/../App/Views/Pages/Documents.php';
} elseif ($page === 'incoming') {
    include __DIR__ . '/../App/Views/Pages/Incoming.php';
} elseif ($page === 'outgoing') {
    include __DIR__ . '/../App/Views/Pages/Outgoing.php';
} elseif ($page === 'received') {
    include __DIR__ . '/../App/Views/Pages/Received.php';
} elseif ($page === 'logout') {
    include __DIR__ . 'location: ../../App/Views/Pages/Login.php';
} else {
    // 404 or default
}
?>