<?php 
$username = "cnttazmedia247_tro_v3";
$password = "cnttazmedia247_tro_v3";
$host = "localhost";
$dbname = "cnttazmedia247_tro_v3";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo; 
    
} catch (PDOException $e) {
    die("Nhắn Telegram : @minhquan2006 để dạy  : " . $e->getMessage());
}
?>