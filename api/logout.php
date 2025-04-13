<?php
    include "database.php";
    include "api.php";
    include "status-codes.php";

    header("Content-Type: application/json");

    $user = api_request("get-user-by-session", []);

    if ($user["status"] != API_OK) {
        echo json_encode($user);
        die();
    }

    $db->query("DELETE FROM `sessions` WHERE id='".$_COOKIE["bs_session"]."'");

    $response = [
        "status" => API_OK
    ];

    echo json_encode($response);
?>