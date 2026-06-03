<?php
// Include security check
include 'cek-akses.php';
require 'cookie.php';

// Include database connection
$pdo = include 'config_minhquan.php';

// TÊN KHU TRỌ CHUẨN DATABASE TIẾNG VIỆT
$nama_kost = 'Trọ Sinh Viên 1'; 

// ==========================================
// XỬ LÝ DỮ LIỆU TRỰC TIẾP (THÊM, SỬA, XÓA)
// ==========================================

// 1. Process Add
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    try {
        $jumlah_penghuni = $_POST['jumlah_penghuni'];
        $nama_penghuni = $_POST['nama_penghuni'];
        $no_hp = $_POST['no_hp'];
        $no_kamar = $_POST['no_kamar'];
        $tanggal_masuk = $_POST['tanggal_masuk'];
        $status_pembayaran = $_POST['status_pembayaran'];
        
        $foto_path = '';
        if (isset($_FILES['foto_penghuni']) && $_FILES['foto_penghuni']['error'] == 0) {
            $target_dir = "uploads/kost/";
            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
            $file_extension = pathinfo($_FILES["foto_penghuni"]["name"], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            if (move_uploaded_file($_FILES["foto_penghuni"]["tmp_name"], $target_file)) {
                $foto_path = $target_file;
            }
        }
        
        $stmt = $pdo->prepare("INSERT INTO data_kost (nama_kost, foto_penghuni, jumlah_penghuni, nama_penghuni, no_hp, no_kamar, tanggal_masuk, status_pembayaran) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nama_kost, $foto_path, $jumlah_penghuni, $nama_penghuni, $no_hp, $no_kamar, $tanggal_masuk, $status_pembayaran]);
        
        $stmt_update = $pdo->prepare("UPDATE data_kamar SET status = 'Đã thuê' WHERE no_kamar = ? AND nama_kost = ?");
        $stmt_update->execute([$no_kamar, $nama_kost]);
        
        $_SESSION['flash_message'] = "Thêm người thuê thành công!";
        $_SESSION['flash_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['flash_message'] = "Lỗi: " . $e->getMessage();
        $_SESSION['flash_type'] = "danger";
    }
    header("Location: quanlytro1.php");
    exit;
}

// 2. Process Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {
    try {
        $id = $_POST['id'];
        $jumlah_penghuni = $_POST['jumlah_penghuni'];
        $nama_penghuni = $_POST['nama_penghuni'];
        $no_hp = $_POST['no_hp'];
        $no_kamar = $_POST['no_kamar'];
        $no_kamar_lama = $_POST['no_kamar_lama'];
        $tanggal_masuk = $_POST['tanggal_masuk'];
        $status_pembayaran = $_POST['status_pembayaran'];
        $foto_existing = $_POST['foto_existing'];
        
        $foto_path = $foto_existing;
        if (isset($_FILES['foto_penghuni']) && $_FILES['foto_penghuni']['error'] == 0) {
            $target_dir = "uploads/kost/";
            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
            $file_extension = pathinfo($_FILES["foto_penghuni"]["name"], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            if (move_uploaded_file($_FILES["foto_penghuni"]["tmp_name"], $target_file)) {
                $foto_path = $target_file;
                if (!empty($foto_existing) && file_exists($foto_existing)) unlink($foto_existing);
            }
        }
        
        $stmt = $pdo->prepare("UPDATE data_kost SET foto_penghuni=?, jumlah_penghuni=?, nama_penghuni=?, no_hp=?, no_kamar=?, tanggal_masuk=?, status_pembayaran=? WHERE id=?");
        $stmt->execute([$foto_path, $jumlah_penghuni, $nama_penghuni, $no_hp, $no_kamar, $tanggal_masuk, $status_pembayaran, $id]);
        
        if ($no_kamar != $no_kamar_lama) {
            $stmt_old = $pdo->prepare("UPDATE data_kamar SET status = 'Trống' WHERE no_kamar = ? AND nama_kost = ?");
            $stmt_old->execute([$no_kamar_lama, $nama_kost]);
            
            $stmt_new = $pdo->prepare("UPDATE data_kamar SET status = 'Đã thuê' WHERE no_kamar = ? AND nama_kost = ?");
            $stmt_new->execute([$no_kamar, $nama_kost]);
        }
        
        $_SESSION['flash_message'] = "Cập nhật dữ liệu thành công!";
        $_SESSION['flash_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['flash_message'] = "Lỗi: " . $e->getMessage();
        $_SESSION['flash_type'] = "danger";
    }
    header("Location: quanlytro1.php");
    exit;
}

// 3. Process Delete
if (isset($_GET['delete'])) {
    try {
        $id = $_GET['delete'];
        $stmt_get = $pdo->prepare("SELECT no_kamar, foto_penghuni FROM data_kost WHERE id = ?");
        $stmt_get->execute([$id]);
        $kost = $stmt_get->fetch(PDO::FETCH_ASSOC);
        
        if ($kost) {
            $stmt_kamar = $pdo->prepare("UPDATE data_kamar SET status = 'Trống' WHERE no_kamar = ? AND nama_kost = ?");
            $stmt_kamar->execute([$kost['no_kamar'], $nama_kost]);
            
            if (!empty($kost['foto_penghuni']) && file_exists($kost['foto_penghuni'])) {
                unlink($kost['foto_penghuni']);
            }
            
            $stmt_del = $pdo->prepare("DELETE FROM data_kost WHERE id = ?");
            $stmt_del->execute([$id]);
            
            $_SESSION['flash_message'] = "Xóa dữ liệu thành công!";
            $_SESSION['flash_type'] = "success";
        }
    } catch (PDOException $e) {
        $_SESSION['flash_message'] = "Lỗi: " . $e->getMessage();
        $_SESSION['flash_type'] = "danger";
    }
    header("Location: quanlytro1.php");
    exit;
}

// ==========================================
// TRUY VẤN DỮ LIỆU HIỂN THỊ
// ==========================================

$stmt_available = $pdo->prepare("SELECT no_kamar FROM data_kamar WHERE nama_kost = ? AND status = 'Trống' ORDER BY no_kamar ASC");
$stmt_available->execute([$nama_kost]);
$available_rooms = $stmt_available->fetchAll(PDO::FETCH_ASSOC);

$stmt_all_rooms = $pdo->prepare("SELECT no_kamar, status FROM data_kamar WHERE nama_kost = ? ORDER BY no_kamar ASC");
$stmt_all_rooms->execute([$nama_kost]);
$all_rooms = $stmt_all_rooms->fetchAll(PDO::FETCH_ASSOC);

$search = isset($_GET['search']) ? $_GET['search'] : '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

if (!empty($search)) {
    $searchParam = "%$search%";
    $stmt_total = $pdo->prepare("SELECT COUNT(*) FROM data_kost WHERE nama_kost = ? AND (nama_penghuni LIKE ? OR no_kamar LIKE ? OR no_hp LIKE ?)");
    $stmt_total->execute([$nama_kost, $searchParam, $searchParam, $searchParam]);
    $total_rows = $stmt_total->fetchColumn();
    
    $stmt_data = $pdo->prepare("SELECT * FROM data_kost WHERE nama_kost = ? AND (nama_penghuni LIKE ? OR no_kamar LIKE ? OR no_hp LIKE ?) ORDER BY id DESC LIMIT $limit");
    $stmt_data->execute([$nama_kost, $searchParam, $searchParam, $searchParam]);
} else {
    $stmt_total = $pdo->prepare("SELECT COUNT(*) FROM data_kost WHERE nama_kost = ?");
    $stmt_total->execute([$nama_kost]);
    $total_rows = $stmt_total->fetchColumn();
    
    $stmt_data = $pdo->prepare("SELECT * FROM data_kost WHERE nama_kost = ? ORDER BY id DESC LIMIT $limit");
    $stmt_data->execute([$nama_kost]);
}
$data_kost = $stmt_data->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Trọ: <?php echo $nama_kost; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="shortcut icon" href="uploads/asset/favicon.ico" type="image/x-icon">
    <link rel="icon" href="uploads/asset/circle.png" type="image/x-icon">
    <style>
        body { z-index: 2; display: flex; background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .wann { margin-top: 80px; width: 90%; padding: 0 15px; }
        .card { border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); border: none; }
        .card-header { background-color: #fff; border-bottom: 1px solid #f1f5f9; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; border-radius: 8px 8px 0 0; }
        .card-title { margin: 0; color: #1e293b; font-weight: 600; }
        .btn-primary { background-color: #3b82f6; border-color: #3b82f6; border-radius: 6px; }
        .btn-primary:hover { background-color: #2563eb; border-color: #2563eb; }
        .table { box-shadow: 0 0 0 1px #f1f5f9; border-radius: 8px; }
        .table thead th { background-color: #f8fafc; border-bottom: 2px solid #e2e8f0; font-weight: 600; color: #475569; }
        .table td { vertical-align: middle; color: #334155; }
        .btn-edit, .btn-delete { width: 32px; height: 32px; padding: 4px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; }
        .btn-edit { background-color: #10b981; border-color: #10b981; color: white;}
        .btn-delete { background-color: #ef4444; border-color: #ef4444; color: white;}
        .btn-edit:hover { background-color: #059669; border-color: #059669; color: white;}
        .btn-delete:hover { background-color: #dc2626; border-color: #dc2626; color: white;}
        .modal-header { background-color: #3b82f6; color: white; border-radius: 8px 8px 0 0; }
        .modal-header .btn-close { filter: brightness(0) invert(1); }
        .penghuni-img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: transform 0.2s; }
        .penghuni-img:hover { transform: scale(1.1); }
        .status-badge { font-weight: 500; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <?php include 'navbar.php'; ?>
    <div class="wann">
    <h2 class="mb-4" style="font-weight: 700; color: #1e293b;"><?php echo $nama_kost; ?></h2>
    
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['flash_type']; ?> alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['flash_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php 
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        ?>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title">Danh sách người thuê</h5>
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addKostModal">
                <i class="bi bi-person-plus-fill me-1"></i> Thêm người thuê
            </button>
        </div>
        <div class="card-body">
            
            <div class="modal fade" id="addKostModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="bi bi-person-plus-fill me-2"></i>Thêm dữ liệu người thuê</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="post" action="quanlytro1.php" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="add">
                                
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Ảnh người thuê</label>
                                    <input type="file" class="form-control" name="foto_penghuni" accept="image/*">
                                    <div class="form-text">Tải lên ảnh người đại diện (JPG, PNG, tối đa 2MB)</div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Tên người thuê <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="nama_penghuni" required placeholder="Nhập họ và tên">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Số điện thoại <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="no_hp" required placeholder="Nhập SĐT">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Số phòng <span class="text-danger">*</span></label>
                                        <select class="form-select" name="no_kamar" required>
                                            <option value="">-- Chọn phòng trống --</option>
                                            <?php foreach ($available_rooms as $room): ?>
                                                <option value="<?php echo htmlspecialchars($room['no_kamar']); ?>">
                                                    Phòng <?php echo htmlspecialchars($room['no_kamar']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Số người ở <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="jumlah_penghuni" required min="1" value="1">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Ngày vào ở <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="tanggal_masuk" required value="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Thanh toán <span class="text-danger">*</span></label>
                                        <select class="form-select" name="status_pembayaran" required>
                                            <option value="Đã thanh toán">Đã thanh toán</option>
                                            <option value="Chưa thanh toán">Chưa thanh toán</option>
                                            <option value="Nợ">Nợ tiền</option>
                                            <option value="Đặt cọc">Đặt cọc</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer px-0 pb-0 pt-3">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                    <button type="submit" class="btn btn-primary">Lưu dữ liệu</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal fade" id="editKostModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Sửa thông tin</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="post" action="quanlytro1.php" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="id" id="edit_id">
                                <input type="hidden" name="no_kamar_lama" id="edit_no_kamar_lama">
                                <input type="hidden" name="foto_existing" id="edit_foto_existing">
                                
                                <div class="mb-3 text-center">
                                    <img id="current_photo" src="" alt="Ảnh" class="img-thumbnail rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Đổi ảnh mới (Tùy chọn)</label>
                                    <input type="file" class="form-control" name="foto_penghuni" accept="image/*">
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Tên người thuê <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="edit_nama_penghuni" name="nama_penghuni" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Số điện thoại <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="edit_no_hp" name="no_hp" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Đổi phòng <span class="text-danger">*</span></label>
                                        <select class="form-select" id="edit_no_kamar" name="no_kamar" required>
                                            <?php foreach ($all_rooms as $room): ?>
                                                <option value="<?php echo htmlspecialchars($room['no_kamar']); ?>">
                                                    Phòng <?php echo htmlspecialchars($room['no_kamar']); ?> (<?php echo htmlspecialchars($room['status']); ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text text-danger">Lưu ý: Nếu chuyển, hãy chọn phòng đang (Trống)</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Số người ở <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="edit_jumlah_penghuni" name="jumlah_penghuni" required min="1">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Ngày vào ở <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="edit_tanggal_masuk" name="tanggal_masuk" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Thanh toán <span class="text-danger">*</span></label>
                                        <select class="form-select" id="edit_status_pembayaran" name="status_pembayaran" required>
                                            <option value="Đã thanh toán">Đã thanh toán</option>
                                            <option value="Chưa thanh toán">Chưa thanh toán</option>
                                            <option value="Nợ">Nợ tiền</option>
                                            <option value="Đặt cọc">Đặt cọc</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="modal-footer px-0 pb-0 pt-3">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mb-3 align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <span class="me-2 text-muted">Hiển thị</span>
                        <select class="form-select form-select-sm" style="width: 80px;" id="limitEntries" onchange="changeLimit(this.value)">
                            <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10</option>
                            <option value="25" <?php echo $limit == 25 ? 'selected' : ''; ?>>25</option>
                            <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50</option>
                            <option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100</option>
                        </select>
                        <span class="ms-2 text-muted">mục</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <form method="get" action="">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" placeholder="Tìm tên, sđt, phòng..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-outline-primary"><i class="bi bi-search"></i> Tìm</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="5%">STT</th>
                            <th width="8%">Ảnh</th>
                            <th>Tên người thuê</th>
                            <th>Số điện thoại</th>
                            <th class="text-center">Số phòng</th>
                            <th class="text-center">Số người</th>
                            <th>Ngày vào</th>
                            <th>Trạng thái</th>
                            <th width="10%" class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($data_kost) > 0): ?>
                            <?php $no = 1; ?>
                            <?php foreach ($data_kost as $kost): ?>
                                <tr>
                                    <td><span class="fw-bold text-muted"><?php echo $no++; ?></span></td>
                                    <td>
                                        <?php if (!empty($kost['foto_penghuni']) && file_exists($kost['foto_penghuni'])): ?>
                                            <img src="<?php echo $kost['foto_penghuni']; ?>" alt="Ảnh" class="penghuni-img">
                                        <?php else: ?>
                                            <img src="uploads/default-user.png" alt="Ảnh" class="penghuni-img">
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-semibold text-primary"><?php echo htmlspecialchars($kost['nama_penghuni']); ?></td>
                                    <td><?php echo htmlspecialchars($kost['no_hp']); ?></td>
                                    <td class="text-center"><span class="badge bg-secondary fs-6 rounded-pill px-3"><?php echo htmlspecialchars($kost['no_kamar']); ?></span></td>
                                    <td class="text-center"><?php echo htmlspecialchars($kost['jumlah_penghuni']); ?> <i class="bi bi-person-fill text-muted"></i></td>
                                    <td><?php echo date('d/m/Y', strtotime($kost['tanggal_masuk'])); ?></td>
                                    <td>
                                        <?php if ($kost['status_pembayaran'] == 'Đã thanh toán'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success status-badge"><i class="bi bi-check-circle me-1"></i>Đã thanh toán</span>
                                        <?php elseif ($kost['status_pembayaran'] == 'Nợ'): ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger status-badge"><i class="bi bi-exclamation-circle me-1"></i>Nợ tiền</span>
                                        <?php elseif ($kost['status_pembayaran'] == 'Đặt cọc'): ?>
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info status-badge"><i class="bi bi-wallet2 me-1"></i>Đặt cọc</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning status-badge"><i class="bi bi-clock-history me-1"></i>Chưa thanh toán</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <button class="btn btn-edit btn-edit-kost" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editKostModal"
                                                    data-id="<?php echo $kost['id']; ?>"
                                                    data-foto-penghuni="<?php echo $kost['foto_penghuni']; ?>"
                                                    data-jumlah-penghuni="<?php echo $kost['jumlah_penghuni']; ?>"
                                                    data-nama-penghuni="<?php echo $kost['nama_penghuni']; ?>"
                                                    data-no-hp="<?php echo $kost['no_hp']; ?>"
                                                    data-no-kamar="<?php echo $kost['no_kamar']; ?>"
                                                    data-tanggal-masuk="<?php echo $kost['tanggal_masuk']; ?>"
                                                    data-status-pembayaran="<?php echo $kost['status_pembayaran']; ?>"
                                                    title="Sửa">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <a href="quanlytro1.php?delete=<?php echo $kost['id']; ?>" 
                                               class="btn btn-delete" 
                                               title="Xóa"
                                               onclick="return confirm('Xóa dữ liệu của người thuê này? Thao tác không thể hoàn tác.')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="bi bi-inboxes fs-1 d-block mb-2"></i> Không có dữ liệu người thuê nào.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3 text-muted small">
                Đang hiển thị <?php echo count($data_kost); ?> / <?php echo $total_rows; ?> mục
            </div>
        </div>
    </div>
    </div>

<div class="modal fade" id="imageViewerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 bg-transparent">
      <div class="modal-header border-0 pb-0">
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center pt-0">
        <img id="enlargedImage" src="" class="img-fluid rounded shadow-lg" style="max-height: 80vh;">
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.btn-edit-kost');
    
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.getAttribute('data-id');
            document.getElementById('edit_jumlah_penghuni').value = this.getAttribute('data-jumlah-penghuni');
            document.getElementById('edit_nama_penghuni').value = this.getAttribute('data-nama-penghuni');
            document.getElementById('edit_no_hp').value = this.getAttribute('data-no-hp');
            document.getElementById('edit_no_kamar_lama').value = this.getAttribute('data-no-kamar');
            document.getElementById('edit_tanggal_masuk').value = this.getAttribute('data-tanggal-masuk');
            document.getElementById('edit_status_pembayaran').value = this.getAttribute('data-status-pembayaran');
            
            const foto = this.getAttribute('data-foto-penghuni');
            document.getElementById('edit_foto_existing').value = foto;
            document.getElementById('current_photo').src = (foto && foto !== '') ? foto : 'uploads/default-user.png';
            
            const selectKamar = document.getElementById('edit_no_kamar');
            selectKamar.value = this.getAttribute('data-no-kamar');
        });
    });

    window.changeLimit = function(limit) {
        const url = new URL(window.location.href);
        url.searchParams.set('limit', limit);
        window.location.href = url.toString();
    };

    document.querySelectorAll('.penghuni-img').forEach(photo => {
        photo.style.cursor = 'pointer';
        photo.addEventListener('click', function() {
            document.getElementById('enlargedImage').src = this.src;
            new bootstrap.Modal(document.getElementById('imageViewerModal')).show();
        });
    });
});
</script>
</body>
</html>