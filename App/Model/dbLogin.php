<?php
// // Prevent any output before redirects
// ob_start();

// // Enable error reporting for debugging
// ini_set('display_errors', 0); // Turn off display errors to prevent output
// ini_set('log_errors', 1);
// error_reporting(E_ALL);

// // Set session cookie parameters
// ini_set('session.cookie_httponly', 1);
// ini_set('session.use_only_cookies', 1);

// // Start session and configure it properly
// session_start();
// session_regenerate_id(true); // Regenerate session ID for security

// include_once '../Model/connect.php';

// // Check if form was submitted
// if (!isset($_POST['uNameLogin']) || !isset($_POST['pNameLogin'])) {
//     header("Location: /dictproj1/App/Views/Pages/Login.php?error=missing_data");
//     exit();
// }

// // Sanitize inputs to prevent SQL injection
// $username = mysqli_real_escape_string($conn, $_POST['uNameLogin']);
// $password = mysqli_real_escape_string($conn, $_POST['pNameLogin']);

// // Prepared Statement to check database
// $stmt = $conn->prepare("SELECT * FROM users WHERE userName=? AND passWord=?");
// $stmt->bind_param("ss", $username, $password);
// $stmt->execute();
// $result = $stmt->get_result();

// if (!$result) {
//     header("Location: /dictproj1/App/Views/Pages/Login.php?error=db_error");
//     exit();
// }

// if ($result && $result->num_rows > 0) {
//     // If a match is found, session variables are set
//     $row = $result->fetch_assoc();
//     $_SESSION['uNameLogin'] = $row["userName"];
//     $_SESSION['user_id'] = $row["userID"];
//     $_SESSION['userID'] = $row["userID"];
//     $_SESSION['login_time'] = time();
    
//     // Check if usertype column exists (note: lowercase in database)
//     if (isset($row['usertype'])) {
//         $_SESSION['userAuthLevel'] = $row['usertype'];
//     } else {
//         // If usertype column doesn't exist, set default user type
//         $_SESSION['userAuthLevel'] = 'Admin';
        
//         // Add usertype column to users table if it doesn't exist
//         $check_column = $conn->query("SHOW COLUMNS FROM users LIKE 'usertype'");
//         if ($check_column->num_rows == 0) {
//             $add_column = "ALTER TABLE users ADD COLUMN usertype VARCHAR(20) DEFAULT 'Admin'";
//             $conn->query($add_column);
            
//             // Update existing users to have Admin type
//             $update_users = "UPDATE users SET usertype = 'Admin' WHERE usertype IS NULL OR usertype = ''";
//             $conn->query($update_users);
//         }
//     }
    
//     // Set user type in session
//     $_SESSION['userAuthLevel'] = $row['usertype'];
    
//     // Insert login record into log_history
//     $user_id = $_SESSION['userID'];
//     $name = $row['name'];
//     $office = $row['office'];
//     $stmt_log = $conn->prepare("INSERT INTO log_history (user_id, name, office, login_time) VALUES (?, ?, ?, NOW())");
//     $stmt_log->bind_param("iss", $user_id, $name, $office);
//     $stmt_log->execute();
//     $stmt_log->close();
    
//     // Clear any output buffer to ensure clean redirect
//     if (ob_get_level()) {
//         ob_end_clean();
//     }
    
//     switch(strtolower($_SESSION['userAuthLevel'])){
//         case 'admin':
//             //redirect to dashboard
//             ob_end_clean(); // Clear any output buffer
//             header("Location: /dictproj1/App/Views/Pages/Dashboard.php");
//             exit();
//             break;
//         case 'provincial':
//             //redirect to dashboard
//             ob_end_clean(); // Clear any output buffer
//             header("Location: /dictproj1/App/Views/RegionalPages/DashboardPO.php");
//             exit();
//             break;
//         case 'superadmin':
//             ob_end_clean();
//             header("Location: /dictproj1/index.php?page=addUser");
//             exit();
//             break;
//         default:
//             ob_end_clean(); // Clear any output buffer
//             header("Location: /dictproj1/App/Views/Pages/Dashboard.php");
//             exit();
//             break;
//     }
// } else {
//     // Clear any output buffer
//     ob_end_clean();
    
//     header("Location: /dictproj1/App/Views/Pages/Login.php?error=invalid_credentials");
//     exit();
// }

// // If we get here, something went wrong - redirect to login
// ob_end_clean();
// header("Location: /dictproj1/App/Views/Pages/Login.php?error=db_error");
// exit();

// // Close Connection
// mysqli_close($conn);
?>
