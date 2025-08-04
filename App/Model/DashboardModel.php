<?php


class DashboardModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getPendingCount() {
        $sql = "SELECT COUNT(*) as pending_count FROM maindoc WHERE status = 'Pending'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['pending_count'] : 0;
    }

    public function getSentTodayCount() {
        $sql = "SELECT COUNT(*) as sent_today_count FROM maindoc WHERE DATE(dateAndTime) = CURDATE()";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();   
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['sent_today_count'] : 0;
    }

    public function getOutgoingCount() {
        $sql = "SELECT COUNT(*) as outgoing_count FROM maindoc WHERE fileType = 'outgoing'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['outgoing_count'] : 0;
    }

    public function getReceivedCount() {
        $sql = "SELECT COUNT(*) as received_count FROM maindoc WHERE status = 'received'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['received_count'] : 0;
    }
}
