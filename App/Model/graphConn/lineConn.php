<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "documents");

if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed"]);
    exit();
}

$sql = "
    SELECT 
        DATE_FORMAT(dateAndTime, '%Y-%m') as month,
        COUNT(*) as count
    FROM maindoc

    GROUP BY month
    ORDER BY month ASC
";

$result = $conn->query($sql);
$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[$row['month']] = (int)$row['count'];
    }
    echo json_encode($data);
} else {
    echo json_encode(["error" => "SQL error"]);
}

$conn->close();
