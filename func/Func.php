<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Checks the registration frequency and returns ban information. For example, no more than 10 requests within 3 minutes, otherwise ban for 1 hour.
 * Before calling this function, you need to start the session, for example by calling session_start();
 * @param int $reg_limit_seconds Registration time limit in seconds
 * @param int $max_requests Maximum number of requests
 * @param int $ban_seconds Ban duration in seconds
 * @return string|null Ban information, or null if no ban is triggered
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
            // If the time limit is exceeded, reset the count and start time
            $_SESSION["reg_count"] = 1;
            $_SESSION["reg_start_time"] = $current_time;
        }
    }

    if ($_SESSION["reg_count"] > $max_requests && !isset($_SESSION["reg_banned"])) {
        // If the request limit is exceeded, set the ban status and unban time
        $_SESSION["reg_ban_time"] = $current_time + $ban_seconds;
        $_SESSION["reg_banned"] = true;
    }

    if (isset($_SESSION["reg_banned"])) {
        if ($current_time < $_SESSION["reg_ban_time"]) {
            // User is currently banned
            $message = "Registration is temporarily banned, please try again later. Unban in " . ($_SESSION["reg_ban_time"] - $current_time) . " seconds.";
            return $message;
        } else {
            // Ban duration has passed, lift the ban
            unset($_SESSION["reg_banned"]);
            unset($_SESSION["reg_ban_time"]);
            $_SESSION["reg_count"] = 1;
            $_SESSION["reg_start_time"] = $current_time;
        }
    }

    return null; // No ban triggered, return null
}

/**
 * Checks the number of login attempts and returns ban information
 * Before calling this function, you need to start the session, for example by calling session_start();
 * @param bool $isPasswordCorrect Indicates if the password is correct
 * @param int $maxAttempts Maximum number of attempts
 * @param int $banDuration Ban duration in seconds
 * @return string|null Ban information, or null if no ban is triggered
 */
function checkLoginAttempt($isPasswordCorrect, $maxAttempts = 5, $banDuration = 3600): ?string
{
    $current_time = time();

    // Check if login is currently banned
    if (isset($_SESSION["login_banned"]) && $_SESSION["login_ban_time"] <= $current_time) {
        // If the ban duration has passed, reset all related session variables
        unset($_SESSION["login_attempts"]);
        unset($_SESSION["login_banned"]);
        unset($_SESSION["login_ban_time"]);
    }

    if (isset($_SESSION["login_banned"]) && $_SESSION["login_ban_time"] > $current_time) {
        $ban_duration = $_SESSION["login_ban_time"] - $current_time;
        return "You are currently banned from logging in, please try again after {$ban_duration} seconds.";
    }

    // Check if the password is correct
    if ($isPasswordCorrect) {
        // If the password is correct, reset the login attempt record
        unset($_SESSION["login_attempts"]);
        unset($_SESSION["login_banned"]);
        unset($_SESSION["login_ban_time"]);
        return null;
    } else {
        // If the password is incorrect, increment the attempt counter
        $_SESSION["login_attempts"] = (isset($_SESSION["login_attempts"]) ? $_SESSION["login_attempts"] : 0) + 1;

        // Check if the maximum number of attempts has been reached
        if ($_SESSION["login_attempts"] >= $maxAttempts) {
            $_SESSION["login_banned"] = true;
            $_SESSION["login_ban_time"] = $current_time + $banDuration; // Ban from logging in for a specified duration
            return "Password entered incorrectly {$maxAttempts} times, you have been banned from logging in for {$banDuration} seconds.";
        }

        return "Password incorrect, you have " . ($maxAttempts - $_SESSION["login_attempts"]) . " more attempt(s).";
    }
}

function isEmpty($str)
{
    return $str === null || empty(trim($str));
}

function strlenMin($str)
{
    return strlen($str) < 6;
}

function json($data)
{
    return json_encode($data, JSON_UNESCAPED_UNICODE);
}
