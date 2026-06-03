<?php
require_once 'cek-akses.php';
checkAdmin();

$success_msg = '';
$error_msg = '';

// 1. Handle Maintenance Status Update
if (isset($_GET['update_status']) && isset($_GET['req_id'])) {
    $new_status = $_GET['update_status'];
    $req_id = $_GET['req_id'];
    
    $upd = $pdo->prepare("UPDATE maintenance_requests SET status = ? WHERE id = ?");
    $upd->execute([$new_status, $req_id]);
    $success_msg = "Cập nhật trạng thái sự cố thành công!";
}

// 2. Handle User Deletion
if (isset($_GET['delete_user'])) {
    $uid = $_GET['delete_user'];
    if ($uid != $_SESSION['user_id']) { // Don't delete yourself
        $del = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $del->execute([$uid]);
        $success_msg = "Đã xóa tài khoản người dùng thành công!";
    }
}

// 3. Fetch Statistics
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$pending_maint = $pdo->query("SELECT COUNT(*) FROM maintenance_requests WHERE status = 'Pending'")->fetchColumn();
$total_rooms = $pdo->query("SELECT COUNT(*) FROM data_kamar")->fetchColumn();

// 4. Fetch Users List
$users_list = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

// 5. Fetch Maintenance Requests List
$maint_requests = $pdo->query("SELECT m.*, u.fullname as requester 
                               FROM maintenance_requests m 
                               JOIN users u ON m.user_id = u.id 
                               ORDER BY m.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản trị Hệ thống - AZMEDIA247</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="min-h-screen">
    <!-- Navbar Admin -->
    <nav class="bg-slate-900 text-white px-8 py-4 flex justify-between items-center sticky top-0 z-50 shadow-2xl">
        <div class="flex items-center gap-4">
            <div class="bg-indigo-600 p-2 rounded-xl text-white font-black">AZ</div>
            <h1 class="text-xl font-black tracking-tight">HỆ THỐNG QUẢN TRỊ <span class="text-indigo-400">HOSTEL</span></h1>
        </div>
        <div class="flex items-center gap-6">
            <div class="text-right hidden sm:block">
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Administrator</p>
                <p class="text-sm font-black text-white"><?= htmlspecialchars($_SESSION['fullname']) ?></p>
            </div>
            <a href="logout.php" class="bg-white/10 hover:bg-red-500/20 text-white px-4 py-2 rounded-xl text-xs font-black transition-all border border-white/10 hover:border-red-500/20">
                <i class="bi bi-box-arrow-right"></i> THOÁT
            </a>
        </div>
    </nav>

    <div class="container mx-auto px-8 py-10">
        <?php if ($success_msg): ?>
            <div class="animate__animated animate__fadeIn border-l-4 border-emerald-500 bg-emerald-50 p-4 mb-10 rounded-r-2xl flex items-center gap-4">
                <i class="bi bi-check-circle-fill text-emerald-500 text-2xl"></i>
                <p class="text-emerald-800 font-black tracking-tight"><?= $success_msg ?></p>
            </div>
        <?php endif; ?>

        <!-- Key Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 group hover:shadow-xl hover:shadow-indigo-50 transition-all cursor-pointer">
                <div class="bg-indigo-50 w-14 h-14 rounded-2xl flex items-center justify-center text-indigo-600 mb-6 group-hover:scale-110 transition-all"><i class="bi bi-people-fill text-2xl"></i></div>
                <h3 class="text-4xl font-black text-slate-800 tracking-tighter mb-1"><?= $total_users ?></h3>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Khách đã đăng ký</p>
            </div>

            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 group hover:shadow-xl hover:shadow-orange-50 transition-all cursor-pointer">
                <div class="bg-orange-50 w-14 h-14 rounded-2xl flex items-center justify-center text-orange-600 mb-6 group-hover:scale-110 transition-all"><i class="bi bi-lightning-charge-fill text-2xl"></i></div>
                <h3 class="text-4xl font-black text-slate-800 tracking-tighter mb-1"><?= $pending_maint ?></h3>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Sự cố đang chờ</p>
            </div>

            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 group hover:shadow-xl hover:shadow-emerald-50 transition-all cursor-pointer">
                <div class="bg-emerald-50 w-14 h-14 rounded-2xl flex items-center justify-center text-emerald-600 mb-6 group-hover:scale-110 transition-all"><i class="bi bi-house-door-fill text-2xl"></i></div>
                <h3 class="text-4xl font-black text-slate-800 tracking-tighter mb-1"><?= $total_rooms ?></h3>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Tổng số phòng</p>
            </div>

            <div class="bg-slate-900 p-8 rounded-[2.5rem] text-white shadow-2xl shadow-indigo-100 text-center flex flex-col justify-center">
                <h3 class="text-lg font-black mb-4 leading-tight">VÀO PHƯƠNG THỨC<br>QUẢN LÝ CŨ</h3>
                <a href="dashboard.php" class="bg-indigo-600 text-white font-black py-4 rounded-2xl shadow-lg shadow-indigo-900 transition-all hover:scale-105 active:scale-95 block">QUẢN LÝ DỮ LIỆU</a>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-10">
            <!-- Section: Maintenance Requests -->
            <div class="xl:col-span-8 bg-white p-10 rounded-[3rem] shadow-sm border border-slate-100">
                <div class="flex justify-between items-center mb-10">
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight">Xử lý sự cố báo hỏng</h2>
                    <span class="text-xs font-black text-slate-300 uppercase tracking-[0.3em]">Hệ thống thời gian thực</span>
                </div>

                <div class="space-y-8">
                    <?php if (count($maint_requests) > 0): ?>
                        <?php foreach ($maint_requests as $req): ?>
                            <div class="flex items-start gap-6 bg-slate-50/50 p-6 rounded-[2rem] border border-slate-50 hover:bg-white hover:shadow-lg hover:shadow-slate-100 transition-all border-l-8 <?= $req['status'] === 'Pending' ? 'border-orange-500' : ($req['status'] === 'In Progress' ? 'border-blue-500' : 'border-emerald-500') ?>">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="text-sm font-black text-slate-800"><?= htmlspecialchars($req['requester']) ?></span>
                                        <span class="text-[10px] bg-slate-200 text-slate-600 px-2 py-0.5 rounded-full font-black uppercase">Phòng <?= htmlspecialchars($req['room_number']) ?></span>
                                    </div>
                                    <h4 class="text-lg font-bold text-slate-900 mb-2"><?= htmlspecialchars($req['issue_type']) ?></h4>
                                    <p class="text-sm text-slate-500 leading-relaxed italic mb-4">"<?= htmlspecialchars($req['description']) ?>."</p>
                                    <div class="text-[10px] text-slate-300 font-bold uppercase tracking-widest"><i class="bi bi-clock me-1"></i> Gửi ngày: <?= date('d/m/Y H:i', strtotime($req['created_at'])) ?></div>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <?php if ($req['status'] === 'Pending'): ?>
                                        <a href="?req_id=<?= $req['id'] ?>&update_status=In Progress" class="bg-blue-600 text-white text-[10px] font-black px-4 py-2 rounded-xl text-center shadow-lg shadow-blue-100 hover:scale-105 transition-all">TIẾP NHẬN</a>
                                    <?php elseif ($req['status'] === 'In Progress'): ?>
                                        <a href="?req_id=<?= $req['id'] ?>&update_status=Solved" class="bg-emerald-600 text-white text-[10px] font-black px-4 py-2 rounded-xl text-center shadow-lg shadow-emerald-100 hover:scale-105 transition-all">HOÀN TẤT</a>
                                    <?php else: ?>
                                        <span class="bg-slate-100 text-slate-400 text-[10px] font-black px-4 py-2 rounded-xl text-center cursor-default">ĐÃ XỬ LÝ XONG</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-20 opacity-30">
                            <i class="bi bi-shield-check text-6xl mb-4 inline-block"></i>
                            <p class="font-black text-xl">HIỆN KHÔNG CÓ SỰ CỐ NÀO</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Section: User Management -->
            <div class="xl:col-span-4 space-y-8">
                <div class="bg-white p-8 rounded-[3rem] shadow-sm border border-slate-100 h-full">
                    <h2 class="text-xl font-black text-slate-900 tracking-tight mb-8 px-2">Danh sách tài khoản</h2>
                    <div class="space-y-6 max-h-[1000px] overflow-y-auto pr-2 custom-scrollbar">
                        <?php foreach ($users_list as $user): ?>
                            <div class="flex items-center justify-between p-4 bg-slate-50/50 rounded-3xl group border border-transparent hover:border-indigo-100 hover:bg-white transition-all cursor-pointer" onclick="openChat(<?= $user['id'] ?>, '<?= addslashes($user['fullname']) ?>')">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 font-black border border-indigo-100 italic">
                                        <?= strtoupper(substr($user['fullname'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-black text-slate-800"><?= htmlspecialchars($user['fullname']) ?></h4>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest"><?= $user['role'] ?></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="?delete_user=<?= $user['id'] ?>" onclick="event.stopPropagation(); return confirm('Xóa người dùng này?')" class="text-slate-300 hover:text-red-500 transition-all opacity-0 group-hover:opacity-100"><i class="bi bi-trash3"></i></a>
                                    <?php endif; ?>
                                    <i class="bi bi-chat-fill text-indigo-300 opacity-0 group-hover:opacity-100 transition-all"></i>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>

    <!-- Chat Modal -->
    <div id="chat-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-6 animate__animated animate__fadeIn">
        <div class="bg-white w-full max-w-2xl h-[80vh] rounded-[3rem] shadow-2xl flex flex-col overflow-hidden animate__animated animate__zoomIn">
            <div class="p-6 bg-slate-900 text-white flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div id="active-chat-avatar" class="w-10 h-10 rounded-2xl bg-indigo-600 flex items-center justify-center font-black">?</div>
                    <div>
                        <h3 id="active-chat-name" class="font-black">Đang chọn hội thoại...</h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Hỗ trợ trực tuyến</p>
                    </div>
                </div>
                <button onclick="toggleChatModal()" class="text-slate-400 hover:text-white"><i class="bi bi-x-circle-fill text-2xl"></i></button>
            </div>
            <div id="admin-chat-messages" class="flex-1 p-8 overflow-y-auto space-y-4 bg-slate-50 flex flex-col">
                <div class="text-center py-20 text-slate-300">
                    <i class="bi bi-chat-left-dots text-4xl mb-4 inline-block"></i>
                    <p class="text-sm font-bold">Chọn một người dùng bên phải để bắt đầu chat</p>
                </div>
            </div>
            <div class="p-6 border-t border-slate-100 flex gap-4">
                <input type="text" id="admin-chat-input" class="flex-1 px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none focus:border-indigo-600 font-medium" placeholder="Nhập tin nhắn phản hồi...">
                <button onclick="sendAdminMessage()" class="bg-indigo-600 text-white px-8 py-4 rounded-2xl font-black shadow-lg shadow-indigo-100 hover:scale-105 active:scale-95 transition-all">GỬI</button>
            </div>
        </div>
    </div>

    <!-- Floating Chat Toggle for Admin -->
    <button onclick="toggleChatModal()" class="fixed bottom-8 right-8 bg-slate-900 text-white p-4 rounded-2xl shadow-2xl hover:scale-110 active:scale-95 transition-all z-40 border border-white/10">
        <i class="bi bi-chat-dots-fill text-2xl"></i>
    </button>

    <script>
        let currentChatUserId = null;
        let adminChatTimer = null;

        function toggleChatModal() {
            const modal = document.getElementById('chat-modal');
            modal.classList.toggle('hidden');
            modal.classList.toggle('flex');
            if (modal.classList.contains('flex')) {
                // Interval to refresh chat
                adminChatTimer = setInterval(() => { if(currentChatUserId) fetchAdminMessages(currentChatUserId); }, 3000);
            } else {
                clearInterval(adminChatTimer);
            }
        }

        function openChat(userId, userName) {
            currentChatUserId = userId;
            document.getElementById('active-chat-name').innerText = userName;
            document.getElementById('active-chat-avatar').innerText = userName.charAt(0).toUpperCase();
            if (document.getElementById('chat-modal').classList.contains('hidden')) {
                toggleChatModal();
            }
            fetchAdminMessages(userId);
        }

        async function fetchAdminMessages(userId) {
            const res = await fetch(`handle_chat.php?action=fetch&with_id=${userId}`);
            const json = await res.json();
            if (json.status === 'success') {
                const container = document.getElementById('admin-chat-messages');
                let html = '';
                json.data.forEach(m => {
                    const isMe = m.sender_id == <?= $_SESSION['user_id'] ?>;
                    html += `
                        <div class="flex ${isMe ? 'justify-end' : 'justify-start'} w-full">
                            <div class="max-w-[70%] px-6 py-3 rounded-3xl ${isMe ? 'bg-indigo-600 text-white rounded-br-none' : 'bg-white text-slate-700 shadow-sm border border-slate-100 rounded-bl-none'} text-sm font-medium">
                                ${m.message}
                            </div>
                        </div>
                    `;
                });
                container.innerHTML = html;
                container.scrollTop = container.scrollHeight;
            }
        }

        async function sendAdminMessage() {
            const input = document.getElementById('admin-chat-input');
            const msg = input.value.trim();
            if (!msg || !currentChatUserId) return;

            const fd = new FormData();
            fd.append('receiver_id', currentChatUserId);
            fd.append('message', msg);

            const res = await fetch('handle_chat.php?action=send', {
                method: 'POST',
                body: fd
            });
            const data = await res.json();
            if (data.status === 'success') {
                input.value = '';
                fetchAdminMessages(currentChatUserId);
            }
        }

        // Add onclick to user list items
        document.addEventListener('DOMContentLoaded', () => {
            const userItems = document.querySelectorAll('.xl\\:col-span-4 .flex.items-center.justify-between.p-4');
            userItems.forEach(item => {
                const name = item.querySelector('h4').innerText;
                // We need the ID, let's inject it into the DOM if not there
            });
            // Re-render user list with onclick in PHP is better
        });
    </script>
</body>
</html>
