<?php
    include "database.php";
    include "status-codes.php";

    header("Content-Type: application/json");

    //var_dump($_COOKIE);

    if (!isset($_COOKIE["bs_session"])) {
        $response = [
            "status" => API_INVALID_SESSION
        ];

        echo json_encode($response);
        die();
    }

    $session = $_COOKIE["bs_session"];

    $sessions_q = $db->query("SELECT `user_id` FROM `sessions` WHERE id='$session'");
    $session = db_fetch_assoc($sessions_q);

    if (!$session) {
        $response = [
            "status" => API_INVALID_SESSION
        ];

        echo json_encode($response);
        die();
    }

    $response = [
        "status" => API_OK,
        "user_id" => $session["user_id"]
    ];

    echo json_encode($response);
?>