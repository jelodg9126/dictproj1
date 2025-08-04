<?php
class AuditLogModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAuditLogs($filters, $limit, $offset) {
        $sql = "SELECT a.*, u.name AS user_fullname, u.office AS user_office 
                FROM audit_log a 
                LEFT JOIN users u ON a.user_id = u.userID 
                WHERE 1";
        $params = [];

        if (!empty($filters['search'])) {
            $search_param = "%" . $filters['search'] . "%";
            $sql .= " AND (a.name LIKE ? OR a.office_name LIKE ? OR a.action LIKE ? OR u.name LIKE ? OR u.office LIKE ?)";
            $params = array_merge($params, array_fill(0, 5, $search_param));
        }

        if (!empty($filters['user'])) {
            $sql .= " AND (a.name = ? OR u.name = ?)";
            $params[] = $filters['user'];
            $params[] = $filters['user'];
        }

        if (!empty($filters['role'])) {
            $sql .= " AND a.role = ?";
            $params[] = $filters['role'];
        }

        if (!empty($filters['action'])) {
            $sql .= " AND a.action LIKE ?";
            $params[] = "%" . $filters['action'] . "%";
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(a.timestamp) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(a.timestamp) <= ?";
            $params[] = $filters['date_to'];
        }

        // LIMIT and OFFSET must NOT be passed as quoted placeholders
        $sql .= " ORDER BY a.timestamp DESC LIMIT $limit OFFSET $offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAuditLogs($filters) {
        $sql = "SELECT COUNT(*) as total 
                FROM audit_log a 
                LEFT JOIN users u ON a.user_id = u.userID 
                WHERE 1";
        $params = [];

        if (!empty($filters['search'])) {
            $search_param = "%" . $filters['search'] . "%";
            $sql .= " AND (a.name LIKE ? OR a.office_name LIKE ? OR a.action LIKE ? OR u.name LIKE ? OR u.office LIKE ?)";
            $params = array_merge($params, array_fill(0, 5, $search_param));
        }

        if (!empty($filters['user'])) {
            $sql .= " AND (a.name = ? OR u.name = ?)";
            $params[] = $filters['user'];
            $params[] = $filters['user'];
        }

        if (!empty($filters['role'])) {
            $sql .= " AND a.role = ?";
            $params[] = $filters['role'];
        }

        if (!empty($filters['action'])) {
            $sql .= " AND a.action LIKE ?";
            $params[] = "%" . $filters['action'] . "%";
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(a.timestamp) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(a.timestamp) <= ?";
            $params[] = $filters['date_to'];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getUniqueUsers() {
        $sql = "SELECT DISTINCT a.name FROM audit_log a WHERE a.name IS NOT NULL AND a.name != '' ORDER BY a.name";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUniqueRoles() {
        $sql = "SELECT DISTINCT a.role FROM audit_log a WHERE a.role IS NOT NULL AND a.role != '' ORDER BY a.role";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUniqueActions() {
        $sql = "SELECT DISTINCT a.action FROM audit_log a WHERE a.action IS NOT NULL AND a.action != '' ORDER BY a.action";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
