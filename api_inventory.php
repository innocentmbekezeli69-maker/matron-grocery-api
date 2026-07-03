<?php

header("Content-Type: application/json");

require_once "db_config.php";

$action = $_GET["action"] ?? "";

if($action == "list")
{
    $sql =
    "SELECT
        ItemID,
        Description,
        Price,
        Image,
        Available
     FROM tblgroceryitems
     ORDER BY ItemID";

    $result = $conn->query($sql);

    $data = [];

    while($row = $result->fetch_assoc())
    {
        $data[] = $row;
    }

    echo json_encode([
        "success" => true,
        "data" => $data
    ]);

    exit;
}

if($action == "save")
{
    $data =
    json_decode(
        file_get_contents("php://input"),
        true
    );

    $itemID =
        trim($data["ItemID"] ?? "");

    $description =
        trim($data["Description"] ?? "");

    $price =
        floatval($data["Price"] ?? 0);

    $image =
        trim($data["Image"] ?? "");

    $available =
        trim($data["Available"] ?? "Y");

    if(
        $itemID == "" ||
        $description == ""
    )
    {
        echo json_encode([
            "success" => false,
            "message" => "Missing required fields."
        ]);
        exit;
    }

    $sql =
    "INSERT INTO tblgroceryitems
    (
        ItemID,
        Description,
        Price,
        Image,
        Available
    )
    VALUES
    (
        ?,?,?,?,?
    )
    ON DUPLICATE KEY UPDATE
        Description = VALUES(Description),
        Price = VALUES(Price),
        Image = VALUES(Image),
        Available = VALUES(Available)";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "ssdss",
        $itemID,
        $description,
        $price,
        $image,
        $available
    );

    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Inventory updated successfully."
    ]);

    exit;
}

echo json_encode([
    "success" => false,
    "message" => "Invalid action."
]);
?>