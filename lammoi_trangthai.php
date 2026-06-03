include 'cek-akses.php';

$pdo = include 'config_minhquan.php';

try {
    // 1. Chỉ reset data_kost (Cập nhật những người đang ở trạng thái 'Đã thanh toán', 'Nợ' thành 'Chưa thanh toán')
    // Giữ nguyên những ai đang ở trạng thái 'Đặt cọc' nếu muốn, hoặc reset hết tùy logic. 
    // Thường thì những người đang ở sẽ bị reset trạng thái cho tháng mới
    $stmt = $pdo->prepare("UPDATE data_kost SET status_pembayaran = 'Chưa thanh toán', tanggal_bayar = NULL WHERE status_pembayaran != 'Đặt cọc'");
    $stmt->execute();
    
    // 2. Tương tự cho data_penghuni nếu có
    $stmt2 = $pdo->prepare("UPDATE data_penghuni SET status_pembayaran = 'Chưa thanh toán'");
    $stmt2->execute();

    $_SESSION['flash_message'] = "Đã làm mới toán bộ trạng thái về CHƯA THANH TOÁN thành công!";
    $_SESSION['flash_type'] = "success";

} catch (PDOException $e) {
    $_SESSION['flash_message'] = "Lỗi khi làm mới trạng thái: " . $e->getMessage();
    $_SESSION['flash_type'] = "danger";
}

// Quay lại bảng điều khiển
header("Location: dashboard.php");
exit;
?>
