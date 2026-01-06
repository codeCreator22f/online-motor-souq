<?php
// index.php = Startup page
session_start();


if (isset($_SESSION['user_id'])) {
    header("Location: /online_motor_souq/user/home.php");
    exit;
}


if (isset($_SESSION['admin_id'])) {
    header("Location: /online_motor_souq/admin/dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Online Motor Souq - Startup</title>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, Helvetica, sans-serif;
}


body {
    min-height: 100vh;
    background: linear-gradient(135deg, #0f172a, #1e293b);
    display: flex;
    justify-content: center;
    align-items: center;
}


.center-page {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
}


.start-card {
    background: #ffffff;
    width: 420px;
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 20px 45px rgba(0,0,0,0.35);
    text-align: center;
}


.start-card h1 {
    margin-bottom: 10px;
    color: #0f172a;
}


.start-card p {
    font-size: 14px;
    color: #444;
    margin-bottom: 20px;
    line-height: 1.6;
}


.btn-group {
    display: flex;
    flex-direction: column;
    gap: 12px;
}


.btn {
    padding: 12px;
    border-radius: 10px;
    text-decoration: none;
    color: #fff;
    font-weight: bold;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}


.btn-user {
    background: #2563eb;
}

.btn-register {
    background: #16a34a;
}

.btn-admin {
    background: #ef4444;
}


.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}


.note {
    margin-top: 18px;
    font-size: 12px;
    color: #666;
}
</style>
</head>

<body>

<div class="center-page">
    <div class="start-card">
        <h1>Online Motor Souq</h1>

        <p>
            Welcome! This platform helps you buy and sell cars in Oman safely.
            Please choose how you want to continue.
        </p>

        <div class="btn-group">
            <a class="btn btn-user" href="/online_motor_souq/user/login.php">User Login</a>
            <a class="btn btn-register" href="/online_motor_souq/user/register.php">User Register</a>
            <a class="btn btn-admin" href="/online_motor_souq/admin/login.php">Admin Login</a>
        </div>

        <div class="note">
            Tip: Users can register and login. Admins must login using admin credentials.
        </div>
    </div>
</div>

</body>
</html>
