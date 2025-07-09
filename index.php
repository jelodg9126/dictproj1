<?php
session_start();
if (isset($_SESSION['userAuthLevel']) && strtolower($_SESSION['userAuthLevel']) === 'superadmin') {
    header('Location: /dictproj1/App/Views/Pages/addUser.php');
    exit();
}
$page = $_GET['page'] ?? 'logout';
if ($page === 'dashboard') {
    include __DIR__ . '/App/Views/Pages/Dashboard.php';
}  elseif ($page === 'documents') {
    include __DIR__ . '/App/Views/Pages/Documents.php';
} elseif ($page === 'incoming') {
    include __DIR__ . '/App/Views/Pages/Incoming.php';
} elseif ($page === 'outgoing') {
    include __DIR__ . '/App/Views/Pages/Outgoing.php';
} elseif ($page === 'received') {
    include __DIR__ . '/App/Views/Pages/Received.php';
} elseif ($page === 'logout') {
    include __DIR__ . '/App/Views/Pages/Login.php';
} elseif ($page === 'endorsed') {
    if (isset($_SESSION['userAuthLevel']) && strtolower($_SESSION['userAuthLevel']) === 'admin') {
        include __DIR__ . '/App/Views/Pages/Endorsed.php';
    } else {
        header('Location: /dictproj1/public/index.php?page=documents');
        exit();
    }
} else {
    // 404 or default
}
?>