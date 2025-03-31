<?php
    include "database.php";

    header("Content-Type: application/json");

    $id = strval($_GET["id"]);

    $post_q = $db->query("SELECT * FROM `posts` WHERE id=$id");
    $post = db_fetch_assoc($post_q);
    
    if (!$post) {
        $response = [
            "status" => 1
        ];
        echo json_encode($response);
        die();
    }

    $response = [
        "status" => 0,
        "data" => $post
    ];
    echo json_encode($response);
?>