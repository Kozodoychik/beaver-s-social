<?php
    include "database.php";

    header("Content-Type: application/json");

    $username = db_escape_string($_GET["username"]);

    $q = $db->query("SELECT id FROM `users` WHERE username='$username'");
    $data = db_fetch_assoc($q);

    if (!$data) {
        $response = [
            "status" => 1
        ];
        echo json_encode($response);
        die();
    }

    $response = [
        "status" => 0,
        "user_id" => $data["id"]
    ];

    echo json_encode($response);
?>