<?php
require_once 'cek-akses.php';
$pdo = include 'config_minhquan.php';

if (!isset($_GET['id'])) die('No Invoice ID provided.');

$stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = ?");
$stmt->execute([$_GET['id']]);
$inv = $stmt->fetch();

if (!$inv) die('Invoice not found.');

// Fetch payment info if paid
$admin_name = 'Admin'; 
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hóa Đơn #<?= $inv['id'] ?> - P.<?= $inv['room_number'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #fff; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; background: #fff; }
            .invoice-wrap { box-shadow: none !important; border: 1px solid #eee; }
        }
    </style>
</head>
<body class="bg-slate-50 p-10">
    <div class="max-w-2xl mx-auto bg-white p-16 rounded-[2.5rem] shadow-2xl invoice-wrap relative overflow-hidden">
        <!-- Watermark/Decor -->
        <div class="absolute top-0 right-0 w-40 h-40 bg-indigo-50 rounded-bl-[10rem] -z-0 opacity-40"></div>
        
        <div class="relative z-10">
            <div class="flex justify-between items-start mb-16 px-4">
                <div>
                    <h1 class="text-4xl font-black text-slate-900 tracking-tighter mb-2 uppercase">Hóa Đơn</h1>
                    <p class="text-xs font-black text-indigo-600 tracking-[0.2em] mb-1">Mã Hóa Đơn: #<?= $inv['id'] ?></p>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tháng <?= $inv['month'] ?> / Năm <?= $inv['year'] ?></p>
                </div>
                <div class="text-right">
                    <h2 class="text-lg font-black text-slate-800 uppercase tracking-widest">AZMEDIA247</h2>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest leading-relaxed mt-2">Dịch Vụ Lưu Trú & Quản Lý Trọ<br>Thái Nguyên - Việt Nam</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-10 mb-16 px-4">
                <div class="bg-slate-50/50 p-6 rounded-3xl border border-slate-100/50">
                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Gửi tới khách thuê</h4>
                    <p class="text-lg font-black text-slate-800 tracking-tight leading-none mb-2 capitalize"><?= htmlspecialchars($inv['tenant_name']) ?></p>
                    <p class="text-sm font-bold text-indigo-600">Phòng <?= $inv['room_number'] ?></p>
                </div>
                <div class="p-6">
                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Trạng thái thanh toán</h4>
                    <?php if($inv['status'] == 'Paid'): ?>
                        <div class="flex items-center gap-2 text-emerald-500">
                            <i class="bi bi-patch-check-fill text-2xl"></i>
                            <span class="font-black text-sm uppercase tracking-widest">Đã thanh toán</span>
                        </div>
                        <p class="text-[9px] text-slate-400 mt-2 font-bold uppercase italic font-serif">Xác nhận ngày: <?= date('d/m/Y H:i', strtotime($inv['payment_date'])) ?></p>
                    <?php else: ?>
                        <div class="flex items-center gap-2 text-rose-500">
                            <i class="bi bi-clock-history text-2xl"></i>
                            <span class="font-black text-sm uppercase tracking-widest">Chưa thanh toán</span>
                        </div>
                        <p class="text-[9px] text-slate-400 mt-2 font-bold uppercase italic">Vui lòng thanh toán trước ngày 10 hàng tháng.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mb-10 px-4">
                <table class="w-full">
                    <thead>
                        <tr class="border-b-2 border-slate-900">
                            <th class="py-4 text-left text-[11px] font-black uppercase tracking-[0.25em]">Mục Thanh Toán</th>
                            <th class="py-4 text-center text-[11px] font-black uppercase tracking-[0.25em]">Số Lượng</th>
                            <th class="py-4 text-right text-[11px] font-black uppercase tracking-[0.25em]">Thành Tiền</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr>
                            <td class="py-6 min-w-[200px]">
                                <h5 class="text-sm font-black text-slate-800 leading-none mb-1 uppercase tracking-tight">Tiền Phòng</h5>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Giá thỏa thuận theo hợp đồng</p>
                            </td>
                            <td class="py-6 text-center text-sm font-bold text-slate-500">1 tháng</td>
                            <td class="py-6 text-right text-sm font-black text-slate-800"><?= number_format($inv['room_price']) ?>đ</td>
                        </tr>
                        <tr>
                            <td class="py-6">
                                <h5 class="text-sm font-black text-slate-800 leading-none mb-1 uppercase tracking-tight">Tiền Điện</h5>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Sử dụng: <?= $inv['electric_usage'] ?> kWh</p>
                            </td>
                            <td class="py-6 text-center text-sm font-bold text-slate-500"><?= number_format(3500) ?>đ / kWh</td>
                            <td class="py-6 text-right text-sm font-black text-slate-800"><?= number_format($inv['electric_cost']) ?>đ</td>
                        </tr>
                        <tr>
                            <td class="py-6">
                                <h5 class="text-sm font-black text-slate-800 leading-none mb-1 uppercase tracking-tight">Tiền Nước</h5>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Sử dụng: <?= $inv['water_usage'] ?> m³</p>
                            </td>
                            <td class="py-6 text-center text-sm font-bold text-slate-500"><?= number_format(15000) ?>đ / m³</td>
                            <td class="py-6 text-right text-sm font-black text-slate-800"><?= number_format($inv['water_cost']) ?>đ</td>
                        </tr>
                        <tr>
                            <td class="py-6">
                                <h5 class="text-sm font-black text-slate-800 leading-none mb-1 uppercase tracking-tight">Phí Dịch Vụ</h5>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Rác, Vệ sinh, Chung</p>
                            </td>
                            <td class="py-6 text-center text-sm font-bold text-slate-500">Gộp tháng</td>
                            <td class="py-6 text-right text-sm font-black text-slate-800"><?= number_format($inv['service_fee']) ?>đ</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end mb-16 px-4">
                <div class="w-full max-w-[280px]">
                    <div class="flex justify-between items-center py-6 border-y-4 border-slate-900">
                        <span class="text-[11px] font-black uppercase tracking-[0.35em]">Tổng Cộng</span>
                        <span class="text-2xl font-black text-indigo-600 tracking-tighter"><?= number_format($inv['total_amount']) ?>đ</span>
                    </div>
                </div>
            </div>

            <div class="flex justify-between items-end px-4">
                <div class="max-w-[200px]">
                    <h5 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 italic">Ghi chú & Phương thức thanh toán</h5>
                    <p class="text-[9px] text-slate-300 font-bold leading-relaxed tracking-tight">Chuyển khoản qua số tài khoản đính kèm hoặc đóng trực tiếp tại văn phòng tầng 1. Chân thành cảm ơn bạn!</p>
                </div>
                <div class="flex flex-col items-center">
                    <div class="w-24 h-24 bg-slate-50 border border-slate-100 rounded-2xl flex items-center justify-center text-slate-100 mb-2">
                        <!-- QR Code simulation -->
                        <i class="bi bi-qr-code text-[60px]"></i>
                    </div>
                    <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">QUÉT ĐỂ TRẢ</span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-2xl mx-auto flex justify-center gap-4 mt-10 no-print">
        <button onclick="window.print()" class="bg-indigo-600 text-white px-8 py-3 rounded-2xl font-black shadow-xl shadow-indigo-600/20 flex items-center gap-3 transition-transform hover:scale-105">
            <i class="bi bi-printer-fill"></i> IN HÓA ĐƠN
        </button>
        <button onclick="window.close()" class="bg-white text-slate-600 px-8 py-3 rounded-2xl font-black border border-slate-200">ĐÓNG</button>
    </div>
</body>
</html>
