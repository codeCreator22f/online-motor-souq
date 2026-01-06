<?php
session_start();
require_once "../config/db.php";


if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["reply"])) {

    $id    = intval($_POST["id"]);
    $reply = trim($_POST["admin_reply"]);

    if ($id > 0 && $reply !== "") {

        $stmt = $conn->prepare("
            UPDATE support_messages
            SET 
                admin_reply = ?, 
                status = 'replied', 
                replied_at = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param("si", $reply, $id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: support.php");
    exit;
}


$messages = $conn->query("
    SELECT 
        sm.id,
        sm.subject,
        sm.message,
        sm.admin_reply,
        sm.status,
        sm.created_at,
        sm.replied_at,
        u.full_name AS user_name,
        u.email AS user_email
    FROM support_messages sm
    LEFT JOIN users u ON sm.user_id = u.id
    ORDER BY sm.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Support Messages</title>

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
}

.container {
    padding:30px;
}

.card {
    background:white;
    border-radius:14px;
    padding:20px;
    margin-bottom:20px;
    box-shadow:0 8px 20px rgba(0,0,0,0.08);
}

.card h3 {
    margin:0 0 6px;
}

.meta {
    font-size:13px;
    color:#555;
    margin-bottom:10px;
}

.message-box {
    background:#f9fafb;
    padding:12px;
    border-radius:8px;
    margin-bottom:12px;
}

.reply-box textarea {
    width:100%;
    padding:10px;
    border-radius:8px;
    border:1px solid #ccc;
    margin-bottom:10px;
}

.btn {
    padding:8px 14px;
    background:#2563eb;
    color:white;
    border:none;
    border-radius:8px;
    cursor:pointer;
}

.status {
    font-weight:bold;
    text-transform: uppercase;
    font-size:13px;
}

.pending { color:#f59e0b; }
.replied { color:#16a34a; }

.back {
    color:white;
    text-decoration:none;
}
</style>
</head>

<body>

<div class="header">
    <h2>Support Messages</h2>
    <a href="dashboard.php" class="back">← Dashboard</a>
</div>

<div class="container">

<?php if ($messages->num_rows === 0): ?>
    <p>No support messages found.</p>
<?php endif; ?>

<?php while ($m = $messages->fetch_assoc()): ?>

<?php
$name  = $m["user_name"] ?? "Unknown";
$email = $m["user_email"] ?? "N/A";


$status = $m["status"];
if ($status === NULL || $status === "") {
    $status = "pending";
}
?>

<div class="card">

    <h3><?= htmlspecialchars($m["subject"]) ?></h3>

    <div class="meta">
        <b>From:</b> <?= htmlspecialchars($name) ?> (<?= htmlspecialchars($email) ?>)<br>
        <b>Date:</b> <?= htmlspecialchars($m["created_at"]) ?><br>
        <b>Status:</b>
        <span class="status <?= $status ?>">
            <?= strtoupper($status) ?>
        </span>
    </div>

    <div class="message-box">
        <?= nl2br(htmlspecialchars($m["message"])) ?>
    </div>

    <?php if ($status !== "replied"): ?>
        <form method="POST" class="reply-box">
            <input type="hidden" name="id" value="<?= $m["id"] ?>">
            <textarea name="admin_reply" rows="4" placeholder="Write admin reply..." required></textarea>
            <button class="btn" name="reply">Send Reply</button>
        </form>
    <?php else: ?>
        <div class="message-box">
            <strong>Admin Reply:</strong><br>
            <?= nl2br(htmlspecialchars($m["admin_reply"])) ?>
        </div>
    <?php endif; ?>

</div>

<?php endwhile; ?>

</div>

</body>
</html>
