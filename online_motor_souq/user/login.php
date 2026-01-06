<?php
session_start();
require_once "../config/db.php";

$message = "";


if (isset($_GET["registered"]) && $_GET["registered"] == "1") {
    $message = "Registration successful! Please login now.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if ($email === "" || $password === "") {
        $message = "Please enter email and password.";
    } else {

        
        $stmt = $conn->prepare("
            SELECT id, full_name, password_hash 
            FROM users 
            WHERE email = ? 
            LIMIT 1
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($id, $full_name, $password_hash);

        if ($stmt->fetch()) {

            if (password_verify($password, $password_hash)) {

                $_SESSION["user_id"] = $id;
                $_SESSION["full_name"] = $full_name;

                header("Location: home.php");
                exit;

            } else {
                $message = "Wrong password. Please try again.";
            }

        } else {
            $message = "No account found with this email.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Login</title>

<style>
body {
    background: #f4f6f8;
    font-family: Arial, sans-serif;
}

.form-box {
    max-width: 480px;
    margin: 60px auto;
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,.08);
}

.form-box h2 {
    margin-bottom: 15px;
}

.form-group {
    margin-bottom: 14px;
}

.form-group label {
    display:block;
    margin-bottom:6px;
    font-weight:bold;
}

.form-group input {
    width:100%;
    padding:10px;
    border-radius:8px;
    border:1px solid #ccc;
}

.btn {
    width:100%;
    padding:12px;
    background:#2563eb;
    color:#fff;
    border:none;
    border-radius:10px;
    font-weight:bold;
    cursor:pointer;
}

.msg {
    margin-bottom: 12px;
    padding: 10px;
    border-radius: 8px;
    background: #eef6ff;
    border: 1px solid #cfe4ff;
    color: #0b3d91;
}

.error {
    margin-bottom: 12px;
    padding: 10px;
    border-radius: 8px;
    background: #fff1f1;
    border: 1px solid #ffd0d0;
    color: #a40000;
}

.link-row {
    margin-top: 10px;
    text-align: center;
    font-size: 14px;
}
</style>
</head>

<body>

<div class="form-box">
    <h2>User Login</h2>

    <?php if ($message !== ""): ?>
        <div class="<?= strpos($message, "successful") !== false ? "msg" : "error" ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Password *</label>
            <input type="password" name="password" required>
        </div>

        <button class="btn" type="submit">Login</button>

        <div class="link-row">
            Don’t have an account?
            <a href="register.php">Register here</a>
        </div>

        <div class="link-row">
            <a href="/online_motor_souq/index.php">Back to Startup Page</a>
        </div>

    </form>
</div>

</body>
</html>
