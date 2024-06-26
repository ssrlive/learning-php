<?php
session_start();

require __DIR__ . "/../func/Db.class.php";
// require $_SERVER['DOCUMENT_ROOT'] . "/func/Db.class.php";

/**
 * 检查注册频率, 返回封禁信息. 例如，3分鐘內不超過10次請求，否則封禁 1 小時
 * @param int $reg_limit_seconds 注册时间限制，单位秒
 * @param int $max_requests 最大请求次数
 * @param int $ban_seconds 封禁时间，单位秒
 */
function checkRegistrationFrequency($reg_limit_seconds = 180, $max_requests = 10, $ban_seconds = 3600): ?string
{
    $current_time = time();

    if (!isset($_SESSION["reg_start_time"])) {
        $_SESSION["reg_start_time"] = $current_time;
        $_SESSION["reg_count"] = 1;
    } else {
        if ($current_time - $_SESSION["reg_start_time"] <= $reg_limit_seconds) {
            $_SESSION["reg_count"]++;
        } else {
            // 超过时间限制，重置计数和开始时间
            $_SESSION["reg_count"] = 1;
            $_SESSION["reg_start_time"] = $current_time;
        }
    }

    if ($_SESSION["reg_count"] > $max_requests && !isset($_SESSION["reg_banned"])) {
        // 超过请求次数限制，设置封禁状态和解封时间
        $_SESSION["reg_ban_time"] = $current_time + $ban_seconds;
        $_SESSION["reg_banned"] = true;
    }

    if (isset($_SESSION["reg_banned"])) {
        if ($current_time < $_SESSION["reg_ban_time"]) {
            // 用户处于封禁状态
            $message = "注册功能暂时封禁，请稍后再试。" . ($_SESSION["reg_ban_time"] - $current_time) . "秒后解封。";
            return $message;
        } else {
            // 封禁时间已过，解除封禁状态
            unset($_SESSION["reg_banned"]);
            unset($_SESSION["reg_ban_time"]);
            $_SESSION["reg_count"] = 1;
            $_SESSION["reg_start_time"] = $current_time;
        }
    }

    return null; // 没有触发封禁，返回null
}

function isEmpty($str)
{
    return $str === null || empty(trim($str));
}

function strlenMin($str)
{
    return strlen($str) < 6;
}

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
    $password = $_POST["password"];
    $result = Db::table("users")->where([["username", "=", $username]])->find();
    if ($result) {
        $result = $username;
        $message = "用户名已存在";
    } else {
        $result = Db::table("users")->insert(["username" => $username, "password" => md5($password)]);
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

print_r(json_encode($responseData, JSON_UNESCAPED_UNICODE));
