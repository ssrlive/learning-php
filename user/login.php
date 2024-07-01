<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/func/Func.php";
if (isSessionLoggedIn()) {
    setSessionLogout();
}
?>

<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户登錄</title>
    <link rel="stylesheet" href="/public/css/login.css">
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/user/common/head.php"; ?>
</head>

<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/user/common/header.php";
    $username = $_GET["username"] ?? "";
    ?>
    <div class="main">

        <div class="container">
            <h2 style="margin-bottom: 10px; text-align: center;">用户登錄</h2>
            <form id="submit_form" onsubmit="submitForm(event)">
                <ul>
                    <li class="line">
                        <span class="title">用戶名</span>
                        <input type="text" name="username" placeholder="用户名" value="<?php echo $username; ?>">
                    </li>
                    <li class="line">
                        <span class="title">密碼</span>
                        <input type="password" name="password" placeholder="密码">
                    </li>
                    <li class="line submit_line" style="margin-top: 50px;">
                        <input type="submit" name="submit" value="登錄" style="margin-left: 100px;">
                        <a href="/user/register.php">沒有账号?去註冊</a>
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

        function submitForm(event) {
            event.preventDefault(); // 阻止 表单提交導致的 頁面刷新
            var form = document.getElementById("submit_form");
            var formData = new FormData(form);
            var datas = formData.entries();
            var pass;
            var username;
            for (var data of datas) {
                if (isEmpty(data[1])) {
                    alert(data[0] + "不能为空");
                    return;
                }
                if (data[0] === "password") {
                    pass = data[1];
                }
                if (data[0] === "username") {
                    username = data[1];
                }
            }
            var url = "/api/login.php";
            fetch(url, {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    console.log(data);
                    if (data.code != 0) {
                        alert(data.message);
                    } else {
                        console.log(data.message);
                        window.location.href = "/user/usercenter.php";
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    </script>
</body>

</html>