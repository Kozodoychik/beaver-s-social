<?php
    include "database.php";

    header("Content-Type: application/json");

    //var_dump($_COOKIE);

    $session = $_COOKIE["bs_session"];

    $sessions_q = $db->query("SELECT `user_id` FROM `sessions` WHERE id='$session'");
    $session = db_fetch_assoc($sessions_q);

    if (!$session) {
        $response = [
            "status" => 1
        ];

        echo json_encode($response);
        die();
    }

    $response = [
        "status" => 0,
        "user_id" => $session["user_id"]
    ];

    echo json_encode($response);
?>