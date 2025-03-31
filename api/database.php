<?php
    include "config.php";


    if ($config["db_use_sqlite3"]) {
        $db = new SQLite3($config["db_sqlite3_file"]);
    }
    else {
        $db = new mysqli(
            $config["db_mysql_host"],
            $config["db_mysql_user"],
            $config["db_mysql_password"],
            $config["db_mysql_database"],
            $config["db_mysql_port"]
        );
    }

    // Функции для поддержки как MySQLi, так и SQLite3

    function db_escape_string($str) {
        global $db, $config;
        
        if ($db instanceof SQLite3) {
            return $db->escapeString($str);
        }
        return $db->real_escape_string($str);
    }

    function db_fetch_assoc($q) {
        global $db;

        if ($q instanceof SQLite3Result) {
            return $q->fetchArray(SQLITE3_ASSOC);
        }
        $d = $q->fetch_all(MYSQLI_ASSOC);
        
        if ($d) return $d[0];
        return false;
    }

    function db_fetch_all($q) {
        global $db;

        if ($q instanceof SQLite3Result) {
            $result = array();
            while ($row = $q->fetchArray(SQLITE3_ASSOC))
                array_push($result, $row);
            return $result;
        }
        $d = $q->fetch_all(MYSQLI_ASSOC);
        
        if ($d) return $d;
        return false;
    }
?>