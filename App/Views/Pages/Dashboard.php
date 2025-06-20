<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="/dictproj1/public/assets/css/dashboard.css">
  <link rel="stylesheet" href="/dictproj1/src/input.css">

    <title>Document</title>
</head>
<body>
   <div class="app-container">

<?php include __DIR__ . '/../components/Sidebar.php'; ?>
     <div class="dboard-layout">

             <div class="box1">
                <div class="">
              <h1 class="text-3xl font-bold text-blue-900">Dashboard</h1>
              </div>
              
            </div>

             <div class="box2">
               <h2 class="text-lg font-semibold pl-1.5">Total Received</h2>
               
            </div>

            
             <div class="box3">
               <h2 class="text-lg font-semibold pl-1.5">Pending</h2>
            </div>

             <div class="box4">
               <h2 class="text-lg font-semibold pl-1.5">Total Sent</h2>
            </div>

             <div class="box5">
               <h2 class="text-lg font-semibold pl-1.5">Daily Sent</h2>
            </div>
             <div class="box6">
               <h2 class="text-lg font-semibold pl-1.5">Sent per PO</h2>
               
            </div>

            <div class="box7">
               <h2 class="text-lg font-semibold pl-1.5">Delivery</h2>
               
            </div>

            <div class="box8">
               <h2 class="text-lg font-semibold pl-1.5">Monthly Sent</h2>
            </div>

            <div class="box9">
               <h2 class="text-lg font-semibold pl-1.5">Table 1</h2>
            </div>

            <div class="box10">
               <h2 class="text-lg font-semibold pl-1.5">Table 2</h2>
            </div>
        </div>
</div>
</body>
</html>