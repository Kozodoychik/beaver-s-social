<?php
    include "database.php";
    include "api.php";

    header("Content-Type: application/json");

    $username = db_escape_string($_POST["username"]);
    $password = db_escape_string($_POST["password"]);

    if ($username == "me") {
        $response = [
            "status" => 1,
        ];
        echo json_encode($response);
        die();
    }

    $user_q = $db->query("SELECT * FROM `users` WHERE `username`='$username'");
    $user = db_fetch_assoc($user_q);

    if ($user) {
        $response = [
            "status" => 2,
        ];
        echo json_encode($response);
        die();
    }

    $db->query("INSERT INTO `users` VALUES (NULL, '$username', '$password', '$username', '', '[]', '[]')");

    //setcookie("bs_session", strval($session_id), 0, "/");
    api_request("auth", ["username"=>$username, "password"=>$password], "POST");

    $response = [
        "status" => 0,
    ];

    echo json_encode($response);
?>