<?php
require_once MODEL_PATH . 'ReceivedModel.php';
require_once CONTROLLER_PATH . 'BaseController.php';

class ReceivedController extends BaseController{

 protected $model;

 function __construct($pdo){
    parent::__construct($pdo);
    $this->model = new ReceivedModel($this->pdo);
  }

 function index(){

  $filters = [  
     'search' => $_GET['search'] ?? '',
     'office_filter' => $_GET['office'] ?? '',
     'delivery_filter' => $_GET['delivery'] ?? '',
     'status_filter' => $_GET['status'] ?? '',
     'date_from' => $_GET['date_from'] ?? '',
     'date_to' => $_GET['date_to'] ?? '',
     'office_match' => $this->getReceivingOffice()
  ];

   $page = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;
   $per_page = 10;
   $offset = ($page - 1) * $per_page;
  
   $documents = $this->model->getReceivedDocuments($filters, $offset, $per_page);
   $total_records = $this->model->getReceivedDocumentsCount($filters);
   $total_pages = ceil($total_records / $per_page);
   $user_filter_sql = $this->getUserReceivingOfficeSQL();
 
      

   extract($filters);


   include PAGES_PATH . 'Received.php';


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