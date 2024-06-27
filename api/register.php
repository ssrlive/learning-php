<?php
session_start();

require __DIR__ . "/../func/Db.class.php";
// require $_SERVER['DOCUMENT_ROOT'] . "/func/Db.class.php";

require __DIR__ . "/../func/Func.php";

$message = null;
foreach (["password", "username"] as $key) {
    if (!isset($_POST[$key]) || isEmpty($_POST[$key])) {
        $message = $key . "不能为空";
        break;
    }

    if ($key === "password" && strlenMin($_POST[$key])) {
        $message = $key . "长度不能小于6位";
        break;
    }
}

if ($message === null) {
    $message = checkRegistrationFrequency();
}

$result = 0;
if ($message === null) {
    $username = $_POST["username"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $result = Db::table("users")->where([["username", "=", $username]])->find();
    if ($result) {
        $result = $username;
        $message = "用户名已存在";
    } else {
        $result = Db::table("users")->insert(["username" => $username, "password" => $password]);
        if ($result >= 0) {
            $message = null;
        } else {
            $message = "注册失败";
        }
    }
}

$responseData = [
    "code" => $message !== null ? 400 : 0,
    "message" => $message,
    "data" => $result,
];

print_r(json($responseData));
