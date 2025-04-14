<?php
    include "database.php";
    include "api.php";
    include "status-codes.php";

    $display_name = db_escape_string($_GET["new_name"]);
    $user_id = api_request("get-user-by-session", []);

    if ($user_id["status"] != API_OK) {
        echo json_encode($user_id);
        die();
    }

    $db->query("UPDATE `users` SET `display_name`='$display_name'");

    $response = [
        "status" => API_OK
    ];

    echo json_encode($response);
?>