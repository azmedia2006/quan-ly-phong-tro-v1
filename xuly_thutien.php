include 'cek-akses.php';

$pdo = include 'config_minhquan.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_kost = $_POST['nama_kost'] ?? '';
    $nama_penghuni = $_POST['nama_penghuni'] ?? '';
    $no_kamar = $_POST['no_kamar'] ?? '';
    $tanggal_bayar = $_POST['tanggal_bayar'] ?? '';
    $keterangan = $_POST['keterangan_bayar'] ?? '';

    if (!empty($nama_kost) && !empty($nama_penghuni) && !empty($no_kamar) && !empty($tanggal_bayar)) {
        try {
            // Lấy id của người thuê dựa vào thông tin (để xử lý insert bảng payment_history)
            $stmt = $pdo->prepare("SELECT id FROM data_kost WHERE nama_kost = ? AND nama_penghuni = ? AND no_kamar = ? ORDER BY id DESC LIMIT 1");
            $stmt->execute([$nama_kost, $nama_penghuni, $no_kamar]);
            $tenant = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($tenant) {
                // 1. Cập nhật trạng thái thanh toán trong data_kost sang "Đã thanh toán"
                // và lưu lại ngày thanh toán, ghi chú
                $stmt_update = $pdo->prepare("UPDATE data_kost SET status_pembayaran = 'Đã thanh toán', tanggal_bayar = ?, keterangan = ? WHERE id = ?");
                $stmt_update->execute([$tanggal_bayar, $keterangan, $tenant['id']]);

                // 2. Ghi log vào bảng payment_history
                $stmt_history = $pdo->prepare("INSERT INTO payment_history (tenant_id, nama_kost, nama_penghuni, no_kamar, tanggal_bayar, keterangan) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_history->execute([$tenant['id'], $nama_kost, $nama_penghuni, $no_kamar, $tanggal_bayar, $keterangan]);

                // 3. Cập nhật luôn trạng thái của data_penghuni nếu có
                $stmt_penghuni = $pdo->prepare("UPDATE data_penghuni SET status_pembayaran = 'Đã thanh toán' WHERE nama_kost = ? AND nama_penghuni = ? AND no_kamar = ?");
                $stmt_penghuni->execute([$nama_kost, $nama_penghuni, $no_kamar]);

                $_SESSION['flash_message'] = "Đã thu tiền thành công cho phòng " . htmlspecialchars($no_kamar) . " (" . htmlspecialchars($nama_penghuni) . ")";
                $_SESSION['flash_type'] = "success";
            } else {
                $_SESSION['flash_message'] = "Không tìm thấy thông tin người thuê để thu tiền.";
                $_SESSION['flash_type'] = "danger";
            }

        } catch (PDOException $e) {
            $_SESSION['flash_message'] = "Lỗi hệ thống khi thu tiền: " . $e->getMessage();
            $_SESSION['flash_type'] = "danger";
        }
    } else {
        $_SESSION['flash_message'] = "Vui lòng nhập đầy đủ ngày thanh toán.";
        $_SESSION['flash_type'] = "warning";
    }

    // Quay lại bảng điều khiển
    header("Location: dashboard.php");
    exit;
} else {
    // Nếu truy cập trực tiếp thì đá về dashboard
    header("Location: dashboard.php");
    exit;
}
?>
