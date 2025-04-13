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
            "status" => API_POST_DOESNT_EXISTS
        ];
        echo json_encode($response);
        die();
    }

    $dislikes = json_decode($user["data"]["dislikes"], true);

    $request_method = $_SERVER["REQUEST_METHOD"];
    //$request_method = $_GET["method"];

    switch ($request_method) {
        case "PUT": {
            if (!in_array($post_id, $dislikes)){
                array_push($dislikes, $post_id);
                $post["data"]["dislikes"]++;
            }
            break;
        }
        case "DELETE": {
            if (in_array($post_id, $dislikes)){
                $dislikes = array_diff($dislikes, [$post_id]);
                $dislikes = array_values($dislikes);
                $post["data"]["dislikes"]--;
            }
            break;
        }
    }
    
    $db->query("UPDATE `users` SET dislikes='".json_encode($dislikes)."' WHERE id=".$user_id["user_id"]);
    $db->query("UPDATE `posts` SET dislikes=".$post["data"]["dislikes"]." WHERE id=$post_id");

    $response = [
        "status" => API_OK
    ];
    echo json_encode($response);
    die();
?>