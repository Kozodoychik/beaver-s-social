<?php
    include "database.php";
    include "status-codes.php";

    header("Content-Type: application/json");

    $id = strval($_GET["id"]);

    $post_q = $db->query("SELECT * FROM `posts` WHERE id=$id");
    $post = db_fetch_assoc($post_q);
    
    if (!$post) {
        $response = [
            "status" => API_POST_DOESNT_EXISTS
        ];
        echo json_encode($response);
        die();
    }

    // Прикол: MySQLi возвращает числовые поля как строки
    $post["id"] = intval($post["id"]);
    $post["author_id"] = intval($post["author_id"]);
    $post["likes"] = intval($post["likes"]);
    $post["dislikes"] = intval($post["dislikes"]);

    $response = [
        "status" => API_OK,
        "data" => $post
    ];
    echo json_encode($response);
?>