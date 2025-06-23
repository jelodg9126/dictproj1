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

<?php
// Include database connection
include __DIR__ . '/../../Model/connect.php';

// Query to get the count of pending documents
$sql_pending = "SELECT COUNT(*) as pending_count FROM maindoc WHERE status = 'pending'";
$result_pending = $conn->query($sql_pending);
$pending_count = 0;
if ($result_pending && $result_pending->num_rows > 0) {
    $row_pending = $result_pending->fetch_assoc();
    $pending_count = $row_pending['pending_count'];
}

// Query to get the count of outgoing documents
$sql_outgoing = "SELECT COUNT(*) as outgoing_count FROM maindoc WHERE fileType = 'outgoing'";
$result_outgoing = $conn->query($sql_outgoing);
$outgoing_count = 0;
if ($result_outgoing && $result_outgoing->num_rows > 0) {
    $row_outgoing = $result_outgoing->fetch_assoc();
    $outgoing_count = $row_outgoing['outgoing_count'];
}

$sql_received = "SELECT COUNT(*) as received_count FROM maindoc WHERE status = 'received'";
$result_received = $conn->query($sql_received);
$received_count = 0;
if ($result_received && $result_received->num_rows > 0) {
    $row_received = $result_received->fetch_assoc();
    $received_count = $row_received['received_count'];
}

include __DIR__ . '/../components/Sidebar.php';
?>
     <div class="dboard-layout">

             <div class="box1">
                <div class="">
              <h1 class="text-3xl font-bold text-blue-900">Dashboard</h1>
              </div>
              
            </div>

             <div class="box2">
               <h2 class="text-lg font-semibold pl-1.5">Total Received</h2>
               <p class="text-sm text-gray-600 pl-1.5"><?php echo $received_count;?></p>
               
            </div>

            
             <div class="box3">
               <h2 class="text-lg font-semibold pl-1.5">Pending</h2>
               <p class="text-2xl font-bold text-gray-800 pl-1.5 text-center"><?php echo $pending_count; ?></p>
            </div>

             <div class="box4">
               <h2 class="text-lg font-semibold pl-1.5">Total Sent</h2>
               <p class="text-2xl font-bold text-gray-800 pl-1.5 text-center"><?php echo $outgoing_count; ?></p>
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