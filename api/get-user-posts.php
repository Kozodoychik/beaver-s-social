<?php
    include "database.php";
    include "api.php";
    include "status-codes.php";

    header("Content-Type: application/json");

    $username = db_escape_string($_GET["username"]);
    $from = strval(isset($_GET["from"]) ? $_GET["from"] : 0);
    $count = strval(isset($_GET["count"]) ? $_GET["count"] : 1);

    $id = api_request("get-id-by-username", array("username"=>$username));
    
    if ($id["status"] != API_OK) {
        echo json_encode($id);
        die();
    }

    $q = $db->query("SELECT * FROM `posts` WHERE `author_id`='".$id["user_id"]."' ORDER BY id DESC LIMIT $count OFFSET $from");
    $data = db_fetch_all($q);
    
    // Прикол: MySQLi возвращает числовые поля как строки
    foreach ($data as &$post) {
        $post["id"] = intval($post["id"]);
        $post["author_id"] = intval($post["author_id"]);
        $post["likes"] = intval($post["likes"]);
        $post["dislikes"] = intval($post["dislikes"]);
    }

    $response = [
        "status" => API_OK,
        "data" => $data ? $data : array()
    ];

    echo json_encode($response);
?>