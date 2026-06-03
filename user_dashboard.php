<?php
require_once 'cek-akses.php';
checkUser();

$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// 1. Fetch current user data
$stmt_user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt_user->execute([$user_id]);
$current_user = $stmt_user->fetch();

// 2. Fetch maintenance requests
$stmt_maint = $pdo->prepare("SELECT * FROM maintenance_requests WHERE user_id = ? ORDER BY created_at DESC");
$stmt_maint->execute([$user_id]);
$my_requests = $stmt_maint->fetchAll();

// 3. Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    
    try {
        $update = $pdo->prepare("UPDATE users SET fullname = ?, phone = ? WHERE id = ?");
        $update->execute([$fullname, $phone, $user_id]);
        $_SESSION['fullname'] = $fullname; // Update session
        $success_msg = "Cập nhật thông tin cá nhân thành công!";
        header("Refresh:2");
    } catch (PDOException $e) {
        $error_msg = "Lỗi cập nhật: " . $e->getMessage();
    }
}

// 4. Handle Maintenance Request Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_repair'])) {
    $room_number = trim($_POST['room_number']);
    $issue_type = $_POST['issue_type'];
    $description = trim($_POST['description']);
    
    if (!empty($room_number) && !empty($description)) {
        try {
            $ins = $pdo->prepare("INSERT INTO maintenance_requests (user_id, room_number, issue_type, description, status) VALUES (?, ?, ?, ?, 'Pending')");
            $ins->execute([$user_id, $room_number, $issue_type, $description]);
            $success_msg = "Yêu cầu báo hỏng đã được gửi thành công!";
            header("Refresh:2");
        } catch (PDOException $e) {
            $error_msg = "Lỗi gửi yêu cầu: " . $e->getMessage();
        }
    } else {
        $error_msg = "Vui lòng nhập đầy đủ thông tin báo hỏng.";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng điều khiển Khách thuê - AZMEDIA247</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .glass-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2); }
        .rule-item { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 12px; }
        .rule-dot { width: 8px; height: 8px; background: #6366f1; border-radius: 50%; margin-top: 6px; flex-shrink: 0; }
    </style>
</head>
<body class="min-h-screen">
    <!-- Header -->
    <header class="bg-white/80 sticky top-0 z-40 border-b border-slate-100 backdrop-blur-md">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="bg-indigo-600 text-white p-2 rounded-xl shadow-lg shadow-indigo-200">
                    <i class="bi bi-house-door-fill text-xl"></i>
                </div>
                <div>
                    <span class="text-xl font-black text-slate-800 block leading-none">AZMEDIA247</span>
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Customer Portal</span>
                </div>
            </div>
            <div class="flex items-center gap-6">
                <div class="text-right hidden sm:block">
                    <p class="text-xs text-slate-400 font-semibold uppercase tracking-tighter">Chào mừng quay lại</p>
                    <p class="text-sm font-bold text-slate-800"><?= htmlspecialchars($current_user['fullname']) ?></p>
                </div>
                <div class="h-10 w-10 rounded-full border-2 border-slate-100 p-0.5">
                    <img src="<?= htmlspecialchars($current_user['avatar']) ?>" class="h-full w-full rounded-full object-cover">
                </div>
                <a href="logout.php" class="bg-red-50 text-red-500 hover:bg-red-100 px-4 py-2 rounded-xl text-sm font-bold transition-all border border-red-100">
                    <i class="bi bi-power"></i> Thoát
                </a>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-6 py-8">
        <?php if ($success_msg): ?>
            <div class="animate__animated animate__fadeIn border-l-4 border-emerald-500 bg-emerald-50 p-4 mb-8 rounded-r-2xl flex items-center gap-3">
                <i class="bi bi-check-circle-fill text-emerald-500 text-xl"></i>
                <p class="text-emerald-700 font-bold"><?= $success_msg ?></p>
            </div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
            <div class="animate__animated animate__shakeX border-l-4 border-red-500 bg-red-50 p-4 mb-8 rounded-r-2xl flex items-center gap-3">
                <i class="bi bi-exclamation-octagon-fill text-red-500 text-xl"></i>
                <p class="text-red-700 font-bold"><?= $error_msg ?></p>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
            <!-- Left Column: Navigation & Profile -->
            <div class="xl:col-span-3 space-y-6">
                <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100 text-center">
                    <div class="relative inline-block mb-4">
                        <img src="<?= htmlspecialchars($current_user['avatar']) ?>" class="w-24 h-24 rounded-full border-4 border-indigo-50 mx-auto object-cover">
                        <div class="absolute bottom-0 right-0 bg-indigo-600 text-white rounded-full p-1 border-2 border-white"><i class="bi bi-camera"></i></div>
                    </div>
                    <h2 class="text-xl font-bold text-slate-800"><?= htmlspecialchars($current_user['fullname']) ?></h2>
                    <p class="text-slate-400 text-xs font-semibold mb-6"><?= htmlspecialchars($current_user['username']) ?></p>
                    
                    <button onclick="toggleModal('modal-profile')" class="w-full bg-slate-50 hover:bg-slate-100 text-slate-700 font-bold py-3 rounded-2xl transition-all flex items-center justify-center gap-2">
                        <i class="bi bi-pencil-square"></i> Cập nhật hồ sơ
                    </button>
                </div>

                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 space-y-2">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest px-2 mb-4">Phòng & Hợp đồng</h3>
                    <div class="flex justify-between items-center bg-slate-50 p-3 rounded-2xl">
                        <span class="text-xs font-bold text-slate-500">Số phòng:</span>
                        <span class="text-sm font-black text-indigo-600">P.102 (Liên hệ Admin)</span>
                    </div>
                    <div class="flex justify-between items-center bg-slate-50 p-3 rounded-2xl">
                        <span class="text-xs font-bold text-slate-500">Khu vực:</span>
                        <span class="text-xs font-bold text-slate-700">Trọ Sinh Viên 1</span>
                    </div>
                </div>

                <div class="bg-indigo-700 p-8 rounded-[2rem] text-white shadow-xl shadow-indigo-200">
                    <h3 class="text-lg font-black mb-2">Hỗ trợ khẩn cấp?</h3>
                    <p class="text-indigo-200 text-xs mb-6 font-medium leading-relaxed">Gặp sự cố điện nước hay trục trặc đồ dùng? Hãy gửi yêu cầu báo hỏng ngay.</p>
                    <button onclick="toggleModal('modal-repair')" class="w-full bg-white text-indigo-700 font-black py-4 rounded-2xl shadow-lg transition-all hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-2">
                        <i class="bi bi-tools"></i> Báo hỏng ngay
                    </button>
                </div>
            </div>

            <!-- Right Column: Dashboard Content -->
            <div class="xl:col-span-9 space-y-8">
                <!-- Welcome Section -->
                <div class="bg-white p-10 rounded-[2.5rem] shadow-sm border border-slate-100 relative overflow-hidden">
                    <div class="relative z-10">
                        <h1 class="text-4xl font-black text-slate-900 tracking-tight mb-4">Chào buổi sáng, <span class="text-indigo-600"><?= explode(' ', $current_user['fullname'])[0] ?></span> đến với website của QUANIT</h1>
                        <p class="text-slate-500 max-w-lg leading-relaxed">Mọi thứ đều ổn định. Bạn không có hóa đơn quá hạn và lịch sử thanh toán rất tốt. Tiếp tục duy trì nhé!</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-8">
                            <div onclick="toggleModal('modal-rules')" class="bg-slate-50 p-6 rounded-3xl group hover:bg-indigo-50 transition-all cursor-pointer">
                                <div class="bg-white w-12 h-12 rounded-2xl flex items-center justify-center text-indigo-600 shadow-sm mb-4 group-hover:scale-110 transition-all"><i class="bi bi-journal-text text-xl"></i></div>
                                <h4 class="font-bold text-slate-800">Quy định nhà trọ</h4>
                                <p class="text-xs text-slate-400 mt-1">Xem các điều khoản và quy định nội bộ.</p>
                            </div>
                            <div onclick="toggleModal('modal-payment')" class="bg-slate-50 p-6 rounded-3xl group hover:bg-emerald-50 transition-all cursor-pointer">
                                <div class="bg-white w-12 h-12 rounded-2xl flex items-center justify-center text-emerald-600 shadow-sm mb-4 group-hover:scale-110 transition-all"><i class="bi bi-wallet2 text-xl"></i></div>
                                <h4 class="font-bold text-slate-800">Cổng thanh toán</h4>
                                <p class="text-xs text-slate-400 mt-1">Quét mã QR để đóng tiền nhanh chóng.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Maintenance History -->
                <div class="bg-white p-10 rounded-[2.5rem] shadow-sm border border-slate-100">
                    <div class="flex justify-between items-center mb-8">
                        <h2 class="text-2xl font-black text-slate-900 tracking-tight">Lịch sử sự cố & Báo hỏng</h2>
                        <span class="text-xs font-bold text-slate-400 uppercase">Gần đây nhất</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-[10px] text-slate-400 font-black uppercase tracking-[0.2em] border-b border-slate-50">
                                    <th class="pb-4 px-4">Loại sự cố</th>
                                    <th class="pb-4">Mô tả</th>
                                    <th class="pb-4">Ngày gửi</th>
                                    <th class="pb-4 text-center">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <?php if (count($my_requests) > 0): ?>
                                    <?php foreach ($my_requests as $req): ?>
                                        <tr class="border-b border-slate-50/50 hover:bg-slate-50/50 transition-all group">
                                            <td class="py-5 px-4 font-bold text-slate-700"><?= htmlspecialchars($req['issue_type']) ?></td>
                                            <td class="py-5 text-slate-500 max-w-xs truncate"><?= htmlspecialchars($req['description']) ?></td>
                                            <td class="py-5 text-slate-400 font-medium"><?= date('d/m/Y', strtotime($req['created_at'])) ?></td>
                                            <td class="py-5 text-center">
                                                <?php if ($req['status'] === 'Pending'): ?>
                                                    <span class="bg-orange-100 text-orange-600 px-3 py-1 rounded-full text-[10px] font-black uppercase">Chờ xử lý</span>
                                                <?php elseif ($req['status'] === 'In Progress'): ?>
                                                    <span class="bg-blue-100 text-blue-600 px-3 py-1 rounded-full text-[10px] font-black uppercase">Đang chữa</span>
                                                <?php else: ?>
                                                    <span class="bg-emerald-100 text-emerald-600 px-3 py-1 rounded-full text-[10px] font-black uppercase">Đã xong</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="py-12 text-center">
                                            <div class="bg-slate-50 rounded-3xl p-10 opacity-60">
                                                <i class="bi bi-emoji-smile text-4xl mb-2 inline-block"></i>
                                                <p class="font-bold text-slate-500">Chưa có sự cố nào được ghi nhận.</p>
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
    </main>

    <!-- Modal: Báo hỏng -->
    <div id="modal-repair" class="hidden fixed inset-0 z-50 overflow-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-6 animate__animated animate__fadeIn">
        <div class="bg-white w-full max-w-lg rounded-[2.5rem] shadow-2xl p-10 animate__animated animate__zoomIn">
            <div class="flex justify-between items-center mb-8">
                <h3 class="text-2xl font-black text-slate-900 leading-none">Báo hỏng & Sửa chữa</h3>
                <button onclick="toggleModal('modal-repair')" class="text-slate-300 hover:text-slate-600 text-2xl"><i class="bi bi-x-circle-fill"></i></button>
            </div>
            <form method="POST" action="" class="space-y-6">
                <input type="hidden" name="submit_repair" value="1">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2 ml-1">Số phòng</label>
                    <input type="text" name="room_number" class="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:bg-white focus:border-indigo-600 outline-none transition-all font-bold" placeholder="VD: 102" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2 ml-1">Loại sự cố</label>
                    <select name="issue_type" class="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:bg-white focus:border-indigo-600 outline-none transition-all font-bold appearance-none">
                        <option>Hỏng Điện</option>
                        <option>Hỏng Nước</option>
                        <option>Đồ dùng / Nội thất</option>
                        <option>Internet / Wifi</option>
                        <option>Vệ sinh / Môi trường</option>
                        <option>Khác</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2 ml-1">Mô tả chi tiết</label>
                    <textarea name="description" rows="4" class="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:bg-white focus:border-indigo-600 outline-none transition-all font-medium" placeholder="Bạn hãy mô tả rõ lỗi để kỹ thuật chuẩn bị linh kiện..." required></textarea>
                </div>
                <button type="submit" class="w-full bg-indigo-600 text-white font-black py-5 rounded-2xl shadow-xl shadow-indigo-100 transition-all transform active:scale-95">Gửi yêu cầu ngay</button>
            </form>
        </div>
    </div>

    <!-- Modal: Profile Update -->
    <div id="modal-profile" class="hidden fixed inset-0 z-50 overflow-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-6 animate__animated animate__fadeIn">
        <div class="bg-white w-full max-w-lg rounded-[2.5rem] shadow-2xl p-10 animate__animated animate__zoomIn">
            <div class="flex justify-between items-center mb-8">
                <h3 class="text-2xl font-black text-slate-900 leading-none">Chỉnh sửa hồ sơ</h3>
                <button onclick="toggleModal('modal-profile')" class="text-slate-300 hover:text-slate-600 text-2xl"><i class="bi bi-x-circle-fill"></i></button>
            </div>
            <form method="POST" action="" class="space-y-6">
                <input type="hidden" name="update_profile" value="1">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2 ml-1">Họ và Tên</label>
                    <input type="text" name="fullname" value="<?= htmlspecialchars($current_user['fullname']) ?>" class="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:bg-white focus:border-indigo-600 outline-none transition-all font-bold" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2 ml-1">Số điện thoại</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($current_user['phone']) ?>" class="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:bg-white focus:border-indigo-600 outline-none transition-all font-bold" placeholder="0xxx.xxx.xxx">
                </div>
                <button type="submit" class="w-full bg-indigo-600 text-white font-black py-5 rounded-2xl shadow-xl shadow-indigo-100 transition-all transform active:scale-95">Lưu thay đổi</button>
            </form>
        </div>
    </div>

    <!-- Modal: Quy định nhà trọ -->
    <div id="modal-rules" class="hidden fixed inset-0 z-50 overflow-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-6 animate__animated animate__fadeIn">
        <div class="bg-white w-full max-w-2xl rounded-[2.5rem] shadow-2xl p-10 animate__animated animate__zoomIn">
            <div class="flex justify-between items-center mb-8">
                <h3 class="text-2xl font-black text-slate-900 leading-none">Nội quy & Quy định</h3>
                <button onclick="toggleModal('modal-rules')" class="text-slate-300 hover:text-slate-600 text-2xl"><i class="bi bi-x-circle-fill"></i></button>
            </div>
            <div class="space-y-4 max-h-[60vh] overflow-y-auto pr-4">
                <div class="rule-item">
                    <div class="rule-dot"></div>
                    <p class="text-slate-600 text-sm leading-relaxed font-medium"><strong class="text-slate-900">An ninh trật tự:</strong> Giữ yên lặng sau 23h00. Không tụ tập ăn nhậu, gây gổ làm mất trật tự trong khu vực chung.</p>
                </div>
                <div class="rule-item">
                    <div class="rule-dot"></div>
                    <p class="text-slate-600 text-sm leading-relaxed font-medium"><strong class="text-slate-900">Phòng cháy chữa cháy:</strong> Không đun nấu bằng bếp than/củi. Tắt các thiết bị điện khi ra khỏi phòng để đảm bảo an toàn.</p>
                </div>
                <div class="rule-item">
                    <div class="rule-dot"></div>
                    <p class="text-slate-600 text-sm leading-relaxed font-medium"><strong class="text-slate-900">Tiếp khách:</strong> Khách đến thăm phải về trước 22h30. Trường hợp khách ở lại qua đêm phải báo cáo và đăng ký với Admin.</p>
                </div>
                <div class="rule-item">
                    <div class="rule-dot"></div>
                    <p class="text-slate-600 text-sm leading-relaxed font-medium"><strong class="text-slate-900">Vệ sinh chung:</strong> Bỏ rác đúng nơi quy định. Giữ gìn vệ sinh khu vực hành lang, nhà xe và sân chung.</p>
                </div>
                <div class="rule-item">
                    <div class="rule-dot"></div>
                    <p class="text-slate-600 text-sm leading-relaxed font-medium"><strong class="text-slate-900">Thanh toán hóa đơn:</strong> Tiền nhà và chi phí dịch vụ cần được thanh toán trước ngày 05 hàng tháng.</p>
                </div>
            </div>
            <button onclick="toggleModal('modal-rules')" class="w-full bg-slate-900 text-white font-black py-4 rounded-2xl mt-8 shadow-xl transition-all active:scale-95">Đã hiểu rõ nội quy</button>
        </div>
    </div>

    <!-- Modal: Cổng thanh toán -->
    <div id="modal-payment" class="hidden fixed inset-0 z-50 overflow-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-6 animate__animated animate__fadeIn">
        <div class="bg-white w-full max-w-lg rounded-[2.5rem] shadow-2xl p-10 animate__animated animate__zoomIn text-center">
            <div class="flex justify-between items-center mb-8">
                <h3 class="text-2xl font-black text-slate-900 leading-none">Thanh toán hóa đơn</h3>
                <button onclick="toggleModal('modal-payment')" class="text-slate-300 hover:text-slate-600 text-2xl"><i class="bi bi-x-circle-fill"></i></button>
            </div>
            
            <div class="bg-slate-50 p-8 rounded-[2rem] border border-slate-100 mb-6">
                <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4 text-left">Quét mã VietQR</p>
                <img src="qr_payment_mockup_1774994720149.png" class="w-48 h-48 mx-auto rounded-3xl shadow-lg border-2 border-white mb-6">
                
                <div class="space-y-4 text-left">
                    <div class="bg-white p-4 rounded-2xl border border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Ngân hàng Quân đội (MB)</p>
                        <p class="text-lg font-black text-slate-900">333322111</p>
                    </div>
                    <div class="bg-white p-4 rounded-2xl border border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Chủ tài khoản</p>
                        <p class="text-lg font-black text-slate-900">DOAN MINH QUAN</p>
                    </div>
                    <div class="bg-white p-4 rounded-2xl border border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Nội dung chuyển khoản</p>
                        <p class="text-sm font-black text-indigo-600 uppercase">TROAZ [PHONG] [THANG]</p>
                    </div>
                </div>
            </div>
            
            <p class="text-[10px] text-slate-400 font-bold px-6 leading-relaxed">Vui lòng chụp lại màn hình giao dịch sau khi chuyển khoản thành công để đối chiếu khi cần thiết.</p>
        </div>
    </div>

    <!-- Floating Chat Button -->
    <div id="chat-bubble" class="fixed bottom-8 right-8 z-50">
        <button onclick="toggleChat()" class="bg-indigo-600 text-white w-14 h-14 rounded-full shadow-2xl flex items-center justify-center hover:scale-110 transition-all active:scale-95 group">
            <i class="bi bi-chat-dots-fill text-2xl group-hover:animate-pulse"></i>
            <div id="chat-notification" class="hidden absolute top-0 right-0 w-4 h-4 bg-red-500 rounded-full border-2 border-white animate-bounce"></div>
        </button>
    </div>

    <!-- Chat Box -->
    <div id="chat-box" class="hidden fixed bottom-24 right-8 z-50 w-80 h-96 bg-white rounded-3xl shadow-2xl flex flex-col border border-slate-100 animate__animated animate__slideInUp">
        <div class="p-4 bg-indigo-600 text-white rounded-t-3xl flex justify-between items-center shadow-lg">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center font-black">A</div>
                <div>
                    <h5 class="text-sm font-black leading-none">Hỗ trợ Admin</h5>
                    <span class="text-[10px] text-indigo-200 uppercase font-black">Đang trực tuyến</span>
                </div>
            </div>
            <button onclick="toggleChat()" class="text-indigo-200 hover:text-white"><i class="bi bi-x-circle-fill"></i></button>
        </div>
        <div id="chat-messages" class="flex-1 p-4 overflow-y-auto space-y-4 text-xs bg-slate-50">
            <!-- Messages will load here -->
        </div>
        <div class="p-4 border-t border-slate-50 flex gap-2">
            <input type="text" id="chat-input" class="flex-1 px-4 py-2 bg-slate-50 border border-slate-100 rounded-2xl outline-none focus:border-indigo-600 text-xs" placeholder="Nhập tin nhắn...">
            <button onclick="sendMessage()" class="bg-indigo-600 text-white p-2 rounded-xl hover:bg-indigo-700 active:scale-90 transition-all">
                <i class="bi bi-send-fill"></i>
            </button>
        </div>
    </div>

    <script>
        let adminId = null;
        let isChatOpen = false;

        function toggleModal(modalID) {
            const modal = document.getElementById(modalID);
            modal.classList.toggle('hidden');
            modal.classList.toggle('flex');
        }

        function toggleChat() {
            const box = document.getElementById('chat-box');
            isChatOpen = !isChatOpen;
            box.classList.toggle('hidden');
            if (isChatOpen) {
                fetchMessages();
                document.getElementById('chat-notification').classList.add('hidden');
            }
        }

        async function initChat() {
            try {
                const res = await fetch('handle_chat.php?action=get_admin');
                const data = await res.json();
                adminId = data.admin_id || 1;
            } catch (e) {
                console.error("Chat Init Error:", e);
                adminId = 1; // Fallback
            }
            
            // Auto refresh
            setInterval(() => { if (isChatOpen) fetchMessages(); }, 3000);
            
            document.getElementById('chat-input').addEventListener('keypress', (e) => {
                if (e.key === 'Enter') sendMessage();
            });
        }

        async function fetchMessages() {
            if (!adminId) return;
            try {
                const res = await fetch(`handle_chat.php?action=fetch&with_id=${adminId}`);
                const json = await res.json();
                if (json.status === 'success') {
                    const container = document.getElementById('chat-messages');
                    let html = '';
                    json.data.forEach(m => {
                        const isMe = m.sender_id == <?= $user_id ?>;
                        html += `
                            <div class="flex ${isMe ? 'justify-end' : 'justify-start'}">
                                <div class="max-w-[80%] px-4 py-2 rounded-2xl ${isMe ? 'bg-indigo-600 text-white rounded-br-none' : 'bg-white text-slate-700 shadow-sm border border-slate-100 rounded-bl-none'}">
                                    ${m.message}
                                </div>
                            </div>
                        `;
                    });
                    container.innerHTML = html;
                    container.scrollTop = container.scrollHeight;
                }
            } catch (e) {}
        }

        async function sendMessage() {
            const input = document.getElementById('chat-input');
            const msg = input.value.trim();
            if (!msg || !adminId) return;

            const fd = new FormData();
            fd.append('receiver_id', adminId);
            fd.append('message', msg);

            try {
                const res = await fetch('handle_chat.php?action=send', {
                    method: 'POST',
                    body: fd
                });
                const data = await res.json();
                if (data.status === 'success') {
                    input.value = '';
                    fetchMessages();
                }
            } catch (e) {
                console.error("Send Error:", e);
            }
        }

        document.addEventListener('DOMContentLoaded', initChat);
    </script>
</body>
</html>
