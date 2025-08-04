<?php
require_once MODEL_PATH . 'EndorsedModel.php';
require_once CONTROLLER_PATH . 'BaseController.php';

class EndorsedController extends BaseController  {
    protected $model;

    public function __construct($pdo) {
        parent::__construct($pdo);
        $this->model = new EndorsedModel($this->pdo);
   
    }



 public function index() {

    $filters = [
        'date_from' => $_POST['date_from'] ?? '',
        'date_to' => $_POST['date_to'] ?? ''
    ];

     $page = isset($_POST['page_num']) ? max(1, intval($_POST['page_num'])) : 1;
        $per_page = 10;
        $offset = ($page - 1) * $per_page;

    date_default_timezone_set('Asia/Manila');
    $currentPage = 'endorsed';
    $results = $this->model->getEndorsedDocuments($filters, $per_page, $offset);

    include PAGES_PATH . 'Endorsed.php';
}



}
