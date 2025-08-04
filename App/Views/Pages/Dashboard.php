<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="manifest" href="/dictproj1/manifest.json">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/dashboard.css">
    <link rel="stylesheet" href="/dictproj1/public/assets/css/chatbot-widget.css">
    <link rel="stylesheet" href="/dictproj1/src/input.css">
    <title>Document</title>
</head>

<body>
    <div class="app-container">

        <?php
        // if (session_status() === PHP_SESSION_NONE) {
        //     session_start();
        // }

        // // Check if user is logged in
        // if (!isset($_SESSION['uNameLogin'])) {
        //     header("Location: Login.php");
        //     exit();
        // }
        
        // // Check user type for validation
        // if (!isset($_SESSION['userAuthLevel'])) {
        //     // Redirect to login if no auth level is set
        //     header("Location: Login.php");
        //     exit();
        // }

        // if (isset($_SESSION['userAuthLevel']) && strtolower($_SESSION['userAuthLevel']) === 'superadmin') {
        //     header('Location: Documents.php');
        //     exit();
        // }

        // // Include database connection
        // include __DIR__ . '/../../Model/connect.php';

        // // Query to get the count of pending documents
        // $sql_pending = "SELECT COUNT(*) as pending_count FROM maindoc WHERE status = 'pending'";
        // $result_pending = $conn->query($sql_pending);
        // $pending_count = 0;
        // if ($result_pending && $result_pending->num_rows > 0) {
        //     $row_pending = $result_pending->fetch_assoc();
        //     $pending_count = $row_pending['pending_count'];
        // }
        // //Quert to get the count of files sent by day
        // $sql_sent_today = "SELECT COUNT(*) as sent_today_count FROM maindoc WHERE DATE(dateAndTime) = CURDATE()";
        // $result_sent_today = $conn->query($sql_sent_today);
        // $sent_today_count = 0;

        // if ($result_sent_today && $result_sent_today->num_rows > 0) {
        //     $row_sent_today = $result_sent_today->fetch_assoc();
        //     $sent_today_count = $row_sent_today['sent_today_count'];
        // }
        // // Query to get the count of outgoing documents
        // $sql_outgoing = "SELECT COUNT(*) as outgoing_count FROM maindoc WHERE fileType = 'outgoing'";
        // $result_outgoing = $conn->query($sql_outgoing);
        // $outgoing_count = 0;
        // if ($result_outgoing && $result_outgoing->num_rows > 0) {
        //     $row_outgoing = $result_outgoing->fetch_assoc();
        //     $outgoing_count = $row_outgoing['outgoing_count'];
        // }

        // $sql_received = "SELECT COUNT(*) as received_count FROM maindoc WHERE status = 'received'";
        // $result_received = $conn->query($sql_received);
        // $received_count = 0;
        // if ($result_received && $result_received->num_rows > 0) {
        //     $row_received = $result_received->fetch_assoc();
        //     $received_count = $row_received['received_count'];
        // }

        include __DIR__ . '/../components/Sidebar.php';
        ?>
        <div class="dboard-layout">

            <div class="box1">
                <div class="box1-wrapper flex justify-between min-w-full items-center max-sm:justify-start">
                    
                    <i data-lucide="menu" id="burger" class="burg w-7 h-10 hidden text-gray-400 stroke-[3.5] max-sm:block" ></i>
                    <h1 class="text-4xl font-bold px-3 text-blue-900">Dashboard</h1>
                 
                    <div class="acc pr-5 max-sm:hidden">
                        <p class="text-lg font-normal capitalize text-gray-600">Welcome, <?php echo htmlspecialchars($_SESSION['uNameLogin']); ?>!</p>
                        <p class="text-gray-500 font-semibold uppercase"><?php echo htmlspecialchars($_SESSION['userAuthLevel']); ?></p>
                    </div>
                </div>

            </div>


            <div class="box2">
                <h2 class="text-md font-semibold mb-4 pl-1.5">Total Received</h2>
                <div class="flex items-center gap-2">
                    <div class="bg-white/30 border border-white/20 backdrop-blur-md p-4 rounded-full flex items-center justify-center shadow-lg">
                        <i data-lucide="file-check-2" class="w-6 h-6 text-[#4E9F3D]" style="stroke-width:2.5;"></i>
                    </div>
                    <p class="text-2xl  font-bold text-white pl-1.5"><?php htmlspecialchars($data['pending_count']) ?></p>

                </div>
            </div>


            <div class="box3">
                <h2 class="text-md font-semibold mb-4 pl-1.5">Pending</h2>
                <div class="flex items-center gap-2">
                    <div class="bg-white/30 border border-white/20 backdrop-blur-md p-4 rounded-full flex items-center justify-center shadow-lg">
                        <i data-lucide="list-todo" class="w-6 h-6 text-[#222831]"></i>
                    </div>
                    <p class="text-2xl font-bold text-white pl-1.5 "><?php echo $pending_count; ?></p>
                </div>
            </div>

            <div class="box4">
                <h2 class="text-md font-semibold mb-4 pl-1.5">Total Sent</h2>
                <div class="flex items-center gap-2">
                    <div class="bg-white/30 border border-white/20 backdrop-blur-md p-4 rounded-full flex items-center justify-center shadow-lg">
                        <i data-lucide="send" class="w-6 h-6 text-[#C62300]"></i>
                    </div>
                    <p class="text-2xl font-bold text-white pl-1.5 "><?php echo $outgoing_count; ?></p>
                </div>
            </div>

            <div class="box5">
                <h2 class="text-md font-semibold mb-4 pl-1.5">Daily Sent</h2>
                <div class="flex items-center gap-2">
                    <div class="bg-white/30 border border-white/20 backdrop-blur-md p-4 rounded-full flex items-center justify-center shadow-lg">
                        <i data-lucide="truck" class="w-6 h-6 text-[#7A1CAC]"></i>
                    </div>
                    <p class="text-2xl font-bold text-white pl-1.5"> <?php echo $sent_today_count; ?></p>
                </div>
            </div>

            <div class="box6">
                <h2 class="text-lg font-semibold pl-1.5">Sent per PO</h2>

                <div class="chart-container flex min-h-[90%]">
                    <div class="chartWrapper m-auto w-full h-[230px] min-w-0 mr-2">
                        <canvas id="papersChart" class="w-full h-full"></canvas>
                    </div>
             <div id="chartLegend" class="chart-legend text-white gap-1.5 flex flex-col justify-center text-[0.6rem]"></div>
                </div>
               

            </div>

            <div class="box7 ">
                <h2 class="text-lg font-semibold pl-1.5">Delivery Modes</h2>
                <div class= "m-auto h-60 max-w-full flex items-center">
                    <canvas id="myChart" class="h-auto w-full max-w-full"></canvas>
                </div>
            </div>

            <div class="box8">
                <h2 class="text-lg font-semibold pl-1.5">Monthly Sent</h2>
                <div class="m-auto h-60 max-w-full flex items-center ">
                    <canvas id="myChart2" class="h-auto w-full max-w-full"></canvas>
                </div>
            </div>

            <div class="box9 ">
                <h2 class="text-lg font-semibold pl-1.5">Recent Documents</h2>
                <div class="overflow-x-auto max-h-[360px]">
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
                    <table class="dashboard-table min-w-full">
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

    <button id="chatbot-float-btn" title="Open Botchokoy">
  <i class="fa-solid fa-robot"></i>
</button>
<div id="chatbot-float-iframe-container">
  <iframe
    id="chatbot-float-iframe"
    src="/dictproj1/chatbot.html"
    style="background: transparent; border: none; box-shadow: none; width: 400px; height: 600px; display: block;"
    allowtransparency="true"
    frameborder="0"
  ></iframe>
</div>
 <script>
        document.addEventListener("DOMContentLoaded", function() {
            lucide.createIcons();
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
     <script src="/dictproj1/public/Scripts/sidebar/mobileSidebar.js"></script>
    <script src="/dictproj1/public/Scripts/graphs/pieGraph.js"></script>
    <script src="/dictproj1/public/Scripts/graphs/barGraph.js"></script>
    <script src="/dictproj1/public/Scripts/graphs/lineGraph.js"></script>
    <script src="/dictproj1/public/Scripts/toggleSidebar.js"></script> <!--for mobile responsive-->
    <script src="/dictproj1/public/Scripts/pwa-init.js"></script>
    <script src="/dictproj1/public/Scripts/chatbot.js"></script>

</body>
</html>