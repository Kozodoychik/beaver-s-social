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
        <script>
            // TODO: Вывести весь этот адовый код в отдельный файл

            var attachmentChunkSize = 8*1024*1024;    // 8 Мб

            var urlParams = new URLSearchParams(document.location.search);
            var posts = document.getElementsByClassName("posts")[0];
            var postsContainer = document.getElementsByClassName("posts")[0];

            var likes = [];
            var dislikes = [];

            var filesToUpload = [];

            var audio = new Audio();
            var currentlyPlaying = -1;
            var currentPlayTime = 0;
            
            function apiRequest(method, params, reqMethod="GET", useMultipartFormData=false) {
                var p = new URLSearchParams(params);
                var req = new XMLHttpRequest();
                
                if (reqMethod == "POST") {
                    req.open(reqMethod, "api/"+method+".php", false);
                    if (useMultipartFormData) {
                        console.log("using multipart/form-data");
                        var formData = new FormData();
                       
                        for (const [key, data] of Object.entries(params)) {
                            formData.append(key, data);
                        }

                        req.send(formData);

                    }
                    else {
                        req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                        req.send(p.toString());
                    }
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

            if (getCookie("bs_session")) {
                var userId = apiRequest("get-user-by-session", {});
                var user = apiRequest("get-user", {id:userId.user_id});

                likes = JSON.parse(user.data.likes);
                dislikes = JSON.parse(user.data.dislikes);
            }

            function logout() {
                console.log(apiRequest("logout", {}));
                document.cookie = "bs_session=; Max-Age=0; path=/; domain=" + location.host + ";expires=Thu, 01 Jan 1970 00:00:01 GMT";
                window.location = "?";
            }

            function sizeToString(size) {
                if (size < 1024) {
                    return `${size.toFixed(2)} Б`;
                }
                
                size /= 1024;

                if (size < 1024) {
                    return `${size.toFixed(2)} Кб`;
                }

                size /= 1024;

                if (size < 1024) {
                    return `${size.toFixed(2)} Мб`;
                }

                size /= 1024;

                return `${size.toFixed(2)} Гб`;
            }

            function addAttachment(e) {
                e.preventDefault();

                var attachmentsContainer = document.getElementById("form-attachments");

                var fileSelect = document.createElement("input");
                fileSelect.setAttribute("type", "file");

                fileSelect.addEventListener("change", async (e) => {
                    var attachmentId = apiRequest("create-attachment", {name: fileSelect.files[0].name, type: fileSelect.files[0].type, size: fileSelect.files[0].size});
                    if (attachmentId.status != 0) return;

                    console.log("zzz");

                    attachmentsContainer.innerHTML += `
                        <div id="${attachmentId.attachment}" class="file-attachment" onclick="removeAttachment(${attachmentId.attachment});">
                            <i class="icon bx bx-file-blank"></i>
                            <span class="file-attachment-name">Загрузка (0%)</span>
                            <span class="file-attachment-size">0 Б</span>
                            <span class="file-attachment-type">${fileSelect.files[0].type}</span>
                        </div>
                    `;

                    var attachment = document.getElementById(attachmentId.attachment.toString());

                    var fileName = attachment.getElementsByClassName("file-attachment-name")[0];
                    var fileSize = attachment.getElementsByClassName("file-attachment-size")[0];
                    var fileType = attachment.getElementsByClassName("file-attachment-type")[0];

                    var file = fileSelect.files[0];
                    var chunkCount = Math.ceil(file.size / attachmentChunkSize);

                    for (var i = 0; i < chunkCount; i++) {
                        var offset = i * attachmentChunkSize;
                        apiRequest("upload-attachment-chunk", {
                            attachment: attachmentId.attachment, 
                            data: file.slice(offset, offset + attachmentChunkSize), 
                            chunk_n: i, 
                            is_final: + (i == chunkCount-1)
                        }, 
                        "POST", true);

                        fileName.innerHTML = `Загрузка (${(i / (chunkCount - 1)) * 100}%)`;
                    }
                    fileName.innerText = fileSelect.files[0].name;
                    fileSize.innerText = sizeToString(fileSelect.files[0].size);

                    filesToUpload.push(attachmentId.attachment);
                });
                
                fileSelect.click();
            }

            // TODO: Удаление файла с сервера при удалении вложения
            function removeAttachment(id) {
                var index = filesToUpload.indexOf(id);
                if (id !== -1) 
                    filesToUpload.splice(index, 1);

                var el = document.getElementById(id.toString());
                el.remove();
            }

            function downloadAttachment(id) {
                var attachmentData = apiRequest("get-attachment-data", {attachment: id});

                if (attachmentData.status != 0) return;

                var a = document.createElement("a");
                a.setAttribute("href", attachmentData.data.path);
                a.setAttribute("download", attachmentData.data.name);
                a.click();
            }

            function uploadPost(e) {
                var form = document.forms["post-form"];
                e.preventDefault();

                if (form.content.value.length == 0) return;

                apiRequest("upload-post", {content: form.content.value, attachments: JSON.stringify(filesToUpload)}, "POST", true);

                document.location = "?u=me";
                
            }

            function like(id) {
                if (!getCookie("bs_session")) return;

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
                if (!getCookie("bs_session")) return;

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

            function playAudioAttachment(id) {
                var attachment = apiRequest("get-attachment-data", {attachment: id});

                if (attachment.status != 0) return;

                var playBtn = document.getElementById(`player-btn-${id}`);

                if (id == currentlyPlaying) {
                    if (audio.paused) {
                        audio.currentTime = currentPlayTime;
                        audio.play();
                        playBtn.classList.remove("bx-play");
                        playBtn.classList.add("bx-pause");
                        return;
                    }
                    currentPlayTime = audio.currentTime;
                    audio.pause();
                    playBtn.classList.remove("bx-pause");
                    playBtn.classList.add("bx-play");
                }
                else {
                    if (currentlyPlaying != -1) {
                        var prevPlayerBtn = document.getElementById(`player-btn-${currentlyPlaying}`);
                    
                        prevPlayerBtn.classList.remove("bx-pause");
                        prevPlayerBtn.classList.add("bx-play");

                        var prevTimeSlider = prevPlayerBtn.parentElement.getElementsByTagName("input")[0];
                        prevTimeSlider.remove();
                    }

                    playBtn.classList.remove("bx-play");
                    playBtn.classList.add("bx-pause");

                    audio.src = attachment.data.path;

                    var timeSlider = document.createElement("input");
                    timeSlider.setAttribute("type", "range");
                    timeSlider.value = 0;

                    audio.play();

                    audio.ondurationchange = (e) => {
                        timeSlider.max = audio.duration;
                        playBtn.parentElement.getElementsByClassName("audio-player")[0].appendChild(timeSlider);
                    }

                    audio.ontimeupdate = (e) => {
                        timeSlider.value = audio.currentTime;
                    }

                    audio.onended = (e) => {
                        playBtn.classList.remove("bx-pause");
                        playBtn.classList.add("bx-play");
                    }

                    timeSlider.oninput = (e) => {
                        audio.currentTime = timeSlider.value;
                        currentPlayTime = timeSlider.value;
                    }

                    currentlyPlaying = id;
                    currentPlayTime = 0;
                }
            }

            if (urlParams.has("u")) {
                var username = urlParams.get("u");
                var user = [];
                var userId = [];
                var userPosts = [];

                if (username == "me") {
                    if (!getCookie("bs_session")) {
                        document.location = "login.php";
                    }
                    
                    var sessionId = getCookie("bs_session");
                    userId = apiRequest("get-user-by-session", {session: sessionId});
                }
                else {
                    userId = apiRequest("get-id-by-username", {username: username});
                }

                user = apiRequest("get-user", {id: userId.user_id}).data;
                userPosts = apiRequest("get-user-posts", {username: user.username, from: 0, count: Number.MAX_SAFE_INTEGER}).data;

                for (var i = 0; i < userPosts.length; i++) {
                    var post = userPosts[i];
                    
                    var attachments = JSON.parse(post.attachments);
                    var attachmentsHTML = "";

                    for (var j = 0; j < attachments.length; j++) {
                        var attachment = attachments[j];
                        var attachmentData = apiRequest("get-attachment-data", {attachment: attachment});

                        if (attachmentData.data.mime_type.split("/")[0] == "audio") {
                            attachmentsHTML += `
                            <div class="file-attachment">
                                <i id="player-btn-${attachmentData.data.id}" class='icon bx bx-play' onclick="playAudioAttachment(${attachmentData.data.id});"></i>
                                <i class='icon bx bxs-download' onclick="downloadAttachment(${attachmentData.data.id});"></i>
                                <div class="audio-player">
                                    <span class="file-attachment-name">${attachmentData.data.name}</span>
                                </div>
                            </div>
                            `;
                        }
                        else {
                            attachmentsHTML += `
                            <div class="file-attachment" onclick="downloadAttachment(${attachmentData.data.id});">
                                <i class="icon bx bx-file-blank"></i>
                                <span class="file-attachment-name">${attachmentData.data.name}</span>
                                <span class="file-attachment-size">${sizeToString(attachmentData.data.size)}</span>
                                <span class="file-attachment-type">${attachmentData.data.mime_type}</span>
                            </div>
                            `;   
                        }
                    }

                    postsContainer.innerHTML += `
                    <div id="post-${post.id}" class="post">
                        <div class="post-header">
                            <img class="user-avatar" src="data/test-ava.png" width="32px" height="32px">
                            <a href="?u=${user.username}" class="post-user-nickname">${user.display_name}</a>
                            <p class="post-user-name">${user.username}</p>
                        </div>
                        <p class="post-content">${post.content}</p>
                        <div class="file-attachments">
                            ${attachmentsHTML}
                        </div>
                        <div class="post-toolbar">
                            <button id="like-${post.id}" onclick="like(${post.id});" class="counter-btn"><i class="icon-small bx ${likes.includes(post.id) ? "bxs-like" : "bx-like"}"></i> ${post.likes}</button>
                            <button id="dislike-${post.id}" onclick="dislike(${post.id});"class="counter-btn"><i class="icon-small bx ${dislikes.includes(post.id) ? "bxs-dislike" : "bx-dislike"}"></i> ${post.dislikes}</button>
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

                    var attachments = JSON.parse(post.attachments);
                    var attachmentsHTML = "";

                    for (var j = 0; j < attachments.length; j++) {
                        var attachment = attachments[j];
                        var attachmentData = apiRequest("get-attachment-data", {attachment: attachment});
                        attachmentsHTML += `
                        <div class="file-attachment" onclick="downloadAttachment(${attachmentData.data.id});">
                            <i class="icon bx bx-file-blank"></i>
                            <span class="file-attachment-name">${attachmentData.data.name}</span>
                            <span class="file-attachment-size">${sizeToString(attachmentData.data.size)}</span>
                            <span class="file-attachment-type">${attachmentData.data.mime_type}</span>
                        </div>
                        `;
                    }
                    
                    if (userReq.status != 0) {
                        user = {
                            display_name: "Неизвестный пользователь",
                            username: "UnknownUser"
                        }
                    }
                    else user = userReq.data;
                    
                    postsContainer.innerHTML += `
                    <div id="post-${post.id}" class="post">
                        <div class="post-header">
                            <img class="user-avatar" src="data/test-ava.png" width="32px" height="32px">
                            <a href="?u=${user.username}" class="post-user-nickname">${user.display_name}</a>
                            <p class="post-user-name">${user.username}</p>
                        </div>
                        <p class="post-content">${post.content}</p>
                        <div class="file-attachments">
                            ${attachmentsHTML}
                        </div>
                        <div class="post-toolbar">
                            <button id="like-${post.id}" onclick="like(${post.id});" class="counter-btn"><i class="icon-small bx ${likes.includes(post.id) ? "bxs-like" : "bx-like"}"></i> ${post.likes}</button>
                            <button id="dislike-${post.id}" onclick="dislike(${post.id});"class="counter-btn"><i class="icon-small bx ${dislikes.includes(post.id) ? "bxs-dislike" : "bx-dislike"}"></i> ${post.dislikes}</button>
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