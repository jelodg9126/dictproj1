<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Document</title>
</head>
<body>
    <?php
    // Get current page from URL parameter
    $currentPage = $_GET['page'] ?? 'dashboard';
    ?>
    
    <div class="h-screen bg-blue-900 min-w-[20%] flex flex-col justify-between">
        <div class="">
            <img src="/dictproj1/public/assets/images/dictWhite.png" class="p-6 h-44 object-cover" alt="Logo" />
        </div>
        
        <div class="h-screen  flex flex-col justify-between text-gray-600 text-md font-[500] font-sans">
            <div class="flex gap-0.5 flex-col text-gray-100">
                <a id="myDiv" class="flex gap-3 items-center p-5 transition duration-300 hover:bg-blue-600 active:bg-blue-700 active:scale-95 <?php echo ($currentPage === 'dashboard') ? 'bg-blue-600 border-l-4 border-white' : 'border-l-4 border-transparent'; ?>" href="/dictproj1/public/index.php?page=dashboard">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Dashboard
                </a>
                
                <a class="flex gap-3 items-center p-5 transition duration-300 hover:bg-blue-600 active:bg-blue-700 active:scale-95 <?php echo ($currentPage === 'documents') ? 'bg-blue-600 border-l-4 border-white' : 'border-l-4 border-transparent'; ?>" href="/dictproj1/public/index.php?page=documents">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Documents
                </a>
            </div>

            <div class="mt-12 text-gray-100">
                <a class="flex gap-3 items-center p-5 transition-transform duration-200 transform  hover:text-[17.5px] <?php echo ($currentPage === 'logout') ? 'bg-blue-600' : ''; ?>" href="/dictproj1/public/index.php?page=logout">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Sign Out
                </a>
            </div>
        </div>
    </div>

    
</body>
</html>