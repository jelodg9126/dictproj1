<?php
require_once MODEL_PATH . 'AddUserModel.php';
require_once CONTROLLER_PATH . 'BaseController.php';

class AddUserController extends BaseController {
   private $userModel;

    public function __construct($pdo){
       parent::__construct($pdo);
       $this->userModel = new AddUserModel($this->pdo);
    }
    

   public function addUser() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userName = trim($_POST['userName'] ?? '');
        $passWord = trim($_POST['passWord'] ?? '');
        $usertype = trim($_POST['usertype'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $contactno = trim($_POST['contactno'] ?? '');

        if ($userName && $passWord && $usertype && $name && $email && $contactno) {
            $hashedPassword = password_hash($passWord, PASSWORD_DEFAULT);
            $result = $this->userModel->insertUser($userName, $hashedPassword, $usertype, $name, $email, $contactno);
            if ($result) {
                $_SESSION['user_added'] = true; // ✅ Flag success
                $this->redirect('/dictproj1/index.php?page=addUser'); // ✅ Redirect
                return;
            } else {
                $_SESSION['add_user_error'] = "Error adding user.";
                $this->redirect('/dictproj1/index.php?page=addUser');
                return;
            }
        } else {
            $_SESSION['add_user_error'] = "Please fill in all required fields.";
            $this->redirect('/dictproj1/index.php?page=addUser');
            return;
        }
    }

    $userRows = $this->userModel->getAllUsers();
    include __DIR__ . '/../Views/Pages/addUser.php';
}

}
?>
