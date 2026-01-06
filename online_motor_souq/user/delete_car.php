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
    SELECT id, title 
    FROM cars 
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

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["confirm_delete"])) {

  
    $delReq = $conn->prepare("DELETE FROM requests WHERE car_id = ?");
    $delReq->bind_param("i", $car_id);
    $delReq->execute();
    $delReq->close();

    
    $imgStmt = $conn->prepare("
        SELECT image_path 
        FROM car_images 
        WHERE car_id = ?
    ");
    $imgStmt->bind_param("i", $car_id);
    $imgStmt->execute();
    $imgs = $imgStmt->get_result();

    while ($row = $imgs->fetch_assoc()) {
        $file = "../uploads/" . $row["image_path"];
        if (file_exists($file)) {
            unlink($file);
        }
    }
    $imgStmt->close();

   
    $delImgs = $conn->prepare("DELETE FROM car_images WHERE car_id = ?");
    $delImgs->bind_param("i", $car_id);
    $delImgs->execute();
    $delImgs->close();

   
    $delCar = $conn->prepare("
        DELETE FROM cars 
        WHERE id = ? AND seller_id = ?
    ");
    $delCar->bind_param("ii", $car_id, $user_id);
    $delCar->execute();
    $delCar->close();

 
    header("Location: my_cars.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Delete Car</title>

<style>
body {
    margin:0;
    font-family: Arial, sans-serif;
    background:#f4f6f8;
}

.container {
    max-width:500px;
    margin:80px auto;
    background:#fff;
    padding:30px;
    border-radius:14px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
    text-align:center;
}

h2 {
    margin-bottom:10px;
}

.warning {
    color:#a40000;
    margin-bottom:20px;
    line-height:1.6;
}

.btn-group {
    display:flex;
    gap:15px;
    justify-content:center;
}

.btn {
    padding:12px 20px;
    border:none;
    border-radius:10px;
    font-weight:bold;
    cursor:pointer;
    text-decoration:none;
    color:white;
}

.delete {
    background:#ef4444;
}

.cancel {
    background:#2563eb;
}
</style>
</head>

<body>

<div class="container">
    <h2>Delete Car</h2>

    <p class="warning">
        Are you sure you want to permanently delete<br>
        <b><?= htmlspecialchars($car["title"]) ?></b>?<br><br>
        This action <b>cannot</b> be undone.
    </p>

    <form method="POST">
        <div class="btn-group">
            <button class="btn delete" type="submit" name="confirm_delete">
                Yes, Delete
            </button>
            <a class="btn cancel" href="my_cars.php">
                Cancel
            </a>
        </div>
    </form>
</div>

</body>
</html>
