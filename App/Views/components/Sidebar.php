<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.lordicon.com/lordicon.js"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="manifest" href="/dictproj1/manifest.json">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <title>Sidebar</title>
</head>

<body>
<?php
$currentPage = $_GET['page'] ?? 'dashboard';
$userType = isset($_SESSION['userAuthLevel']) ? strtolower($_SESSION['userAuthLevel']) : '';
$isSuperAdmin = $userType === 'superadmin';
$isAdmin = $userType === 'admin';
$isProvincial = $userType === 'provincial';
?>

<!-- Desktop Sidebar -->
<div id="sidebar" class="h-screen bg-blue-950 w-[100px] transition-all duration-500 flex flex-col justify-between relative rounded-r-xl shadow-[rgba(0,0,6,0.1)_6px_1px_3px] overflow-hidden max-sm:hidden">

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
         class="absolute transition-opacity duration-500 opacity-100 h-auto max-w-[80px] w-full p-3 object-cover" alt="Collapsed Logo" />
    <img id="logoExpanded" src="/dictproj1/public/assets/images/dictWhite.png"
         class="absolute transition-opacity duration-500 opacity-0 h-44 p-6 object-cover" alt="Expanded Logo" />
  </div>

  <!-- Navigation Links -->
  <div class="flex flex-col justify-between text-white text-sm font-semibold font-sans h-full">
    <div class="flex flex-col gap-0.5">
      <?php if ($isSuperAdmin): ?>
        <!-- Only Add User for SuperAdmin -->
        <a href="/dictproj1/index.php?page=addUser"
          class="flex items-center gap-3 p-5 transition duration-300 hover:bg-blue-800 <?php echo ($currentPage === 'addUser') ? 'bg-blue-700' : ''; ?>">
          <i data-lucide="user-plus" class="w-6 h-6 text-white"></i>
          <span class="sidebar-label hidden transition-opacity duration-300">Add User</span>
        </a>

         <a href="/dictproj1/index.php?page=auditLog"
          class="flex items-center gap-3 p-5 transition duration-300 hover:bg-blue-800 <?php echo ($currentPage === 'auditLog') ? 'bg-blue-700' : ''; ?>">
          <i data-lucide="file-sliders" class="w-6 h-6 text-white"></i>
          <span class="sidebar-label hidden transition-opacity duration-300">Audit Log</span>
        </a>

         <a href="/dictproj1/index.php?page=logHistory"
          class="flex items-center gap-3 p-5 transition duration-300 hover:bg-blue-800 <?php echo ($currentPage === 'logHistory') ? 'bg-blue-700' : ''; ?>">
          <i data-lucide="history" class="w-6 h-6 text-white"></i>
          <span class="sidebar-label hidden transition-opacity duration-300">Log History</span>
        </a>
      <?php elseif ($isAdmin || $isProvincial): ?>
        <!-- Dashboard for Admin and Provincial -->
        <a class="flex items-center gap-3 p-5 transition duration-300 hover:bg-blue-800 active:bg-blue-700 active:scale-95 <?php echo ($currentPage === 'dashboard') ? 'bg-blue-800 border-l-4 border-white' : 'border-l-4 border-transparent'; ?>"
          href="/dictproj1/index.php?page=dashboard">
          <i data-lucide="chart-spline" class="w-6 h-6 text-white"></i>
          <span class="sidebar-label hidden transition-opacity duration-300">Dashboard</span>
        </a>
        <!-- Documents Dropdown for Admin and Provincial -->
        <div class="relative">
          <button id="documentsDropdown"
            class="flex items-center gap-3 p-5 w-full text-left transition duration-300 hover:bg-blue-800 active:bg-blue-700 active:scale-95 <?php echo (in_array($currentPage, ['documents', 'incoming', 'outgoing', 'received', 'endorsed'])) ? 'bg-blue-800 border-l-4 border-white' : 'border-l-4 border-transparent'; ?>">
            <i data-lucide="file-text" class="w-6 h-6 text-white"></i>
            <span class="sidebar-label hidden transition-opacity duration-300">Documents</span>
            <i data-lucide="chevron-down" class="w-4 h-4 text-white ml-auto transition-transform duration-200"></i>
          </button>

          <div id="documentsDropdownMenu" class="absolute left-0 right-0 top-full bg-blue-900 border-l-4 border-white hidden">
            <a href="/dictproj1/index.php?page=incoming"
              class="flex items-center gap-3 p-4 pl-12 transition duration-300 hover:bg-blue-800 <?php echo ($currentPage === 'incoming') ? 'bg-blue-700' : ''; ?>">
              <i data-lucide="file-input" class="w-6 h-6 text-white"></i>
              <span class="sidebar-label hidden transition-opacity duration-300">Incoming</span>
            </a>
            <a href="/dictproj1/index.php?page=outgoing"
              class="flex items-center gap-3 p-4 pl-12 transition duration-300 hover:bg-blue-800 <?php echo ($currentPage === 'outgoing') ? 'bg-blue-700' : ''; ?>">
              <i data-lucide="file-output" class="w-6 h-6 text-white"></i>
              <span class="sidebar-label hidden transition-opacity duration-300">Outgoing</span>
            </a>
            <a href="/dictproj1/index.php?page=received"
              class="flex items-center gap-3 p-4 pl-12 transition duration-300 hover:bg-blue-800 <?php echo ($currentPage === 'received') ? 'bg-blue-700' : ''; ?>">
              <i data-lucide="file-check" class="w-6 h-6 text-white"></i>
              <span class="sidebar-label hidden transition-opacity duration-300">Received</span>
            </a>
            <?php if ($isAdmin): ?>
            <a href="/dictproj1/index.php?page=endorsed"
              class="flex items-center gap-3 p-4 pl-12 transition duration-300 hover:bg-blue-700 <?php echo ($currentPage === 'endorsed') ? 'bg-blue-700' : ''; ?>">
              <i data-lucide="refresh-ccw" class="w-6 h-6 text-white"></i>
              <span class="sidebar-label hidden transition-opacity duration-300">Endorsed</span>
            </a>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- Footer -->
    <div class="text-white">
      <a id="logout" class="flex gap-3 items-center p-5 mb-1 transition duration-300 hover:bg-blue-800 
        <?php echo ($currentPage === 'logout') ? 'bg-blue-600' : ''; ?>" 
        href="/dictproj1/index.php?page=logout">
         <i data-lucide="log-out" class="w-6 h-6 text-white"></i>
        <span class="sidebar-label hidden transition-opacity duration-300">Sign Out</span>
      </a>
    </div>
    
  </div>
