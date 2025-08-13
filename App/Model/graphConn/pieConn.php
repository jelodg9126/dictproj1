<?php

try {
    // Query to get papers count by provincial office and month
    // Adjust the table name and column names according to your database structure
    $sql = "SELECT 
                officeName,
                MONTH(dateAndTime) as month,
                COUNT(*) as paper_count
            FROM maindoc 
            WHERE YEAR(dateAndTime) = YEAR(CURRENT_DATE())
            GROUP BY officeName, MONTH(dateAndTime)
            ORDER BY officeName, month";
    
    $stmt = $pdo->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process data for Chart.js
    $offices = [];
    $months = [];
    $paperCounts = [];
    
    foreach ($data as $row) {
        $office = $row['officeName'];
        $month = $row['month'];
        $count = (int)$row['paper_count'];
        
        if (!in_array($office, $offices)) {
            $offices[] = $office;
        }
        if (!in_array($month, $months)) {
            $months[] = $month;
        }
        
        $paperCounts[$office][$month] = $count;
    }
    
    // Sort months
    sort($months);
    
    // Prepare response
    $response = [
        'offices' => $offices,
        'months' => $months,
        'data' => $paperCounts
    ];
    
    echo json_encode($response);
    
} catch(PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 