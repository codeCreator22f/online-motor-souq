<?php
session_start();
require_once "../config/db.php";


if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}


if (isset($_GET["delete"])) {
    $id = intval($_GET["delete"]);

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: users.php");
    exit;
}

if (isset($_POST["add_user"])) {

    $name  = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $pass  = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        INSERT INTO users (full_name, email, phone, password_hash)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("ssss", $name, $email, $phone, $pass);
    $stmt->execute();
    $stmt->close();

    header("Location: users.php");
    exit;
}


$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Users</title>

<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6f8;
}
.header {
    background: #0f172a;
    color: white;
    padding: 15px 25px;
    display: flex;
    justify-content: space-between;
}
.container {
    padding: 30px;
}
table {
    width: 100%;
    background: white;
    border-collapse: collapse;
    border-radius: 12px;
    overflow: hidden;
}
th, td {
    padding: 12px;
    border-bottom: 1px solid #eee;
}
th {
    background: #e5e7eb;
}
.action-btn {
    padding: 6px 10px;
    border-radius: 6px;
    text-decoration: none;
    color: white;
    font-size: 13px;
}
.edit { background: #2563eb; }
.delete { background: #ef4444; }
form {
    background: white;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 30px;
}
.form-group {
    margin-bottom: 12px;
}
input {
    width: 100%;
    padding: 8px;
}
.btn {
    padding: 10px 16px;
    background: #16a34a;
    color: white;
    border: none;
    border-radius: 8px;
}
</style>
</head>

<body>

<div class="header">
    <h2>Manage Users</h2>
    <a href="dashboard.php" style="color:white;">← Dashboard</a>
</div>

<div class="container">


<form method="POST">
    <h3>Add New User</h3>

    <div class="form-group">
        <input type="text" name="full_name" placeholder="Full Name" required>
    </div>

    <div class="form-group">
        <input type="email" name="email" placeholder="Email" required>
    </div>

    <div class="form-group">
        <input type="text" name="phone" placeholder="Phone">
    </div>

    <div class="form-group">
        <input type="password" name="password" placeholder="Password" required>
    </div>

    <button class="btn" name="add_user">Add User</button>
</form>


<table>
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Email</th>
    <th>Phone</th>
    <th>Actions</th>
</tr>

<?php while ($u = $users->fetch_assoc()): ?>
<tr>
    <td><?= $u["id"] ?></td>
    <td><?= htmlspecialchars($u["full_name"]) ?></td>
    <td><?= htmlspecialchars($u["email"]) ?></td>
    <td><?= htmlspecialchars($u["phone"]) ?></td>
    <td>
        <a class="action-btn edit"
           href="edit_user.php?id=<?= $u['id'] ?>">Edit</a>

        <a class="action-btn delete"
           href="users.php?delete=<?= $u['id'] ?>"
           onclick="return confirm('Delete this user?')">Delete</a>
    </td>
</tr>
<?php endwhile; ?>

</table>

</div>
</body>
</html>
