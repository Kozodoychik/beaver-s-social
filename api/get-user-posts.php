<?php
    include "database.php";
    include "api.php";

    header("Content-Type: application/json");

    $username = db_escape_string($_GET["username"]);
    $from = strval(isset($_GET["from"]) ? $_GET["from"] : 0);
    $count = strval(isset($_GET["count"]) ? $_GET["count"] : 1);

    $id = api_request("get-id-by-username", array("username"=>$username));
    
    if ($id["status"] != 0) {
        echo json_encode($id);
        die();
    }

    $q = $db->query("SELECT * FROM `posts` WHERE `author_id`='".$id["user_id"]."' ORDER BY id DESC LIMIT $count OFFSET $from");
    $data = db_fetch_all($q);
    
    $response = [
        "status" => 0,
        "data" => $data ? $data : array()
    ];

    echo json_encode($response);
?>