<?php

header("Content-Type: application/json");

require_once "db_config.php";

$action = $_GET["action"] ?? "";

if($action == "list")
{
    $sql =
    "SELECT
        u.Username AS MemberID,
        m.Name,
        m.Surname,
        u.Password,
        u.Role,
        m.Active
    FROM tblusers u
    LEFT JOIN tblmembers m
        ON u.Username = m.MemberID
    ORDER BY u.Username";

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

    $memberID =
        trim($data["MemberID"] ?? "");

    $name =
        trim($data["Name"] ?? "");

    $surname =
        trim($data["Surname"] ?? "");

    $password =
        trim($data["Password"] ?? "");

    $role =
        trim($data["Role"] ?? "Member");

    $active =
        trim($data["Active"] ?? "Y");

    if(
        $memberID == "" ||
        $name == "" ||
        $surname == "" ||
        $password == ""
    )
    {
        echo json_encode([
            "success"=>false,
            "message"=>"All fields are required."
        ]);

        exit;
    }

    $sqlUser =
    "INSERT INTO tblusers
    (Username,Password,Role)
    VALUES(?,?,?)
    ON DUPLICATE KEY UPDATE
    Password=VALUES(Password),
    Role=VALUES(Role)";

    $stmt = $conn->prepare($sqlUser);

    $stmt->bind_param(
        "sss",
        $memberID,
        $password,
        $role
    );

    $stmt->execute();

    $sqlMember =
    "INSERT INTO tblmembers
    (MemberID,Name,Surname,Active)
    VALUES(?,?,?,?)
    ON DUPLICATE KEY UPDATE
    Name=VALUES(Name),
    Surname=VALUES(Surname),
    Active=VALUES(Active)";

    $stmt = $conn->prepare($sqlMember);

    $stmt->bind_param(
        "ssss",
        $memberID,
        $name,
        $surname,
        $active
    );

    $stmt->execute();

    echo json_encode([
        "success"=>true,
        "message"=>"Member saved successfully."
    ]);

    exit;
}

echo json_encode([
    "success"=>false,
    "message"=>"Invalid action."
]);

?>