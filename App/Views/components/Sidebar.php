<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <title>Sidebar</title>
</head>

<body>
<?php
$currentPage = $_GET['page'] ?? 'dashboard';
$isSuperAdmin = isset($_SESSION['userAuthLevel']) && strtolower($_SESSION['userAuthLevel']) === 'superadmin';
?>

<div id="sidebar" class="h-screen bg-blue-950 w-[8%] transition-all duration-500 flex flex-col justify-between relative overflow-hidden">

  <!-- Toggle Button -->
 <button id="toggleSidebar"
  class="absolute right-0 mr-2 top-1/2 -translate-y-1/2 transform bg-blue-900 text-white w-10 h-10 flex items-center justify-center rounded-full hover:bg-blue-800 z-50 shadow transition-all duration-300">
  <span id="rotateWrapper" class="inline-block transition-transform duration-300">
    <i data-lucide="chevrons-right" class="w-5 h-5"></i>
  </span>
</button>
  <!-- Logo Section -->
  <div class="relative h-36 w-full flex items-center justify-center">
    <img id="logoCollapsed" src="/dictproj1/public/assets/images/dictWhiteCircle.png"
         class="absolute transition-opacity duration-500 opacity-100 h-28 p-3 object-cover" alt="Collapsed Logo" />
    <img id="logoExpanded" src="/dictproj1/public/assets/images/dictWhite.png"
         class="absolute transition-opacity duration-500 opacity-0 h-44 p-6 object-cover" alt="Expanded Logo" />
  </div>

  <!-- Navigation Links -->
  <div class="flex flex-col justify-between text-white text-sm font-semibold font-sans h-full">
    <div class="flex flex-col gap-0.5">
      <a class="flex items-center gap-3 p-5 transition duration-300 hover:bg-blue-800 active:bg-blue-700 active:scale-95 
        <?php echo ($currentPage === 'dashboard') ? 'bg-blue-800 border-l-4 border-white' : 'border-l-4 border-transparent'; ?>"
        href="/dictproj1/public/index.php?page=dashboard">
        <i data-lucide="chart-spline" class="w-6 h-6 text-white"></i>
        <span class="sidebar-label hidden transition-opacity duration-300">Dashboard</span>
      </a>

      <!-- Documents Dropdown -->
      <div class="relative">
        <button id="documentsDropdown"
          class="flex items-center gap-3 p-5 w-full text-left transition duration-300 hover:bg-blue-800 active:bg-blue-700 active:scale-95 
            <?php echo (in_array($currentPage, ['documents', 'incoming', 'outgoing'])) ? 'bg-blue-800 border-l-4 border-white' : 'border-l-4 border-transparent'; ?>">
          <i data-lucide="file-text" class="w-6 h-6 text-white"></i>
          <span class="sidebar-label hidden transition-opacity duration-300">Documents</span>
          <svg class="w-4 h-4 ml-auto transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </button>

        <div id="documentsDropdownMenu" class="absolute left-0 right-0 top-full bg-blue-900 border-l-4 border-white hidden">
          <a href="/dictproj1/public/index.php?page=incoming"
            class="flex items-center gap-3 p-4 pl-12 transition duration-300 hover:bg-blue-800 
              <?php echo ($currentPage === 'incoming') ? 'bg-blue-700' : ''; ?>">
            <i data-lucide="file-input" class="w-6 h-6 text-white"></i>
            <span class="sidebar-label hidden transition-opacity duration-300">Incoming</span>
          </a>
          <a href="/dictproj1/public/index.php?page=outgoing"
            class="flex items-center gap-3 p-4 pl-12 transition duration-300 hover:bg-blue-800 
              <?php echo ($currentPage === 'outgoing') ? 'bg-blue-700' : ''; ?>">
            <i data-lucide="file-output" class="w-6 h-6 text-white"></i>
            <span class="sidebar-label hidden transition-opacity duration-300">Outgoing</span>
          </a>
          <a href="/dictproj1/public/index.php?page=received"
            class="flex items-center gap-3 p-4 pl-12 transition duration-300 hover:bg-blue-800 
              <?php echo ($currentPage === 'received') ? 'bg-blue-700' : ''; ?>">
            <i data-lucide="file-check" class="w-6 h-6 text-white"></i>
            <span class="sidebar-label hidden transition-opacity duration-300">Received</span>
          </a>
          <?php if ($isSuperAdmin): ?>
          <a href="/dictproj1/public/index.php?page=endorsed"
            class="flex items-center gap-3 p-4 pl-12 transition duration-300 hover:bg-blue-700 
              <?php echo ($currentPage === 'endorsed') ? 'bg-blue-700' : ''; ?>">
            <i data-lucide="refresh-ccw" class="w-6 h-6 text-white"></i>
            <span class="sidebar-label hidden transition-opacity duration-300">Endorsed</span>
          </a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="text-white">
      <a id="logout" class="flex gap-3 items-center p-5 mb-1 transition duration-300 hover:bg-blue-800 
        <?php echo ($currentPage === 'logout') ? 'bg-blue-600' : ''; ?>" 
        href="/dictproj1/App/Model/logout.php">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
          </path>
        </svg>
        <span class="sidebar-label hidden transition-opacity duration-300">Sign Out</span>
      </a>
    </div>
  </div>
</div>

<!-- Scripts -->

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script src="/dictproj1/public/Scripts/sidebar.js"> </script>
<script src="/dictproj1/public/Scripts/SidebarDropdown.js"></script>
<script src="/dictproj1/public/Scripts/LogoutConfirm.js"></script> 

</body>
</html>
