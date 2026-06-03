<?php
$host = 'localhost';
$dbname = 'cnttazmedia247_tro_v3';
$username = 'cnttazmedia247_tro_v3'; // Thay đổi theo config của bạn
$password = 'cnttazmedia247_tro_v3'; // Thay đổi theo config của bạn

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Bật chế độ báo lỗi exception để dễ debug
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Thiết lập fetch dữ liệu dạng mảng liên hợp
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage());
}
