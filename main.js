var attachmentChunkSize = 8*1024*1024;    // 8 Мб
var urlParams = new URLSearchParams(document.location.search);
var posts = document.getElementsByClassName("posts")[0];
var postsContainer = document.getElementById("posts");
var likes = [];
var dislikes = [];
var filesToUpload = [];
var audio = new Audio();
var currentlyPlaying = -1;
var currentPlayTime = 0;
var loggedIn = false;

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

var userId = apiRequest("get-user-by-session", {});
if (userId.status == 0) {
    var user = apiRequest("get-user", {id:userId.user_id});
    likes = JSON.parse(user.data.likes);
    dislikes = JSON.parse(user.data.dislikes);
    loggedIn = true;
}

function logout() {
    console.log(apiRequest("logout", {}));
    document.cookie = "bs_session=; Max-Age=0; path=/;expires=Thu, 01 Jan 1970 00:00:01 GMT";
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
    var imageAttachmentsContainer = document.getElementById("form-image-attachments");

    var fileSelect = document.createElement("input");
    fileSelect.setAttribute("type", "file");
    fileSelect.addEventListener("change", async (e) => {
        var attachmentId = apiRequest("create-attachment", {name: fileSelect.files[0].name, type: fileSelect.files[0].type, size: fileSelect.files[0].size});
        if (attachmentId.status != 0) return;
        console.log("zzz");
        var c = (fileSelect.files[0].type.split("/")[0] == "image") ? imageAttachmentsContainer : attachmentsContainer;
        c.innerHTML += `
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
        fileType.innerText = fileSelect.files[0].type;
        filesToUpload.push(attachmentId.attachment);

        if (fileSelect.files[0].type.split("/")[0] == "image") {
            attachment.innerHTML = `<img src="${attachmentId.path}" width="100%">`;
        }
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
    if (form.content.value.length == 0 && filesToUpload.length == 0 && !urlParams.get("repost")) return;
    apiRequest("upload-post", {content: form.content.value, attachments: JSON.stringify(filesToUpload), repost: (urlParams.get("repost") ? urlParams.get("repost") : -1)}, "POST", true);
    document.location = "?u=me";
}
function like(id) {
    if (!loggedIn) return;
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
    if (!loggedIn) return;
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
            playBtn.parentElement.getElementsByClassName("file-attachment-info")[0].appendChild(timeSlider);
        }
        audio.ontimeupdate = (e) => {
            timeSlider.value = audio.currentTime;
            currentPlayTime = audio.currentTime;
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
function updateAvatar() {
    if (!urlParams.has("u")) return;
    if (urlParams.get("u") != "me") return;
    var fileSelect = document.createElement("input");
    fileSelect.setAttribute("type", "file");
    fileSelect.setAttribute("accept", "image/png, image/jpeg");
    fileSelect.addEventListener("change", (e) => {
        apiRequest("update-avatar", {
            avatar: fileSelect.files[0]
        }, "POST", true);
        window.location.reload();
    });
    fileSelect.click();
    fileSelect.remove();
}
function changeNickame(e) {
    var parent = e.target.parentElement;
    var oldNickname = e.target.innerText;
    var input = document.createElement("input");
    input.classList.add("user-nickname");

    input.value = oldNickname;
    input.placeholder = oldNickname;
    parent.replaceChild(input, e.target);

    input.addEventListener("change", (e) => {
        console.log(input.value);
        if (input.value == "")
            input.value = oldNickname;
        apiRequest("update-nickname", {new_name: input.value});
        window.location.reload();
    })
}

function renderPost(user, post, parentElement, noToolbar=false) {
    var attachments = JSON.parse(post.attachments);
    var attachmentsHTML = "";
    var imageAttachmentsHTML = "";

    for (var j = 0; j < attachments.length; j++) {
        var attachment = attachments[j];
        var attachmentData = apiRequest("get-attachment-data", {attachment: attachment});

        var mime_type = attachmentData.data.mime_type.split("/")

        if (mime_type[0] == "audio") {
            attachmentsHTML += `
            <div class="file-attachment">
                <i id="player-btn-${attachmentData.data.id}" class='icon bx bx-play' onclick="playAudioAttachment(${attachmentData.data.id});"></i>
                <i class='icon bx bxs-download' onclick="downloadAttachment(${attachmentData.data.id});"></i>
                <div class="file-attachment-info">
                    <span class="file-attachment-name">${attachmentData.data.name}</span>
                </div>
            </div>
            `;
        }
        else if (mime_type[0] == "image" || mime_type[0] == "video") {
            imageAttachmentsHTML += `
            <div class="file-attachment">
                <${(mime_type[0] == "image" ? "img" : "video controls")} src="${attachmentData.data.path}" width="100%">
            </div>
            `;
        }
        else {
            attachmentsHTML += `
            <div class="file-attachment" onclick="downloadAttachment(${attachmentData.data.id});">
                <i class="icon bx bx-file-blank"></i>
                <div class="file-attachment-info">
                    <span class="file-attachment-name">${attachmentData.data.name}</span>
                    <span class="file-attachment-size">${sizeToString(attachmentData.data.size)}</span>
                    <span class="file-attachment-type">${attachmentData.data.mime_type}</span>
                </div>
            </div>
            `;   
        }
    }
    parentElement.innerHTML += `
    <div id="post-${post.id}" class="post">
        <div class="post-header">
            <img class="user-avatar" src="${user.avatar_file}" width="32px" height="32px">
            <a href="?u=${user.username}" class="post-user-nickname">${user.display_name}</a>
            <p class="post-user-name">${user.username}</p>
        </div>
        <p class="post-content">${post.content}</p>
        <div class="image-attachments">
            ${imageAttachmentsHTML}
        </div>
        <div class="file-attachments">
            ${attachmentsHTML}
        </div>
        <div class="repost">
        </div>
        ${((noToolbar) ? `` : `
        <div class="post-toolbar">
            <button id="like-${post.id}" onclick="like(${post.id});" class="counter-btn"><i class="icon-small bx ${likes.includes(post.id) ? "bxs-like" : "bx-like"}"></i> ${post.likes}</button>
            <button id="dislike-${post.id}" onclick="dislike(${post.id});"class="counter-btn"><i class="icon-small bx ${dislikes.includes(post.id) ? "bxs-dislike" : "bx-dislike"}"></i> ${post.dislikes}</button>
            <button class="counter-btn"><a href="?u=me&repost=${post.id}"><i class='icon-small bx bx-repost'></i></a></button>
            <button class="ellipsis-btn"><i class="icon-small bx bx-dots-vertical-rounded"></i></button-->
        </div>`)
        }
    </div>
    `;

    if (post.repost > -1) {
        var repostContainer = parentElement.children[`post-${post.id}`].getElementsByClassName("repost")[0];
        renderRepost(post.repost, repostContainer);
    }
}


function renderRepost(id, element) {
    var post = apiRequest("get-post", {id: id});
    if (post.status != 0) return;

    var author = apiRequest("get-user", {id: post.data.author_id});
    if (author.status != 0) return;

    renderPost(author.data, post.data, element, true);
}

if (urlParams.has("u")) {
    var username = urlParams.get("u");
    var user = [];
    var userId = [];
    var userPosts = [];
    if (username == "me") {
        userId = apiRequest("get-user-by-session");
        if (userId.status != 0) {
            document.location = "login.php";
        }
        if (urlParams.get("repost")) {
            renderRepost(urlParams.get("repost"), document.getElementById("form-repost"));
        }
    }
    else {
        userId = apiRequest("get-id-by-username", {username: username});
    }
    user = apiRequest("get-user", {id: userId.user_id}).data;
    userPosts = apiRequest("get-user-posts", {username: user.username, from: 0, count: Number.MAX_SAFE_INTEGER}).data;
    for (var i = 0; i < userPosts.length; i++) {
        var post = userPosts[i];

        var attachments = JSON.parse(post.attachments);
        if (attachments.length == 0 && urlParams.get("filter") == "media") continue;

        renderPost(user, post, postsContainer);
    }
}
else {
    var posts = apiRequest("get-posts", {from: 0, count: Number.MAX_SAFE_INTEGER}).data;
    for (var i = 0;i < posts.length; i++) {
        var post = posts[i];
        var user = apiRequest("get-user", {id: post.author_id});
        
        renderPost(user.data, post, postsContainer);
    }
}