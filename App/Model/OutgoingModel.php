<?php

class OutgoingModel {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getOutgoingDocuments($filters, $offset, $limit) {
        $sql = "SELECT * FROM maindoc WHERE filetype = 'outgoing'";
        $params = [];

        // Office session-based
        if (!empty($filters['user_office'])) {
            $sql .= " AND officeName = ?";
            $params[] = $filters['user_office'];
        }

        // Search
        if (!empty($filters['search'])) {
            $sql .= " AND (doctitle LIKE ? OR officeName LIKE ? OR senderName LIKE ? OR emailAdd LIKE ? OR courierName LIKE ?)";
            $search_param = "%" . $filters['search'] . "%";
            $params = array_merge($params, array_fill(0, 5, $search_param));
        }

        // Office filter
        if (!empty($filters['office_filter'])) {
            $sql .= " AND officeName = ?";
            $params[] = $filters['office_filter'];
        }

        // Delivery Mode
        if (!empty($filters['delivery_filter'])) {
            $sql .= " AND modeOfDel = ?";
            $params[] = $filters['delivery_filter'];
        }

        // Status
        if (!empty($filters['status_filter'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status_filter'];
        }
        // Date range
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(dateAndTime) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(dateAndTime) <= ?";
            $params[] = $filters['date_to'];
        }

        $sql .= " ORDER BY dateAndTime DESC LIMIT $limit OFFSET $offset";
        
        // $params[] = (int)$limit;
        // $params[] = (int)$offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOutgoingDocumentsCount($filters) {
        $sql = "SELECT COUNT(*) as total FROM maindoc WHERE filetype = 'outgoing'";
        $params = [];

        if (!empty($filters['user_office'])) {
            $sql .= " AND officeName = ?";
            $params[] = $filters['user_office'];
        }

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
        } 
        

        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(dateAndTime) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(dateAndTime) <= ?";
            $params[] = $filters['date_to'];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getDistinctOffices($userOffice = '') {
        $sql = "SELECT DISTINCT officeName FROM maindoc WHERE filetype = 'outgoing'";
        if (!empty($userOffice)) {
            $sql .= " AND officeName = ?";
            $sql .= " ORDER BY officeName";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userOffice]);
        } else {
            $sql .= " ORDER BY officeName";
            $stmt = $this->pdo->query($sql);
        }

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
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
