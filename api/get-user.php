<?php
    include "database.php";
    include "status-codes.php";

    header("Content-Type: application/json");

    $id = strval($_GET["id"]);

    $q = $db->query("SELECT id, username, display_name, avatar_file, likes, dislikes FROM `users` WHERE id=$id");
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
        "data" => $data
    ];
    echo json_encode($response);
?>