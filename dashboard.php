<?php
// Include security check
include 'cek-akses.php';
require 'cookie.php';

// Redundant but ensuring $pdo is available after potential cek-akses override
if (!isset($pdo)) {
    require 'db.php';
}

// Count available rooms
try {
    $stmt_available = $pdo->query("SELECT COUNT(*) as count FROM data_kamar WHERE status = 'Trống'");
    $available_rooms = $stmt_available->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
} catch (Exception $e) {
    $available_rooms = 0;
}

// Count total residents
try {
    $stmt_residents = $pdo->query("SELECT SUM(jumlah_penghuni) as count FROM data_kost");
    $total_residents = $stmt_residents->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
} catch (Exception $e) {
    $total_residents = 0;
}

// Stats from data_kost
try {
    $stmt_stats = $pdo->query("SELECT 
        SUM(CASE WHEN status_pembayaran = 'Nợ' THEN 1 ELSE 0 END) as nunggak_count,
        SUM(CASE WHEN status_pembayaran = 'Đã thanh toán' THEN 1 ELSE 0 END) as paid_count,
        SUM(CASE WHEN status_pembayaran IN ('Chưa thanh toán', '') THEN 1 ELSE 0 END) as unpaid_count
        FROM data_kost");
    $res_stats = $stmt_stats->fetch();
    $nunggak_count = $res_stats['nunggak_count'] ?? 0;
    $paid_count = $res_stats['paid_count'] ?? 0;
    $unpaid_count = $res_stats['unpaid_count'] ?? 0;
} catch (Exception $e) {
    $nunggak_count = $paid_count = $unpaid_count = 0;
}

// Estimated Total Income (based on prices of rented rooms)
try {
    $stmt_income = $pdo->query("SELECT SUM(k.harga) as total 
                                FROM data_kost d 
                                JOIN data_kamar k ON d.no_kamar = k.no_kamar AND d.nama_kost = k.nama_kost 
                                WHERE d.status_pembayaran = 'Đã thanh toán'");
    $total_income = $stmt_income->fetch()['total'] ?? 0;
} catch (Exception $e) {
    $total_income = 0;
}

// Get recent transactions (data_kost)
try {
    $stmt_transactions = $pdo->query("SELECT * FROM data_kost ORDER BY tanggal_masuk DESC LIMIT 50");
    $transactions = $stmt_transactions->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $transactions = [];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    <title>Quản lý Trọ | Dashboard</title>
    <link rel="shortcut icon" href="uploads/asset/favicon.ico" type="image/x-icon">
    <link rel="icon" href="uploads/asset/circle.png" type="image/x-icon">
    <style>
        :root {
            --bg-body: #f4f7fe;
            --text-main: #2b3674;
            --text-muted: #a3aed1;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
        }
        .wann {
            margin-top: 80px;
        }
        .page-header {
            margin-bottom: 2rem;
        }
        .page-header h1 {
            font-weight: 800;
            font-size: 2rem;
            color: var(--text-main);
            letter-spacing: -0.5px;
            margin-bottom: 0.2rem;
        }
        .page-header p {
            font-weight: 500;
            color: var(--text-muted);
            font-size: 1rem;
        }

        /* Gradient Cards */
        .stat-card {
            border-radius: 20px;
            border: none;
            color: white;
            padding: 24px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 18px 40px rgba(112, 144, 176, 0.12);
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 22px 45px rgba(112, 144, 176, 0.2);
        }
        
        /* Card Background Gradients */
        .bg-grad-blue { background: linear-gradient(135deg, #4318FF 0%, #868CFF 100%); }
        .bg-grad-purple { background: linear-gradient(135deg, #8A2BE2 0%, #D480FF 100%); }
        .bg-grad-green { background: linear-gradient(135deg, #01B574 0%, #34D399 100%); }
        .bg-grad-orange { background: linear-gradient(135deg, #FFB547 0%, #FF8A00 100%); }
        .bg-grad-red { background: linear-gradient(135deg, #EE5D50 0%, #FF9E9E 100%); }
        .bg-grad-teal { background: linear-gradient(135deg, #00B4D8 0%, #90E0EF 100%); }

        .stat-card h3 {
            font-size: 2.2rem;
            font-weight: 800;
            margin: 8px 0;
            z-index: 2;
        }
        .stat-card p {
            font-size: 0.95rem;
            font-weight: 600;
            opacity: 0.9;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            z-index: 2;
        }
        .card-link {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.85rem;
            font-weight: 700;
            text-decoration: none;
            margin-top: 12px;
            z-index: 2;
            display: inline-flex;
            align-items: center;
        }
        .card-link:hover { color: white; }
        
        /* Large Background Icon */
        .bg-icon {
            position: absolute;
            right: -10px;
            bottom: -15px;
            font-size: 6rem;
            color: rgba(255, 255, 255, 0.15);
            z-index: 1;
            transform: rotate(-15deg);
            transition: all 0.3s ease;
        }
        .stat-card:hover .bg-icon {
            transform: rotate(0deg) scale(1.1);
            color: rgba(255, 255, 255, 0.25);
        }

        /* Modern Table Card */
        .table-container {
            background: #ffffff;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 18px 40px rgba(112, 144, 176, 0.12);
            margin-top: 1rem;
        }
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .table-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--text-main);
            margin: 0;
        }
        
        .table {
            color: var(--text-main);
            vertical-align: middle;
        }
        .table thead th {
            font-weight: 700;
            color: var(--text-muted);
            border-bottom: 2px solid #e9edf7;
            padding: 16px 12px;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .table tbody td {
            padding: 16px 12px;
            border-bottom: 1px solid #f4f7fe;
            font-weight: 600;
            font-size: 0.95rem;
        }
        .table tbody tr:hover {
            background-color: #f8faff;
        }

        /* Badges */
        .badge {
            padding: 8px 16px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 0.75rem;
        }
        
        /* Tùy chỉnh cột PHÒNG cho nổi bật, chống lỗi tàng hình chữ */
        .badge-room {
            background-color: #e0e8ff !important; /* Nền xanh nhạt */
            color: #1e3a8a !important; /* Chữ xanh đậm rõ nét */
            border: 1px solid #bfdbfe !important; /* Viền mỏng */
            font-weight: 800 !important;
            padding: 6px 14px !important;
            border-radius: 8px !important;
            font-size: 0.85rem !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05) !important;
            display: inline-block;
        }

        .bg-soft-success { background-color: #e5f8ed; color: #01B574; }
        .bg-soft-warning { background-color: #fff4e5; color: #FFB547; }
        .bg-soft-danger { background-color: #feeceb; color: #EE5D50; }
        .bg-soft-info { background-color: #e5f6fa; color: #00B4D8; }

        .btn-action {
            border-radius: 10px;
            font-weight: 700;
            padding: 6px 14px;
            font-size: 0.8rem;
            transition: all 0.2s;
        }
        .btn-outline-primary {
            border: 2px solid #4318FF;
            color: #4318FF;
        }
        .btn-outline-primary:hover {
            background: #4318FF;
            color: white;
        }

        .table-scroll {
            max-height: 450px;
            overflow-y: auto;
        }
        .table-scroll::-webkit-scrollbar { width: 8px; height: 8px; }
        .table-scroll::-webkit-scrollbar-track { background: #f4f7fe; border-radius: 10px; }
        .table-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .table-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <?php include 'navbar.php'; ?>
    
    <div class="wann">
        <div class="page-header">
            <h1>Bảng điều khiển</h1>
            <p>Hệ thống Quản lý Trọ thông minh</p>
        </div>
                
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['flash_type']; ?> alert-dismissible fade show shadow-sm border-0 rounded-4" role="alert">
                <i class="bi bi-bell-fill me-2"></i> <?php echo $_SESSION['flash_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php 
            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_type']);
            ?>
        <?php endif; ?>
                
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="container-fluid p-0">
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                
                <div class="col">
                    <div class="stat-card bg-grad-blue">
                        <i class="bi bi-door-open-fill bg-icon"></i>
                        <p>Phòng trống</p>
                        <h3><?php echo $available_rooms; ?></h3>
                        <a href="bangdieukhien_quanglyphong.php" class="card-link">Xem chi tiết <i class="bi bi-arrow-right-short fs-5 ms-1"></i></a>
                    </div>
                </div>
                
                <div class="col">
                    <div class="stat-card bg-grad-purple">
                        <i class="bi bi-people-fill bg-icon"></i>
                        <p>Tổng người thuê</p>
                        <h3><?php echo $total_residents; ?></h3>
                    </div>
                </div>
                
                <div class="col">
                    <div class="stat-card bg-grad-green">
                        <i class="bi bi-wallet-fill bg-icon"></i>
                        <p>Doanh thu tháng <?php echo date('m/Y'); ?></p>
                        <h3><?php echo number_format($total_income, 0, ',', '.'); ?> <small class="fs-5">đ</small></h3>
                    </div>
                </div>
                
                <div class="col">
                    <div class="stat-card bg-grad-orange">
                        <i class="bi bi-exclamation-circle-fill bg-icon"></i>
                        <p>Đang nợ tiền</p>
                        <h3><?php echo $nunggak_count; ?></h3>
                    </div>
                </div>
                
                <div class="col">
                    <div class="stat-card bg-grad-red">
                        <i class="bi bi-cash-stack bg-icon"></i>
                        <p>Chưa thanh toán</p>
                        <h3><?php echo $unpaid_count; ?></h3>
                    </div>
                </div>
                
                <div class="col">
                    <div class="stat-card bg-grad-teal">
                        <i class="bi bi-check-circle-fill bg-icon"></i>
                        <p>Đã thanh toán</p>
                        <h3><?php echo $paid_count; ?></h3>
                    </div>
                </div>
            </div>

             
             
             
             <!-- TẠM ẨN: Lịch sử Giao dịch (mở lại khi cần)  -->
                 <!-- Đây 2 dấu bên cạnh nghiwax tạm ngưng --> 
           <div class="table-container">
                <div class="table-header">
                    <h5 class="table-title">Lịch sử Giao dịch</h5>
                    <a href="lammoi_trangthai.php" class="btn btn-danger btn-action shadow-sm" onclick="return confirm('Bạn có chắc chắn muốn thiết lập lại TẤT CẢ trạng thái thanh toán về CHƯA THANH TOÁN cho tháng mới? Thao tác này KHÔNG THỂ HOÀN TÁC.');">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Làm mới Trạng thái
                    </a>
                </div> 
                
                
                
                
                <div class="table-responsive border-0">
                    <div class="table-scroll">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Khu trọ</th>
                                    <th>Tên người thuê</th>
                                    <th>Phòng</th>
                                    <th>Số người</th>
                                    <th>Ngày vào</th>
                                    <th>Ngày thanh toán</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($transactions) > 0): ?>
                                    <?php foreach ($transactions as $transaction): ?>
                                        <tr>
                                            <td>
                                                <span class="fw-bold" style="color: #4318FF;">
                                                    <?php echo htmlspecialchars($transaction['nama_kost']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-light rounded-circle p-2 me-2 text-primary">
                                                        <i class="bi bi-person-fill"></i>
                                                    </div>
                                                    <?php echo htmlspecialchars($transaction['nama_penghuni']); ?>
                                                </div>
                                            </td>
                                            <td><span class="badge badge-room"><?php echo htmlspecialchars($transaction['no_kamar']); ?></span></td>
                                            <td><?php echo htmlspecialchars($transaction['jumlah_penghuni']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($transaction['tanggal_masuk'])); ?></td>
                                            <td>
                                                <?php if ($transaction['status_pembayaran'] == 'Đã thanh toán' && !empty($transaction['tanggal_bayar']) && $transaction['tanggal_bayar'] != '0000-00-00'): ?>
                                                    <span class="text-success"><i class="bi bi-calendar-check me-1"></i> <?php echo date('d/m/Y', strtotime($transaction['tanggal_bayar'])); ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted fst-italic">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($transaction['status_pembayaran'] == 'Đã thanh toán'): ?>
                                                        <span class="badge bg-soft-success">Đã thanh toán</span>
                                                    <?php elseif ($transaction['status_pembayaran'] == 'Đặt cọc'): ?>
                                                        <span class="badge bg-soft-info">Đặt cọc</span>
                                                    <?php elseif ($transaction['status_pembayaran'] == 'Nợ'): ?>
                                                        <span class="badge bg-soft-warning me-2">Nợ tiền</span>
                                                        <button type="button" class="btn btn-outline-primary btn-action" data-bs-toggle="modal" data-bs-target="#bayarModal" data-nama-kost="<?php echo htmlspecialchars($transaction['nama_kost']); ?>" data-nama-penghuni="<?php echo htmlspecialchars($transaction['nama_penghuni']); ?>" data-no-kamar="<?php echo htmlspecialchars($transaction['no_kamar']); ?>">
                                                            Thu tiền
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="badge bg-soft-danger me-2">Chưa đóng</span>
                                                        <button type="button" class="btn btn-outline-primary btn-action" data-bs-toggle="modal" data-bs-target="#bayarModal" data-nama-kost="<?php echo htmlspecialchars($transaction['nama_kost']); ?>" data-nama-penghuni="<?php echo htmlspecialchars($transaction['nama_penghuni']); ?>" data-no-kamar="<?php echo htmlspecialchars($transaction['no_kamar']); ?>">
                                                            Thu tiền
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="bi bi-folder2-open fs-1 d-block mb-2"></i>
                                                Không có dữ liệu giao dịch.
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="bayarModal" tabindex="-1" aria-labelledby="bayarModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold" id="bayarModalLabel" style="color: #2b3674;">Cập nhật Thu tiền</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="xuly_thutien.php" method="POST">
                    <div class="modal-body px-4 pt-4">
                        <input type="hidden" id="modal_nama_kost" name="nama_kost">
                        <input type="hidden" id="modal_nama_penghuni" name="nama_penghuni">
                        <input type="hidden" id="modal_no_kamar" name="no_kamar">
                        
                        <div class="mb-4">
                            <label for="tanggal_bayar" class="form-label fw-bold" style="color: #a3aed1; font-size: 0.85rem; text-transform: uppercase;">Ngày thanh toán</label>
                            <input type="date" class="form-control form-control-lg bg-light border-0" id="tanggal_bayar" name="tanggal_bayar" value="<?php echo date('Y-m-d'); ?>" required style="border-radius: 12px; font-weight: 600; color: #2b3674;">
                        </div>
                        
                        <div class="mb-2">
                            <label for="keterangan_bayar" class="form-label fw-bold" style="color: #a3aed1; font-size: 0.85rem; text-transform: uppercase;">Ghi chú (Tùy chọn)</label>
                            <textarea class="form-control bg-light border-0" id="keterangan_bayar" name="keterangan_bayar" rows="3" style="border-radius: 12px; font-weight: 500; color: #2b3674;"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 px-4 pb-4 pt-2">
                        <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal" style="border-radius: 12px; padding: 10px 20px; color: #a3aed1;">Hủy bỏ</button>
                        <button type="submit" class="btn fw-bold text-white shadow-sm" style="border-radius: 12px; padding: 10px 20px; background: linear-gradient(135deg, #4318FF 0%, #868CFF 100%); border: none;">
                            Xác nhận Thu tiền
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    
    <script>
        // Check for successful login
        <?php
        if (isset($_SESSION['login_success']) && $_SESSION['login_success'] === true) {
            unset($_SESSION['login_success']);
        ?>
            Swal.fire({
                title: 'Thành công',
                text: 'Đăng nhập thành công!',
                icon: 'success',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                toast: true,
                position: 'top-end'
            });
        <?php } ?>
    </script>
    
    <script>
        // Set modal data when payment button is clicked
        document.addEventListener('DOMContentLoaded', function() {
            const bayarModal = document.getElementById('bayarModal');
            if (bayarModal) {
                bayarModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const namaKost = button.getAttribute('data-nama-kost');
                    const namaPenghuni = button.getAttribute('data-nama-penghuni');
                    const noKamar = button.getAttribute('data-no-kamar');
                    
                    document.getElementById('modal_nama_kost').value = namaKost;
                    document.getElementById('modal_nama_penghuni').value = namaPenghuni;
                    document.getElementById('modal_no_kamar').value = noKamar;
                    
                    bayarModal.querySelector('.modal-title').textContent = 
                        'Thu tiền: ' + namaPenghuni + ' - Phòng ' + noKamar;
                });
            }
        });
    </script>
</body>
</html>