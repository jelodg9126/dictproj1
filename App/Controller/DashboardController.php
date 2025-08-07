<?php
require_once MODEL_PATH . 'DashboardModel.php';
require_once CONTROLLER_PATH . 'BaseController.php';

class DashboardController extends BaseController
{
    protected $model;

    public function __construct($pdo)
    {
        parent::__construct($pdo); // sets $this->pdo
        $this->model = new DashboardModel($this->pdo);
    }



    public function index()
    {
        // session_start();

        // if (!isset($_SESSION['uNameLogin']) || !isset($_SESSION['userAuthLevel'])) {
        //     header("Location: /dictproj1/index.php?page=login");
        //     exit();
        // }

        // if (strtolower($_SESSION['userAuthLevel']) === 'superadmin') {
        //     header("Location: /dictproj1/index.php?page=addUser");
        //     exit();
        // }


        $location = $this->getUserLocationName();
        $contactDetails = $this->model->getUserContactDetails($_SESSION['userID']);


        $data = [
            'pending_count'     => $this->model->getPendingCount(),
            'sent_today_count'  => $this->model->getSentTodayCount(),
            'outgoing_count'    => $this->model->getOutgoingCount(),
            'received_count'    => $this->model->getReceivedCount(),
            'name'              => htmlspecialchars($_SESSION['uNameLogin']),
            'email'  => htmlspecialchars($contactDetails['email'] ?? 'Unknown Email'),
            'office' => htmlspecialchars($contactDetails['office'] ?? 'Unknown Office'),
            'auth_level'        => htmlspecialchars($_SESSION['userAuthLevel']),
            'location'          => $location

        ];

        extract($data);
        require_once PAGES_PATH . 'Dashboard.php';
    }
}
