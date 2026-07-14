<?php
header("Content-Type: application/json");
require_once "db_config.php"; // Connect to Railway database

$action = $_GET["action"] ?? "";

// Helper to return error in a clean JSON format
function sendError($message) {
    echo json_encode(["success" => false, "message" => $message]);
    exit;
}

// 1. WHOLESALE REPORT: Aggregates total demand per item for a specific week
if ($action == "wholesale") {
    $start = $_GET["start"] ?? "";
    $end = $_GET["end"] ?? "";
    
    // Using SUM and GROUP BY to consolidate individual orders into a master list
    $sql = "SELECT gi.ItemID, gi.Description, SUM(mo.Quantity) AS TotalVolumeDemanded, SUM(mo.TotalPrice) AS CumulativeCostValue 
            FROM tblmemberorders mo 
            INNER JOIN tblgroceryitems gi ON mo.ItemID = gi.ItemID 
            WHERE mo.OrderDate BETWEEN ? AND ? 
            GROUP BY gi.ItemID, gi.Description ORDER BY TotalVolumeDemanded DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start, $end);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) { $data[] = $row; }
    echo json_encode(["success" => true, "data" => $data]);
} 
// 2. POPULARITY REPORT: Analyzes item ordering trends
elseif ($action == "popularity") {
    // Left join ensures all items are shown even if they have 0 orders
    $sql = "SELECT gi.ItemID, gi.Description, COUNT(mo.OrderID) AS TotalFrequencyOrdered, SUM(mo.Quantity) AS GrossUnitsDistributed 
            FROM tblgroceryitems gi 
            LEFT JOIN tblmemberorders mo ON gi.ItemID = mo.ItemID 
            GROUP BY gi.ItemID, gi.Description ORDER BY TotalFrequencyOrdered DESC";
    $result = $conn->query($sql);
    $data = [];
    while ($row = $result->fetch_assoc()) { $data[] = $row; }
    echo json_encode(["success" => true, "data" => $data]);
} 
// 3. NEW: MEMBER PARTICIPATION REPORT
elseif ($action == "member_participation") {
    $start = $_GET["start"] ?? "";
    $end = $_GET["end"] ?? "";
    
    // Changed 'tblUsers' to 'tblusers' to match your database
    $sql = "SELECT u.MemberID, u.Name, u.Surname, 
                   COUNT(mo.OrderID) AS TotalOrdersPlaced,
                   SUM(mo.TotalPrice) AS TotalSpending 
            FROM tblusers u 
            LEFT JOIN tblmemberorders mo ON u.MemberID = mo.MemberID 
            WHERE mo.OrderDate BETWEEN ? AND ? 
            GROUP BY u.MemberID, u.Name, u.Surname 
            ORDER BY TotalOrdersPlaced DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start, $end);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) { $data[] = $row; }
    echo json_encode(["success" => true, "data" => $data]);
}
else {
    sendError("Invalid action.");
}
?>
