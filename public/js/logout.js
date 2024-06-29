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

// 绑定事件监听器
document.addEventListener("DOMContentLoaded", function () {
    const logoutElement = document.getElementById("logout");
    if (logoutElement) {
        logoutElement.addEventListener("click", doLogout);
    }

    const logoutElement2 = document.getElementById("logout2");
    if (logoutElement2) {
        logoutElement2.addEventListener("click", doLogout);
    }
});
