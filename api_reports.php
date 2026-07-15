<?php
header("Content-Type: application/json");
require_once "db_config.php";

$action = $_GET["action"] ?? "";

function sendError($message) {
    echo json_encode(["success" => false, "message" => $message]);
    exit;
}

// 1. WHOLESALE REPORT
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
elseif ($action == "member_participation") {
    $start = $_GET["start"] ?? "";
    $end = $_GET["end"] ?? "";

    if (empty($start) || empty($end)) {
        sendError("Start and End dates are required.");
    }

    $sql = "SELECT u.Username, m.Name, m.Surname, 
                   COUNT(mo.OrderID) AS TotalOrdersPlaced, 
                   COALESCE(SUM(mo.TotalPrice), 0) AS TotalSpending 
            FROM tblusers u 
            INNER JOIN tblmembers m ON u.Username = m.MemberID 
            LEFT JOIN tblmemberorders mo ON m.MemberID = mo.MemberID AND mo.OrderDate BETWEEN ? AND ? 
            GROUP BY u.Username, m.Name, m.Surname 
            ORDER BY TotalOrdersPlaced DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) { sendError($conn->error); }
    $stmt->bind_param("ss", $start, $end);
    if (!$stmt->execute()) { sendError($stmt->error); }
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) { $data[] = $row; }
    echo json_encode(["success" => true, "data" => $data]);
    $stmt->close();
} 

elseif ($action == "member_summary") {

    $start = $_GET["start"] ?? "";
    $end = $_GET["end"] ?? "";

    if (empty($start) || empty($end)) {
        sendError(
            "Start and End dates are required."
        );
    }

    $sql =
    "SELECT
        mo.MemberID,
        m.Name,
        m.Surname,
        gi.Description,
        mo.Quantity,
        mo.TotalPrice
     FROM tblmemberorders mo
     INNER JOIN tblmembers m
        ON mo.MemberID = m.MemberID
     INNER JOIN tblgroceryitems gi
        ON mo.ItemID = gi.ItemID
     WHERE mo.OrderDate
        BETWEEN ? AND ?
     ORDER BY
        mo.MemberID,
        gi.Description";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "ss",
        $start,
        $end
    );

    $stmt->execute();

    $result =
        $stmt->get_result();

    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode([
        "success" => true,
        "data" => $data
    ]);

    $stmt->close();
}    
else {
    sendError("Invalid action.");
}
?>
