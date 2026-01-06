<?php
session_start();
require_once "../config/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST["full_name"]);
    $email     = trim($_POST["email"]);
    $phone     = trim($_POST["phone"]);
    $password  = $_POST["password"];

   
    if ($full_name === "" || $email === "" || $password === "") {
        $message = "All required fields must be filled.";
    } else {

        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "This email is already registered.";
        } else {

            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            
            $stmt = $conn->prepare("
                INSERT INTO users (full_name, email, phone, password_hash)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param(
                "ssss",
                $full_name,
                $email,
                $phone,
                $hashed_password
            );

            if ($stmt->execute()) {
                header("Location: login.php?registered=1");
                exit;
            } else {
                $message = "Registration failed. Please try again.";
            }

            $stmt->close();
        }

        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Registration</title>

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
    background:#16a34a;
    color:#fff;
    border:none;
    border-radius:10px;
    font-weight:bold;
    cursor:pointer;
}

.error {
    margin-bottom: 12px;
    padding: 10px;
    border-radius: 8px;
    background: #fff1f1;
    border: 1px solid #ffd0d0;
    color: #a40000;
}

.login-link {
    margin-top: 12px;
    text-align: center;
    font-size: 14px;
}
</style>
</head>

<body>

<div class="form-box">
    <h2>User Registration</h2>

    <?php if ($message !== ""): ?>
        <div class="error"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="form-group">
            <label>Full Name *</label>
            <input type="text" name="full_name" required>
        </div>

        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone">
        </div>

        <div class="form-group">
            <label>Password *</label>
            <input type="password" name="password" required>
        </div>

        <button class="btn" type="submit">Register</button>

        <div class="login-link">
            Already have an account?
            <a href="login.php">Login here</a>
        </div>

    </form>
</div>

</body>
</html>
