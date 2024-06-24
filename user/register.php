<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户注册</title>
    <link rel="stylesheet" href="/public/css/login.css">
</head>

<body>
    <div class="container">
        <h2 style="margin-bottom: 10px; text-align: center;">用户注册</h2>
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
</body>

</html>