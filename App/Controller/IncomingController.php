<?php

require_once MODEL_PATH . 'IncomingModel.php';
require_once CONTROLLER_PATH . 'BaseController.php';

class IncomingController extends BaseController {
    protected $model;

    public function __construct($pdo) {
        parent::__construct($pdo);
        $this->model = new IncomingModel($this->pdo);
    }

    public function index() {
        
        $show_success = isset($_GET['success']) && $_GET['success'] == '1';
        $filters = [
            'search' => $_POST['search'] ?? '',
            'office_filter' => $_POST['office'] ?? '',
            'delivery_filter' => $_POST['delivery'] ?? '',
            'status_filter' => $_POST['status'] ?? '',
            'date_from' => $_POST['date_from'] ?? '',
            'date_to' => $_POST['date_to'] ?? '',
            'office_match' => $this->getReceivingOffice()
        ];

        $page = isset($_POST['page_num']) ? max(1, intval($_POST['page_num'])) : 1;
        $per_page = 10;
        $offset = ($page - 1) * $per_page;

        $documents = $this->model->getFilteredDocuments($filters, $per_page, $offset);
        $total_records = $this->model->getTotalCount($filters);
        $total_pages = ceil($total_records / $per_page);

        $user_filter_sql = $this->getUserReceivingOfficeSQL();
        $offices = $this->model->getOffices($user_filter_sql);
        $statuses = $this->model->getStatuses($user_filter_sql);

        $receiverName = '';
        if (isset($_SESSION['userID'])) {
            $receiverName = $this->model->getReceiverName($_SESSION['userID']);
        }

        require __DIR__ . '/../Views/Pages/Incoming.php';
    }

    private function getReceivingOffice() {
        $username = strtolower($_SESSION['uNameLogin'] ?? '');
        $map = [
            'dictbulacan' => 'dictbulacan',
            'dictpampanga' => 'dictpampanga',
            'dictaurora' => 'dictaurora',
            'dictbataan' => 'dictbataan',
            'dictne' => 'dictne',
            'dicttarlac' => 'dicttarlac',
            'dictzambales' => 'dictzambales',
            'admin' => 'maindoc',
            'maindoc' => 'maindoc',
            'others' => 'others'
        ];
        return $map[$username] ?? '';
    }

    private function getUserReceivingOfficeSQL() {
        $username = strtolower($_SESSION['uNameLogin'] ?? '');
        $map = [
            'dictbulacan' => 'RdictBulacan',
            'dictpampanga' => 'RdictPampanga',
            'dictaurora' => 'RdictAurora',
            'dictbataan' => 'RdictBataan',
            'dictne' => 'RdictNE',
            'dicttarlac' => 'RdictTarlac',
            'dictzambales' => 'RdictZambales',
            'admin' => 'Rmaindoc',
            'maindoc' => 'Rmaindoc',
            'others' => 'ROthers'
        ];
        return isset($map[$username]) ? " AND addressTo = " . $this->pdo->quote($map[$username]) : '';
    }
}
