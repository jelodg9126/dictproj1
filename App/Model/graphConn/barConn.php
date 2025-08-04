<?php

     class barConn{
         private $dbconn;

         function __construct($conn){
            $this->conn= $dbconn;
         }

     }


header('Content-Type: application/json');


$conn = new mysqli("localhost", "root", "", "documents"); 
if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit();
}


//sql contents 

$sql = "SELECT modeOfDel, COUNT(*) as count FROM maindoc GROUP BY modeOfDel";
$result = $conn->query($sql);

$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[$row['modeOfDel']] = (int)$row['count'];
    }
} else {
    echo json_encode(["error" => "SQL error: " . $conn->error]);
    exit();
}

echo json_encode($data);
$conn->close();
