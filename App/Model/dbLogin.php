<?php
	$servername="localhost";
    $username="root";
	$password="";
	$dbname="documents";

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);

	// Check connection
	if ($conn->connect_error) {
	    die("Connection failed : ".$conn->connect_error);
    }


    // SQL query to select user
    $sql = "SELECT * FROM users WHERE userName='". $_POST['uNameLogin'] ."' AND passWord='". $_POST['pNameLogin'] . "'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
    // If a match is found, session variables are set
    while($row = $result->fetch_assoc()) {
        $_SESSION['uNameLogin'] = $row["username"];
        $_SESSION['pNameLogin'] = $row["password"];
        header("Location: ../Views/Pages/Documents.php");
        exit();
    }
    } else {
        ?>
        <script>
            window.alert("Invalid username or password");
        </script>
        <?php
        header("Location: ../Views/Pages/Login.php");
        exit();
    }
    
    // Close Conection
    mysqli_close($conn);

?>
