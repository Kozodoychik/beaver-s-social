<?php
    include "database.php";
    include "status-codes.php";

    header("Content-Type: application/json");

    $username = db_escape_string($_GET["username"]);

    $q = $db->query("SELECT id FROM `users` WHERE username='$username'");
    $data = db_fetch_assoc($q);

    if (!$data) {
        $response = [
            "status" => API_USER_DOESNT_EXISTS
        ];
        echo json_encode($response);
        die();
    }

    $response = [
        "status" => API_OK,
        "user_id" => $data["id"]
    ];

    echo json_encode($response);
?>