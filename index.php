<?php
session_start();
if (isset($_SESSION['userAuthLevel']) && strtolower($_SESSION['userAuthLevel']) === 'superadmin') {
    $page = $_GET['page'] ?? '';
    if ($page === '' || $page === 'dashboard') {
        header('Location: /dictproj1/index.php?page=addUser');
        exit();
    }
}

$page = $_GET['page'] ?? 'logout';

switch ($page) {
    case 'dashboard':
        include __DIR__ . '/App/Views/Pages/Dashboard.php';
        break;
    case 'documents':
        include __DIR__ . '/App/Views/Pages/Documents.php';
        break;
    case 'incoming':
        include __DIR__ . '/App/Views/Pages/Incoming.php';
        break;
    case 'outgoing':
        include __DIR__ . '/App/Views/Pages/Outgoing.php';
        break;
    case 'received':
        include __DIR__ . '/App/Views/Pages/Received.php';
        break;
    case 'logout':
        include __DIR__ . '/App/Views/Pages/Login.php'; // fixed bad "location:" usage
        break;
    case 'addUser':
        include __DIR__ . '/App/Views/Pages/addUser.php';
        break;
    case 'auditLog':
        include __DIR__ . '/App/Views/Pages/auditLog.php';
        break;
    case 'logHistory':
        include __DIR__ . '/App/Views/Pages/logHistory.php';
        break;
    case 'endorsed':
        if (isset($_SESSION['userAuthLevel']) && strtolower($_SESSION['userAuthLevel']) === 'admin') {
            include __DIR__ . '/App/Views/Pages/Endorsed.php';
        } else {
            header('Location: /dictproj1/index.php?page=documents');
            exit();
        }
        break;
    default:
        // Optional: show a 404 page
        http_response_code(404);
        echo "404 Page Not Found";
        break;
}
?>
