<?php
session_start();


$_SESSION = [];


session_destroy();


header("Location: /online_motor_souq/index.php");
exit;
