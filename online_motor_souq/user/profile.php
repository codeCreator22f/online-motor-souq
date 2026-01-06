<?php
session_start();
require_once "../config/db.php";


if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$message = "";


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_profile"])) {

    $full_name = trim($_POST["full_name"]);
    $phone     = trim($_POST["phone"]);

    if ($full_name === "") {
        $message = "Full name is required.";
    } else {
        $stmt = $conn->prepare("
            UPDATE users 
            SET full_name = ?, phone = ?
            WHERE id = ?
        ");
        $stmt->bind_param("ssi", $full_name, $phone, $user_id);

        if ($stmt->execute()) {
            $_SESSION["full_name"] = $full_name;
            $message = "Profile updated successfully.";
        } else {
            $message = "Failed to update profile.";
        }
        $stmt->close();
    }
}


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["change_password"])) {

    $current = $_POST["current_password"];
    $new     = $_POST["new_password"];

    if ($current === "" || $new === "") {
        $message = "All password fields are required.";
    } else {

        $stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($password_hash);
        $stmt->fetch();
        $stmt->close();

        if (!password_verify($current, $password_hash)) {
            $message = "Current password is incorrect.";
        } else {
            $new_hash = password_hash($new, PASSWORD_DEFAULT);

            $update = $conn->prepare("
                UPDATE users SET password_hash = ? WHERE id = ?
            ");
            $update->bind_param("si", $new_hash, $user_id);

            if ($update->execute()) {
                $message = "Password changed successfully.";
            } else {
                $message = "Failed to change password.";
            }
            $update->close();
        }
    }
}


$stmt = $conn->prepare("
    SELECT full_name, email, phone 
    FROM users WHERE id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Profile</title>

<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6f8;
}

.container {
    max-width: 600px;
    margin: 40px auto;
    background: #fff;
    padding: 25px;
    border-radius: 14px;
    box-shadow: 0 10px 25px rgba(0,0,0,.08);
}

h2 {
    margin-bottom: 15px;
}

.form-group {
    margin-bottom: 14px;
}

label {
    font-weight: bold;
    display: block;
    margin-bottom: 6px;
}

input {
    width: 100%;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
}

.btn {
    padding: 12px;
    width: 100%;
    border: none;
    border-radius: 10px;
    font-weight: bold;
    cursor: pointer;
    background: #2563eb;
    color: #fff;
    margin-top: 10px;
}

.btn-red {
    background: #ef4444;
}

.msg {
    margin-bottom: 15px;
    padding: 12px;
    border-radius: 10px;
    background: #eef6ff;
    border: 1px solid #cfe4ff;
    color: #0b3d91;
}

.back {
    margin-top: 15px;
    text-align: center;
}
</style>
</head>

<body>

<div class="container">
    <h2>My Profile</h2>

    <?php if ($message !== ""): ?>
        <div class="msg"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>


    <form method="POST">
        <input type="hidden" name="update_profile">

        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" value="<?= htmlspecialchars($user["full_name"]) ?>" required>
        </div>

        <div class="form-group">
            <label>Email (cannot change)</label>
            <input type="email" value="<?= htmlspecialchars($user["email"]) ?>" disabled>
        </div>

        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($user["phone"]) ?>">
        </div>

        <button class="btn">Update Profile</button>
    </form>

    <hr style="margin:25px 0">

 
    <form method="POST">
        <input type="hidden" name="change_password">

        <h3>Change Password</h3>

        <div class="form-group">
            <label>Current Password</label>
            <input type="password" name="current_password" required>
        </div>

        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password" required>
        </div>

        <button class="btn btn-red">Change Password</button>
    </form>

    <div class="back">
        <a href="home.php">← Back to Home</a>
    </div>
</div>

</body>
</html>
