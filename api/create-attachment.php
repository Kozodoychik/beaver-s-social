<?php
    include "database.php";
    include "api.php";
    include "config.php";
    include "status-codes.php";

    header("Content-Type: application/json");

    $user = api_request("get-user-by-session", []);

    if ($user["status"] != 0) {
        $response = [
            "status" => $user["status"]
        ];

        echo json_encode($response);
        die();
    }

    if (!isset($_GET["name"]) || !isset($_GET["type"])) {
        $response = [
            "status" => API_INVALID_PARAMS
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

    $db->query("INSERT INTO `attachments` VALUES (NULL, ".$user["user_id"].", '$mime_type', '$name', $size, 'data/$internal_filename')");

    $query = $db->query("SELECT MAX(id) FROM `attachments`");
    $id = db_fetch_assoc($query);

    $response = [
        "status" => API_OK,
        "attachment" => intval(($id["MAX(id)"])),
        "path" => "data/$internal_filename"
    ];

    echo json_encode($response);
?>