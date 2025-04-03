<?php
    include "api/api.php";

    if (isset($_COOKIE["bred_session"])) {
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
                <?php if (isset($id)) echo '<li><a href="?u=me"><i class="icon-small bx bx-user"></i><span class="nav-label">Профиль</span></a></li>'; ?>
                <li><a href="index.php"><i class="icon-small bx bx-dock-top"></i><span class="nav-label">Лента</span></a></li>
                <!--li><a href="#">Настройки</a></li-->
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
                            <img class="user-avatar" src="data/test-ava.png" width="64px" height="64px">
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
                            <form id="post-form" onsubmit="uploadPost(event);">
                                <textarea form="post-form" name="content" placeholder="Чем вы хотите поделиться сегодня?" rows="5"></textarea>
                                <div class="toolbar">
                                    <button type="submit"><i class="icon bx bx-right-arrow-alt"></i></button>
                                    <!--button><i class="icon bx bx-paperclip"></i></button-->
                                </div>
                            </form>
                        </div>
                        ';
                    }
                }
                
            ?>
        </div>
        <script>
            var urlParams = new URLSearchParams(document.location.search);
            var posts = document.getElementsByClassName("posts")[0];
            var postsContainer = document.getElementsByClassName("posts")[0];

            var likes = [];
            var dislikes = [];
            
            function apiRequest(method, params, reqMethod="GET") {
                var p = new URLSearchParams(params);
                var req = new XMLHttpRequest();
                
                if (reqMethod == "POST") {
                    req.open(reqMethod, "api/"+method+".php", false);
                    req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                    req.send(p.toString());
                }
                else {
                    req.open(reqMethod, "api/"+method+".php?"+p.toString(), false);
                    req.send();
                }
                
                if (req.status != 200) {
                    console.error("apiRequest fail: HTTP "+req.status);
                    return;
                }

                var response = JSON.parse(req.responseText);
                
                if (response.status != 0) {
                    console.warn("apiRequest "+method+" response status != 0 ("+response.status+")");
                }

                return response;
            }

            function getCookie(name) {
                var rows = document.cookie.split(";");

                for (var i = 0; i < rows.length; i++) {
                    var row = rows[i];
                    var data = row.split("=")

                    if (data[0] == name) {
                        return data[1];
                    }
                }
            }

            if (getCookie("bred_session")) {
                var userId = apiRequest("get-user-by-session", {});
                var user = apiRequest("get-user", {id:userId.user_id});

                likes = JSON.parse(user.data.likes);
                dislikes = JSON.parse(user.data.dislikes);
            }

            function logout() {
                console.log(apiRequest("logout", {}));
                document.cookie = "bred_session=; Max-Age=0; path=/; domain=" + location.host + ";expires=Thu, 01 Jan 1970 00:00:01 GMT";
                window.location = "?";
            }

            function uploadPost(e) {
                var form = document.forms["post-form"];
                e.preventDefault();
                apiRequest("upload-post", {content: form.content.value}, "POST");

                document.location = "?u=me";
                
            }

            function like(id) {
                if (!getCookie("bred_session")) return;

                var method = "PUT";
                if (likes.includes(id)) {
                    method = "DELETE";
                }

                if (method == "PUT" && dislikes.includes(id)) {
                    dislike(id);
                }

                var req = apiRequest("like", {id: id}, method);

                var post = apiRequest("get-post", {id: id});

                if (req.status == 0) {
                    var likeBtn = document.getElementById("like-"+id.toString());
                    var i = likeBtn.getElementsByTagName("i")[0];

                    switch (method) {
                        case "PUT": {
                            i.classList.remove("bx-like");
                            i.classList.add("bxs-like");
                            likes.push(id);
                            break;
                        }
                        case "DELETE": {
                            i.classList.remove("bxs-like");
                            i.classList.add("bx-like");
                            var index = likes.indexOf(id);
                            if (index !== -1) {
                                likes.splice(index, 1);
                            }
                            break;
                        }
                    }

                    likeBtn.innerHTML = i.outerHTML.toString() + " "+post.data.likes.toString();
                }
            }

            function dislike(id) {
                if (!getCookie("bred_session")) return;

                var method = "PUT";
                if (dislikes.includes(id)) {
                    method = "DELETE";
                }

                if (method == "PUT" && likes.includes(id)) {
                    like(id);
                }

                var req = apiRequest("dislike", {id: id}, method);

                var post = apiRequest("get-post", {id: id});

                if (req.status == 0) {
                    var dislikeBtn = document.getElementById("dislike-"+id.toString());
                    var i = dislikeBtn.getElementsByTagName("i")[0];

                    switch (method) {
                        case "PUT": {
                            i.classList.remove("bx-dislike");
                            i.classList.add("bxs-dislike");
                            dislikes.push(id);
                            break;
                        }
                        case "DELETE": {
                            i.classList.remove("bxs-dislike");
                            i.classList.add("bx-dislike");
                            var index = dislikes.indexOf(id);
                            if (index !== -1) {
                                dislikes.splice(index, 1);
                            }
                            break;
                        }
                    }

                    dislikeBtn.innerHTML = i.outerHTML.toString() + " "+post.data.dislikes.toString();
                }
            }

            if (urlParams.has("u")) {
                var username = urlParams.get("u");
                var user = [];
                var userId = [];
                var userPosts = [];

                if (username == "me") {
                    if (!getCookie("bred_session")) {
                        document.location = "login.php";
                    }
                    
                    var sessionId = getCookie("bred_session");
                    userId = apiRequest("get-user-by-session", {session: sessionId});
                }
                else {
                    userId = apiRequest("get-id-by-username", {username: username});
                }

                user = apiRequest("get-user", {id: userId.user_id}).data;
                userPosts = apiRequest("get-user-posts", {username: user.username, from: 0, count: Number.MAX_SAFE_INTEGER}).data;

                for (var i = 0;i < userPosts.length; i++) {
                    var post = userPosts[i];
                    postsContainer.innerHTML += `
                    <div id="post-`+post.id.toString()+`" class="post">
                        <div class="post-header">
                            <img class="user-avatar" src="data/test-ava.png" width="32px" height="32px">
                            <a href="?u=`+user.username+`" class="post-user-nickname">`+user.display_name+`</a>
                            <p class="post-user-name">`+user.username+`</p>
                        </div>
                        <p class="post-content">`+post.content+`</p>
                        <div class="post-toolbar">
                            <button id="like-`+post.id.toString()+`" onclick="like(`+post.id.toString()+`);" class="counter-btn"><i class="icon-small bx `+(likes.includes(post.id) ? "bxs-like" : "bx-like")+`"></i> `+post.likes.toString()+`</button>
                            <button id="dislike-`+post.id.toString()+`" onclick="dislike(`+post.id.toString()+`);"class="counter-btn"><i class="icon-small bx `+(dislikes.includes(post.id) ? "bxs-dislike" : "bx-dislike")+`"></i> `+post.dislikes.toString()+`</button>
                            <!--button class="counter-btn"><i class="icon-small bx bx-comment"></i> 0</button>
                            <button class="ellipsis-btn"><i class="icon-small bx bx-dots-vertical-rounded"></i></button-->
                        </div>
                    </div>
                    `;
                }
            }
            else {
                var posts = apiRequest("get-posts", {from: 0, count: Number.MAX_SAFE_INTEGER}).data;

                for (var i = 0;i < posts.length; i++) {
                    var post = posts[i];
                    var user;
                    var userReq = apiRequest("get-user", {id: post.author_id});
                    
                    if (userReq.status != 0) {
                        user = {
                            display_name: "Неизвестный пользователь",
                            username: "UnknownUser"
                        }
                    }
                    else user = userReq.data;
                    
                    postsContainer.innerHTML += `
                    <div id="post_`+post.id.toString()+`" class="post">
                        <div class="post-header">
                            <img class="user-avatar" src="data/test-ava.png" width="32px" height="32px">
                            <a href="?u=`+user.username+`" class="post-user-nickname">`+user.display_name+`</a>
                            <p class="post-user-name">`+user.username+`</p>
                        </div>
                        <p class="post-content">`+post.content+`</p>
                        <div class="post-toolbar">
                            <button id="like-`+post.id.toString()+`" onclick="like(`+post.id.toString()+`);" class="counter-btn"><i class="icon-small bx `+(likes.includes(post.id) ? "bxs-like" : "bx-like")+`"></i> `+post.likes.toString()+`</button>
                            <button id="dislike-`+post.id.toString()+`" onclick="dislike(`+post.id.toString()+`);" class="counter-btn"><i class="icon-small bx `+(dislikes.includes(post.id) ? "bxs-dislike" : "bx-dislike")+`"></i> `+post.dislikes.toString()+`</button>
                            <!--button class="counter-btn"><i class="icon-small bx bx-comment"></i> 0</button>
                            <button class="ellipsis-btn"><i class="icon-small bx bx-dots-vertical-rounded"></i></button-->
                        </div>
                    </div>
                    `;
                }
            }
        </script>
    </body>
</html>