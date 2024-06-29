<!DOCTYPE html>
<html lang="zh-CN">

<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/func/Func.php";
if (!isSessionLoggedIn()) {
    header("Location: /user/login.php");
    exit();
}
$userinfo = getSessionLoginInfo();

require_once $_SERVER['DOCUMENT_ROOT'] . "/func/Db.class.php";
$log = Db::table("login_log")->where([
    ["user_id", "=", $userinfo["userid"]]
])->orderBy("login_time", "desc")->find();
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User center</title>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/user/common/head.php"; ?>
    <style>
        .right-box {
            background: #fff;
            padding: 10px 20px;
            border-radius: 10px;
            margin: 0 20px;
            width: 100%;
        }

        .right-box input {
            padding: 8px 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .right-box button {
            padding: 8px 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #6767ab;
            color: #fff;
            cursor: pointer;
        }

        .userinfo .username {
            font-size: 30px;
            font-weight: bold;
        }

        .inline-line {
            margin-top: 20px;
        }

        .inline-line .line {
            display: inline-block;
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
                    <div class="img">
                        <?php $username_first = mb_substr($userinfo["username"], 0, 1, "UTF-8");
                        echo "<img src='https://dummyimage.com/100x100/000/fff&text={$username_first}' alt='User image' style='border-radius: 50%; width: 100px; height: 100px;'>";
                        ?>
                    </div>
                    <div class="userinfo" style="margin-left: 20px; justify-content: space-between;">
                        <div class="line username">
                            <?php echo $userinfo["username"]; ?>
                            <span id="edit-btn" style="font-size: 13px; cursor: pointer;">modify name</span>
                        </div>
                        <div class="inline-line">
                            <div class="line logtime">
                                Last login time: <?php echo $log["login_time"]; ?>
                            </div>
                            <div class="line logip">
                                Last login IP: <?php echo $log["login_ip"]; ?>
                            </div>
                        </div>
                        <div class="edid-box" style="display: none;">
                            <input type="text" name="newname">
                            <button id="submit" onclick="submitModify()">Submit modify</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/user/common/footer.php"; ?>

    <script>
        document.getElementById("edit-btn").addEventListener("click", function() {
            document.querySelector(".edid-box").style.display = "block";
        });

        function submitModify() {
            var newname = document.querySelector("input[name='newname']").value;
            if (newname.length < 3) {
                alert("The new name must be at least 3 characters long");
                return;
            }
            var formData = new FormData();
            formData.append("username", newname);
            fetch("/api/user_edit.php", {
                    method: "POST",
                    body: formData
                })
                .then(res => res.json()).then(data => {
                    if (data.code === 0) {
                        location.reload();
                    } else {
                        document.querySelector("input[name='newname']").value = "";
                        alert(data.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                });
        }
    </script>
</body>

</html>