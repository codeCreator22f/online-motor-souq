<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$message = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $subject = trim($_POST["subject"]);
    $msg     = trim($_POST["message"]);

    if ($subject === "" || $msg === "") {
        $message = "Please fill subject and message.";
    } else {

        $stmt = $conn->prepare("
            INSERT INTO support_messages (user_id, subject, message, status)
            VALUES (?, ?, ?, 'unread')
        ");
        $stmt->bind_param("iss", $user_id, $subject, $msg);

        if ($stmt->execute()) {
            $success = true;
            $message = "Your message has been sent to admin support successfully.";
        } else {
            $message = "Failed to send message. Please try again.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Contact Support</title>

<style>
body{
    margin:0;
    font-family: Arial, sans-serif;
    background:#f4f6f8;
}

.header{
    background:#0f172a;
    color:#fff;
    padding: 15px 20px;
    display:flex;
    justify-content: space-between;
    align-items:center;
}

.header a{
    color:#fff;
    text-decoration:none;
    font-weight:bold;
}

.container{
    max-width: 650px;
    margin: 25px auto;
    padding: 0 15px;
}

.card{
    background:#fff;
    padding: 20px;
    border-radius: 14px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}

.card h2{
    margin-top:0;
}

.form-group{
    margin-bottom: 14px;
}

label{
    display:block;
    font-weight:bold;
    margin-bottom: 6px;
}

input, textarea{
    width:100%;
    padding: 10px;
    border-radius: 10px;
    border:1px solid #ccc;
    outline:none;
}

textarea{
    resize: vertical;
    min-height: 140px;
}

.btn{
    width:100%;
    padding: 12px;
    border:none;
    border-radius: 10px;
    background:#2563eb;
    color:#fff;
    font-weight:bold;
    cursor:pointer;
}

.msg {
    margin-bottom: 15px;
    padding: 12px;
    border-radius: 10px;
    background:#eef6ff;
    border: 1px solid #cfe4ff;
    color:#0b3d91;
}

.error {
    margin-bottom: 15px;
    padding: 12px;
    border-radius: 10px;
    background:#fff1f1;
    border: 1px solid #ffd0d0;
    color:#a40000;
}
</style>
</head>

<body>

<div class="header">
    <div><b>Contact Support</b></div>
    <a href="home.php">← Back to Home</a>
</div>

<div class="container">

    <?php if ($message !== ""): ?>
        <div class="<?= $success ? "msg" : "error" ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2>Send a Message to Admin</h2>
        <p style="color:#444; line-height:1.6;">
            If you have an issue with a listing, account, or request, please write to our support team.
            Your message will appear in the admin support page.
        </p>

        <form method="POST">
            <div class="form-group">
                <label>Subject *</label>
                <input type="text" name="subject" placeholder="Example: Problem with my request" required>
            </div>

            <div class="form-group">
                <label>Message *</label>
                <textarea name="message" placeholder="Write your message here..." required></textarea>
            </div>

            <button class="btn" type="submit">Send Message</button>
        </form>
    </div>

</div>

</body>
</html>
