<div class="sider">
    <ul>
        <li class="menu"><a class="link" href="/user/userinfo.php">用户信息</a></li>
        <li class="menu"><a class="link" href="/user/usercenter.php">登錄記錄</a></li>
        <li class="menu"><a class="link" href="/user/changepwd.php">修改密码</a></li>
        <li class="menu"><a class="link" id="logout">Logout</a></li>
    </ul>
</div>

<script>
    document.getElementById("logout").addEventListener("click", doLogout);
    document.getElementById("logout2").addEventListener("click", doLogout);

    function doLogout() {
        fetch("/api/logout.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: "logout=1",
        }).then(response => response.json()).then(data => {
            console.log(data);
            if (data.code === 0) {
                window.location.href = "/user/login.php";
            } else {
                alert(data.message);
            }
        }).catch(error => {
            console.error("Error:", error);
        });
    }
</script>