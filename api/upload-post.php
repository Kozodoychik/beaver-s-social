<?php
    include "database.php";
    include "api.php";
    include "status-codes.php";

    header("Content-Type: application/json");

    $content = db_escape_string($_POST["content"]);
    $attachments = db_escape_string(isset($_POST["attachments"]) ? $_POST["attachments"] : "[]");
    $repost = intval($_POST["repost"]);

    // Попытка защиты от XSS-уязвимости
    $content = htmlentities($content);

    $user = api_request("get-user-by-session", []);

    if ($user["status"] != API_OK) {
        $response = [
            "status" => API_INVALID_SESSION
        ];

        echo json_encode($response);
        die();
    }

    if ($repost != -1) {
        $post = api_request("get-post", ["id"=>$repost]);

        if ($post["status"] != 0) {
            $response = [
                "status" => API_INVALID_PARAMS
            ];
    
            echo json_encode($response);
            die();
        }
    }

    $q = $db->query("INSERT INTO `posts` VALUES (NULL, ".$user["user_id"].", '$content', '$attachments', 0, 0, $repost)");

    $response = [
        "status" => API_OK
    ];
    
    echo json_encode($response);
?>