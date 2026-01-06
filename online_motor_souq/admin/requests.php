<?php
session_start();
require_once "../config/db.php";


if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}


if (isset($_GET["action"], $_GET["id"]) && is_numeric($_GET["id"])) {

    $request_id = (int) $_GET["id"];
    $action = $_GET["action"];

    /* Get request + car info */
    $stmt = $conn->prepare("
        SELECT r.car_id, c.status AS car_status
        FROM requests r
        JOIN cars c ON r.car_id = c.id
        WHERE r.id = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        header("Location: requests.php");
        exit;
    }

    $row = $res->fetch_assoc();
    $car_id = $row["car_id"];
    $stmt->close();


    if ($action === "approve") {

        $stmt = $conn->prepare("UPDATE requests SET status='approved' WHERE id=?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("
            UPDATE requests 
            SET status='rejected'
            WHERE car_id=? AND id!=? AND status='pending'
        ");
        $stmt->bind_param("ii", $car_id, $request_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("UPDATE cars SET status='sold' WHERE id=?");
        $stmt->bind_param("i", $car_id);
        $stmt->execute();
        $stmt->close();
    }


    if ($action === "reject") {
        $stmt = $conn->prepare("UPDATE requests SET status='rejected' WHERE id=?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->close();
    }


    if ($action === "reopen") {

        $stmt = $conn->prepare("UPDATE requests SET status='pending' WHERE id=?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("UPDATE cars SET status='active' WHERE id=?");
        $stmt->bind_param("i", $car_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: requests.php");
    exit;
}


$requests = $conn->query("
    SELECT 
        r.id,
        r.status,
        u.full_name AS buyer,
        c.title AS car,
        c.price,
        c.status AS car_status
    FROM requests r
    JOIN users u ON r.buyer_id = u.id
    JOIN cars c ON r.car_id = c.id
    ORDER BY r.id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Requests</title>

<style>
body {
    margin:0;
    font-family: Arial, sans-serif;
    background:#f4f6f8;
}
.header {
    background:#0f172a;
    color:white;
    padding:18px 30px;
    display:flex;
    justify-content: space-between;
}
.container { padding:30px; }

table {
    width:100%;
    background:white;
    border-collapse: collapse;
    border-radius:12px;
    overflow:hidden;
}

th {
    background:#e5e7eb;
    padding:14px;
    text-align:left;
}

td {
    padding:14px;
    border-bottom:1px solid #eee;
}

.badge {
    padding:6px 12px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
}

.pending  { background:#fff7ed; color:#f59e0b; }
.approved { background:#ecfdf5; color:#16a34a; }
.rejected { background:#fef2f2; color:#ef4444; }

.btn {
    padding:6px 12px;
    border-radius:6px;
    text-decoration:none;
    font-size:12px;
    color:white;
    margin-right:4px;
}

.approve { background:#16a34a; }
.reject  { background:#ef4444; }
.reopen  { background:#2563eb; }

.disabled {
    color:#999;
    font-size:12px;
}

.back {
    color:white;
    text-decoration:none;
}
</style>
</head>

<body>

<div class="header">
    <h2>Manage Requests</h2>
    <a href="dashboard.php" class="back">← Dashboard</a>
</div>

<div class="container">

<table>
<tr>
    <th>ID</th>
    <th>Buyer</th>
    <th>Car</th>
    <th>Price</th>
    <th>Status</th>
    <th>Actions</th>
</tr>

<?php while ($r = $requests->fetch_assoc()): ?>
<tr>
    <td><?= $r["id"] ?></td>
    <td><?= htmlspecialchars($r["buyer"]) ?></td>
    <td><?= htmlspecialchars($r["car"]) ?></td>
    <td><?= number_format($r["price"],2) ?> OMR</td>
    <td>
        <span class="badge <?= $r["status"] ?>">
            <?= strtoupper($r["status"]) ?>
        </span>
    </td>
    <td>
        <?php if ($r["status"] === "pending"): ?>
            <a class="btn approve" href="requests.php?action=approve&id=<?= $r['id'] ?>">Approve</a>
            <a class="btn reject"  href="requests.php?action=reject&id=<?= $r['id'] ?>">Reject</a>

        <?php elseif ($r["status"] === "approved"): ?>
            <a class="btn reopen" href="requests.php?action=reopen&id=<?= $r['id'] ?>">Reopen</a>

        <?php else: ?>
            <span class="disabled">No action</span>
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>

</table>

</div>

</body>
</html>
