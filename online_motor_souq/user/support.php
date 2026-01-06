<?php
session_start();
require_once "../config/db.php";


if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION["user_id"];


$stmt = $conn->prepare("
    SELECT id, subject, message, admin_reply, status, created_at, replied_at
    FROM support_messages
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$messages = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Support</title>

<style>
body {
    margin:0;
    font-family: Arial, sans-serif;
    background:#f4f6f8;
}

.header {
    background:#0f172a;
    color:white;
    padding:15px 25px;
    display:flex;
    justify-content: space-between;
    align-items:center;
}

.header a {
    color:white;
    text-decoration:none;
    font-weight:bold;
}

.container {
    max-width: 1100px;
    margin: 25px auto;
    padding: 0 15px;
}

.top-actions {
    display:flex;
    justify-content: space-between;
    align-items:center;
    margin-bottom: 15px;
}

.new-btn {
    background:#2563eb;
    color:white;
    padding:10px 14px;
    border-radius:10px;
    text-decoration:none;
    font-weight:bold;
}

.card {
    background:white;
    border-radius:14px;
    padding:18px;
    margin-bottom:18px;
    box-shadow:0 8px 20px rgba(0,0,0,0.08);
}

.subject {
    display:flex;
    justify-content: space-between;
    align-items:center;
    gap: 10px;
}

.subject h3 {
    margin:0;
    font-size:18px;
}

.badge {
    padding:6px 10px;
    border-radius:10px;
    font-size:12px;
    font-weight:bold;
    text-transform: uppercase;
    color:white;
    white-space: nowrap;
}

.badge.pending { background:#f59e0b; }
.badge.replied { background:#16a34a; }

.meta {
    margin-top:6px;
    font-size:13px;
    color:#555;
}

.box {
    background:#f9fafb;
    padding:12px;
    border-radius:10px;
    margin-top:12px;
    line-height:1.6;
}

.admin-box {
    background:#eef6ff;
    border:1px solid #cfe4ff;
    padding:12px;
    border-radius:10px;
    margin-top:12px;
    line-height:1.6;
}

.empty {
    background:white;
    padding:30px;
    border-radius:14px;
    box-shadow:0 8px 20px rgba(0,0,0,0.08);
    text-align:center;
    color:#444;
}
</style>
</head>

<body>

<div class="header">
    <h2>Support</h2>
    <a href="home.php">← Back to Home</a>
</div>

<div class="container">

    <div class="top-actions">
        <h3 style="margin:0;">My Support Messages</h3>
        <a class="new-btn" href="contact.php">+ New Message</a>
    </div>

    <?php if ($messages->num_rows === 0): ?>
        <div class="empty">
            <p>You have no support messages yet.</p>
            <p>Click <b>New Message</b> to contact support.</p>
        </div>
    <?php else: ?>

        <?php while ($m = $messages->fetch_assoc()): ?>
            <div class="card">
                <div class="subject">
                    <h3><?= htmlspecialchars($m["subject"]) ?></h3>
                    <span class="badge <?= htmlspecialchars($m["status"]) ?>">
                        <?= htmlspecialchars($m["status"]) ?>
                    </span>
                </div>

                <div class="meta">
                    <b>Sent:</b> <?= htmlspecialchars($m["created_at"]) ?>
                    <?php if (!empty($m["replied_at"])): ?>
                        | <b>Replied:</b> <?= htmlspecialchars($m["replied_at"]) ?>
                    <?php endif; ?>
                </div>

                <div class="box">
                    <b>Your Message:</b><br>
                    <?= nl2br(htmlspecialchars($m["message"])) ?>
                </div>

                <?php if ($m["status"] === "replied" && !empty($m["admin_reply"])): ?>
                    <div class="admin-box">
                        <b>Admin Reply:</b><br>
                        <?= nl2br(htmlspecialchars($m["admin_reply"])) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>

    <?php endif; ?>

</div>

</body>
</html>
