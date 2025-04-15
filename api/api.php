<?php
    include "config.php";

    function api_request($method, $params, $req_method="GET") {
        global $config;

        $ch = curl_init("http://".$_SERVER["SERVER_NAME"]."/".$config["base_directory"]."api/$method.php?".http_build_query($params));

        if ($req_method == "POST") {
            $ch = curl_init("http://".$_SERVER["SERVER_NAME"]."/".$config["base_directory"]."api/$method.php");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_COOKIE, http_build_query($_COOKIE, "", "; "));

        //$response = file_get_contents("http://".$_SERVER["SERVER_NAME"]."/".$config["base_directory"]."api/$method.php?".http_build_query($params));
        $response = curl_exec($ch);

        return json_decode($response, true);
    }
?>