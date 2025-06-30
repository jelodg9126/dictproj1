<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>Document</title>
</head>

<body>
    <?php
    // Get current page from URL parameter
    $currentPage = $_GET['page'] ?? 'dashboard';
    $isSuperAdmin = isset($_SESSION['userAuthLevel']) && strtolower($_SESSION['userAuthLevel']) === 'superadmin';
    ?>

    <div class="h-screen bg-blue-950 min-w-[20%] flex flex-col justify-between ">
        <div class="">
            <img src="/dictproj1/public/assets/images/dictWhite.png" class="p-6 h-44 object-cover" alt="Logo" />
        </div>

        <div class="h-screen  flex flex-col justify-between text-gray-600 text-md font-[500] font-sans">
            <div class="flex gap-0.5 flex-col text-gray-100">
                <a id="myDiv" class="flex gap-3 items-center p-5 transition duration-300 hover:bg-blue-600 active:bg-blue-700 active:scale-95 <?php echo ($currentPage === 'dashboard') ? 'bg-blue-600 border-l-4 border-white' : 'border-l-4 border-transparent'; ?>" href="/dictproj1/public/index.php?page=dashboard">
                    <i data-lucide="chart-spline" class="w-6 h-6 text-white" style="stroke-width:1.5;"></i>
                    Dashboard
                </a>

                <!-- Documents Dropdown -->
                <div class="relative">
                    <button id="documentsDropdown" class="flex gap-3 items-center p-5 w-full text-left transition duration-300 hover:bg-blue-600 active:bg-blue-700 active:scale-95 <?php echo (in_array($currentPage, ['documents', 'incoming', 'outgoing'])) ? 'bg-blue-600 border-l-4 border-white' : 'border-l-4 border-transparent'; ?>">
                        <i data-lucide="file-search" class="w-6 h-6 text-white-700" style="stroke-width:1.5;"></i>
                        <span>Documents</span>
                        <svg class="w-4 h-4 ml-auto transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="documentsDropdownMenu" class="absolute left-0 right-0 top-full bg-blue-800 border-l-4 border-white hidden">
                        <a href="/dictproj1/public/index.php?page=incoming" class="flex gap-3 items-center p-4 pl-12 transition duration-300 hover:bg-blue-700 <?php echo ($currentPage === 'incoming') ? 'bg-blue-700' : ''; ?>">
                            <i data-lucide="file-input" class="w-6 h-6 text-white-700" style="stroke-width:1.5;"></i>
                            Incoming
                        </a>
                        <a href="/dictproj1/public/index.php?page=outgoing" class="flex gap-3 items-center p-4 pl-12 transition duration-300 hover:bg-blue-700 <?php echo ($currentPage === 'outgoing') ? 'bg-blue-700' : ''; ?>">
                            <i data-lucide="file-output" class="w-6 h-6 text-white" style="stroke-width:1.5;"></i>
                            Outgoing
                        </a>
                        <a href="/dictproj1/public/index.php?page=received" class="flex gap-3 items-center p-4 pl-12 transition duration-300 hover:bg-blue-700 <?php echo ($currentPage === 'received') ? 'bg-blue-700' : ''; ?>">
                            <i data-lucide="file-check" class="w-6 h-6 text-white" style="stroke-width:1.5;"></i>
                            Received Documents
                        </a>
                        <?php if ($isSuperAdmin): ?>
                            <a href="/dictproj1/public/index.php?page=endorsed" class="flex gap-3 items-center p-4 pl-12 transition duration-300 hover:bg-blue-700 <?php echo ($currentPage === 'endorsed') ? 'bg-blue-700' : ''; ?>">
                                <i data-lucide="refresh-ccw" class="w-6 h-6 text-white" style="stroke-width:1.5;"></i>
                                Endorsed Documents
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="mt-12 text-gray-100">
                <a class="flex gap-3 items-center p-5 transition-transform duration-200 transform  hover:text-[17.5px] <?php echo ($currentPage === 'logout') ? 'bg-blue-600' : ''; ?>" id="logout" href="/dictproj1/App/Model/logout.php" oncli">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Sign Out
                </a>
            </div>
        </div>
    </div>

    <script>
        // Documents dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownButton = document.getElementById('documentsDropdown');
            const dropdownMenu = document.getElementById('documentsDropdownMenu');
            const dropdownArrow = dropdownButton.querySelector('svg:last-child');

            if (dropdownButton && dropdownMenu) {
                dropdownButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    const isOpen = !dropdownMenu.classList.contains('hidden');

                    if (isOpen) {
                        dropdownMenu.classList.add('hidden');
                        dropdownArrow.style.transform = 'rotate(0deg)';
                    } else {
                        dropdownMenu.classList.remove('hidden');
                        dropdownArrow.style.transform = 'rotate(180deg)';
                    }
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!dropdownButton.contains(e.target) && !dropdownMenu.contains(e.target)) {
                        dropdownMenu.classList.add('hidden');
                        dropdownArrow.style.transform = 'rotate(0deg)';
                    }
                });

                // Keep dropdown open if current page is incoming, outgoing, received, or endorsed
                const currentPage = '<?php echo $currentPage; ?>';
                if (['incoming', 'outgoing', 'received', 'endorsed'].includes(currentPage)) {
                    dropdownMenu.classList.remove('hidden');
                    dropdownArrow.style.transform = 'rotate(180deg)';
                }
            }
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            lucide.createIcons();
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="/dictproj1/public/Scripts/LogoutConfirm.js"></script>
</body>

</html>