</div>

<!-- Mobile Sidebar Overlay -->
<div id="mobileSidebar" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden sm:hidden">
  <div id="mobileSidebarContent" class="fixed left-0 top-0 h-full border border-green-300 w-64 bg-blue-950 transform -translate-x-full transition-transform duration-300 ease-in-out overflow-y-auto">

    <!-- Logo Section -->
    <div class="relative h-36 w-full flex items-center justify-center">
      <img src="/dictproj1/public/assets/images/dictWhite.png"
           class="h-44 p-6 object-cover" alt="Mobile Logo" />
    </div>

    <!-- Navigation Links -->
    <div class="flex flex-col justify-between text-white text-sm font-semibold font-sans h-full">
      <div class="flex flex-col gap-0.5">

        <?php if ($isSuperAdmin): ?>
          <!-- Only Add User for SuperAdmin -->
          <a href="/dictproj1/index.php?page=addUser"
            class="flex items-center gap-3 p-5 transition duration-300 hover:bg-blue-800 <?php echo ($currentPage === 'addUser') ? 'bg-blue-700' : ''; ?>">
            <i data-lucide="user-plus" class="w-6 h-6 text-white"></i>
            <span>Add User</span>
          </a>

           <a href="/dictproj1/index.php?page=auditLog"
            class="flex items-center gap-3 p-5 transition duration-300 hover:bg-blue-800 <?php echo ($currentPage === 'auditLog') ? 'bg-blue-700' : ''; ?>">
            <i data-lucide="file-sliders" class="w-6 h-6 text-white"></i>
            <span>Audit Log</span>
          </a>

           <a href="/dictproj1/index.php?page=logHistory"
            class="flex items-center gap-3 p-5 transition duration-300 hover:bg-blue-800 <?php echo ($currentPage === 'logHistory') ? 'bg-blue-700' : ''; ?>">
            <i data-lucide="history" class="w-6 h-6 text-white"></i>
            <span>Log History</span>
          </a>
        <?php elseif ($isAdmin || $isProvincial): ?>
          <!-- Dashboard for Admin and Provincial -->
          <a class="flex items-center gap-3 p-5 transition duration-300 hover:bg-blue-800 active:bg-blue-700 active:scale-95 <?php echo ($currentPage === 'dashboard') ? 'bg-blue-800 border-l-4 border-white' : 'border-l-4 border-transparent'; ?>"
            href="/dictproj1/index.php?page=dashboard">
            <i data-lucide="chart-spline" class="w-6 h-6 text-white"></i>
            <span>Dashboard</span>
          </a>
          <!-- Documents Dropdown for Admin and Provincial -->
          <div class="relative">
            <button id="mobileDocumentsDropdown"
              class="flex items-center gap-3 p-5 w-full text-left transition duration-300 hover:bg-blue-800 active:bg-blue-700 active:scale-95 <?php echo (in_array($currentPage, ['documents', 'incoming', 'outgoing', 'received', 'endorsed'])) ? 'bg-blue-800 border-l-4 border-white' : 'border-l-4 border-transparent'; ?>">
              <i data-lucide="file-text" class="w-6 h-6 text-white"></i>
              <span>Documents</span>
               <i data-lucide="chevron-down" class="w-4 h-4 text-white ml-auto transition-transform duration-200"></i>
            </button>

            <div id="mobileDocumentsDropdownMenu" class="bg-blue-900 border-l-4 border-white hidden">
              <a href="/dictproj1/index.php?page=incoming"
                class="flex items-center gap-3 p-4 pl-12 transition duration-300 hover:bg-blue-800 <?php echo ($currentPage === 'incoming') ? 'bg-blue-700' : ''; ?>">
                <i data-lucide="file-input" class="w-6 h-6 text-white"></i>
                <span>Incoming</span>
              </a>
              <a href="/dictproj1/index.php?page=outgoing"
                class="flex items-center gap-3 p-4 pl-12 transition duration-300 hover:bg-blue-800 <?php echo ($currentPage === 'outgoing') ? 'bg-blue-700' : ''; ?>">
                <i data-lucide="file-output" class="w-6 h-6 text-white"></i>
                <span>Outgoing</span>
              </a>
              <a href="/dictproj1/index.php?page=received"
                class="flex items-center gap-3 p-4 pl-12 transition duration-300 hover:bg-blue-800 <?php echo ($currentPage === 'received') ? 'bg-blue-700' : ''; ?>">
                <i data-lucide="file-check" class="w-6 h-6 text-white"></i>
                <span>Received</span>
              </a>
              <?php if ($isAdmin): ?>
              <a href="/dictproj1/index.php?page=endorsed"
                class="flex items-center gap-3 p-4 pl-12 transition duration-300 hover:bg-blue-700 <?php echo ($currentPage === 'endorsed') ? 'bg-blue-700' : ''; ?>">
                <i data-lucide="refresh-ccw" class="w-6 h-6 text-white"></i>
                <span>Endorsed</span>
              </a>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
       </div>

       <!-- Footer -->
        <div class="text-white border border-yellow-500">
         <a id="mobileLogout" class="flex gap-3 items-center p-5 mb-1 transition duration-300 hover:bg-blue-800 
           <?php echo ($currentPage === 'logout') ? 'bg-blue-600' : ''; ?>" 
           href="/dictproj1/index.php?page=logout">
           <i data-lucide="log-out" class="w-6 h-6 text-white"></i>
            <span class="">Sign Out</span>
         </a>
        </div>
      
    </div>
  </div>
</div>

<!-- Scripts -->


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script src="/dictproj1/public/Scripts/sidebar/collapse-sidebar.js"> </script>
<script src="/dictproj1/public/Scripts/pwa-init.js"></script>
<script src="/dictproj1/public/Scripts/sidebar/SidebarDropdown.js"></script>
<script src="/dictproj1/public/Scripts/sidebar/LogoutConfirm.js"></script> 
</body>
</html>
