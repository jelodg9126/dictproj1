<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>Document</title>
</head>
<body>
    <form action="../../dictproj1/App/Model/dbLogin.php" method="POST">
        
             <div class="login-container h-screen w-full flex items-center justify-center">
           <div class="login-card p-12 pr-6 border border-gray-400 rounded-sm ">
            <div class="login-header-wrapper flex items-center">
                <h1 class="text-3xl font-bold">Log in</h1> 
                <img src="dictproj1/public/assets/images/dictStandard.png" class="h-38 min-w-auto object-cover" alt="dict logo"/>
            </div>
            <label>Username</label>
             <input type="text" name="uNameLogin"class="border"/>
            <label>Password</label>
                 <input type="password" name="pNameLogin" class="border"/>
              <button class="btn btn-wide mt-4 bg-blue-900 text-white">Submit</button>
           </div>
        </div>
    </form>

</body>
</html>