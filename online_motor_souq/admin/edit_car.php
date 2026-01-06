<?php
session_start();
require_once "../config/db.php";


if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}


if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid car ID.");
}

$car_id = intval($_GET["id"]);
$message = "";


$stmt = $conn->prepare("
    SELECT * FROM cars
    WHERE id = ?
    LIMIT 1
");
$stmt->bind_param("i", $car_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Car not found.");
}

$car = $result->fetch_assoc();
$stmt->close();


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title  = trim($_POST["title"]);
    $year   = intval($_POST["year"]);
    $color  = trim($_POST["color"]);
    $price  = floatval($_POST["price"]);
    $status = $_POST["status"];

    if ($title === "" || $year <= 0 || $price <= 0) {
        $message = "Please fill all required fields correctly.";
    } else {

        $stmt = $conn->prepare("
            UPDATE cars
            SET title=?, year=?, color=?, price=?, status=?
            WHERE id=?
        ");
        $stmt->bind_param(
            "sisdis",
            $title,
            $year,
            $color,
            $price,
            $status,
            $car_id
        );

        if ($stmt->execute()) {
            header("Location: cars.php");
            exit;
        } else {
            $message = "Failed to update car.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Car</title>

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

input, select {
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
    <h2>Edit Car Details</h2>
    <a href="cars.php" style="color:white; text-decoration:none;">← Back</a>
</div>

<div class="container">

<?php if ($message): ?>
    <div class="msg"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="POST">

    <div class="form-group">
        <label>Title</label>
        <input type="text" name="title"
               value="<?= htmlspecialchars($car["title"]) ?>" required>
    </div>

    <div class="form-group">
        <label>Year</label>
        <input type="number" name="year"
               value="<?= $car["year"] ?>" required>
    </div>

    <div class="form-group">
        <label>Color</label>
        <input type="text" name="color"
               value="<?= htmlspecialchars($car["color"]) ?>">
    </div>

    <div class="form-group">
        <label>Price (OMR)</label>
        <input type="number" step="0.01" name="price"
               value="<?= $car["price"] ?>" required>
    </div>

    <div class="form-group">
        <label>Status</label>
        <select name="status">
            <option value="available" <?= $car["status"]=="available"?"selected":"" ?>>Available</option>
            <option value="pending"   <?= $car["status"]=="pending"?"selected":"" ?>>Pending</option>
            <option value="sold"      <?= $car["status"]=="sold"?"selected":"" ?>>Sold</option>
        </select>
    </div>

    <button class="btn">Update Car</button>

</form>
</div>

</body>
</html>
