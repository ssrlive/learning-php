<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . "/../func/Db.class.php";
require __DIR__ . "/../func/Func.php";

$message = null;
foreach (["password", "username"] as $key) {
    if (!isset($_POST[$key]) || isEmpty($_POST[$key])) {
        $message = $key . "不能为空";
        break;
    }
}

if ($message === null) {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $userinfo = Db::table("users")->where([["username", "=", $username]])->find();
    if ($userinfo !== false) {
        $isPasswordCorrect = password_verify($password, $userinfo["password"]);
        $message = checkLoginAttempt($isPasswordCorrect);
        if ($message === null) {
            $result =  Db::table("login_log")->insert([
                "user_id" => $userinfo["id"],
                "login_ip" => $_SERVER["REMOTE_ADDR"],
            ]);
            if ($result >= 0) {
                $loginInfo = [
                    "userid" => $userinfo["id"],
                    "username" => $userinfo["username"],
                ];
                setSessionLogin($loginInfo);
            } else {
                $message = "記錄登錄信息失敗";
            }
        }
    } else {
        $message = "用户不存在";
    }
}

$responseData = [
    "code" => $message !== null ? 400 : 0,
    "message" => $message !== null ? $message : "登录成功",
    "data" => getSessionLoginInfo()
];
print_r(json($responseData));
