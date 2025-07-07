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
} elseif ($page === 'addUser') {
    include __DIR__ . '/../App/Views/Pages/addUser.php';
} elseif ($page === 'endorsed') {
    session_start();
    if (isset($_SESSION['userAuthLevel']) && strtolower($_SESSION['userAuthLevel']) === 'superadmin') {
        include __DIR__ . '/../App/Views/Pages/Endorsed.php';
    } else {
        header('Location: /dictproj1/public/index.php?page=documents');
        exit();
    }
} else {
    // 404 or default
}
?>