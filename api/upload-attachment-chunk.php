<?php
    include "database.php";
    include "api.php";

    header("Content-Type: application/json");

    $session_id = db_escape_string($_COOKIE["bs_session"]);
    $attachment_id = intval($_POST["attachment"]);
    $chunk_n = intval($_POST["chunk_n"]);
    $is_final = boolval($_POST["is_final"]);


    $user = api_request("get-user-by-session", ["session"=>$session_id]);

    if ($user["status"] != 0) {
        $response = [
            "status" => $user["status"]
        ];

        echo json_encode($response);
        die();
    }

    $attachment = api_request("get-attachment-data", ["attachment"=>$attachment_id]);
    if ($attachment["status"] != 0) {
        $response = [
            "status" => $attachment["status"]
        ];

        echo json_encode($response);
        die();
    }

    move_uploaded_file($_FILES["data"]["tmp_name"], "../data/tmp/$attachment_id.$chunk_n");

    if ($is_final) {
        $file = fopen("..".$attachment["data"]["path"], "a");
        $chunks = glob("../data/tmp/$attachment_id.*");
        sort($chunks, SORT_NATURAL);

        foreach ($chunks as $chunk) {
            fwrite($file, file_get_contents($chunk));
            unlink($chunk);
        }

        fclose($file);
    }

    $response = [
        "status" => 0
    ];

    echo json_encode($response);
?>