<?php
    include "database.php";

    header("Content-Type: application/json");

    $from = intval(isset($_GET["from"]) ? $_GET["from"] : 0);
    $count = intval(isset($_GET["count"]) ? $_GET["count"] : 1);

    $posts_q = $db->query("SELECT * FROM `posts` ORDER BY id DESC LIMIT $count OFFSET $from");
    $posts = db_fetch_all($posts_q);

    $response = [
        "status" => 0,
        "data" => $posts
    ];

    echo json_encode($response);
    die();
?>