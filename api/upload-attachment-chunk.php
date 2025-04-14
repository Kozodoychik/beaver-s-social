<?php
    include "database.php";
    include "api.php";
    include "status-codes.php";

    header("Content-Type: application/json");

    $attachment_id = intval($_POST["attachment"]);
    $chunk_n = intval($_POST["chunk_n"]);
    $is_final = boolval($_POST["is_final"]);


    $user = api_request("get-user-by-session", []);

    if ($user["status"] != API_OK) {
        echo json_encode($user);
        die();
    }

    $attachment = api_request("get-attachment-data", ["attachment"=>$attachment_id]);
    if ($attachment["status"] != API_OK) {
        echo json_encode($attachment);
        die();
    }

    move_uploaded_file($_FILES["data"]["tmp_name"], "../data/tmp/$attachment_id.$chunk_n");

    if ($is_final) {
        $file = fopen("../".$attachment["data"]["path"], "a");
        $chunks = glob("../data/tmp/$attachment_id.*");
        sort($chunks, SORT_NATURAL);

        foreach ($chunks as $chunk) {
            fwrite($file, file_get_contents($chunk));
            unlink($chunk);
        }

        fclose($file);
    }

    $response = [
        "status" => API_OK
    ];

    echo json_encode($response);
?>