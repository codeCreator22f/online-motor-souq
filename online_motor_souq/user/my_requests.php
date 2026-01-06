<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare("
    SELECT 
        r.status AS request_status,
        r.created_at,
        c.title,
        c.year,
        c.color,
        c.price,
        c.description,
        c.status AS car_status,
        (
            SELECT image_path 
            FROM car_images 
            WHERE car_id = c.id 
            ORDER BY id ASC
            LIMIT 1
        ) AS image
    FROM requests r
    JOIN cars c ON r.car_id = c.id
    WHERE r.buyer_id = ?
    ORDER BY r.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Requests</title>

<style>
body {
    margin:0;
    font-family:Arial, sans-serif;
    background:#f4f6f8;
}

.header {
    background:#0f172a;
    color:white;
    padding:15px 20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.header a {
    color:white;
    text-decoration:none;
    font-weight:bold;
}

.container {
    max-width:1200px;
    margin:auto;
    padding:30px;
}

.requests-grid {
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(280px,1fr));
    gap:20px;
}

.request-card {
    background:white;
    border-radius:12px;
    padding:15px;
    box-shadow:0 8px 20px rgba(0,0,0,0.08);
}

.request-card img {
    width:100%;
    height:160px;
    object-fit:cover;
    border-radius:8px;
}

.request-card h3 {
    margin:10px 0 5px;
}

.request-card p {
    margin:4px 0;
    font-size:14px;
}

.price {
    font-weight:bold;
    margin-top:6px;
}


.badge {
    margin-top:10px;
    padding:6px;
    text-align:center;
    border-radius:8px;
    font-size:13px;
    font-weight:bold;
    color:white;
    text-transform:uppercase;
}

.pending { background:#f59e0b; }
.approved { background:#16a34a; }
.rejected { background:#ef4444; }
.sold { background:#9ca3af; }

.desc {
    background:#f9fafb;
    padding:10px;
    border-radius:8px;
    margin-top:10px;
    font-size:14px;
    line-height:1.5;
}

.empty {
    background:white;
    padding:30px;
    border-radius:12px;
    text-align:center;
    color:#555;
}
</style>
</head>

<body>

<div class="header">
    <h2>My Requests</h2>
    <a href="home.php">← Back to Home</a>
</div>

<div class="container">

<?php if ($result->num_rows === 0): ?>
    <div class="empty">
        <p>You have not requested any cars yet.</p>
    </div>
<?php else: ?>

<div class="requests-grid">
<?php while ($row = $result->fetch_assoc()): ?>

<?php
$img = "../assets/img/car.jpg";
if (!empty($row["image"])) {
    $img = "../uploads/" . $row["image"];
}
?>

<div class="request-card">

    <img src="<?= htmlspecialchars($img) ?>" alt="Car">

    <h3><?= htmlspecialchars($row["title"]) ?></h3>
    <p>Year: <?= htmlspecialchars($row["year"]) ?></p>
    <p>Color: <?= htmlspecialchars($row["color"]) ?></p>
    <p class="price"><?= number_format($row["price"],2) ?> OMR</p>

    <div class="desc">
        <b>Description:</b><br>
        <?= $row["description"]
            ? nl2br(htmlspecialchars($row["description"]))
            : "No description provided." ?>
    </div>

    <div class="badge <?= $row["request_status"] ?>">
        Request: <?= strtoupper($row["request_status"]) ?>
    </div>

    <div class="badge <?= $row["car_status"] ?>">
        Car: <?= strtoupper($row["car_status"]) ?>
    </div>

</div>

<?php endwhile; ?>
</div>

<?php endif; ?>

</div>

</body>
</html>
