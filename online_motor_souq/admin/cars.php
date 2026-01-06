<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}


if (isset($_GET["delete"]) && is_numeric($_GET["delete"])) {
    $car_id = intval($_GET["delete"]);

    /* Delete image files */
    $img = $conn->prepare("SELECT image_path FROM car_images WHERE car_id=?");
    $img->bind_param("i", $car_id);
    $img->execute();
    $res = $img->get_result();
    while ($r = $res->fetch_assoc()) {
        $file = "../uploads/" . $r["image_path"];
        if (file_exists($file)) unlink($file);
    }
    $img->close();

    /* Delete DB records */
    $stmt = $conn->prepare("DELETE FROM car_images WHERE car_id=?");
    $stmt->bind_param("i", $car_id);
    $stmt->execute();

    $stmt = $conn->prepare("DELETE FROM requests WHERE car_id=?");
    $stmt->bind_param("i", $car_id);
    $stmt->execute();

    $stmt = $conn->prepare("DELETE FROM cars WHERE id=?");
    $stmt->bind_param("i", $car_id);
    $stmt->execute();

    header("Location: cars.php");
    exit;
}


if (isset($_POST["update_car"])) {

    $id     = intval($_POST["id"]);
    $title  = trim($_POST["title"]);
    $year   = intval($_POST["year"]);
    $color  = trim($_POST["color"]);
    $price  = floatval($_POST["price"]);
    $status = $_POST["status"];

    $stmt = $conn->prepare("
        UPDATE cars
        SET title=?, year=?, color=?, price=?, status=?
        WHERE id=?
    ");
    $stmt->bind_param("sisdis", $title, $year, $color, $price, $status, $id);
    $stmt->execute();

    header("Location: cars.php");
    exit;
}


$cars = $conn->query("
    SELECT c.*, u.full_name AS seller,
           (SELECT image_path FROM car_images WHERE car_id=c.id LIMIT 1) AS image
    FROM cars c
    JOIN users u ON c.seller_id = u.id
    ORDER BY c.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Cars</title>

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

table {
    width:100%;
    background:white;
    border-collapse: collapse;
    border-radius:12px;
    overflow:hidden;
}

th, td {
    padding:12px;
    border-bottom:1px solid #eee;
}

th {
    background:#e5e7eb;
}

img {
    width:90px;
    height:60px;
    object-fit:cover;
    border-radius:6px;
}

input, select {
    width:100%;
    padding:6px;
}

.btn {
    padding:6px 10px;
    border-radius:6px;
    color:white;
    border:none;
    cursor:pointer;
    text-decoration:none;
    display:inline-block;
}

.save   { background:#16a34a; }
.edit   { background:#2563eb; }
.delete { background:#ef4444; }

.back {
    color:white;
    text-decoration:none;
}
</style>
</head>

<body>

<div class="header">
    <h2>Manage Cars</h2>
    <a href="dashboard.php" class="back">← Dashboard</a>
</div>

<div class="container">

<table>
<tr>
    <th>ID</th>
    <th>Image</th>
    <th>Seller</th>
    <th>Title</th>
    <th>Year</th>
    <th>Color</th>
    <th>Price (OMR)</th>
    <th>Status</th>
    <th>Actions</th>
</tr>

<?php while ($c = $cars->fetch_assoc()): ?>
<tr>
<form method="POST">

    <td><?= $c["id"] ?></td>

    <td>
        <img src="<?= $c["image"] ? '../uploads/'.$c["image"] : '../assets/img/car.jpg' ?>">
    </td>

    <td><?= htmlspecialchars($c["seller"]) ?></td>

    <td><input type="text" name="title" value="<?= htmlspecialchars($c["title"]) ?>"></td>
    <td><input type="number" name="year" value="<?= $c["year"] ?>"></td>
    <td><input type="text" name="color" value="<?= htmlspecialchars($c["color"]) ?>"></td>
    <td><input type="number" step="0.01" name="price" value="<?= $c["price"] ?>"></td>

    <td>
        <select name="status">
            <option value="available" <?= $c["status"]=="available"?"selected":"" ?>>Available</option>
            <option value="pending"   <?= $c["status"]=="pending"?"selected":"" ?>>Pending</option>
            <option value="sold"      <?= $c["status"]=="sold"?"selected":"" ?>>Sold</option>
        </select>
    </td>

    <td>
        <input type="hidden" name="id" value="<?= $c["id"] ?>">
        <button class="btn save" name="update_car">Save</button>
        <a class="btn edit" href="edit_car.php?id=<?= $c['id'] ?>">Edit</a>
        <a class="btn delete"
           href="cars.php?delete=<?= $c['id'] ?>"
           onclick="return confirm('Delete this car permanently?')">
           Delete
        </a>
    </td>

</form>
</tr>
<?php endwhile; ?>

</table>

</div>

</body>
</html>
