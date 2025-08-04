<?php

class IncomingModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getFilteredDocuments($filters, $per_page, $offset) {
        $sql = "SELECT * FROM maindoc WHERE 1";
        $params = [];

        // Receiving office filter
        if (!empty($filters['office_match'])) {
            $sql .= " AND LOWER(SUBSTRING(addressTo, 2)) = ?";
            $params[] = strtolower($filters['office_match']);
        }

        // Search filters
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

        $sql .= " AND (status IS NULL OR (status != 'Received' AND status != 'Endorsed'))";

        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(dateAndTime) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(dateAndTime) <= ?";
            $params[] = $filters['date_to'];
        }

       $sql .= " ORDER BY dateAndTime DESC LIMIT $per_page OFFSET $offset";

        // $params[] = $per_page;
        // $params[] = $offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalCount($filters) {
        $sql = "SELECT COUNT(*) as total FROM maindoc WHERE 1";
        $params = [];

        if (!empty($filters['office_match'])) {
            $sql .= " AND LOWER(SUBSTRING(addressTo, 2)) = ?";
            $params[] = strtolower($filters['office_match']);
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

        $sql .= " AND (status IS NULL OR (status != 'Received' AND status != 'Endorsed'))";

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

    public function getOffices($user_receiving_office_filter) {
        $sql = "SELECT DISTINCT officeName FROM maindoc WHERE filetype = 'incoming' $user_receiving_office_filter ORDER BY officeName";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getStatuses($user_receiving_office_filter) {
        $sql = "SELECT DISTINCT status FROM maindoc WHERE filetype = 'incoming' AND status IS NOT NULL AND status != '' $user_receiving_office_filter ORDER BY status";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getReceiverName($userID) {
        $stmt = $this->pdo->prepare("SELECT name FROM users WHERE userID = ?");
        $stmt->execute([$userID]);
        return $stmt->fetchColumn();
    }
}
