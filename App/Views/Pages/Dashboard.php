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
session_start();

// Check if user is logged in
if (!isset($_SESSION['uNameLogin'])) {
    header("Location: Login.php");
    exit();
}

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
     <div class="dboard-layout opacity-95">

             <div class="box1">
                <div class="flex justify-between w-full items-center">
              <h1 class="text-3xl font-bold px-3 text-blue-900">Dashboard</h1>
              <p class="text-sm text-gray-600 p-3 border rounded-2xl">Welcome, <?php echo htmlspecialchars($_SESSION['uNameLogin']); ?>!</p>
              </div>
              
            </div>
            

             <div class="box2">
               <h2 class="text-lg font-thin pl-1.5">Total Received</h2>
               <p class="text-sm text-gray-600 pl-1.5"><?php echo $received_count;?></p>
               
            </div>

            
             <div class="box3">
               <h2 class="text-lg font-thin pl-1.5">Pending</h2>
               <p class="text-2xl font-bold text-gray-800 pl-1.5 "><?php echo $pending_count; ?></p>
            </div>

             <div class="box4">
               <h2 class="text-lg font-thin pl-1.5">Total Sent</h2>
               <p class="text-2xl font-bold text-gray-800 pl-1.5 "><?php echo $outgoing_count; ?></p>
            </div>

             <div class="box5">
               <h2 class="text-lg font-semibold pl-1.5">Daily Sent</h2>
            </div>

             <div class="box6">
            <h2 class="text-lg font-semibold pl-1.5">Sent per PO</h2>

            <div class="chart-container">

                <div class="chartWrapper">
                    <canvas id="papersChart" width="80" height="140"></canvas>
                </div>

                <div id="chartLegend" class="chart-legend"></div>

            </div>
            
            <div id="errorMessage" class="error" style="display: none;"></div>

             </div>

        

            <div class="box7 ">
               <h2 class="text-lg font-semibold pl-1.5 ">Delivery Modes</h2>
                <div class="m-auto h-60 max-w-full flex items-center ">
                   <canvas id="myChart" class="h-auto w-full max-w-full"></canvas>
                </div>
            </div>

            <div class="box8">
                <h2 class="text-lg font-semibold pl-1.5">Monthly Sent</h2>
                 <div class="m-auto h-60 max-w-full flex items-center ">
                   <canvas id="myChart2" class="h-auto w-full max-w-full"></canvas>
                </div>
            </div>

            <div class="box9">
               <h2 class="text-lg font-semibold pl-1.5">Recent Documents</h2>
               <div class="overflow-x-auto">
                   <table class="dashboard-table">
                       <thead>
                           <tr>
                               <th>Document Title</th>
                               <th>Type</th>
                               <th>Status</th>
                               <th>Date Created</th>
                           </tr>
                       </thead>
                       <tbody>
                           <tr>
                               <td>Annual Report 2024</td>
                               <td>Outgoing</td>
                               <td>
                                   <span class="status-badge status-sent">Sent</span>
                               </td>
                               <td>2024-01-15</td>
                           </tr>
                           <tr>
                               <td>Budget Proposal Q1</td>
                               <td>Incoming</td>
                               <td>
                                   <span class="status-badge status-pending">Pending</span>
                               </td>
                               <td>2024-01-14</td>
                           </tr>
                           <tr>
                               <td>Project Guidelines</td>
                               <td>Outgoing</td>
                               <td>
                                   <span class="status-badge status-received">Received</span>
                               </td>
                               <td>2024-01-13</td>
                           </tr>
                           <tr>
                               <td>Meeting Minutes</td>
                               <td>Internal</td>
                               <td>
                                   <span class="status-badge status-pending">Pending</span>
                               </td>
                               <td>2024-01-12</td>
                           </tr>
                           <tr>
                               <td>Policy Update</td>
                               <td>Outgoing</td>
                               <td>
                                   <span class="status-badge status-sent">Sent</span>
                               </td>
                               <td>2024-01-11</td>
                           </tr>
                       </tbody>
                   </table>
               </div>
            </div>

            <div class="box10">
               <h2 class="text-lg font-semibold pl-1.5">Documents by Status</h2>
               <div class="overflow-x-auto">
                   <table class="dashboard-table">
                       <thead>
                           <tr>
                               <th>Status</th>
                               <th>Count</th>
                               <th>Percentage</th>
                           </tr>
                       </thead>
                       <tbody>
                           <tr>
                               <td>
                                   <span class="status-badge status-pending">Pending</span>
                               </td>
                               <td>15</td>
                               <td>30%</td>
                           </tr>
                           <tr>
                               <td>
                                   <span class="status-badge status-received">Received</span>
                               </td>
                               <td>25</td>
                               <td>50%</td>
                           </tr>
                           <tr>
                               <td>
                                   <span class="status-badge status-sent">Sent</span>
                               </td>
                               <td>10</td>
                               <td>20%</td>
                           </tr>
                       </tbody>
                   </table>
               </div>
            </div>
        </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="/dictproj1/public/Scripts/pieGraph.js"></script>
<script src="/dictproj1/public/Scripts/barGraph.js"></script>
<script src="/dictproj1/public/Scripts/lineGraph.js"></script>
</body>
</html>