<?php
    include "database.php";

    header("Content-Type: application/json");

    $attachment = intval($_GET["attachment"]);

    $query = $db->query("SELECT * FROM `attachments` WHERE id=$attachment");
    $data = db_fetch_assoc($query);

    if (!$data) {
        $response = [
            "status" => 1
        ];

        echo json_encode($response);
        die();
    }

    // Прикол: MySQLi возвращает числовые поля как строки
    $data["id"] = intval($data["id"]);
    $data["author_id"] = intval($data["author_id"]);
    $data["size"] = intval($data["size"]);

    $response = [
        "status" => 0,
        "data" => $data
    ];

    echo json_encode($response);
?>