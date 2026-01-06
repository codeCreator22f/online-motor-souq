<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}


if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid user ID.");
}

$user_id = intval($_GET["id"]);
$message = "";


$stmt = $conn->prepare("
    SELECT id, full_name, email, phone
    FROM users
    WHERE id = ?
    LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User not found.");
}

$user = $result->fetch_assoc();
$stmt->close();


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name  = trim($_POST["full_name"]);
    $phone = trim($_POST["phone"]);
    $pass  = trim($_POST["password"]);

    if ($name === "") {
        $message = "Full name is required.";
    } else {

        /* Update without password */
        if ($pass === "") {
            $stmt = $conn->prepare("
                UPDATE users
                SET full_name = ?, phone = ?
                WHERE id = ?
            ");
            $stmt->bind_param("ssi", $name, $phone, $user_id);
        } 
        /* Update with password */
        else {
            $hashed = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("
                UPDATE users
                SET full_name = ?, phone = ?, password_hash = ?
                WHERE id = ?
            ");
            $stmt->bind_param("sssi", $name, $phone, $hashed, $user_id);
        }

        if ($stmt->execute()) {
            header("Location: users.php");
            exit;
        } else {
            $message = "Failed to update user.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit User</title>

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
    max-width:600px;
    margin:40px auto;
    background:#fff;
    padding:30px;
    border-radius:14px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
}

.form-group {
    margin-bottom:14px;
}

label {
    font-weight:bold;
    display:block;
    margin-bottom:6px;
}

input {
    width:100%;
    padding:10px;
    border-radius:8px;
    border:1px solid #ccc;
}

.btn {
    width:100%;
    padding:12px;
    background:#2563eb;
    color:white;
    border:none;
    border-radius:10px;
    font-weight:bold;
    cursor:pointer;
}

.msg {
    background:#fff1f1;
    border:1px solid #ffd0d0;
    padding:10px;
    border-radius:8px;
    margin-bottom:12px;
}
</style>
</head>

<body>

<div class="header">
    <h2>Edit User</h2>
    <a href="users.php" style="color:white;">← Back</a>
</div>

<div class="container">

<?php if ($message): ?>
    <div class="msg"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="POST">

    <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="full_name"
               value="<?= htmlspecialchars($user["full_name"]) ?>" required>
    </div>

    <div class="form-group">
        <label>Email (cannot change)</label>
        <input type="email"
               value="<?= htmlspecialchars($user["email"]) ?>" disabled>
    </div>

    <div class="form-group">
        <label>Phone</label>
        <input type="text" name="phone"
               value="<?= htmlspecialchars($user["phone"]) ?>">
    </div>

    <div class="form-group">
        <label>New Password (leave empty to keep current)</label>
        <input type="password" name="password">
    </div>

    <button class="btn">Update User</button>

</form>
</div>

</body>
</html>
