<?php
// Prevent any output before redirects
ob_start();

// Enable error reporting for debugging
ini_set('display_errors', 0); // Turn off display errors to prevent output
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Set session cookie parameters
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

// Start session and configure it properly
session_start();
session_regenerate_id(true); // Regenerate session ID for security

include_once '../Model/connect.php';

// Check if form was submitted
if (!isset($_POST['uNameLogin']) || !isset($_POST['pNameLogin'])) {
    header("Location: /dictproj1/App/Views/Pages/Login.php?error=missing_data");
    exit();
}

// Sanitize inputs to prevent SQL injection
$username = mysqli_real_escape_string($conn, $_POST['uNameLogin']);
$password = mysqli_real_escape_string($conn, $_POST['pNameLogin']);

// Prepared Statement to check database
$stmt = $conn->prepare("SELECT * FROM users WHERE userName=? AND passWord=?");
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    header("Location: /dictproj1/App/Views/Pages/Login.php?error=db_error");
    exit();
}

if ($result && $result->num_rows > 0) {
    // If a match is found, session variables are set
    $row = $result->fetch_assoc();
    $_SESSION['uNameLogin'] = $row["userName"];
    $_SESSION['user_id'] = $row["id"]; // Assuming there's an id column
    $_SESSION['login_time'] = time();
    
    // Check if userType column exists, if not set default to superAdmin
    if (isset($row['userType'])) {
        $_SESSION['userAuthLevel'] = $row['userType'];
    } else {
        // If userType column doesn't exist, set default user type
        $_SESSION['userAuthLevel'] = 'superAdmin';
        
        // Add userType column to users table if it doesn't exist
        $check_column = $conn->query("SHOW COLUMNS FROM users LIKE 'userType'");
        if ($check_column->num_rows == 0) {
            $add_column = "ALTER TABLE users ADD COLUMN userType VARCHAR(20) DEFAULT 'superAdmin'";
            $conn->query($add_column);
            
            // Update existing users to have superAdmin type
            $update_users = "UPDATE users SET userType = 'superAdmin' WHERE userType IS NULL OR userType = ''";
            $conn->query($update_users);
        }
    }
    
    // Debug: Log the user type
    error_log("User type: " . $_SESSION['userAuthLevel']);
    
    // Clear any output buffer to ensure clean redirect
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    switch($_SESSION['userAuthLevel']){
        case 'superAdmin':
            //redirect to dashboard
            error_log("Redirecting superAdmin to dashboard");
            ob_end_clean(); // Clear any output buffer
            header("Location: /dictproj1/App/Views/Pages/Dashboard.php");
            exit();
            break;
        case 'provincial':
            //redirect to dashboard
            error_log("Redirecting provincial to DashboardPO");
            ob_end_clean(); // Clear any output buffer
            header("Location: /dictproj1/App/Views/RegionalPages/DashboardPO.php");
            exit();
            break;
        default:
            error_log("Unknown user type: " . $_SESSION['userAuthLevel'] . " - defaulting to superAdmin");
            ob_end_clean(); // Clear any output buffer
            header("Location: /dictproj1/App/Views/Pages/Dashboard.php");
            exit();
            break;
    }
} else {
    // Clear any output buffer
    ob_end_clean();
    
    header("Location: /dictproj1/App/Views/Pages/Login.php?error=invalid_credentials");
    exit();
}

// If we get here, something went wrong - redirect to login
ob_end_clean();
header("Location: /dictproj1/App/Views/Pages/Login.php?error=db_error");
exit();

// Close Connection
mysqli_close($conn);
?>
