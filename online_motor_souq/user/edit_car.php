<?php
session_start();
require_once "../config/db.php";


if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$message = "";


if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid car ID.");
}

$car_id = (int) $_GET["id"];


$stmt = $conn->prepare("
    SELECT * FROM cars
    WHERE id = ? AND seller_id = ?
    LIMIT 1
");
$stmt->bind_param("ii", $car_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Access denied or car not found.");
}

$car = $result->fetch_assoc();
$stmt->close();


$image = "";
$imgStmt = $conn->prepare("
    SELECT image_path FROM car_images
    WHERE car_id = ?
    LIMIT 1
");
$imgStmt->bind_param("i", $car_id);
$imgStmt->execute();
$imgRes = $imgStmt->get_result();

if ($imgRes->num_rows > 0) {
    $image = $imgRes->fetch_assoc()["image_path"];
}
$imgStmt->close();


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"]);
    $year  = (int) $_POST["year"];
    $color = trim($_POST["color"]);
    $price = (float) $_POST["price"];
    $desc  = trim($_POST["description"]);

    if ($title === "" || $year <= 0 || $color === "" || $price <= 0) {
        $message = "All required fields must be filled.";
    } else {

      
        $update = $conn->prepare("
            UPDATE cars
            SET title = ?, year = ?, color = ?, price = ?, description = ?
            WHERE id = ? AND seller_id = ?
        ");
        $update->bind_param(
            "sisssii",
            $title,
            $year,
            $color,
            $price,
            $desc,
            $car_id,
            $user_id
        );

        if ($update->execute()) {

            
            if (!empty($_FILES["image"]["name"])) {

                $new_img = time() . "_" . basename($_FILES["image"]["name"]);
                $target = "../uploads/" . $new_img;

                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target)) {

                   
                    $delImg = $conn->prepare("DELETE FROM car_images WHERE car_id = ?");
                    $delImg->bind_param("i", $car_id);
                    $delImg->execute();
                    $delImg->close();

                    
                    $addImg = $conn->prepare("
                        INSERT INTO car_images (car_id, image_path)
                        VALUES (?, ?)
                    ");
                    $addImg->bind_param("is", $car_id, $new_img);
                    $addImg->execute();
                    $addImg->close();
                }
            }

            header("Location: my_cars.php");
            exit;

        } else {
            $message = "Failed to update car.";
        }

        $update->close();
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
    padding:15px 20px;
    display:flex;
    justify-content: space-between;
}
.header a {
    color:white;
    text-decoration:none;
    font-weight:bold;
}
.container {
    max-width:600px;
    margin:30px auto;
    background:#fff;
    padding:25px;
    border-radius:14px;
    box-shadow:0 10px 25px rgba(0,0,0,0.08);
}
.form-group {
    margin-bottom:14px;
}
.form-group label {
    font-weight:bold;
    display:block;
    margin-bottom:6px;
}
.form-group input,
.form-group textarea {
    width:100%;
    padding:10px;
    border-radius:8px;
    border:1px solid #ccc;
}
textarea {
    min-height:90px;
}
.btn {
    width:100%;
    padding:12px;
    background:#2563eb;
    color:white;
    border:none;
    border-radius:10px;
    font-weight:bold;
}
.error {
    background:#fff1f1;
    border:1px solid #ffd0d0;
    padding:10px;
    border-radius:8px;
    margin-bottom:12px;
}
.preview img {
    width:100%;
    height:180px;
    object-fit:cover;
    border-radius:10px;
    margin-bottom:12px;
}
</style>
</head>

<body>

<div class="header">
    <h2>Edit Car</h2>
    <a href="my_cars.php">← Back</a>
</div>

<div class="container">

<?php if ($message): ?>
    <div class="error"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="preview">
    <img src="<?= $image ? "../uploads/$image" : "../assets/img/car.jpg" ?>">
</div>

<form method="POST" enctype="multipart/form-data">

    <div class="form-group">
        <label>Car Title *</label>
        <input type="text" name="title" value="<?= htmlspecialchars($car["title"]) ?>" required>
    </div>

    <div class="form-group">
        <label>Year *</label>
        <input type="number" name="year" value="<?= $car["year"] ?>" required>
    </div>

    <div class="form-group">
        <label>Color *</label>
        <input type="text" name="color" value="<?= htmlspecialchars($car["color"]) ?>" required>
    </div>

    <div class="form-group">
        <label>Price (OMR) *</label>
        <input type="number" step="0.01" name="price" value="<?= $car["price"] ?>" required>
    </div>

    <div class="form-group">
        <label>Description</label>
        <textarea name="description"><?= htmlspecialchars($car["description"]) ?></textarea>
    </div>

    <div class="form-group">
        <label>Replace Image</label>
        <input type="file" name="image" accept="image/*">
    </div>

    <button class="btn">Update Car</button>

</form>

</div>

</body>
</html>
