<!DOCTYPE html>
<html lang="zh-CN">

<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/func/Func.php";

if (!isSessionLoggedIn()) {
    header("Location: /user/login.php");
    exit();
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change password</title>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/user/common/head.php"; ?>
    <style>
        .right-box {
            background: #fff;
            padding: 10px 20px;
            border-radius: 10px;
            margin: 0 20px;
            width: 100%;
        }

        .right-box button {
            padding: 8px 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #6767ab;
            color: #fff;
            cursor: pointer;
        }

        .edit-box {
            width: 100%;
        }

        .input-group {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .input-group span {
            flex: 0 1 auto;
            text-align: right;
            margin-right: 10px;
            width: 180px;
        }

        .input-group input {
            flex: 1;
        }

        .button-container {
            display: flex;
            justify-content: center;
            width: 100%;
        }
    </style>
</head>

<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/user/common/header.php"; ?>
    <div class="main">
        <div class="container-full" style="padding-top: 20px; display: flex; justify-content: flex-start;">
            <?php include $_SERVER['DOCUMENT_ROOT'] . "/user/common/sider.php"; ?>
            <div class="right-box">
                <div class="infos" style="justify-content: flex-start; display: flex;">
                    <div class="userinfo" style="margin-left: 20px; justify-content: space-between;">
                        <div class="edit-box">
                            <form action="" id="submit_form" onsubmit="submitForm(event)">
                                <div class="input-group">
                                    <span>Original password:</span> <input type="password" name="oldpwd">
                                </div>
                                <div class="input-group">
                                    <span>New password:</span> <input type="password" name="newpwd">
                                </div>
                                <div class="input-group">
                                    <span>Confirm new password:</span> <input type="password" name="newpwd2">
                                </div>
                                <div class="button-container">
                                    <button type="submit">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/user/common/footer.php"; ?>

    <script>
        function submitForm(event) {
            event.preventDefault();
            var oldpwd = document.querySelector("input[name=oldpwd]").value.trim();
            var newpwd = document.querySelector("input[name=newpwd]").value.trim();
            var newpwd2 = document.querySelector("input[name=newpwd2]").value.trim();
            if (newpwd != newpwd2) {
                alert("The new password is not the same as the confirmation password.");
                return;
            }
            if (newpwd.length < 6 || oldpwd.length < 6) {
                alert("The password must be at least 6 characters long.");
                return;
            }
            var form = document.querySelector("#submit_form");
            var formData = new FormData(form);
            fetch("/api/changepwd.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.code == 0) {
                        console.log("Password changed successfully.");
                        window.location.href = "/user/login.php";
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
    </script>
</body>

</html>