<?php
    include "api/api.php";

    if (isset($_COOKIE["bs_session"])) {
        $id = api_request("get-user-by-session", []);
        if ($id["status"] != 0) {
            header("Location: login.php");
            die();
        }
    }
?>  
<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width,initial-scale=1"/>
        <title>ИП по инфе</title>
        <link rel="stylesheet" href="style.css"/>
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    </head>
    <body>
        <div class="nav-panel">
            <ul>
                <li><a href="index.php"><i class="icon-small bx bx-dock-top"></i><span class="nav-label">Лента</span></a></li>
                <?php 
                    if (isset($id)){
                        echo '<li><a href="?u=me"><i class="icon-small bx bx-user"></i><span class="nav-label">Профиль</span></a></li>
                        <!--li><a href="#"><i class="icon-small bx bx-cog"></i><span class="nav-label">Настройки</span></a></li-->';
                    }
                ?>
                <li><?php
                    if (isset($id)) {
                        echo '<a onclick="logout();"><i class="icon-small bx bx-log-out"></i><span class="nav-label">Выход</span></a>';
                    }
                    else {
                        echo '<a href="login.php"><i class="icon-small bx bx-log-in"></i><span class="nav-label">Вход</span></a>';
                    }
                ?></li>
            </ul>
        </div>
        <div class="posts">
            <?php
                if (isset($_GET["u"])) {
                    if ($_GET["u"] == "me") {
                        $u = api_request("get-user", ["id"=>$id["user_id"]]);
                    }
                    else {
                        $user_id = api_request("get-id-by-username", ["username"=>$_GET["u"]]);
                    
                        if ($user_id["status"] == 0) {
                            $user = api_request("get-user", ["id"=>$user_id["user_id"]]);
                            $u = $user;
                        }
                        else {
                            echo '<div class="post">
                            Такого пользователя не существует
                            </div>';
                            die();
                        }
                    }
                    echo '
                    <div class="post">
                        <div class="user-info">
                            <img class="user-avatar '.($_GET["u"] == "me" ? "pointer-on-hover" : "").'" src="'.$u["data"]["avatar_file"].'" onclick="updateAvatar();" width="64px" height="64px">
                            <div class="usernames-container">
                                <p class="user-nickname">'.$u["data"]["display_name"].'</p>
                                <p class="user-name">'.$u["data"]["username"].'</p>
                            </div>
                        </div>
                        <div class="user-sections-panel">
                            <a class="section-btn-active" href="#">Всё</a>
                            <a href="#">Медиа</a>
                            <a href="#">Всякое</a>
                        </div>
                    </div>
                    ';
                }
            ?>
            <?php
                if (isset($_GET["u"])) {
                    if ($_GET["u"] == "me" && isset($id)) {
                        echo '
                        <div class="post">
                            <form id="post-form">
                                <textarea form="post-form" name="content" placeholder="Чем вы хотите поделиться сегодня?" rows="5"></textarea>
                                <div id="form-attachments" class="file-attachments">
                                </div>
                                <div class="toolbar">
                                    <button onclick="uploadPost(event);" type="submit"><i class="icon bx bx-right-arrow-alt"></i></button>
                                    <button onclick="addAttachment(event);"><i class="icon bx bx-paperclip"></i></button>
                                </div>
                            </form>
                        </div>
                        ';
                    }
                }
                
            ?>
        </div>
        <script src="main.js"></script>
    </body>
</html>