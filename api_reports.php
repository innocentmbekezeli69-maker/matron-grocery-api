<?php
header("Content-Type: application/json");
require_once "db_config.php";

$action = $_GET["action"] ?? "";

// Helper function to return standardized error messages
function sendError($message) {
    echo json_encode(["success" => false, "message" => $message]);
    exit;
}

// 1. WHOLESALE REPORT
// Consolidates quantity and cost for inventory demand tracking
if ($action == "wholesale") {
    $start = $_GET["start"] ?? "";
    $end = $_GET["end"] ?? "";
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

// 2. POPULARITY REPORT
// Analyzes item frequency regardless of specific time ranges
elseif ($action == "popularity") {
    $sql = "SELECT gi.ItemID, gi.Description, COUNT(mo.OrderID) AS TotalFrequencyOrdered, SUM(mo.Quantity) AS GrossUnitsDistributed 
            FROM tblgroceryitems gi 
            LEFT JOIN tblmemberorders mo ON gi.ItemID = mo.ItemID 
            GROUP BY gi.ItemID, gi.Description ORDER BY TotalFrequencyOrdered DESC";
    $result = $conn->query($sql);
    $data = [];
    while ($row = $result->fetch_assoc()) { $data[] = $row; }
    echo json_encode(["success" => true, "data" => $data]);
} 

// 3. MEMBER PARTICIPATION REPORT
// Links Users to Orders via MemberID to calculate loyalty metrics
elseif ($action == "member_participation") {
    $start = $_GET["start"] ?? "";
    $end = $_GET["end"] ?? "";
    
    // SQL joins tblusers (u) and tblmemberorders (mo) 
    // Uses Username from users and MemberID from orders as the join key
    $sql = "SELECT u.Username, u.Name, u.Surname, 
                   COUNT(mo.OrderID) AS TotalOrdersPlaced,
                   SUM(mo.TotalPrice) AS TotalSpending 
            FROM tblusers u 
            LEFT JOIN tblmemberorders mo ON u.Username = mo.MemberID 
            WHERE mo.OrderDate BETWEEN ? AND ? 
            GROUP BY u.Username, u.Name, u.Surname 
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
