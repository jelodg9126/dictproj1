<?php
require __DIR__ .'/base_path.php';
require CONTROLLER_PATH .'AuthController.php';
require CONTROLLER_PATH .'DashboardController.php';
require CONTROLLER_PATH .'AddUserController.php';
require CONTROLLER_PATH .'AuditLogController.php';
require CONTROLLER_PATH .'IncomingController.php';
require CONTROLLER_PATH .'OutgoingController.php';
require CONTROLLER_PATH .'ReceivedController.php';
require CONTROLLER_PATH .'EndorsedController.php';
require_once CORE_PATH . 'database.php';


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
        $dashboardController = new DashboardController($pdo);
        $dashboardController->index();
        break;

    case 'incoming':
        $incomingcontroller = new IncomingController($pdo);
        $incomingcontroller->index(); 
        break;

    case 'outgoing':
        $outgoingController = new OutgoingController($pdo);
        $outgoingController->index();
        break;

    case 'received':
        $receivedController = new ReceivedController($pdo);
        $receivedController->index();
        break;

    case 'login':
        $logincontroller = new AuthController($pdo);
        $logincontroller->login(); 
        break;

    case 'logout':
        $logoutcontroller = new AuthController($pdo);
        $logoutcontroller->logout();
        break;

    case 'addUser':
        $addController = new AddUserController($pdo);
        $addController->addUser();
        break;

    case 'auditLog':
        $auditController = new AuditLogController($pdo);
        $auditController->index();
        break;

    case 'logHistory':
        include PAGES_PATH . 'logHistory.php';
        break;

    case 'endorsed':
        if (isset($_SESSION['userAuthLevel']) && strtolower($_SESSION['userAuthLevel']) === 'admin') {
             $endorsedController = new EndorsedController($pdo);
             $endorsedController->index();
            // include PAGES_PATH . 'Endorsed.php';
        } else {
            header('Location: /dictproj01/index.php?page=documents');
            exit();
        }
        break;

    default:
        http_response_code(404);
        echo "404 Page Not Found";
        break;
}
?>
