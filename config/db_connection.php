<?php
//define('BASE_URL', 'http://localhost/FoodWise/public');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "foodwise";

// Create connection
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>