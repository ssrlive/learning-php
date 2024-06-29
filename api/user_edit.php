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

if (isset($_POST["username"]) && !isEmpty($_POST["username"])) {
    $username = trim($_POST["username"]);
    $result = Db::table("users")->where([["username", "=", $username]])->find();
    if ($result) {
        $message = "Username already exists";
    } else {
        $user_id = $userinfo["userid"];
        $result = Db::table("users")->where([["id", "=", $user_id]])->update(["username" => $username]);
        if ($result >= 0) {
            $message = null;
            $loginInfo = [
                "userid" => $user_id,
                "username" => $username,
            ];
            setSessionLogin($loginInfo);
        } else {
            $message = "Update failed";
        }
    }
} else {
    $message = "Username cannot be empty";
}

$responseData = [
    "code" => $message !== null ? 400 : 0,
    "message" => $message !== null ? $message : "Update successful",
    "data" => null,
];

print_r(json($responseData));
