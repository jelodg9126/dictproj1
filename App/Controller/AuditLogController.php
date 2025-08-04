<?php
require_once MODEL_PATH . 'AuditLogModel.php';
require_once CONTROLLER_PATH . 'BaseController.php';

class AuditLogController extends BaseController {
    protected $model;

    public function __construct($pdo) {
        parent::__construct($pdo);
        $this->model = new AuditLogModel($this->pdo);
    }

    public function index() {
   
        date_default_timezone_set('Asia/Manila');


        $filters = [
            'search'    => $_GET['search']    ?? '',
            'user'      => $_GET['user']      ?? '',
            'role'      => $_GET['role']      ?? '',
            'action'    => $_GET['action']    ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to'   => $_GET['date_to']   ?? ''
        ];

        $page      = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;
        $per_page  = 10;
        $offset    = ($page - 1) * $per_page;

   
        $auditLogs     = $this->model->getAuditLogs($filters, $per_page, $offset);
        $total_records = $this->model->countAuditLogs($filters);
        $total_pages   = ceil($total_records / $per_page);

       
        $users_filter   = array_column($this->model->getUniqueUsers(), 'name');
        $roles_filter   = array_column($this->model->getUniqueRoles(), 'role');
        $actions_filter = array_column($this->model->getUniqueActions(), 'action');

    
        include PAGES_PATH . 'auditLog.php';
    }
}
