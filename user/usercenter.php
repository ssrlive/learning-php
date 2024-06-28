<!DOCTYPE html>
<html lang="zh-CN">

<?php
require $_SERVER['DOCUMENT_ROOT'] . "/func/Func.php";
if (!isSessionLoggedIn()) {
    header("Location: /user/login.php");
    exit();
}
$userinfo = getSessionLoginInfo();
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户中心</title>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/user/common/head.php"; ?>
</head>

<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/user/common/header.php"; ?>
    <div class="main">
        <div class="container-full" style="padding-top: 20px;">
            <?php include $_SERVER['DOCUMENT_ROOT'] . "/user/common/sider.php"; ?>
        </div>
    </div>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/user/common/footer.php"; ?>
</body>

</html>