<?php

header("Content-Type: application/json");

require_once "db_config.php";

$data =
json_decode(
    file_get_contents("php://input"),
    true
);

$memberID =
$data["MemberID"] ?? "";

$orderDate =
$data["OrderDate"] ?? date("Y-m-d");

$items =
$data["Items"] ?? [];

if(empty($items))
{
    echo json_encode([
        "success"=>false,
        "message"=>"No items supplied."
    ]);
    exit;
}

$conn->begin_transaction();

try
{
    $sql =
    "INSERT INTO tblmemberorders
    (
        MemberID,
        OrderDate,
        ItemID,
        Quantity,
        TotalPrice
    )
    VALUES
    (
        ?,?,?,?,?
    )";

    $stmt =
    $conn->prepare($sql);

    foreach($items as $item)
    {
        $itemID =
            $item["ItemID"];

        $quantity =
            intval($item["Quantity"]);

        $totalPrice =
            floatval($item["TotalPrice"]);

        $stmt->bind_param(
            "sssid",
            $memberID,
            $orderDate,
            $itemID,
            $quantity,
            $totalPrice
        );

        $stmt->execute();
    }

    $conn->commit();

    echo json_encode([
        "success"=>true
    ]);
}
catch(Exception $ex)
{
    $conn->rollback();

    echo json_encode([
        "success"=>false,
        "message"=>$ex->getMessage()
    ]);
}
?>