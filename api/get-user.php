<?php
    include "database.php";

    header("Content-Type: application/json");

    $id = strval($_GET["id"]);

    $q = $db->query("SELECT id, username, display_name, avatar_file, likes, dislikes FROM `users` WHERE id=$id");
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
        "data" => $data
    ];
    echo json_encode($response);
?>