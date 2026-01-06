<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$cars = $conn->query("
    SELECT 
        c.id,
        c.title,
        c.year,
        c.color,
        c.price,
        c.status,
        (
            SELECT image_path 
            FROM car_images 
            WHERE car_id = c.id 
            ORDER BY id ASC 
            LIMIT 1
        ) AS image
    FROM cars c
    WHERE c.status IS NULL
       OR LOWER(TRIM(c.status)) NOT IN ('sold','hidden')
    ORDER BY c.id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Home</title>

<style>

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6f8;
}


.navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 20px;
    background: #0f172a;
    color: white;
}

.nav-left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.logo {
    width: 40px;
    height: 40px;
}

.app-name {
    font-size: 18px;
    font-weight: bold;
}

.nav-links a {
    color: #e5e7eb;
    text-decoration: none;
    margin: 0 10px;
    font-weight: 500;
}

.nav-links a:hover {
    color: white;
}

.logout-btn {
    background: #ef4444;
    color: white;
    padding: 8px 14px;
    border-radius: 8px;
    text-decoration: none;
}


.hero {
    height: 280px;
    background: url("../assets/img/hero-car.jpg") center/cover no-repeat;
}

.hero-overlay {
    background: rgba(0,0,0,0.55);
    height: 100%;
    color: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.hero h1 {
    font-size: 32px;
}


.cars-section {
    padding: 30px;
}

.cars-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 20px;
}

.car-card {
    background: white;
    padding: 15px;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    text-align: center;
}

.car-card img {
    width: 100%;
    height: 160px;
    object-fit: cover;
    border-radius: 8px;
}

.price {
    font-weight: bold;
    margin: 8px 0;
}

.details-btn {
    display: inline-block;
    padding: 8px 14px;
    background: #16a34a;
    color: white;
    border-radius: 8px;
    text-decoration: none;
}


.about-section {
    padding: 50px 30px;
    background: white;
    text-align: center;
}

.about-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
    margin-top: 30px;
}

.about-card {
    background: #f9fafb;
    padding: 25px 20px;
    border-radius: 14px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
}


.about-image {
    width: 100%;
    height: 180px;           
    display: flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    border-radius: 10px;
    margin-bottom: 15px;
}


.about-image img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;     
}

.about-card h3 {
    margin-bottom: 10px;
    color: #0f172a;
}

.about-card p {
    color: #444;
    line-height: 1.6;
    font-size: 15px;
}



.contact-section {
    padding: 40px;
    background: #0f172a;
    color: white;
    text-align: center;
}

.contact-btn {
    display: inline-block;
    margin-top: 12px;
    padding: 10px 18px;
    background: #2563eb;
    color: white;
    border-radius: 8px;
    text-decoration: none;
}
</style>
</head>

<body>

<header class="navbar">
    <div class="nav-left">
        <img src="logo.png" class="logo">
        <span class="app-name">Online Motor Souq</span>
    </div>

    <nav class="nav-links">
        <a href="home.php">Home</a>
        <a href="#about">About Us</a>
        <a href="my_cars.php">My Cars</a>
        <a href="my_requests.php">My Requests</a>
        <a href="#contact">Contact Us</a>
        <a href="support.php">Support</a>
        <a href="profile.php">Profile</a>
    </nav>

    <a class="logout-btn" href="/online_motor_souq/user/logout.php">Logout</a>
</header>

<section class="hero">
    <div class="hero-overlay">
        <h1>Welcome, <?= htmlspecialchars($_SESSION["full_name"]) ?></h1>
        <p>Buy and sell cars in Oman easily and securely</p>
    </div>
</section>

<section class="cars-section">
    <h2>Available Cars</h2>

    <div class="cars-grid">
        <?php if ($cars->num_rows === 0): ?>
            <p>No available cars at the moment.</p>
        <?php else: ?>
            <?php while ($car = $cars->fetch_assoc()): ?>
                <?php
                $img = "../assets/img/car.jpg";
                if (!empty($car["image"])) {
                    $img = "../uploads/" . $car["image"];
                }
                ?>
                <div class="car-card">
                    <img src="<?= htmlspecialchars($img) ?>">
                    <h3><?= htmlspecialchars($car["title"]) ?></h3>
                    <p>Year: <?= htmlspecialchars($car["year"]) ?></p>
                    <p>Color: <?= htmlspecialchars($car["color"]) ?></p>
                    <p class="price"><?= htmlspecialchars($car["price"]) ?> OMR</p>
                    <a class="details-btn" href="car_details.php?id=<?= $car["id"] ?>">View Details</a>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</section>

<section id="about" class="about-section">
    <h2>About Online Motor Souq</h2>

    <div class="about-grid">

        <div class="about-card">
            <div class="about-image">
                <img src="about1.png" alt="Trusted Marketplace">
            </div>
            <h3>Trusted Marketplace</h3>
            <p>
                A secure digital platform connecting car buyers and sellers
                across Oman in a transparent and reliable environment.
            </p>
        </div>

        <div class="about-card">
            <div class="about-image">
                <img src="about2.png" alt="Easy Car Listings">
            </div>
            <h3>Easy Car Listings</h3>
            <p>
                Sellers can list their vehicles quickly with full details,
                images, and pricing for maximum visibility.
            </p>
        </div>

        <div class="about-card">
            <div class="about-image">
                <img src="about3.png" alt="Safe Transactions">
            </div>
            <h3>Safe Transactions</h3>
            <p>
                All purchase requests are reviewed and managed to ensure a
                safe, trusted, and user-friendly experience.
            </p>
        </div>

    </div>
</section>


<section id="contact" class="contact-section">
    <h2>Contact Us</h2>
    <p>Need help? Our support team is here to assist you.</p>
    <a class="contact-btn" href="contact.php">Contact Support</a>
</section>

</body>
</html>
