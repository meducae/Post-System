<?php
$host = "localhost";
$user = "root";     
$pass = "Posts1";        
$db   = "pos_system";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database ulanishda xatolik: " . $conn->connect_error);
}

mysqli_set_charset($conn, "utf8mb4");
?>
