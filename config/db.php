<?php
// config/db.php
require_once __DIR__ . '/../classes/Database.php';

$dsn  = "mysql:host=localhost;dbname=training_db;charset=utf8mb4";
$user = "root";
$pass = ""; // Default XAMPP password is empty

try {
    $db = Database::getInstance($dsn, $user, $pass);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}