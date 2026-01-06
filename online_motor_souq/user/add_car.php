<?php
session_start();
require_once "../config/db.php";


if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$message = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"]);
    $year  = intval($_POST["year"]);
    $color = trim($_POST["color"]);
    $price = floatval($_POST["price"]);
    $desc  = trim($_POST["description"]);

    if ($title === "" || $year === 0 || $color === "" || $price === 0) {
        $message = "Please fill all required fields.";
    } else {


        $stmt = $conn->prepare("
            INSERT INTO cars (seller_id, title, year, color, price, description, status)
            VALUES (?, ?, ?, ?, ?, ?, 'available')
        ");
        $stmt->bind_param("isisss", $user_id, $title, $year, $color, $price, $desc);

        if ($stmt->execute()) {

            $car_id = $stmt->insert_id;

            
            if (!empty($_FILES["images"]["name"][0])) {

                $upload_dir = "../uploads/";

                foreach ($_FILES["images"]["tmp_name"] as $index => $tmp_name) {

                    if ($_FILES["images"]["error"][$index] === UPLOAD_ERR_OK) {

                        $original_name = basename($_FILES["images"]["name"][$index]);
                        $ext = pathinfo($original_name, PATHINFO_EXTENSION);

                        $new_name = time() . "_" . uniqid() . "." . $ext;
                        $target = $upload_dir . $new_name;

                        if (move_uploaded_file($tmp_name, $target)) {

                            $imgStmt = $conn->prepare("
                                INSERT INTO car_images (car_id, image_path)
                                VALUES (?, ?)
                            ");
                            $imgStmt->bind_param("is", $car_id, $new_name);
                            $imgStmt->execute();
                            $imgStmt->close();
                        }
                    }
                }
            }

            header("Location: my_cars.php");
            exit;

        } else {
            $message = "Failed to add car.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add New Car</title>

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
    align-items:center;
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

h2 {
    margin-bottom:15px;
}

.form-group {
    margin-bottom:14px;
}

.form-group label {
    display:block;
    margin-bottom:6px;
    font-weight:bold;
}

.form-group input,
.form-group textarea {
    width:100%;
    padding:10px;
    border-radius:8px;
    border:1px solid #ccc;
}

textarea {
    resize: vertical;
    min-height:90px;
}

.btn {
    width:100%;
    padding:12px;
    background:#16a34a;
    color:white;
    border:none;
    border-radius:10px;
    font-weight:bold;
    cursor:pointer;
}

.error {
    margin-bottom:12px;
    padding:10px;
    border-radius:8px;
    background:#fff1f1;
    border:1px solid #ffd0d0;
    color:#a40000;
}
</style>
</head>

<body>

<div class="header">
    <h2>Add New Car</h2>
    <a href="my_cars.php">← Back</a>
</div>

<div class="container">

<?php if ($message !== ""): ?>
    <div class="error"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

    <div class="form-group">
        <label>Car Title *</label>
        <input type="text" name="title" required>
    </div>

    <div class="form-group">
        <label>Year *</label>
        <input type="number" name="year" min="1990" max="2025" required>
    </div>

    <div class="form-group">
        <label>Color *</label>
        <input type="text" name="color" required>
    </div>

    <div class="form-group">
        <label>Price (OMR) *</label>
        <input type="number" step="0.01" name="price" required>
    </div>

    <div class="form-group">
        <label>Description</label>
        <textarea name="description"></textarea>
    </div>

    <div class="form-group">
        <label>Car Images (Multiple allowed)</label>
        <input type="file" name="images[]" accept="image/*" multiple>
    </div>

    <button class="btn" type="submit">Add Car</button>

</form>
</div>

</body>
</html>
