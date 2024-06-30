<?php
require_once __DIR__ . "/../func/Func.php";
require_once __DIR__ . "/../func/Db.class.php";

$responseData = [
    "code" => 400,
    "message" => "You are not logged in",
    "data" => null,
];
if (!isSessionLoggedIn()) {
    print_r(json($responseData));
    exit();
}

$message = null;
$userinfo = getSessionLoginInfo();

$oldpwd = null;
if (isset($_POST["oldpwd"]) && !isEmpty($_POST["oldpwd"])) {
    $oldpwd = trim($_POST["oldpwd"]);
}

$newpwd = null;
if (isset($_POST["newpwd"]) && !isEmpty($_POST["newpwd"])) {
    $newpwd = trim($_POST["newpwd"]);
}

if ($oldpwd === null || $newpwd === null) {
    $message = "Password cannot be empty";
} else {
    $user_id = $userinfo["userid"];
    $result = Db::table("users")->where([["id", "=", $user_id]])->find();
    if ($result) {
        $isPasswordCorrect = password_verify($oldpwd, $result["password"]);
        if (!$isPasswordCorrect) {
            $message = "The original password is incorrect";
        } else {
            $newpwd = password_hash($newpwd, PASSWORD_DEFAULT);
            $result = Db::table("users")->where([["id", "=", $user_id]])->update(["password" => $newpwd]);
            if ($result >= 0) {
                $message = null;
                setSessionLogout();
            } else {
                $message = "Update failed";
            }
        }
    } else {
        $message = "User not found";
    }
}

$responseData = [
    "code" => $message !== null ? 400 : 0,
    "message" => $message !== null ? $message : "Password changed successfully",
    "data" => null,
];

print_r(json($responseData));
