<?php
$host = "127.0.0.1";
$port = 3307;
$user = "root";
$pass = "";
$db   = "equipment_db";

$conn = mysqli_connect($host, $user, $pass, $db, $port);
if (!$conn) {
    die("เชื่อมต่อไม่ได้: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");

$types = ["แอร์", "คอม", "ทีวี", "โต๊ะ", "ปลั๊กไฟ"];