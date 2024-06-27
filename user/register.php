<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户注册</title>
    <link rel="stylesheet" href="/public/css/login.css">
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/user/common/head.php"; ?>
</head>

<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/user/common/header.php"; ?>
    <div class="main">
        <div class="container">
            <h2 style="margin-bottom: 10px; text-align: center;">用户注册</h2>
            <form id="submit_form" onsubmit="submitForm(event)">
                <ul>
                    <li class="line">
                        <span class="title">用戶名</span>
                        <input type="text" name="username" placeholder="用户名">
                    </li>
                    <li class="line">
                        <span class="title">密碼</span>
                        <input type="password" name="password" placeholder="密码">
                    </li>
                    <li class="line">
                        <span class="title">確認密碼</span>
                        <input type="password" name="password2" placeholder="确认密码">
                    </li>
                    <li class="line submit_line" style="margin-top: 50px;">
                        <input type="submit" name="submit" value="註冊" style="margin-left: 100px;">
                        <a href="/user/login.php">已有账号?去登录</a>
                    </li>
                </ul>
            </form>
        </div>
    </div>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/user/common/footer.php"; ?>

    <script>
        function isEmpty(str) {
            return str === undefined || str === null || str.trim() === "";
        }

        function strlenMin(str) {
            return str.length < 6;
        }

        function submitForm(event) {
            event.preventDefault(); // 阻止 表单提交導致的 頁面刷新
            var form = document.getElementById("submit_form");
            var formData = new FormData(form);
            var datas = formData.entries();
            var pass, pass2;
            var username;
            for (var data of datas) {
                if (isEmpty(data[1])) {
                    alert(data[0] + "不能为空");
                    return;
                }
                if (data[0].indexOf("password") >= 0) {
                    if (strlenMin(data[1])) {
                        alert("密码长度不能小于6位");
                        return;
                    }
                    if (data[0] === "password") {
                        pass = data[1];
                    } else {
                        pass2 = data[1];
                    }
                }
                if (data[0] === "username") {
                    username = data[1];
                }
            }
            if (pass && pass2 && pass !== pass2) {
                alert("两次密码不一致");
                return;
            }

            var url = "/api/register.php";
            fetch(url, {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    console.log(data);
                    if (data.code === 0) {
                        console.log("注册成功");
                        window.location.href = "/user/login.php?username=" + username;
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    </script>
</body>

</html>