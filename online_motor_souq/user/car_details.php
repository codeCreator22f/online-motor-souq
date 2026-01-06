<?php
session_start();
require_once "../config/db.php";


if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];


if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid car ID.");
}

$car_id = intval($_GET["id"]);


$stmt = $conn->prepare("
    SELECT c.*, u.full_name AS seller_name
    FROM cars c
    JOIN users u ON c.seller_id = u.id
    WHERE c.id = ?
      AND c.status != 'sold'
    LIMIT 1
");
$stmt->bind_param("i", $car_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    die("Car not found or not available.");
}

$car = $res->fetch_assoc();
$stmt->close();


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["request_car"])) {

    
    if ($car["seller_id"] == $user_id) {
        $error = "You cannot request your own car.";
    } else {

        
        $check = $conn->prepare("
            SELECT id FROM requests
            WHERE buyer_id=? AND car_id=?
        ");
        $check->bind_param("ii", $user_id, $car_id);
        $check->execute();
        $checkRes = $check->get_result();

        if ($checkRes->num_rows > 0) {
            $error = "You have already requested this car.";
        } else {

           
            $ins = $conn->prepare("
                INSERT INTO requests (buyer_id, car_id, status, created_at)
                VALUES (?, ?, 'pending', NOW())
            ");
            $ins->bind_param("ii", $user_id, $car_id);
            $ins->execute();
            $ins->close();

            header("Location: my_requests.php");
            exit;
        }
        $check->close();
    }
}


$images = [];
$img = $conn->prepare("SELECT image_path FROM car_images WHERE car_id=?");
$img->bind_param("i", $car_id);
$img->execute();
$r = $img->get_result();
while ($row = $r->fetch_assoc()) {
    $images[] = $row["image_path"];
}
$img->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($car["title"]) ?></title>

<style>
body { font-family: Arial; background:#f4f6f8; }
.container {
    max-width:900px;
    margin:30px auto;
    background:#fff;
    padding:25px;
    border-radius:14px;
}
.images { display:flex; gap:12px; margin-bottom:20px; }
.images img {
    width:220px;
    height:150px;
    object-fit:cover;
    border-radius:10px;
}
.price { font-size:20px; font-weight:bold; }
.desc {
    background:#f9fafb;
    padding:15px;
    border-radius:10px;
    margin-top:15px;
}
.btn {
    padding:12px 20px;
    background:#16a34a;
    color:white;
    border:none;
    border-radius:10px;
    cursor:pointer;
    margin-top:15px;
}
.error {
    background:#fff1f1;
    padding:10px;
    border-radius:8px;
    color:#a40000;
    margin-bottom:10px;
}
</style>
</head>

<body>

<div class="container">

<h2><?= htmlspecialchars($car["title"]) ?></h2>
<p><b>Seller:</b> <?= htmlspecialchars($car["seller_name"]) ?></p>
<p><b>Year:</b> <?= $car["year"] ?></p>
<p><b>Color:</b> <?= htmlspecialchars($car["color"]) ?></p>
<div class="price"><?= number_format($car["price"],2) ?> OMR</div>

<div class="images">
<?php if ($images): foreach ($images as $img): ?>
    <img src="../uploads/<?= htmlspecialchars($img) ?>">
<?php endforeach; else: ?>
    <img src="../assets/img/car.jpg">
<?php endif; ?>
</div>

<div class="desc">
<b>Description:</b><br>
<?= $car["description"]
    ? nl2br(htmlspecialchars($car["description"]))
    : "No description provided." ?>
</div>

<?php if (!empty($error)): ?>
<div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if ($user_id != $car["seller_id"]): ?>
<form method="POST">
    <button class="btn" name="request_car">Request This Car</button>
</form>
<?php else: ?>
<p><b>You are the seller of this car.</b></p>
<?php endif; ?>

</div>

</body>
</html>
