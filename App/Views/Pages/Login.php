<?php

// if (isset($_SESSION['uNameLogin'])) {
//     header("Location: Dashboard.php");
//     exit();
// }
session_start();
$error_message = "";
if (isset($_SESSION['error'])) {
    switch ($_SESSION['error']) {
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
       unset($_SESSION['error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="manifest" href="/dictproj1/manifest.json"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" href="/dictproj1/public/assets/images/mainCircle.png" type="image/png">
    <title>DCIT — Login</title>
</head>
<body>
   <form method="POST" action="/dictproj1/index.php?page=login">
        <div class="login-container h-screen  bg-gray-100 w-full flex items-center justify-center">
          <img src="/dictproj1/public/assets/images/bg3.png" alt="bg image" class="h-screen w-full object-cover opacity-25">
           <div class="login-card p-10 px-30 bg-[rgba(240,240,240,0.1)] py-18 flex flex-col border absolute border-gray-300 rounded-sm shadow-2xl">
                <img src="/dictproj1/public/assets/images/dictStandard2.png" class="mx-auto max-w-auto w-full h-36 min-w-0 object-cover" alt="dict logo"/>
                <h1 class="text-4xl font-bold text-blue-900 text-center mb-1 mt-8">Welcome Back!</h1> 
                <p class="text-center font-regular text-blue-800 text-xl mb-5">Sign In to your Account.</p>
                <?php if (!empty($error_message)): ?>
                    <div id="error-message" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
            <div class="flex flex-col gap-3">
                <div class="relative">
              <input type="text" name="uNameLogin" class="border border-gray-400 rounded-xl p-2 pl-10 w-[350px] mx-auto focus:outline-blue-800 invalid:outline-red-700" placeholder="Username" required/>
              <i data-lucide="circle-user" id="user" class="absolute top-2.5 left-1.5 cursor-pointer w-8 h-5.5 text-blue-900" style="stroke-width:2.5;"></i>
                </div>
              <div class="relative">
              <input type="password" id="password" name="pNameLogin" class="border border-gray-400 rounded-xl p-2 pl-10 w-[350px] mx-auto focus:outline-blue-800" placeholder="Password" required/>
                 <i data-lucide="lock-keyhole" id="password" class="absolute top-2.5 left-1.5 cursor-pointer w-8 h-5.5 text-blue-900" style="stroke-width:2.5;"></i>
                <i data-lucide="eye-closed" id="eye" class="absolute top-2 right-2 cursor-pointer w-8 h-7 text-gray-500" style="stroke-width:1.5;"></i>
                <i data-lucide="eye" id="eye2" class="absolute hidden top-2 right-2 cursor-pointer w-8 h-7 text-gray-500" style="stroke-width:1.5;"></i>
                </div>
              <button class="btn bg-blue-900 font-bold text-white p-2.5 tracking-wide rounded-xl text-lg cursor-pointer mt-4 max-w-xl w-full mx-auto transition-[0.3s] hover:translate-y-[-2px]  hover:bg-blue-800">Submit</button>
            </div>
         </div>
        </div>
    </form>
     <script>
        document.addEventListener("DOMContentLoaded", function() {
            lucide.createIcons();
            // Hide error message after 3 seconds
            var errorMsg = document.getElementById('error-message');
            if (errorMsg) {
                setTimeout(function() {
                    errorMsg.style.display = 'none';
                }, 3000);
            }
        });
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="/dictproj1/public/Scripts/login/passVisible.js"></script>
    <script src="/dictproj1/public/Scripts/pwa-init.js"></script>
</body>
</html>