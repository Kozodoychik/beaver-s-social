<?php
    include "database.php";
    include "api.php";
    include "status-codes.php";

    header("Content-Type: application/json");

    $post_id = intval($_GET["id"]);
    $user_id = api_request("get-user-by-session", []);

    if ($user_id["status"] != API_OK) {
        $response = [
            "status" => $user_id["status"]
        ];
        echo json_encode($response);
        die();
    }

    $user = api_request("get-user", ["id"=>$user_id["user_id"]]);

    if ($user["status"] != API_OK) {
        $resposne = [
            "status" => $user["status"]
        ];
        echo json_encode($response);
        die();
    }

    $post = api_request("get-post", ["id"=>$post_id]);

    if ($post["status"] != API_OK) {
        $response = [
            "status" => $post["status"]
        ];
        echo json_encode($response);
        die();
    }

    $likes = json_decode($user["data"]["likes"], true);

    $request_method = $_SERVER["REQUEST_METHOD"];
    //$request_method = $_GET["method"];

    switch ($request_method) {
        case "PUT": {
            if (!in_array($post_id, $likes)){
                array_push($likes, $post_id);
                $post["data"]["likes"]++;
            }
            break;
        }
        case "DELETE": {
            if (in_array($post_id, $likes)){
                $likes = array_diff($likes, [$post_id]);
                $likes = array_values($likes);
                $post["data"]["likes"]--;
            }
            break;
        }
    }
    
    $db->query("UPDATE `users` SET likes='".json_encode($likes)."' WHERE id=".$user_id["user_id"]);
    $db->query("UPDATE `posts` SET likes=".$post["data"]["likes"]." WHERE id=$post_id");

    $response = [
        "status" => API_OK
    ];
    echo json_encode($response);
    die();
?>