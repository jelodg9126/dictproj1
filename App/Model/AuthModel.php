<?php
class AuthModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function dblogin($username)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE userName = :username");
        $stmt->execute(['username' => $username]);
        return $stmt;
    }


    public function ensureUsertypeColumn()
    {
        $check = $this->pdo->query("SHOW COLUMNS FROM users LIKE 'usertype'");
        if ($check->rowCount() === 0) {
            $this->pdo->exec("ALTER TABLE users ADD COLUMN usertype VARCHAR(20) DEFAULT 'Admin'");
            $this->pdo->exec("UPDATE users SET usertype = 'Admin' WHERE usertype IS NULL OR usertype = ''");
        }
    }

    public function logHistory($user_id, $name, $office)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO log_history (user_id, name, office, login_time)
            VALUES (:user_id, :name, :office, NOW())
        ");
        $stmt->execute([
            'user_id' => $user_id,
            'name' => $name,
            'office' => $office
        ]);
    }

    public function getUserByCredentials($username, $password)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM users 
            WHERE BINARY userName = :username AND BINARY passWord = :password
        ");
        $stmt->execute([
            'username' => $username,
            'password' => $password
        ]);
        return $stmt;
    }

    public function getLogoutTime($user_id)
    {
        //database or model
        $stmt = $this->pdo->prepare("UPDATE log_history 
                            SET logout_time = NOW() WHERE user_id = ? 
                            AND logout_time IS NULL ORDER BY login_time 
                            DESC LIMIT 1");
        $stmt->execute([$user_id]);
    }

    public function updatePassword($userID, $newPassword)
    {
        $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare("UPDATE users SET passWord = :pass WHERE userID = :id");
        return $stmt->execute([':pass' => $hashed, ':id' => $userID]);
    }

    public function getUserLocation($userID)
    {
        $stmt = $this->pdo->prepare("SELECT office FROM users WHERE userID = ?");
        $stmt->execute([$userID]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['office'] : null;
    }
}
