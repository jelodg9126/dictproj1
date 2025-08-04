<?php

require_once MODEL_PATH . 'OutgoingModel.php';
require_once CONTROLLER_PATH . 'BaseController.php';

class OutgoingController extends BaseController {
    protected $model;

    public function __construct($pdo) {
        parent::__construct($pdo);
        $this->model = new OutgoingModel($this->pdo);
    }

    public function index() {
        if (!isset($_SESSION['uNameLogin'])) {
            header("Location: Login.php");
            exit();
        }

         $show_success = isset($_GET['success']) && $_GET['success'] == '1';

        $username = strtolower($_SESSION['uNameLogin']);
        $username_to_office = [
            'dictbulacan' => 'dictBulacan',
            'dictpampanga' => 'dictPampanga',
            'dictaurora' => 'dictAurora',
            'dictbataan' => 'dictBataan',
            'dictne' => 'dictNE',
            'dicttarlac' => 'dictTarlac',
            'dictzambales' => 'dictZambales',
            'admin' => 'maindoc',
            'maindoc' => 'maindoc',
            'others' => 'Others'
        ];

        $user_office = $username_to_office[$username] ?? '';

        $filters = [
            'search' => $_GET['search'] ?? '',
            'office_filter' => $_GET['office'] ?? '',
            'delivery_filter' => $_GET['delivery'] ?? '',
            'status_filter' => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'user_office' => $user_office
        ];

        $page = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;
        $per_page = 10;
        $offset = ($page - 1) * $per_page;

        $documents = $this->model->getOutgoingDocuments($filters, $offset, $per_page);
        $total_records = $this->model->getOutgoingDocumentsCount($filters);
        $total_pages = ceil($total_records / $per_page);
        $offices = $this->model->getDistinctOffices($user_office);

        extract($filters); 

        // Load the view and pass the data
        include_once PAGES_PATH. 'Outgoing.php';
    }
}
