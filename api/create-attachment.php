<?php
    include "database.php";
    include "api.php";
    include "config.php";

    header("Content-Type: application/json");

    $session_id = $_COOKIE["bs_session"];
    
    $user = api_request("get-user-by-session", ["session"=>$session_id]);

    if ($user["status"] != 0) {
        $response = [
            "status" => $user["status"]
        ];

        echo json_encode($response);
        die();
    }

    if (!isset($_GET["name"]) || !isset($_GET["type"])) {
        $response = [
            "status" => 1
        ];

        echo json_encode($response);
        die();
    }

    $name = db_escape_string($_GET["name"]);
    $mime_type = db_escape_string($_GET["type"]);
    $size = intval($_GET["size"]);

    // Попытка защиты от XSS-уязвимости
    $name = htmlentities($name);

    $internal_filename = strval(time());

    if ($config["db_use_sqlite3"])
        $query = $db->query("INSERT INTO `attachments` VALUES (NULL, ".$user["user_id"].", '$mime_type', '$name', $size, '/data/$internal_filename') RETURNING id");
    else
        $db->query("INSERT INTO `attachments` VALUES (NULL, ".$user["user_id"].", '$mime_type', '$name', $size, '/data/$internal_filename')");
        $query = $db->query("SELECT LAST_INSERT_ID()");
    
    $id = db_fetch_assoc($query);

    $response = [
        "status" => 0,
        "attachment" => intval($id["LAST_INSERT_ID()"])
    ];

    echo json_encode($response);
?>