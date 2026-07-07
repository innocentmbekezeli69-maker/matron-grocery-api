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
        m.Email,
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
// SAVE / UPDATE MEMBER
// ============================================================
if ($action == "save") {

    try {

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

        $email =
            trim($data["Email"] ?? "");

        $password =
            trim($data["Password"] ?? "");

        $role =
            trim($data["Role"] ?? "Member");

        $active =
            trim($data["Active"] ?? "Y");


        // ====================================================
        // VALIDATIONS
        // ====================================================
        if (
            $memberID == "" ||
            $name == "" ||
            $surname == "" ||
            $contact == "" ||
            $email == "" ||
            $password == ""
        ) {

            echo json_encode([
                "success" => false,
                "message" => "All fields are required."
            ]);

            exit;

        }


        // ====================================================
        // SAVE USER ACCOUNT
        // ====================================================
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


        // ====================================================
        // SAVE MEMBER PROFILE
        // ====================================================
        $sqlMember = "
        INSERT INTO tblmembers
        (
            MemberID,
            Name,
            Surname,
            Contact,
            Email,
            Active
        )
        VALUES
        (
            ?,
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
            Email = VALUES(Email),
            Active = VALUES(Active)";

        $stmt = $conn->prepare($sqlMember);

        $stmt->bind_param(
            "ssssss",
            $memberID,
            $name,
            $surname,
            $contact,
            $email,
            $active
        );

        $stmt->execute();

        echo json_encode([
            "success" => true,
            "message" => "Member saved successfully."
        ]);

    } catch (Exception $ex) {

        echo json_encode([
            "success" => false,
            "message" => $ex->getMessage()
        ]);

    }

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
