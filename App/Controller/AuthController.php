<?php
require_once MODEL_PATH . 'AuthModel.php';
require_once CONTROLLER_PATH . 'BaseController.php';


class AuthController extends BaseController{

    protected $AuthModel;

    public function __construct($pdo){
        parent::__construct($pdo);
        $this->AuthModel = new AuthModel($this->pdo);
    }
    
    public function login() {
    //   require __DIR__ . '/../Core/database.php'; 

        if (!isset($_POST['uNameLogin']) || !isset($_POST['pNameLogin'])) {
            header("Location: /dictproj1/index.php?page=logout&error=missing_data");
            exit();
        }

        // No need for mysqli_real_escape_string with PDO
        $username = $_POST['uNameLogin'];
        $password = $_POST['pNameLogin'];

        // Authenticate using PDO
        $AuthModel = new AuthModel($this->pdo);
        $result = $AuthModel->dblogin($username, $password);

        if ($result && $result->rowCount() > 0) {
            $row = $result->fetch(PDO::FETCH_ASSOC);

            $_SESSION['uNameLogin'] = $row['userName'];
            $_SESSION['userID'] = $row['userID'];
            $_SESSION['login_time'] = time();
            $_SESSION['userAuthLevel'] = $row['usertype'] ?? 'Admin';

            $AuthModel->logHistory($row['userID'], $row['name'], $row['office']);

         
            switch(strtolower($_SESSION['userAuthLevel'])) {
                case 'superadmin':
                    $this->redirect("/dictproj1/index.php?page=addUser");
                
                case 'provincial':
                    $this->redirect("/dictproj1/index.php?page=dashboard");
                
                default:
                    $this->redirect("/dictproj1/index.php?page=dashboard");
            }
        } else {
            header("Location: /dictproj1/index.php?page=logout&error=invalid_credentials");
        }

        exit();
    }

     public function logout(){

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
}
