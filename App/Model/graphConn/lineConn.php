<?php
header('Content-Type: application/json');

$sql = "SELECT 
        DATE_FORMAT(dateAndTime, '%Y-%m') as month,
        COUNT(*) as count
        FROM maindoc
        GROUP BY month
        ORDER BY month ASC ";

try{
     $stmt = $pdo->query($sql);
     $data = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[$row['month']] = (int)$row['count'];
    }
    echo json_encode($data);

} catch (PDOException $e) {
    echo json_encode(["error" => "SQL error" . $e->getMessage()]);
    exit();
}

// $conn->close();
