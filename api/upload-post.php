<?php
    include "database.php";
    include "api.php";

    header("Content-Type: application/json");

    $content = db_escape_string($_POST["content"]);
    $attachments = db_escape_string(isset($_POST["attachments"]) ? $_POST["attachments"] : "[]");

    // Попытка защиты от XSS-уязвимости
    $content = htmlentities($content);

    $user = api_request("get-user-by-session", []);

    if ($user["status"] != 0) {
        $response = [
            "status" => 2
        ];

        echo json_encode($response);
        die();
    }

    $q = $db->query("INSERT INTO `posts` VALUES (NULL, ".$user["user_id"].", '$content', '$attachments', 0, 0)");

    $response = [
        "status" => 0
    ];
    
    echo json_encode($response);
?>