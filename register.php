<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($fullname) || empty($password) || empty($confirm_password)) {
        $error = "Vui lòng nhập đầy đủ thông tin.";
    } elseif ($password !== $confirm_password) {
        $error = "Mật khẩu xác nhận không khớp.";
    } elseif (strlen($password) < 6) {
        $error = "Mật khẩu phải có ít nhất 6 ký tự.";
    } else {
        try {
            // Check if username (email) exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->rowCount() > 0) {
                $error = "Tên đăng nhập (Email) này đã được sử dụng.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $role = 'user'; // Default role is user
                $room_number = $_POST['room_number'] ?? null;

                $stmt = $pdo->prepare("INSERT INTO users (username, password, fullname, phone, role, room_number) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$username, $hashed_password, $fullname, $phone, $role, $room_number])) {
                    $success = "Đăng ký tài khoản thành công! Đang chuyển đến trang đăng nhập...";
                    header("refresh:2;url=login.php");
                } else {
                    $error = "Lỗi kỹ thuật, không thể tạo tài khoản lúc này.";
                }
            }
        } catch (PDOException $e) {
            $error = "Lỗi hệ thống: " . $e->getMessage();
        }
    }
}

// Fetch all rooms for the selection
$available_rooms = $pdo->query("SELECT no_kamar, nama_kost FROM data_kamar ORDER BY no_kamar ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản | AZMEDIA247</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">
    <div class="bg-white w-full max-w-lg rounded-[2.5rem] shadow-2xl shadow-slate-200/50 p-10 border border-slate-100 animate__animated animate__fadeIn">
        <div class="text-center mb-10">
            <div class="bg-indigo-600 text-white w-16 h-16 flex items-center justify-center rounded-2xl mx-auto mb-4 shadow-lg shadow-indigo-200">
                <i class="bi bi-person-plus-fill text-3xl"></i>
            </div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Tạo tài khoản mới</h1>
            <p class="text-slate-500 text-sm mt-2">Dành cho khách hàng thuê phòng của AZMEDIA247</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-100 text-red-600 px-4 py-3 rounded-2xl mb-6 text-sm flex items-center gap-3">
                <i class="bi bi-exclamation-circle-fill"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-emerald-50 border border-emerald-100 text-emerald-600 px-4 py-3 rounded-2xl mb-6 text-sm flex items-center gap-3">
                <i class="bi bi-check-circle-fill"></i> <?= $success ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-700 text-xs font-bold uppercase tracking-wider mb-2 ml-1">Họ và Tên</label>
                    <input type="text" name="fullname" class="w-full px-4 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none" placeholder="Quân Media" required>
                </div>
                <div>
                    <label class="block text-slate-700 text-xs font-bold uppercase tracking-wider mb-2 ml-1">Số điện thoại</label>
                    <input type="text" name="phone" class="w-full px-4 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none" placeholder="0xxx.xxx.xxx">
                </div>
            </div>

            <div>
                <label class="block text-slate-700 text-xs font-bold uppercase tracking-wider mb-2 ml-1">Tên đăng nhập (Email)</label>
                <div class="relative">
                    <i class="bi bi-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="email" name="username" class="w-full pl-12 pr-4 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none" placeholder="email@vi-du.com" required>
                </div>
            </div>

            <div>
                <label class="block text-slate-700 text-xs font-bold uppercase tracking-wider mb-2 ml-1">Chọn phòng bạn ở</label>
                <div class="relative">
                    <i class="bi bi-door-open absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <select name="room_number" class="w-full pl-12 pr-4 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none appearance-none" required>
                        <option value="">-- Chọn số phòng của bạn --</option>
                        <?php foreach($available_rooms as $rm): ?>
                            <option value="<?= $rm['no_kamar'] ?>"><?= $rm['no_kamar'] ?> (<?= $rm['nama_kost'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-700 text-xs font-bold uppercase tracking-wider mb-2 ml-1">Mật khẩu</label>
                    <input type="password" name="password" class="w-full px-4 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none" placeholder="••••••••" required>
                </div>
                <div>
                    <label class="block text-slate-700 text-xs font-bold uppercase tracking-wider mb-2 ml-1">Xác nhận</label>
                    <input type="password" name="confirm_password" class="w-full px-4 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-2xl shadow-xl shadow-indigo-100 transition-all transform active:scale-95 mt-4">Đăng ký ngay</button>
        </form>

        <div class="mt-8 text-center">
            <p class="text-sm text-slate-500">Đã có tài khoản? <a href="login.php" class="text-indigo-600 font-bold hover:underline">Đăng nhập tại đây</a></p>
        </div>
    </div>
</body>
</html>
