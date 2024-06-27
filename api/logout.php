<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../func/Func.php";

if (isset($_SESSION["userinfo"])) {
    unset($_SESSION["userinfo"]);
    $message = "Logout successfully";
    $code = 0;
} else {
    $message = "You are not logged in";
    $code = 400;
}

$responseData = [
    "code" => $code,
    "message" => $message,
    "data" => null,
];
print_r(json($responseData));
