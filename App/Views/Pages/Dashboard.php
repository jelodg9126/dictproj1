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
    <link rel="icon" href="/dictproj1/public/assets/images/mainCircle.png" type="image/png">
    <title>
        <?php
        $authLevel = strtolower($_SESSION['userAuthLevel'] ?? '');

        if ($authLevel === 'admin' || $authLevel === 'superadmin') {
            echo 'DICT — ' . ucfirst($authLevel);
        } else {
            echo 'DICT — ' . htmlspecialchars($location ?? 'Office');
        }
        ?>
    </title>
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
                    <div>
                        <i data-lucide="menu" id="burger" class="burg w-7 h-10 hidden text-gray-400 stroke-[3.5] max-sm:block"></i>
                        <h1 class="text-4xl font-bold px-3 text-blue-900">Dashboard</h1>
                    </div>



                    <div class="flex items-center max-w-xs">

                        <!-- Left Side: Welcome and Role -->
                        <div class="flex flex-col leading-tight mr-4">
                            <p class="text-lg font-normal capitalize text-gray-600">Welcome, <?php echo ($data['name']); ?>!</p>
                            <p class="text-gray-500 font-semibold uppercase"><?php echo htmlspecialchars($_SESSION['userAuthLevel']); ?></p>
                        </div>

                        <!-- Right Side: Icon -->
                        <div class="relative inline-block ml-4">
                            <button id="iconButton" class="transition duration-300 transform hover:scale-110 hover:bg-gradient-to-r from-blue-600 to-cyan-400 rounded-full border border-white p-2">
                                <i data-lucide="user-round" class="w-8 h-8 text-white"></i>
                            </button>

                            <!-- Cute Dropdown -->
                            <div id="dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-2 z-50">
                                <a href="#" id="openProfileBtn" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-100">Profile</a>
                                <a href="#" id="openEmailConfirmBtn" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-100">Reset Password</a>
                                <button id="logoutBtn" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-100">Logout</button>
                            </div>
                        </div>

                        <!-- Email Modal -->
                        <div id="emailConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden scale-0 opacity-0 transition-transform duration-300 origin-center">
                            <div class="bg-white rounded-2xl p-6 w-full max-w-lg min-w-sm">
                                <div class="text-center">
                                    <i data-lucide="badge-alert"
                                        class="w-12 h-12 text-green-500 mx-auto mb-2"
                                        style="animation: breathe 2s ease-in-out infinite;"></i>
                                    <h2 class="text-2xl font-bold mb-2 text-center">Email Verification</h2>
                                    <p class="text-sm text-black mb-8 !text-black text-center">Please confirm your email before resetting your password.</p>
                                </div>
                                <div class="mb-4">
                                    <label for="confirmEmail" class="block text-lg font-medium text-gray-700 mb-1">Email Address</label>
                                    <input type="email" id="confirmEmail" class="w-full px-3 py-2 border rounded bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="@dict.gov.ph">
                                </div>
                                <div class="flex justify-end gap-2">
                                    <button id="closeEmailConfirmBtn" class="px-4 py-2 text-sm bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
                                    <button id="proceedToReset" class="px-4 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">Continue</button>
                                </div>
                            </div>
                        </div>
                        <!-- OTP Modal -->
                        <div id="otpModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden scale-0 opacity-0 transition-transform duration-300 origin-center">
                            <div class="bg-white rounded-lg shadow-md p-6 w-full max-w-md">
                                <h2 class="text-xl font-bold mb-4 text-center">Enter OTP</h2>
                                <p class="text-sm text-black mb-4 text-center">We have sent an OTP to your email.</p>
                                <div class="mb-4">
                                    <label for="otpInput" class="block text-sm font-medium text-gray-700 mb-1">OTP Code</label>
                                    <input type="text" id="otpInput" maxlength="6" class="w-full px-3 py-2 border rounded bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                </div>
                                <div class="flex justify-end gap-2">
                                    <button id="closeOtpModalBtn" class="px-4 py-2 text-sm bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
                                    <button id="verifyOtpBtn" class="px-4 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">Verify OTP</button>
                                </div>
                            </div>
                        </div>


                        <!-- Settings Modal -->
                        <div id="modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden scale-0 opacity-0 transition-transform duration-300 origin-center">
                            <div class="bg-white p-8 rounded-3xl shadow-lg w-96 relative">
                                <h2 class="text-xl font-bold mb-4">Password Reset</h2>
                                <div class="mb-3 text-left transition-transform duration-200">
                                    <label class="block mb-1 text-sm font-medium text-gray-700">New Password</label>
                                    <div class="relative">
                                        <input
                                            type="password"
                                            id="newPassword"
                                            class="w-full px-3 py-2 pr-10 border rounded bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-400 transform focus:scale-105 transition-transform duration-200">
                                        <button
                                            type="button"
                                            id="toggleNewPassword"
                                            class="absolute inset-y-0 right-2 flex items-center text-gray-500 hover:text-gray-800"
                                            tabindex="-1">
                                            <i id="newPasswordIcon" data-lucide="eye-closed" class="w-5 h-5"></i>
                                        </button>
                                    </div>
                                    <!-- ✅ Put this right after the input box, still inside .mb-3 -->
                                    <ul id="passwordValidation" class="mt-2 text-sm text-gray-600 space-y-1 pl-1">
                                        <li class="flex items-center gap-2" data-rule="length">
                                            <i data-lucide="circle" class="w-5 h-5 text-gray-400"></i>
                                            At least 8 characters
                                        </li>
                                        <li class="flex items-center gap-2" data-rule="uppercase">
                                            <i data-lucide="circle" class="w-5 h-5 text-gray-400"></i>
                                            At least 1 uppercase letter
                                        </li>
                                        <li class="flex items-center gap-2" data-rule="number">
                                            <i data-lucide="circle" class="w-5 h-5 text-gray-400"></i>
                                            At least 1 number
                                        </li>
                                        <li class="flex items-center gap-2" data-rule="special">
                                            <i data-lucide="circle" class="w-5 h-5 text-gray-400"></i>
                                            At least 1 special character (!@#$...)
                                        </li>
                                    </ul>
                                </div>

                                <div class="mb-3 text-left transition-transform duration-200">
                                    <label class="block mb-1 text-sm font-medium text-gray-700">Confirm Password</label>
                                    <div class="relative">
                                        <input
                                            type="password"
                                            id="confirmPassword"
                                            class="w-full px-3 py-2 pr-10 border rounded bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-400 transform focus:scale-105 transition-transform duration-200">
                                        <button
                                            type="button"
                                            id="toggleConfirmPassword"
                                            class="absolute inset-y-0 right-2 flex items-center text-gray-500 hover:text-gray-800"
                                            tabindex="-1">
                                            <i data-lucide="eye-closed" class="w-5 h-5"></i>
                                        </button>
                                    </div>
                                    <p id="confirmPasswordError" class=" hidden mt-1 text-sm text-red-600 !text-red-600 !shadow-none !filter-none !text-opacity-100">
                                        Passwords do not match
                                    </p>
                                </div>

                                <div class="flex justify-end space-x-2 mt-4">
                                    <button id="closeModalBtn" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                                    <button id="confirmButton" class="bg-blue-600 text-white px-4 py-2 rounded disabled:opacity-50" disabled>
                                        Confirm
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Profile -->
                        <div id="profileModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 scale-0 transition-transform duration-300 origin-center">
                            <div class="bg-white p-8 rounded-3xl shadow-lg w-[700px] relative">
                                <div class="text-center mb-2 ">
                                    <i data-lucide="user-round-check" class="w-10 h-10 text-black-600 mx-auto mb-2"></i>
                                    <h2 class="text-xl font-semibold mb-4">User Profile</h2>
                                </div>

                                <div class="text-left space-y-4">
                                    <!-- Name -->
                                    <div>
                                        <p><span class="font-semibold text-black">Name</span></p>
                                        <div class="mt-1 px-4 py-2 bg-gray-100 border border-gray-300 rounded text-m text-gray-700 transition-all duration-300 hover:scale-[1.02] hover:shadow-md">
                                            <?php echo ($data['name']); ?>
                                        </div>
                                    </div>

                                    <!-- Email -->
                                    <div>
                                        <p><span class="font-semibold text-black">Email</span></p>
                                        <div class="mt-1 px-4 py-2 bg-gray-100 border border-gray-300 rounded text-m text-gray-700 transition-all duration-300 hover:scale-[1.02] hover:shadow-md">
                                            <?= $data['email'] ?>
                                        </div>
                                    </div>

                                    <!-- Office -->
                                    <div>
                                        <p><span class="font-semibold text-black">Office</span> </p>
                                        <div class="mt-1 px-4 py-2 bg-gray-100 border border-gray-300 rounded text-m text-gray-700 transition-all duration-300 hover:scale-[1.02] hover:shadow-md">
                                            <?= $data['office'] ?>
                                        </div>
                                    </div>

                                    <!-- Role -->
                                    <div>
                                        <p><span class="font-semibold text-black">Role</span></p>
                                        <div class="mt-1 px-4 py-2 bg-gray-100 border border-gray-300 rounded text-m text-gray-700 transition-all duration-300 hover:scale-[1.02] hover:shadow-md">
                                            <?php echo htmlspecialchars($_SESSION['userAuthLevel']); ?>
                                        </div>
                                    </div>
                                </div>


                                <div class="flex justify-end space-x-2 mt-6">
                                    <button id="closeProfileBtn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Close</button>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>

            </div>

















            <div class="box2">
                <h2 class="text-md font-semibold mb-4 pl-1.5">Total Received</h2>
                <div class="flex items-center gap-2">
                    <div class="bg-white/30 border border-white/20 backdrop-blur-md p-4 rounded-full flex items-center justify-center shadow-lg">
                        <i data-lucide="file-check-2" class="w-6 h-6 text-[#4E9F3D]" style="stroke-width:2.5;"></i>
                    </div>
                    <p class="text-2xl  font-bold text-white pl-1.5"><?php echo htmlspecialchars($data['pending_count']) ?></p>

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
                <div class="m-auto h-60 max-w-full flex items-center">
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
            frameborder="0"></iframe>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            lucide.createIcons();
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="/dictproj1/public/Scripts/dashboard.js"></script>
    <script src="/dictproj1/public/Scripts/sidebar/mobileSidebar.js"></script>
    <script src="/dictproj1/public/Scripts/graphs/pieGraph.js"></script>
    <script src="/dictproj1/public/Scripts/graphs/barGraph.js"></script>
    <script src="/dictproj1/public/Scripts/graphs/lineGraph.js"></script>
    <script src="/dictproj1/public/Scripts/toggleSidebar.js"></script> <!--for mobile responsive-->
    <script src="/dictproj1/public/Scripts/pwa-init.js"></script>
    <script src="/dictproj1/public/Scripts/chatbot.js"></script>

</body>

</html>