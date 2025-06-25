<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>Document</title>
</head>
<body>
    <?php
    // Set session cookie parameters BEFORE starting the session
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
    
    // If user is already logged in, redirect to dashboard
    if (isset($_SESSION['uNameLogin'])) {
        header("Location: Dashboard.php");
        exit();
    }
    
    // Handle error messages
    $error_message = "";
    if (isset($_GET['error'])) {
        switch ($_GET['error']) {
            case 'invalid_credentials':
                $error_message = "Invalid username or password";
                break;
            case 'missing_data':
                $error_message = "Please enter both username and password";
                break;
            case 'db_error':
                $error_message = "Database error occurred";
                break;
            default:
                $error_message = "An error occurred";
        }
    }
    ?>
    
    <form action="../../Model/dbLogin.php" method="POST">
        
        <div class="login-container h-screen bg-gray-100 w-full flex items-center justify-center">
           <div class="login-card p-10 px-30 bg-white py-20 flex flex-col border border-gray-400 rounded-sm shadow-xl">
                

                <img src="/dictproj1/public/assets/images/dictStandard2.png" class="mx-auto max-w-auto w-full h-36 min-w-0 object-cover" alt="dict logo"/>
                <h1 class="text-4xl font-bold text-blue-900 text-center mb-6 mt-8">Log In</h1> 
               
                <?php if (!empty($error_message)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
          
                <div class="flex flex-col gap-3">
             <input type="text" name="uNameLogin" class="border border-gray-400 rounded-xl p-2 pl-3 w-[350px] mx-auto" placeholder="Username" required/>
             <input type="password" name="pNameLogin" class="border border-gray-400 rounded-xl p-2 pl-3 w-[350px] mx-auto" placeholder="Password" required/>
              <button class="btn bg-blue-900 font-bold text-white p-2 rounded-xl cursor-pointer max-w-xl w-full mx-auto">Submit</button>
                </div>
            </div>
        </div>
    </form>

</body>
</html>