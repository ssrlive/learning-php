<div class="header">
    <div class="container-full">
        <div class="sitename">
            Coder Old bastard
        </div>
        <div class="userinfo">
            <?php if (isset($userinfo)) {
                echo '<span class="username">' . $userinfo["username"] . '</span>';
                echo ' <a href="javascript:void(0);" id="logout2" style="text-decoration: none; color: inherit;">Logout</a>';
            } else {
                echo '<a href="/user/login.php" style="text-decoration: none; color: inherit;">Login</a>';
                echo ' | <a href="/user/register.php" style="text-decoration: none; color: inherit;">Register</a>';
            }
            ?>
        </div>
    </div>
</div>

<script src="/public/js/logout.js">
</script>