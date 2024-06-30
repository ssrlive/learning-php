<?php
if (version_compare(PHP_VERSION, '7.4', '<')) {
    die('This Application requires PHP version 7.4 or higher.');
}

require_once $_SERVER['DOCUMENT_ROOT'] . "/func/Func.php";
if (isSessionLoggedIn()) {
    header("Location: /user/usercenter.php");
} else {
    header("Location: /user/login.php");
}
