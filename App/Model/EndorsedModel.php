<?php

class EndorsedModel{
    
     private $pdo;

  public function __construct($pdo){
       $this->pdo = $pdo;
  }


 public function getEndorsedDocuments($filters, $limit, $offset) {
    $sql = "SELECT * FROM maindoc 
            WHERE filetype = 'outgoing' 
            AND status = 'Endorsed' 
            AND endorsedToName IS NOT NULL AND endorsedToName != '' 
            AND endorsedToSignature IS NOT NULL AND endorsedToSignature != '' 
            AND endorsedDocProof IS NOT NULL AND endorsedDocProof != ''";
    
    $params = [];

    // Optional date range filters
    if (!empty($filters['date_from'])) {
        $sql .= " AND DATE(dateAndTime) >= ?";
        $params[] = $filters['date_from'];
    }

    if (!empty($filters['date_to'])) {
        $sql .= " AND DATE(dateAndTime) <= ?";
        $params[] = $filters['date_to'];
    }

    // Add ordering and pagination
    $sql .= " ORDER BY endorsementTimestamp DESC";
    // $params[] = (int)$limit;
    // $params[] = (int)$offset;

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    



}