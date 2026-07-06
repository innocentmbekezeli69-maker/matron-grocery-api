<?php

$host = "hayabusa.proxy.rlwy.net";
$db_user = "root";
$db_pass = "ODdiTdUeXiJkRZraKoObScKhLybYReLV";
$db_name = "railway";
$db_port = 31364;

$conn = new mysqli(
    $host,
    $db_user,
    $db_pass,
    $db_name,
    $db_port
);

if ($conn->connect_error) {

    header("Content-Type: application/json");

    echo json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $conn->connect_error
    ]);

    exit;
}

$conn->set_charset("utf8");

?>
