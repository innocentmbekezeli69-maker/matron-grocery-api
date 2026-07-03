<?php

header("Content-Type: application/json");

require_once "db_config.php";

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$username = trim($data["Username"] ?? "");
$password = trim($data["Password"] ?? "");

if($username == "" || $password == "")
{
    echo json_encode([
        "success"=>false,
        "message"=>"Username and password required."
    ]);
    exit;
}

$sql =
"SELECT
    u.Username,
    u.Role,
    m.Name,
    m.Surname,
    m.Active
FROM tblusers u
LEFT JOIN tblmembers m
    ON u.Username = m.MemberID
WHERE u.Username = ?
AND u.Password = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ss",
    $username,
    $password
);

$stmt->execute();

$result = $stmt->get_result();

if($row = $result->fetch_assoc())
{

    if(
        strtolower($row["Role"]) != "admin" &&
        strtoupper($row["Active"]) != "Y"
    )
    {
        echo json_encode([
            "success"=>false,
            "message"=>"Access Denied: This account is currently inactive."
        ]);

        exit;
    }

    echo json_encode([
        "success"=>true,
        "Username"=>$row["Username"],
        "Role"=>$row["Role"],
        "FullName"=>trim(
            ($row["Name"] ?? "") .
            " " .
            ($row["Surname"] ?? "")
        )
    ]);

}
else
{
    echo json_encode([
        "success"=>false,
        "message"=>"Invalid Username or password combination."
    ]);
}

$conn->close();

?>