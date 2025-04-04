<?php
    include "api/api.php";

    if (isset($_COOKIE["bs_session"])){
        //$sessions_q = $db->query("SELECT * FROM `sessions` WHERE `id`='".$_COOKIE["bs_session"]."'");
        $session = api_request("get-user-by-session", ["session"=>$_COOKIE["bs_session"]]);
        if ($session["status"] == 0) {
            header("Location: index.php");
            die();
        }
    }
    
    
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width,initial-scale=1"/>
        <title>Вход</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body class="login-page">
        <div class="post login-form">
            <form name="loginForm">
                <input type="hidden" name="action" value="login">
                <input required type="text" name="username" placeholder="Имя пользователя">
                <input required type="password" name="password" placeholder="Пароль">
                <input type="submit" value="Вход">
                <span class="error" id="login-error"></span>
            </form>
        </div>
        <p>или</p>
        <div class="post login-form">
            <form name="registerForm">
                <input type="hidden" name="action" value="register">
                <input required type="text" name="username" placeholder="Имя пользователя">
                <input required type="password" name="password" placeholder="Придумайте пароль">
                <!--input type="password" name="password" placeholder="Повторите пароль"-->
                <input type="submit" value="Регистрация">
                <span class="error" id="register-error"></span>
            </form>
        </div>
        <script>
            var loginForm = document.getElementsByName("loginForm")[0];
            var registerForm = document.getElementsByName("registerForm")[0];

            var loginError = document.getElementById("login-error");
            var registerError = document.getElementById("register-error");

            async function login() {
                var username = loginForm.elements.username;
                var password = loginForm.elements.password;

                req = await fetch("api/auth.php", {
                    method: "post",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "username="+username.value+"&password="+password.value
                });

                response = await req.json();

                if (response.status == 0) {
                    //document.cookie += "bs_session="+response.session_id+";";
                    document.location = "index.php";
                    return;
                }
                else if (response.status == 1) {
                    loginError.innerHTML = "Неверное имя пользователя или пароль";
                    return;
                }
            }

            async function register() {
                var username = registerForm.elements.username;
                var password = registerForm.elements.password;

                console.log(username.value);

                var req = await fetch("api/register.php", {
                    method: "post",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "username="+username.value+"&password="+password.value
                });

                var response = await req.json();
                
                if (response.status == 2) {
                    registerError.innerHTML = "Такой пользователь уже существует";
                    return;
                }

                req = await fetch("api/auth.php", {
                    method: "post",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "username="+username.value+"&password="+password.value
                });

                //console.log(await req.text());
                response = await req.json();

                if (response.status == 0) {
                    //document.cookie += "bs_session="+response.session_id+";";
                    document.location = "index.php";
                    return;
                }

                registerError.innerHTML += "Что-то пошло не так";
            }

            loginForm.addEventListener("submit", (e) => {
                e.preventDefault();
                login();
            });

            registerForm.addEventListener("submit", (e) => {
                e.preventDefault();
                register();
            });

        </script>
    </body>
</html>