<?php

/**
 * 检查注册频率, 返回封禁信息. 例如，3分鐘內不超過10次請求，否則封禁 1 小時
 * 調用這個函數之前，需要先啟用 session，例如對 session_start(); 的調用
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

// Double md5 hash
function md5x2($str, $salt = "salt")
{
    return md5(md5($str) . $salt);
}

function json($data)
{
    return json_encode($data, JSON_UNESCAPED_UNICODE);
}
