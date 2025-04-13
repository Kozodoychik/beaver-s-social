<?php
    include "database.php";
    include "status-codes.php";

    header("Content-Type: application/json");

    $attachment = intval($_GET["attachment"]);

    $query = $db->query("SELECT * FROM `attachments` WHERE id=$attachment");
    $data = db_fetch_assoc($query);

    if (!$data) {
        $response = [
            "status" => API_ATTACHMENT_DOESNT_EXISTS
        ];

        echo json_encode($response);
        die();
    }

    // Прикол: MySQLi возвращает числовые поля как строки
    $data["id"] = intval($data["id"]);
    $data["author_id"] = intval($data["author_id"]);
    $data["size"] = intval($data["size"]);

    $response = [
        "status" => API_OK,
        "data" => $data
    ];

    echo json_encode($response);
?>