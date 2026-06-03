<?php
require_once 'cek-akses.php';
checkAdmin();

$pdo = include 'config_minhquan.php';

$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Rates (can be moved to a settings table later)
$rate_electric = 3500; 
$rate_water = 15000;
$service_fee = 50000; 

// 1. Handle Meter Input
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'save_readings') {
    $room = $_POST['room_number'];
    $reading_type = $_POST['type'];
    $curr_value = (float)$_POST['curr_value'];
    
    // Get last reading for previous month/year
    $stmt_prev = $pdo->prepare("SELECT curr_value FROM meter_readings WHERE room_number = ? AND type = ? ORDER BY year DESC, month DESC LIMIT 1");
    $stmt_prev->execute([$room, $reading_type]);
    $prev_val = $stmt_prev->fetchColumn() ?: 0;

    // Check if current reading already exists for this month
    $stmt_check = $pdo->prepare("SELECT id FROM meter_readings WHERE room_number = ? AND type = ? AND month = ? AND year = ?");
    $stmt_check->execute([$room, $reading_type, $month, $year]);
    $existing = $stmt_check->fetchColumn();

    if ($existing) {
        $stmt = $pdo->prepare("UPDATE meter_readings SET curr_value = ?, prev_value = ? WHERE id = ?");
        $stmt->execute([$curr_value, $prev_val, $existing]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO meter_readings (room_number, type, prev_value, curr_value, month, year) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$room, $reading_type, $prev_val, $curr_value, $month, $year]);
    }
    
    header("Location: quanly_hoadon.php?month=$month&year=$year&success=1");
    exit;
}

// 2. Handle Invoice Generation
if (isset($_GET['action']) && $_GET['action'] == 'generate' && isset($_GET['room'])) {
    $room = $_GET['room'];
    
    // Fetch room data
    $stmt_room = $pdo->prepare("SELECT * FROM data_kamar WHERE no_kamar = ?");
    $stmt_room->execute([$room]);
    $room_data = $stmt_room->fetch();
    
    // Fetch tenant data
    $stmt_tenant = $pdo->prepare("SELECT nama_penghuni FROM data_kost WHERE no_kamar = ? LIMIT 1");
    $stmt_tenant->execute([$room]);
    $tenant_name = $stmt_tenant->fetchColumn() ?: 'Khách lạ';

    // Fetch readings
    $stmt_el = $pdo->prepare("SELECT usage_value FROM meter_readings WHERE room_number = ? AND type = 'Electric' AND month = ? AND year = ?");
    $stmt_el->execute([$room, $month, $year]);
    $el_usage = $stmt_el->fetchColumn() ?: 0;

    $stmt_wa = $pdo->prepare("SELECT usage_value FROM meter_readings WHERE room_number = ? AND type = 'Water' AND month = ? AND year = ?");
    $stmt_wa->execute([$room, $month, $year]);
    $wa_usage = $stmt_wa->fetchColumn() ?: 0;

    $room_price = $room_data['harga'] ?? 0;
    $el_cost = $el_usage * $rate_electric;
    $wa_cost = $wa_usage * $rate_water;
    $total = $room_price + $el_cost + $wa_cost + $service_fee;

    // Check if invoice exists
    $stmt_inv = $pdo->prepare("SELECT id FROM invoices WHERE room_number = ? AND month = ? AND year = ?");
    $stmt_inv->execute([$room, $month, $year]);
    $inv_id = $stmt_inv->fetchColumn();

    if ($inv_id) {
        $stmt = $pdo->prepare("UPDATE invoices SET tenant_name = ?, room_price = ?, electric_usage = ?, electric_cost = ?, water_usage = ?, water_cost = ?, service_fee = ?, total_amount = ? WHERE id = ?");
        $stmt->execute([$tenant_name, $room_price, $el_usage, $el_cost, $wa_usage, $wa_cost, $service_fee, $total, $inv_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO invoices (room_number, tenant_name, room_price, electric_usage, electric_cost, water_usage, water_cost, service_fee, total_amount, month, year) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$room, $tenant_name, $room_price, $el_usage, $el_cost, $wa_usage, $wa_cost, $service_fee, $total, $month, $year]);
    }

    header("Location: quanly_hoadon.php?month=$month&year=$year&success=2");
    exit;
}

