<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: /online_motor_souq/user/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];


$query = "
SELECT c.*,
       (SELECT image_path FROM car_images WHERE car_id = c.id LIMIT 1) AS image
FROM cars c
WHERE c.seller_id = ?
ORDER BY c.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Cars</title>

<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6f8;
}

.header {
    background:#0f172a;
    color:white;
    padding:15px 20px;
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
    max-width: 1200px;
    margin: auto;
    padding: 30px;
}

.add-btn {
    display:inline-block;
    margin-bottom:20px;
    padding:10px 16px;
    background:#16a34a;
    color:white;
    border-radius:8px;
    text-decoration:none;
    font-weight:bold;
}

.cars-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 20px;
}

.car-card {
    background: #fff;
    border-radius: 12px;
    padding: 15px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
}

.car-card img {
    width: 100%;
    height: 160px;
    object-fit: cover;
    border-radius: 8px;
}

.car-card h3 {
    margin: 10px 0 5px;
}

.car-card p {
    margin: 4px 0;
    font-size: 14px;
}

.price {
    font-weight: bold;
    margin-top: 6px;
}

.status {
    margin-top: 6px;
    font-size: 13px;
    font-weight: bold;
}

.status.available { color: #16a34a; }
.status.pending   { color: #f59e0b; }
.status.sold      { color: #ef4444; }

.actions {
    margin-top: 10px;
    display: flex;
    gap: 10px;
}

.actions a {
    flex: 1;
    text-align: center;
    padding: 8px;
    border-radius: 8px;
    text-decoration: none;
    color: white;
    font-size: 14px;
}

.edit-btn {
    background: #2563eb;
}

.delete-btn {
    background: #ef4444;
}

.empty {
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    text-align: center;
    color: #555;
}
</style>
</head>

<body>

<div class="header">
    <h2>My Cars</h2>
    <a href="home.php">← Back to Home</a>
</div>

<div class="container">

<a class="add-btn" href="add_car.php">+ Add New Car</a>

<?php if ($result->num_rows == 0): ?>
    <div class="empty">
        <p>You have not added any cars yet.</p>
    </div>
<?php else: ?>
    <div class="cars-grid">
        <?php while ($car = $result->fetch_assoc()): ?>
            <div class="car-card">

                <img src="<?= $car['image'] ? '../uploads/'.$car['image'] : '../assets/img/car.jpg' ?>" alt="Car">

                <h3><?= htmlspecialchars($car["title"]) ?></h3>
                <p>Year: <?= htmlspecialchars($car["year"]) ?></p>
                <p>Color: <?= htmlspecialchars($car["color"]) ?></p>
                <p class="price"><?= htmlspecialchars($car["price"]) ?> OMR</p>

                <div class="status <?= $car["status"] ?>">
                    Status: <?= strtoupper($car["status"]) ?>
                </div>

                <div class="actions">
                    <a href="edit_car.php?id=<?= $car['id'] ?>" class="edit-btn">Edit</a>

                    <a href="delete_car.php?id=<?= $car['id'] ?>" 
                       class="delete-btn"
                       onclick="return confirm('Are you sure you want to delete this car?')">
                       Delete
                    </a>
                </div>

            </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

</div>
</body>
</html>
