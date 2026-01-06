<?php
$conn = new mysqli("localhost", "root", "", "online_motor_souq");

if ($conn->connect_error) {
    die("Database connection failed");
}
?>