// 3. Mark as Paid
if (isset($_GET['action']) && $_GET['action'] == 'pay' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("UPDATE invoices SET status = 'Paid', payment_date = NOW() WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    header("Location: quanly_hoadon.php?month=$month&year=$year&success=3");
    exit;
}

// Fetch all rooms with their readings and invoice status
$rooms = $pdo->query("SELECT k.no_kamar, k.nama_kost, k.status as room_status,
                      (SELECT curr_value FROM meter_readings WHERE room_number = k.no_kamar AND type = 'Electric' AND month = $month AND year = $year) as el_curr,
                      (SELECT curr_value FROM meter_readings WHERE room_number = k.no_kamar AND type = 'Water' AND month = $month AND year = $year) as wa_curr,
                      (SELECT id FROM invoices WHERE room_number = k.no_kamar AND month = $month AND year = $year) as inv_id,
                      (SELECT status FROM invoices WHERE room_number = k.no_kamar AND month = $month AND year = $year) as inv_status
                      FROM data_kamar k ORDER BY k.no_kamar ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Hóa đơn & Điện nước - AZMEDIA247</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f7fe; }
        .glass { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.3); }
    </style>
</head>
<body class="min-h-screen">
    <?php include 'sidebar.php'; ?>
    
    <div class="main p-8">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-end mb-10">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight uppercase">Quản lý hóa đơn</h1>
                    <p class="text-slate-500 font-bold tracking-widest text-[10px] mt-1">GHI CHỈ SỐ ĐIỆN NƯỚC & XUẤT HÓA ĐƠN THÁNG <?= $month ?>/<?= $year ?></p>
                </div>
                <div class="flex gap-4">
                    <form action="" method="get" class="flex gap-2 bg-white p-2 rounded-2xl shadow-sm border border-slate-100">
                        <select name="month" class="bg-transparent border-none outline-none font-bold text-xs px-2">
                            <?php for($m=1; $m<=12; $m++): ?>
                                <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>>Tháng <?= $m ?></option>
                            <?php endfor; ?>
                        </select>
                        <select name="year" class="bg-transparent border-none outline-none font-bold text-xs px-2">
                            <?php for($y=date('Y')-1; $y<=date('Y')+1; $y++): ?>
                                <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-xs font-black shadow-lg shadow-indigo-600/20">LỌC</button>
                    </form>
                </div>
            </div>

            <?php if(isset($_GET['success'])): ?>
                <div class="bg-emerald-500 text-white p-4 rounded-2xl mb-8 font-bold text-sm shadow-xl shadow-emerald-500/20 animate-bounce">
                    <i class="bi bi-check-circle-fill mr-2"></i> Thao tác thành công!
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 gap-6">
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-100">
                                <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Phòng</th>
                                <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Khu vực</th>
                                <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-amber-500">Số Điện</th>
                                <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-blue-500">Số Nước</th>
                                <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Hóa Đơn</th>
                                <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Trạng Thái</th>
                                <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php foreach($rooms as $r): ?>
                                <tr class="hover:bg-slate-50/30 transition-colors">
                                    <td class="px-8 py-6">
                                        <span class="font-black text-slate-800">P.<?= $r['no_kamar'] ?></span>
                                    </td>
                                    <td class="px-8 py-6">
                                        <span class="text-xs font-bold text-slate-400"><?= $r['nama_kost'] ?></span>
                                    </td>
                                    <td class="px-8 py-6">
                                        <form action="" method="post" class="flex items-center gap-2">
                                            <input type="hidden" name="action" value="save_readings">
                                            <input type="hidden" name="type" value="Electric">
                                            <input type="hidden" name="room_number" value="<?= $r['no_kamar'] ?>">
                                            <input type="number" step="0.01" name="curr_value" value="<?= $r['el_curr'] ?>" class="w-20 bg-amber-50 border border-amber-100 rounded-lg px-2 py-1 text-xs font-bold text-amber-700 focus:outline-none focus:border-amber-500">
                                            <button type="submit" class="text-amber-500 hover:scale-110 transition-transform"><i class="bi bi-save-fill"></i></button>
                                        </form>
                                    </td>
                                    <td class="px-8 py-6">
                                        <form action="" method="post" class="flex items-center gap-2">
                                            <input type="hidden" name="action" value="save_readings">
                                            <input type="hidden" name="type" value="Water">
                                            <input type="hidden" name="room_number" value="<?= $r['no_kamar'] ?>">
                                            <input type="number" step="0.01" name="curr_value" value="<?= $r['wa_curr'] ?>" class="w-20 bg-blue-50 border border-blue-100 rounded-lg px-2 py-1 text-xs font-bold text-blue-700 focus:outline-none focus:border-blue-500">
                                            <button type="submit" class="text-blue-500 hover:scale-110 transition-transform"><i class="bi bi-save-fill"></i></button>
                                        </form>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <?php if($r['inv_id']): ?>
                                            <a href="print_invoice.php?id=<?= $r['inv_id'] ?>" target="_blank" class="text-indigo-600 hover:underline font-black text-xs">#<?= $r['inv_id'] ?> VIEW</a>
                                        <?php else: ?>
                                            <span class="text-[10px] text-slate-300 font-bold">CHƯA TẠO</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-8 py-6">
                                        <?php if($r['inv_status'] == 'Paid'): ?>
                                            <span class="bg-emerald-50 text-emerald-600 text-[10px] font-black px-3 py-1 rounded-full border border-emerald-100 italic">ĐÃ ĐÓNG</span>
                                        <?php elseif($r['inv_status'] == 'Unpaid'): ?>
                                            <span class="bg-rose-50 text-rose-600 text-[10px] font-black px-3 py-1 rounded-full border border-rose-100 italic">CHƯA ĐÓNG</span>
                                        <?php else: ?>
                                            <span class="text-slate-300">--</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <div class="flex justify-end gap-2 text-xs">
                                            <a href="?month=<?= $month ?>&year=<?= $year ?>&action=generate&room=<?= $r['no_kamar'] ?>" class="bg-white border border-slate-200 text-slate-600 px-3 py-2 rounded-xl font-black hover:bg-slate-50 transition-colors">LẬP HĐ</a>
                                            <?php if($r['inv_status'] == 'Unpaid'): ?>
                                                <a href="?month=<?= $month ?>&year=<?= $year ?>&action=pay&id=<?= $r['inv_id'] ?>" class="bg-indigo-600 text-white px-3 py-2 rounded-xl font-black shadow-lg shadow-indigo-600/20 hover:bg-indigo-700 transition-colors">XÁC NHẬN</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="bg-indigo-900 rounded-[2.5rem] p-10 text-white shadow-2xl relative overflow-hidden">
                    <div class="relative z-10 grid grid-cols-1 md:grid-cols-3 gap-10">
                        <div>
                            <h4 class="text-indigo-300 font-black text-[10px] uppercase tracking-widest mb-2">Biểu Giá Hiện Tại</h4>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center bg-white/10 p-4 rounded-2xl">
                                    <span class="text-sm font-bold">1 kWh Điện</span>
                                    <span class="font-black"><?= number_format($rate_electric) ?>đ</span>
                                </div>
                                <div class="flex justify-between items-center bg-white/10 p-4 rounded-2xl">
                                    <span class="text-sm font-bold">1 m³ Nước</span>
                                    <span class="font-black"><?= number_format($rate_water) ?>đ</span>
                                </div>
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <h4 class="text-indigo-300 font-black text-[10px] uppercase tracking-widest mb-2">Hướng dẫn sử dụng</h4>
                            <ol class="text-sm text-indigo-100/80 space-y-2 list-decimal list-inside">
                                <li>Nhập số điện/nước đo được tại đồng hồ vào ô tương ứng.</li>
                                <li>Nhấn biểu tượng <i class="bi bi-save-fill"></i> để lưu (hệ thống tự lấy số tháng trước làm số đầu).</li>
                                <li>Nhấn nút <b>LẬP HĐ</b> để hệ thống tính toán tiền phòng + điện + nước.</li>
                                <li>Sau khi khách thanh toán, nhấn <b>XÁC NHẬN</b> để chốt hóa đơn.</li>
                            </ol>
                        </div>
                    </div>
                    <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
