<?php
session_start();
require_once "../config/db.php";

/* Protect admin dashboard */
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

/* Fetch statistics */
$users_count    = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$cars_count     = $conn->query("SELECT COUNT(*) FROM cars")->fetch_row()[0];
$requests_count = $conn->query("SELECT COUNT(*) FROM requests")->fetch_row()[0];
$support_count  = $conn->query("SELECT COUNT(*) FROM support_messages WHERE status='pending'")->fetch_row()[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>

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
    align-items: center;
}

.logout {
    background: #ef4444;
    color: white;
    padding: 8px 14px;
    border-radius: 8px;
    text-decoration: none;
}

.container {
    padding: 30px;
}

.cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px,1fr));
    gap: 20px;
}

.card {
    background: white;
    padding: 20px;
    border-radius: 14px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    text-align: center;
}

.card h2 {
    margin: 0;
    font-size: 32px;
}

.card p {
    margin-top: 6px;
    color: #555;
}

/* ===== SECTIONS ===== */
.section {
    margin-top: 40px;
}

.section h3 {
    margin-bottom: 15px;
}

.section a {
    display: inline-block;
    margin-right: 12px;
    margin-bottom: 10px;
    padding: 10px 18px;
    background: #2563eb;
    color: white;
    text-decoration: none;
    border-radius: 8px;
}
</style>
</head>

<body>

<div class="header">
    <h2>Admin Dashboard</h2>
    <a href="/online_motor_souq/admin/logout.php" class="logout">Logout</a>
</div>

<div class="container">

    <!-- STAT CARDS -->
    <div class="cards">
        <div class="card">
            <h2><?= $users_count ?></h2>
            <p>Total Users</p>
        </div>
        <div class="card">
            <h2><?= $cars_count ?></h2>
            <p>Total Cars</p>
        </div>
        <div class="card">
            <h2><?= $requests_count ?></h2>
            <p>Total Requests</p>
        </div>

    </div>

    <!-- MANAGEMENT LINKS -->
    <div class="section">
        <h3>Management</h3>
        <a href="users.php">Manage Users</a>
        <a href="cars.php">Manage Cars</a>
        <a href="requests.php">Manage Requests</a>
        <a href="support.php">Support Messages</a>
    </div>

</div>

</body>
</html>
