<?php
    include "database.php";
    include "api.php";

    header("Content-Type: application/json");

    $session_id = db_escape_string($_COOKIE["bs_session"]);
    $attachment_id = intval(isset($_POST["attachment"]) ? $_POST["attachment"] : time());


    $user = api_request("get-user-by-session", ["session"=>$session_id]);

    if ($user["status"] != 0) {
        $response = [
            "status" => $user["status"]
        ];

        echo json_encode($response);
        die();
    }

    $file = fopen("../data/$attachment_id", "a");

    fwrite($file, file_get_contents($_FILES["data"]["tmp_name"]));

    fclose($file);

    $response = [
        "status" => 0
    ];

    echo json_encode($response);
?>