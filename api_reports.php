<?php
header("Content-Type: application/json");
require_once "db_config.php";

$action = $_GET["action"] ?? "";

// 1. WHOLESALE REPORT (Consolidated Weekly Order)
if ($action == "wholesale") {
    $start = $_GET["start"] ?? "";
    $end = $_GET["end"] ?? "";

    $sql = "SELECT gi.ItemID, gi.Description, 
                   SUM(mo.Quantity) AS TotalVolumeDemanded, 
                   SUM(mo.TotalPrice) AS CumulativeCostValue
            FROM tblmemberorders mo
            INNER JOIN tblgroceryitems gi ON mo.ItemID = gi.ItemID
            WHERE mo.OrderDate BETWEEN ? AND ?
            GROUP BY gi.ItemID, gi.Description
            ORDER BY TotalVolumeDemanded DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start, $end);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) { $data[] = $row; }

    echo json_encode(["success" => true, "data" => $data]);
    exit;
}

// 2. POPULARITY REPORT (Item Trends)
if ($action == "popularity") {
    $sql = "SELECT gi.ItemID, gi.Description, 
                   COUNT(mo.OrderID) AS TotalFrequencyOrdered, 
                   SUM(mo.Quantity) AS GrossUnitsDistributed
            FROM tblgroceryitems gi
            LEFT JOIN tblmemberorders mo ON gi.ItemID = mo.ItemID
            GROUP BY gi.ItemID, gi.Description
            ORDER BY TotalFrequencyOrdered DESC";

    $result = $conn->query($sql);
    $data = [];
    while ($row = $result->fetch_assoc()) { $data[] = $row; }

    echo json_encode(["success" => true, "data" => $data]);
    exit;
}

// 3. NEW: MEMBER PARTICIPATION REPORT (Required for Phase 3)
if ($action == "member_participation") {
    $start = $_GET["start"] ?? "";
    $end = $_GET["end"] ?? "";

    $sql = "SELECT u.Username, u.Name, u.Surname, 
                   COUNT(mo.OrderID) AS TotalOrdersPlaced,
                   SUM(mo.TotalPrice) AS TotalSpending
            FROM tblUsers u
            LEFT JOIN tblmemberorders mo ON u.Username = mo.Username
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
    exit;
}

echo json_encode([
    "success" => false, 
    "message" => "Invalid report action or missing parameters."
]);
?>
