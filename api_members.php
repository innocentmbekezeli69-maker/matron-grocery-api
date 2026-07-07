<?php

header("Content-Type: application/json");

require_once "db_config.php";

$action = $_GET["action"] ?? "";


// ============================================================
// LIST MEMBERS
// ============================================================
if ($action == "list") {

    $sql = "
    SELECT
        u.Username AS MemberID,
        m.Name,
        m.Surname,
        m.Contact,
        u.Password,
        u.Role,
        m.Active
    FROM tblusers u
    LEFT JOIN tblmembers m
        ON u.Username = m.MemberID
    ORDER BY u.Username";

    $result = $conn->query($sql);

    $data = [];

    while ($row = $result->fetch_assoc()) {

        $data[] = $row;

    }

    echo json_encode([
        "success" => true,
        "data" => $data
    ]);

    exit;
}


// ============================================================
// SAVE MEMBER
// ============================================================
if ($action == "save") {

    $data = json_decode(
        file_get_contents("php://input"),
        true
    );

    $memberID =
        trim($data["MemberID"] ?? "");

    $name =
        trim($data["Name"] ?? "");

    $surname =
        trim($data["Surname"] ?? "");

    $contact =
        trim($data["Contact"] ?? "");

    $password =
        trim($data["Password"] ?? "");

    $role =
        trim($data["Role"] ?? "Member");

    $active =
        trim($data["Active"] ?? "Y");


    // ========================================================
    // VALIDATIONS
    // ========================================================
    if (
        $memberID == "" ||
        $name == "" ||
        $surname == "" ||
        $contact == "" ||
        $password == ""
    ) {

        echo json_encode([
            "success" => false,
            "message" => "All fields are required."
        ]);

        exit;

    }


    // ========================================================
    // SAVE USER ACCOUNT
    // ========================================================
    $sqlUser = "
    INSERT INTO tblusers
    (
        Username,
        Password,
        Role
    )
    VALUES
    (
        ?,
        ?,
        ?
    )
    ON DUPLICATE KEY UPDATE
        Password = VALUES(Password),
        Role = VALUES(Role)";

    $stmt = $conn->prepare($sqlUser);

    $stmt->bind_param(
        "sss",
        $memberID,
        $password,
        $role
    );

    $stmt->execute();


    // ========================================================
    // SAVE MEMBER PROFILE
    // ========================================================
    $sqlMember = "
    INSERT INTO tblmembers
    (
        MemberID,
        Name,
        Surname,
        Contact,
        Active
    )
    VALUES
    (
        ?,
        ?,
        ?,
        ?,
        ?
    )
    ON DUPLICATE KEY UPDATE
        Name = VALUES(Name),
        Surname = VALUES(Surname),
        Contact = VALUES(Contact),
        Active = VALUES(Active)";

    $stmt = $conn->prepare($sqlMember);

    $stmt->bind_param(
        "sssss",
        $memberID,
        $name,
        $surname,
        $contact,
        $active
    );

    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Member saved successfully."
    ]);

    exit;
}


// ============================================================
// INVALID ACTION
// ============================================================
echo json_encode([
    "success" => false,
    "message" => "Invalid action."
]);

?>
