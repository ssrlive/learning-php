<?php

function isEmpty($str)
{
    return $str === null || empty(trim($str));
}

function strlenMin($str)
{
    return strlen($str) < 6;
}

$message = "";
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

$responseData = [
    "code" => 0,
    "message" => $message,
    "data" => []
];
if ($message !== "") {
    $responseData["code"] = 400;
}

print_r(json_encode($responseData, JSON_UNESCAPED_UNICODE));
