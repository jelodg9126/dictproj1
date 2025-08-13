<?php

// SQL contents
$sql = "SELECT modeOfDel, COUNT(*) AS count FROM maindoc GROUP BY modeOfDel";
try {
    $stmt = $pdo->query($sql);

    $data = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[$row['modeOfDel']] = (int)$row['count'];
    }
    echo json_encode($data);

} catch (PDOException $e) {
    echo json_encode(["error" => "SQL error: " . $e->getMessage()]);
    exit();
}
