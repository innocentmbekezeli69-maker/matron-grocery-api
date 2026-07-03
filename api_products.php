<?php

header("Content-Type: application/json");

require_once "db_config.php";

$sql =
"SELECT
    ItemID,
    Description,
    Price,
    Image
FROM tblgroceryitems
WHERE Available='Y'
ORDER BY Description";

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