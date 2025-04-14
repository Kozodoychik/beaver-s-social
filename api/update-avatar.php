<?php
    include "database.php";
    include "api.php";
    include "status-codes.php";

    header("Content-Type: application/json");

    $user_id = api_request("get-user-by-session", []);

    if ($user_id["status"] != API_OK) {
        echo json_encode($user_id);
        die();
    }

    $user = api_request("get-user", ["id" => $user_id["user_id"]]);

    if ($user["status"] != API_OK) {
        echo json_encode($user);
        die();
    }

    if ($user["data"]["avatar_file"] != "data/default_avatar.png")
        unlink("../".$user["data"]["avatar_file"]);

    $internal_filename = strval(time());

    move_uploaded_file($_FILES["avatar"]["tmp_name"], "../data/avatars/$internal_filename");

    $db->query("UPDATE `users` SET `avatar_file`='data/avatars/$internal_filename' WHERE `id`=".$user_id["user_id"]);

    $response = [
        "status" => API_OK
    ];

    echo json_encode($response);
?>