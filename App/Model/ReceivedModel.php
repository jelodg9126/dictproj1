<?php

class ReceivedModel{

    private $pdo;

 function __construct($pdo){
    $this->pdo = $pdo;
 }

 function getReceivedDocuments($filters, $offset, $limit){
    $sql = "SELECT * FROM maindoc WHERE status = 'Received' 
            AND (endorsedToName IS NULL OR endorsedToName = '' 
            OR endorsedToSignature IS NULL OR endorsedToSignature = '' 
            OR endorsedDocProof IS NULL OR endorsedDocProof = '')";
   $params = [];

   if (!empty($filters['office_match'])) {
       $sql .= " AND LOWER(SUBSTRING(addressTo, 2)) = ?";
       $params[] = strtolower($filters['office_match']);
    }

   if (!empty($search)) {
      $sql .= " AND (doctitle LIKE ? OR officeName LIKE ? OR senderName LIKE ? OR emailAdd LIKE ? OR courierName LIKE ?)";
      $search_param = "%" . $filters['search'] . "%";
      $params = array_merge($params, array_fill(0, 5, $search_param)); 
    }


   if (!empty($filters['office_filter'])) {
      $sql .= " AND officeName = ?";
      $params[] = $filters['office_filter'];
    }

   if (!empty($filters['delivery_filter'])) {
      $sql .= " AND modeOfDel = ?";
      $params[] = $filters['delivery_filter'];
   }
 if (!empty($filters['status_filter'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status_filter'];
        }else {
            $sql .= " AND status != ?";
            $params[] = 'Endorsed';
    }
   if (!empty($filters['date_from'])) {
      $sql .= " AND DATE(dateAndTime) >= ?";
      $params[] = $filters['date_from'];
   }

   if (!empty($filters['date_to'])) {
      $sql .= " AND DATE(dateAndTime) <= ?";
      $params[] = $filters['date_to'];
   }



$sql .= " ORDER BY dateAndTime DESC LIMIT $limit OFFSET $offset";


// $params[] = $per_page;
// $params[] = $offset;

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
   return $stmt->fetchAll(PDO::FETCH_ASSOC);

 }

 function getReceivedDocumentsCount($filters){


 if (!empty($filters['search'])) {
    $sql .= " AND (doctitle LIKE ? OR officeName LIKE ? OR senderName LIKE ? OR emailAdd LIKE ? OR courierName LIKE ?)";
    $search_param = "%" . $filters['search'] . "%";
    $params = array_merge($params, array_fill(0, 5, $search_param)); 
    }

 if (!empty($filters['office_filter'])) {
     $sql .= " AND officeName = ?";
     $params[] = $filters['office_filter'];
    }

 if (!empty($filters['delivery_filter'])) {
    $sql .= " AND modeOfDel = ?";
    $params[] = $filters['delivery_filter'];
   }
   
 if (!empty($filters['status_filter'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status_filter'];
        }else {
            $params[] = 'Endorsed';
    }

 if (!empty($filters['date_from'])) {
    $sql .= " AND DATE(dateAndTime) >= ?";
    $params[] = $filters['date_from'];
   }  

if (!empty($filters['date_to'])) {
    $sql .= " AND DATE(dateAndTime) <= ?";
    $params[] = $filters['date_to'];
   }


 }

//   public function getDistinctOffice($userOffice = '') {
//         $sql = "SELECT DISTINCT officeName FROM maindoc WHERE filetype = 'incoming' AND status = 'Received'";
//         if (!empty($userOffice)) {
//             $sql .= " AND officeName = ?";
//             $sql .= " ORDER BY officeName";
//             $stmt = $this->pdo->prepare($sql);
//             $stmt->execute([$userOffice]);
//         } else {
//             $sql .= " ORDER BY officeName";
//             $stmt = $this->pdo->query($sql);
//         }

//         return $stmt->fetchAll(PDO::FETCH_COLUMN);
//     }


  public function getReceiverName($userID) {
        $stmt = $this->pdo->prepare("SELECT name FROM users WHERE userID = ?");
        $stmt->execute([$userID]);
        return $stmt->fetchColumn();
    }

  public function getOfficeDisplayName($code) {
        $map = [
            'dictbulacan' => 'Provincial Office Bulacan',
            'dictaurora' => 'Provincial Office Aurora',
            'dictbataan' => 'Provincial Office Bataan',
            'dictpampanga' => 'Provincial Office Pampanga',
            'dictne' => 'Provincial Office Nueva Ecija',
            'dicttarlac' => 'Provincial Office Tarlac',
            'dictzambales' => 'Provincial Office Zambales',
            'others' => 'Provincial Office Others',
            'maindoc' => 'DICT Region 3 Office',
            // Add more if needed
        ];

        $lower = strtolower($code);
        return $map[$lower] ?? $code;
    }
  



}