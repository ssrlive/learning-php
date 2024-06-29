<div class="header">
    <div class="container-full">
        <div class="sitename">
            Coder老混混
        </div>
        <div class="userinfo">
            <?php if (isset($userinfo)) {
                echo '<span class="username">' . $userinfo["username"] . '</span>';
                echo ' <a href="javascript:void(0);" id="logout2" style="text-decoration: none; color: inherit;">退出</a>';
            } else {
                echo '<a href="/user/login.php" style="text-decoration: none; color: inherit;">登錄</a>';
                echo ' | <a href="/user/register.php" style="text-decoration: none; color: inherit;">註冊</a>';
            }
            ?>
        </div>
    </div>
</div>