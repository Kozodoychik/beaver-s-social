<?php
    include "database.php";
    include "api.php";

    header("Content-Type: application/json");

    $user = api_request("get-user-by-session", []);

    if ($user["status"] != 0) {
        $response = [
            "status" => 1
        ];
    
        echo json_encode($response);
        die();
    }

    $db->query("DELETE FROM `sessions` WHERE id='".$_COOKIE["bred_session"]."'");

    $response = [
        "status" => 0
    ];

    echo json_encode($response);
?>