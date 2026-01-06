<?php
session_start();
require_once "../config/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if ($email === "" || $password === "") {
        $error = "Please enter email and password.";
    } else {

        $stmt = $conn->prepare("
            SELECT id, full_name, password_hash
            FROM admins
            WHERE email = ?
            LIMIT 1
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();

            if (password_verify($password, $admin["password_hash"])) {

                $_SESSION["admin_id"]   = $admin["id"];
                $_SESSION["admin_name"] = $admin["full_name"];

                header("Location: dashboard.php");
                exit;

            } else {
                $error = "Incorrect password.";
            }

        } else {
            $error = "Admin account not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Login</title>

<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #0f172a;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.login-box {
    background: #ffffff;
    padding: 30px;
    width: 360px;
    border-radius: 14px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.3);
}

.login-box h2 {
    text-align: center;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 14px;
}

.form-group label {
    font-weight: bold;
    display: block;
    margin-bottom: 6px;
}

.form-group input {
    width: 100%;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
}

.btn {
    width: 100%;
    padding: 12px;
    background: #2563eb;
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    cursor: pointer;
}

.error {
    background: #fee2e2;
    color: #991b1b;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 15px;
    text-align: center;
}

.back-link {
    text-align: center;
    margin-top: 12px;
    font-size: 14px;
}

.back-link a {
    text-decoration: none;
    color: #2563eb;
}
</style>
</head>

<body>

<div class="login-box">
    <h2>Admin Login</h2>

    <?php if ($error != ""): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <button class="btn" type="submit">Login</button>
    </form>

    <div class="back-link">
        <a href="/online_motor_souq/index.php">← Back to Startup Page</a>
    </div>
</div>

</body>
</html>
