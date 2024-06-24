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
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/user/common/header.php"; ?>
    <div class="main">

        <div class="container">
            <h2 style="margin-bottom: 10px; text-align: center;">用户登錄</h2>
            <form id="submit_form">
                <ul>
                    <li class="line">
                        <span class="title">用戶名</span>
                        <input type="text" name="username" placeholder="用户名">
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
</body>

</html>