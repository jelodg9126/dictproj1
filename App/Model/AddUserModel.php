<?php
class AddUserModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    

    public function insertUser($userName, $hashedPassword, $usertype, $name, $email, $contactno) {
        $stmt = $this->pdo->prepare("INSERT INTO users (userName, passWord, usertype, name, email, contactno) VALUES (?, ?, ?, ?, ?, ?)");

       return $stmt->execute([
            $userName, $hashedPassword, $usertype, $name, $email, $contactno
        ]);
    }

    public function getAllUsers() {
        // $userRows = [];
        $stmt = $this->pdo->query("SELECT userName, passWord, usertype, name, email, contactno FROM users");
        
         $userRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $userRows;
    }
}
?>
