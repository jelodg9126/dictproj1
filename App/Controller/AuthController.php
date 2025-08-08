<?php

ob_start();
if (!defined('MODEL_PATH')) {
    define('MODEL_PATH', __DIR__ . '/../Model/');
}

if (!defined('CONTROLLER_PATH')) {
    define('CONTROLLER_PATH', __DIR__ . '/../Controller/');
}

require_once MODEL_PATH . 'AuthModel.php';
require_once CONTROLLER_PATH . 'BaseController.php';


class AuthController extends BaseController
{

    protected $AuthModel;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->AuthModel = new AuthModel($this->pdo);
    }

    public function login()
    {
        //   require __DIR__ . '/../Core/database.php'; 
        if (!isset($_POST['uNameLogin']) || !isset($_POST['pNameLogin'])) {
            echo "Missing form fields.";
            var_dump($_POST);
            exit();
        }

        // No need for mysqli_real_escape_string with PDO
        $username = $_POST['uNameLogin'];
        $password = $_POST['pNameLogin'];

        // Authenticate using PDO
        $AuthModel = new AuthModel($this->pdo);
        $result = $AuthModel->dblogin($username, $password);

        $result = $this->AuthModel->dblogin($username);

        if ($result && $result->rowCount() > 0) {
            $row = $result->fetch(PDO::FETCH_ASSOC);

            if (password_verify($password, $row['passWord'])) {
                $_SESSION['uNameLogin'] = $row['name'];
                $_SESSION['userID'] = $row['userID'];
                $_SESSION['login_time'] = time();
                $_SESSION['userAuthLevel'] = $row['usertype'] ?? 'Admin';

                $this->AuthModel->logHistory($row['userID'], $row['name'], $row['office']);

                switch (strtolower($_SESSION['userAuthLevel'])) {
                    case 'superadmin':
                        $this->redirect("/dictproj1/index.php?page=addUser");
                    case 'provincial':
                        $this->redirect("/dictproj1/index.php?page=dashboard");
                    default:
                        $this->redirect("/dictproj1/index.php?page=dashboard");
                }
            } else {
            ob_end_clean();
             $_SESSION['error'] = "invalid_credentials";
             $this->redirect("/dictproj1/App/Views/Pages/Login.php");

            }
        } else {
            ob_end_clean();
        $_SESSION['error'] = "invalid_credentials";
             $this->redirect("/dictproj1/App/Views/Pages/Login.php");
        }
    }

    public function logout()  {
        if (isset($_SESSION['userID'])) {
            $user_id = $_SESSION['userID'];
            $this->AuthModel->getLogoutTime($user_id);
        }
        // Unset all session variables
        $_SESSION = array();
        // Destroy the session
        session_destroy();
        // Redirect to login page
        $this->redirect("/dictproj1/App/Views/Pages/Login.php");
    }


    public function updatePassword()
    {
        session_start();

        header('Content-Type: application/json');

        if (!isset($_SESSION['userID'])) {
            echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
            return;
        }

        $userID = $_SESSION['userID'];
        $newPassword = $_POST['newPassword'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';

        // Validate passwords
        if ($newPassword !== $confirmPassword) {
            echo json_encode(['status' => 'error', 'message' => 'Passwords do not match']);
            return;
        }

        if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $newPassword)) {
            echo json_encode(['status' => 'error', 'message' => 'Password does not meet requirements']);
            return;
        }

        try {
            $model = new AuthModel($this->pdo);

            $success = $model->updatePassword($userID, $newPassword);

            if ($success) {
                echo json_encode(['status' => 'success', 'message' => 'Password updated successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update password']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }

    
}
