<?php
require_once 'cek-akses.php';
require_once 'db.php';

// Ensure authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$my_id = $_SESSION['user_id'];
$my_role = $_SESSION['role'];
$my_name = $_SESSION['fullname'];

// If Admin, they need to select a user to chat. 
// If User, they always chat with the admin.
$admin_id = 1;
if ($my_role !== 'admin') {
    $admin = $pdo->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1")->fetch();
    $admin_id = $admin['id'] ?? 1;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống Chat - AZMEDIA247</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .message-bubble { max-width: 80%; padding: 12px 16px; border-radius: 18px; font-size: 14px; position: relative; }
        .sent { background: #4f46e5; color: white; margin-left: auto; border-bottom-right-radius: 4px; }
        .received { background: white; color: #1e293b; margin-right: auto; border-bottom-left-radius: 4px; border: 1px solid #e2e8f0; }
        #chat-messages::-webkit-scrollbar { width: 6px; }
        #chat-messages::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body class="bg-slate-50 h-screen overflow-hidden flex flex-col">

    <!-- Header -->
    <header class="bg-white border-b border-slate-100 px-8 py-4 flex justify-between items-center shrink-0 shadow-sm z-10">
        <div class="flex items-center gap-3">
            <a href="javascript:history.back()" class="bg-slate-50 hover:bg-slate-100 w-10 h-10 rounded-xl flex items-center justify-center text-slate-500 transition-all">
                <i class="bi bi-chevron-left"></i>
            </a>
            <div class="bg-indigo-600 p-2 rounded-xl text-white font-black text-xs">AZ</div>
            <h1 class="text-lg font-black text-slate-900 tracking-tight">HỆ THỐNG TRỰC TUYẾN</h1>
        </div>
        <div class="flex items-center gap-4">
            <div class="text-right hidden sm:block">
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest leading-none">VAI TRÒ: <?= strtoupper($my_role) ?></p>
                <p class="text-sm font-black text-slate-800"><?= $my_name ?></p>
            </div>
            <a href="logout.php" class="bg-red-50 text-red-500 w-10 h-10 rounded-xl flex items-center justify-center hover:bg-red-100 transition-all border border-red-50">
                <i class="bi bi-power"></i>
            </a>
        </div>
    </header>

    <main class="flex-1 overflow-hidden flex">
        
        <?php if ($my_role === 'admin'): ?>
        <!-- Left Panel: User List (Admin Only) -->
        <aside class="w-full md:w-[350px] bg-white border-r border-slate-100 flex flex-col shrink-0" id="user-list-sidebar">
            <div class="p-6 border-b border-slate-50">
                <h2 class="text-xl font-black text-slate-900 mb-4 px-1">Khách Hàng</h2>
                <div class="relative">
                    <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="text" placeholder="Tìm kiếm..." class="w-full bg-slate-50 border border-slate-100 rounded-2xl py-3 pl-10 pr-4 text-xs outline-none focus:border-indigo-600 transition-colors">
                </div>
            </div>
            <div id="admin-user-list" class="flex-1 overflow-y-auto p-4 space-y-2">
                <!-- User items will load here -->
            </div>
        </aside>
        <?php endif; ?>

        <!-- Right Panel: Chat Box -->
        <section class="flex-1 flex flex-col bg-slate-50 h-full relative" id="chat-section">
            <!-- Empty State for Admin -->
            <?php if ($my_role === 'admin'): ?>
            <div id="chat-empty-state" class="absolute inset-0 flex flex-col items-center justify-center p-8 text-center z-10 bg-slate-50">
                <div class="w-24 h-24 bg-indigo-50 text-indigo-500 rounded-[2rem] flex items-center justify-center text-4xl mb-6 animate-bounce">
                    <i class="bi bi-chat-quote-fill"></i>
                </div>
                <h3 class="text-2xl font-black text-slate-900 mb-2">Chào Admin!</h3>
                <p class="text-sm text-slate-400 max-w-xs leading-relaxed">Chọn một khách thuê ở danh sách bên trái để bắt đầu hỗ trợ nhé.</p>
            </div>
            <?php endif; ?>

            <!-- Chat Active Header -->
            <div id="chat-active-header" class="hidden shrink-0 bg-white/80 backdrop-blur-md px-8 py-4 border-b border-slate-100 flex items-center justify-between z-20">
                <div class="flex items-center gap-4">
                    <div id="target-avatar" class="w-12 h-12 rounded-full bg-indigo-600 flex items-center justify-center text-white font-black text-xl">?</div>
                    <div>
                        <h4 id="target-name" class="font-black text-slate-900 leading-tight">Admin</h4>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                            <span class="text-[10px] text-emerald-500 font-bold uppercase tracking-widest">Đang trực tuyến</span>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <?php if ($my_role === 'admin'): ?>
                    <button onclick="confirmDeleteHistory()" class="bg-red-50 text-red-500 px-4 py-2 rounded-xl text-[10px] font-black hover:bg-red-100 transition-all flex items-center gap-2">
                        <i class="bi bi-trash3-fill"></i>
                        XÓA LỊCH SỬ
                    </button>
                    <?php endif; ?>
                    <button class="w-10 h-10 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center hover:bg-slate-200"><i class="bi bi-telephone-fill"></i></button>
                    <button class="w-10 h-10 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center hover:bg-slate-200"><i class="bi bi-three-dots-vertical"></i></button>
                </div>
            </div>

            <!-- Messages List -->
            <div id="chat-messages" class="flex-1 overflow-y-auto p-8 space-y-6">
                <!-- Messages load here -->
            </div>

            <!-- Input Area -->
            <div class="p-6 bg-white border-t border-slate-100 shrink-0">
                <div class="flex items-center gap-4 bg-slate-50 border border-slate-100 p-2 rounded-2xl pr-3">
                    <button class="w-10 h-10 rounded-xl text-slate-400 hover:text-indigo-600 transition-colors"><i class="bi bi-plus-lg"></i></button>
                    <input type="text" id="chat-input" placeholder="Nhập tin nhắn của bạn..." class="flex-1 bg-transparent border-none outline-none text-sm text-slate-700 py-2">
                    <button onclick="sendMessage()" class="bg-indigo-600 hover:bg-indigo-700 text-white w-12 h-12 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-200 transition-all active:scale-95">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </div>
            </div>
        </section>
    </main>

    <script>
        let currentTargetId = <?= ($my_role === 'admin') ? 'null' : $admin_id ?>;
        const myId = <?= $my_id ?>;
        const myRole = '<?= $my_role ?>';

        function init() {
            if (myRole === 'admin') {
                fetchUsers();
                setInterval(fetchUsers, 5000);
            } else {
                // For users, the target is always admin
                selectUser(currentTargetId, 'Admin Hệ Thống');
            }
            
            setInterval(fetchMessages, 3000);
            
            // Enter key to send
            document.getElementById('chat-input').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') sendMessage();
            });
        }

        function fetchUsers() {
            fetch('handle_chat.php?action=list_users')
                .then(r => {
                    if (!r.ok) throw new Error("Network response was not ok");
                    return r.json();
                })
                .then(users => {
                    const box = document.getElementById('admin-user-list');
                    if (!box) return;
                    box.innerHTML = '';
                    if (!users.length) {
                        box.innerHTML = '<p class="p-4 text-xs text-slate-400 font-medium">Chưa có người dùng nào</p>';
                        return;
                    }
                    users.forEach(u => {
                        const item = document.createElement('div');
                        item.className = `p-4 rounded-2xl flex items-center gap-4 cursor-pointer transition-all border ${currentTargetId == u.id ? 'bg-indigo-50 border-indigo-100 shadow-sm' : 'hover:bg-slate-50 border-transparent'}`;
                        item.onclick = () => selectUser(u.id, u.fullname);
                        
                        const initials = u.fullname.split(' ').map(n => n[0]).join('').substr(0,2).toUpperCase();
                        const unreadBadge = u.unread_count > 0 ? `<div class="bg-red-500 text-white text-[10px] font-black w-5 h-5 rounded-full flex items-center justify-center absolute -top-1 -right-1 border-2 border-white">${u.unread_count}</div>` : '';

                        item.innerHTML = `
                            <div class="relative">
                                <div class="w-12 h-12 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold overflow-hidden">
                                    ${u.avatar && u.avatar !== 'uploads/default-user.png' ? `<img src="${u.avatar}" class="w-full h-full object-cover">` : initials}
                                </div>
                                ${unreadBadge}
                            </div>
                            <div class="flex-1 overflow-hidden">
                                <div class="flex justify-between items-center mb-1">
                                    <h4 class="font-bold text-slate-800 text-sm truncate uppercase">${u.fullname}</h4>
                                    <span class="text-[9px] text-slate-400 font-bold tracking-tighter">${u.last_time ? formatTime(u.last_time) : ''}</span>
                                </div>
                                <p class="text-xs text-slate-400 truncate font-medium">${u.last_msg || 'Chưa có tin nhắn'}</p>
                            </div>
                        `;
                        box.appendChild(item);
                    });
                });
        }

        function selectUser(id, name) {
            currentTargetId = id;
            if (myRole === 'admin') {
                document.getElementById('chat-empty-state').classList.add('hidden');
            }
            document.getElementById('chat-active-header').classList.remove('hidden');
            document.getElementById('target-name').innerText = name;
            document.getElementById('target-avatar').innerText = name[0].toUpperCase();
            
            fetchMessages();
            if (myRole === 'admin') fetchUsers(); // Refresh unread badges
        }

        function fetchMessages() {
            if (!currentTargetId) return;
            fetch(`handle_chat.php?action=fetch&other_id=${currentTargetId}`)
                .then(r => r.json())
                .then(msgs => {
                    const box = document.getElementById('chat-messages');
                    const isAtBottom = box.scrollHeight - box.scrollTop <= box.clientHeight + 100;
                    
                    box.innerHTML = '';
                    if (msgs.length === 0) {
                        box.innerHTML = `<div class="flex flex-col items-center justify-center py-20 opacity-20"><i class="bi bi-chat-left-text text-6xl mb-4"></i><p class="font-black uppercase tracking-widest text-xs">Bắt đầu cuộc trò chuyện</p></div>`;
                        return;
                    }

                    msgs.forEach(m => {
                        const div = document.createElement('div');
                        div.className = `flex flex-col ${m.sender_id == myId ? 'items-end' : 'items-start'}`;
                        div.innerHTML = `
                            <div class="message-bubble ${m.sender_id == myId ? 'sent' : 'received'} shadow-sm">
                                ${m.message}
                            </div>
                            <span class="text-[9px] text-slate-400 font-bold uppercase mt-2 px-1 tracking-tighter">${formatTime(m.created_at)}</span>
                        `;
                        box.appendChild(div);
                    });

                    if (isAtBottom) box.scrollTop = box.scrollHeight;
                });
        }

        function sendMessage() {
            const input = document.getElementById('chat-input');
            const msg = input.value.trim();
            if (!msg || !currentTargetId) return;

            const formData = new FormData();
            formData.append('receiver_id', currentTargetId);
            formData.append('message', msg);

            fetch('handle_chat.php?action=send', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        input.value = '';
                        fetchMessages();
                    }
                });
        }

        function confirmDeleteHistory() {
            if (!currentTargetId) return;
            if (confirm("Bạn có chắc chắn muốn xóa toàn bộ lịch sử chat với người này? Hành động này sẽ xóa lịch sử của cả hai bên.")) {
                const fd = new FormData();
                fd.append('other_id', currentTargetId);
                
                fetch('handle_chat.php?action=delete_history', { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(res => {
                        if (res.status === 'success') {
                            alert("Đã xóa lịch sử chat thành công!");
                            fetchMessages();
                            if (myRole === 'admin') fetchUsers();
                        } else {
                            alert("Lỗi: " + res.message);
                        }
                    });
            }
        }

        function formatTime(sqlDate) {
            const date = new Date(sqlDate);
            return date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
        }

        window.onload = init;
    </script>
</body>
</html>
