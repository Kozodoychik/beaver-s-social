<?php
    include "database.php";
    include "status-codes.php";

    header("Content-Type: application/json");

    $username = db_escape_string($_POST["username"]);
    $password = db_escape_string($_POST["password"]);

    $q = $db->query("SELECT * FROM `users` WHERE `username`='$username' AND `password`='$password'");

    $data = db_fetch_assoc($q);

    if ($data) {
        $session_id = uniqid();
        $q = $db->query("INSERT INTO `sessions` (`id`, `user_id`, `ip`) VALUES ('$session_id', ".$data["id"].", '".$_SERVER["REMOTE_ADDR"]."')");

        setcookie("bs_session", strval($session_id), 0, "/");

        $response = [
            "status" => API_OK,
            "session_id" => $session_id
        ];

        echo json_encode($response);
        die();
    }

    $response = [
        "status" => 1
    ];

    echo json_encode($response);
?>