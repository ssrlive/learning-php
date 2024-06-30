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

$count = Db::table("login_log")->where([
    ["user_id", "=", $userinfo["userid"]]
])->count();

$pageSize = 4; // 每页显示的记录数
$pages = ceil($count / $pageSize);

$page = isset($_GET["page"]) ? intval($_GET["page"]) : 1;
if ($page > $pages) {
    $page = $pages;
}

$offset = ($page - 1) * $pageSize;

$logs = Db::table("login_log")->where([
    ["user_id", "=", $userinfo["userid"]]
])->orderBy("login_time", "desc")->limit($pageSize, $offset)->select();
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User logs</title>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/user/common/head.php"; ?>
    <style>
        .right-box {
            background: #fff;
            padding: 10px 20px;
            border-radius: 10px;
            margin: 0 20px;
            width: 100%;
        }

        #logs {
            border-collapse: collapse;
            width: 100%;
        }

        #logs th {
            background: #6767ab;
            color: #fff;
        }

        #logs th,
        #logs td {
            border: 1px solid #ddd;
            padding: 3px 5px;
        }

        .pagelist ul {
            display: flex;
            justify-content: flex-start;
        }

        .pagelist li {
            width: 40px;
            height: 30px;
            line-height: 30px;
            text-align: center;
            border: 1px solid #ddd;
            margin-right: 10px;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/user/common/header.php"; ?>
    <div class="main">
        <div class="container-full" style="padding-top: 20px; display: flex; justify-content: flex-start;">
            <?php include $_SERVER['DOCUMENT_ROOT'] . "/user/common/sider.php"; ?>
            <div class="right-box">
                <div class="lists">
                    <h2>Logs of <?php echo $userinfo["username"]; ?></h2>
                    <?php if (isset($logs) && count($logs) > 0) { ?>
                        <table id="logs">
                            <tr>
                                <th>Log ID</th>
                                <th>Login time</th>
                                <th>IP</th>
                            </tr>
                            <?php foreach ($logs as $log) { ?>
                                <tr>
                                    <td><?php echo $log["id"]; ?></td>
                                    <td><?php echo $log["login_time"]; ?></td>
                                    <td><?php echo $log["login_ip"]; ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                        <div class="pagelist">
                            <ul>
                                <?php if ($page > 1) { ?>
                                    <li class="page"><a href="/user/logs.php?page=1">First</a></li>
                                    <li class="page"><a href="/user/logs.php?page=<?php echo $page - 1; ?>">Prev</a></li>
                                <?php } ?>

                                <?php
                                // 设置显示的页码数，这里设置为从当前页前后各显示2页
                                $range = 2;
                                for ($x = ($page - $range); $x < (($page + $range) + 1); $x++) {
                                    // 确保页码有效
                                    if (($x > 0) && ($x <= $pages)) {
                                        // 如果是当前页，则高亮显示
                                        if ($x == $page) {
                                            echo "<li class='page' style='background: #6767ab; color: #fff;'><strong>$x</strong></li>";
                                        } else {
                                            echo "<li class='page'><a href='/user/logs.php?page=$x'>$x</a></li>";
                                        }
                                    }
                                }
                                ?>

                                <?php if ($page < $pages) { ?>
                                    <li class="page"><a href="/user/logs.php?page=<?php echo $page + 1; ?>">Next</a></li>
                                    <li class="page"><a href="/user/logs.php?page=<?php echo $pages; ?>">Last</a></li>
                                <?php } ?>
                            </ul>
                        </div>
                    <?php } else { ?>
                        <p>No logs yet</p>
                    <?php } ?>
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