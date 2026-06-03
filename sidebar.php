<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"> 

<style>
    :root {
        --bg-main: #f4f7fe; 
        --sidebar-bg: #0b0f19; 
        --sidebar-width: 280px;
        --sidebar-collapsed: 85px;
        --text-muted: #8b9bb4;
        --text-light: #ffffff;
        --accent-color: #38bdf8; 
        --hover-bg: rgba(255, 255, 255, 0.05);
        --border-color: rgba(255, 255, 255, 0.08);
        
        /* Gia tốc chuyển động cực mượt */
        --smooth-transition: 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        --dropdown-transition: 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    a { text-decoration: none; }
    li { list-style: none; }

    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background-color: var(--bg-main);
        overflow-x: hidden;
    }

    /* Bố cục chính */
    /* .wrapper removed because aside is position fixed */

    /* ================= SIDEBAR PC (Tối ưu GPU) ================= */
    #sidebar {
        width: var(--sidebar-collapsed);
        min-width: var(--sidebar-collapsed);
        z-index: 1000;
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        display: flex;
        flex-direction: column;
        background-color: var(--sidebar-bg);
        border-right: 1px solid var(--border-color);
        transition: width var(--smooth-transition);
        will-change: width;
        overflow-y: auto;
        overflow-x: hidden;
    }

    #sidebar::-webkit-scrollbar { width: 4px; }
    #sidebar::-webkit-scrollbar-thumb { background: #2a2d3d; border-radius: 10px; }
    #sidebar.expand { width: var(--sidebar-width); min-width: var(--sidebar-width); }

    .main, .wann {
        width: calc(100% - var(--sidebar-collapsed));
        margin-left: var(--sidebar-collapsed);
        padding: 2rem !important;
        transition: margin-left var(--smooth-transition), width var(--smooth-transition);
        will-change: margin-left, width;
    }
    #sidebar.expand ~ .main, #sidebar.expand ~ .wann { 
        margin-left: var(--sidebar-width); 
        width: calc(100% - var(--sidebar-width));
    }

    /* Header Sidebar */
    .sidebar-header {
        display: flex; align-items: center; padding: 1.5rem 0; min-height: 80px;
    }

    #toggle-btn {
        background: transparent; border: none; color: var(--text-muted); font-size: 1.6rem;
        width: var(--sidebar-collapsed); min-width: var(--sidebar-collapsed); cursor: pointer;
        transition: transform 0.2s ease, color 0.2s ease;
    }
    #toggle-btn:hover { color: var(--text-light); transform: scale(1.1); }

    .sidebar-logo {
        white-space: nowrap; opacity: 0; transform: translateX(-10px);
        transition: opacity var(--smooth-transition), transform var(--smooth-transition);
    }
    #sidebar.expand .sidebar-logo { opacity: 1; transform: translateX(0); }
    .sidebar-logo a { color: var(--text-light); font-size: 1.4rem; font-weight: 700; }
    .sidebar-logo span { color: var(--accent-color); }

    .sidebar-nav { padding: 1rem 0; flex: 1 1 auto; }
    .sidebar-item { margin: 0.3rem 1rem; }

    /* Nút Menu */
    a.sidebar-link {
        display: flex; align-items: center; padding: 0.8rem 1rem;
        color: var(--text-muted); font-size: 0.95rem; font-weight: 500;
        border-radius: 10px; white-space: nowrap;
        transition: background-color 0.2s ease, color 0.2s ease;
        position: relative;
    }

    a.sidebar-link i {
        font-size: 1.3rem; min-width: 30px; margin-right: 15px;
        transition: color 0.2s ease, transform 0.2s ease;
    }

    a.sidebar-link:hover { background-color: var(--hover-bg); color: var(--text-light); }
    a.sidebar-link:hover i { color: var(--accent-color); transform: translateX(3px); }

    #sidebar:not(.expand) a.sidebar-link span,
    #sidebar:not(.expand) .sidebar-link[data-bs-toggle="collapse"]::after {
        opacity: 0; display: none;
    }

    /* ========================================================
       SỬA LỖI GIẬT LAG MENU CON (DROPDOWN) CHUẨN MƯỢT
       ======================================================== */
    /* 1. Xóa margin-top gây lỗi nhảy giật chiều cao */
    .sidebar-dropdown { 
        padding-left: 2.2rem; 
        position: relative; 
    }
    
    /* 2. Ép class collapsing của Bootstrap chạy mượt mà */
    .sidebar-dropdown.collapsing {
        transition: height var(--dropdown-transition) !important;
    }

    .sidebar-dropdown::before {
        content: ''; position: absolute; top: 0; bottom: 15px; left: 30px;
        width: 1px; background: var(--border-color);
    }
    .sidebar-dropdown .sidebar-link { 
        padding: 0.6rem 1rem; font-size: 0.85rem; position: relative; 
        margin-top: 0.3rem; /* Đẩy margin vào bên trong thẻ con để không bị giật */
    }
    .sidebar-dropdown .sidebar-link::before {
        content: ''; position: absolute; left: -19px; top: 50%; transform: translateY(-50%);
        width: 10px; height: 1px; background: var(--border-color); transition: background 0.2s ease;
    }
    .sidebar-dropdown .sidebar-link:hover::before { background: var(--accent-color); }

    /* 3. Mũi tên xổ xuống chuyển động từ từ 0.4s */
    .sidebar-link[data-bs-toggle="collapse"]::after {
        content: "\F282"; font-family: "bootstrap-icons";
        position: absolute; right: 1rem; font-size: 0.8rem;
        transition: transform var(--dropdown-transition), color 0.3s ease;
    }
    .sidebar-link[data-bs-toggle="collapse"][aria-expanded="true"]::after {
        transform: rotate(-180deg); color: var(--accent-color);
    }

    /* Logic Dropdown PC thu nhỏ dạng Popup */
    @media (min-width: 769px) {
        #sidebar:not(.expand) .sidebar-item .sidebar-dropdown {
            position: absolute; top: 0; left: 75px; background: #141b2d;
            border: 1px solid var(--border-color); box-shadow: 4px 4px 15px rgba(0,0,0,0.3);
            border-radius: 12px; padding: 10px; min-width: 220px;
            /* Phá vỡ Bootstrap khi ở chế độ popup */
            display: none !important; height: auto !important; z-index: 100;
        }
        #sidebar:not(.expand) .sidebar-item:hover .sidebar-dropdown { display: block !important; }
        #sidebar:not(.expand) .sidebar-dropdown::before,
        #sidebar:not(.expand) .sidebar-dropdown .sidebar-link::before { display: none; }
    }

    /* Footer Đăng xuất */
    .sidebar-footer { padding: 1.5rem 1rem; border-top: 1px solid var(--border-color); }
    .sidebar-footer a { background: rgba(239, 68, 68, 0.05); }
    .sidebar-footer a:hover { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
    .sidebar-footer a:hover i { color: #ef4444; transform: translateX(3px); }

    /* Overlay Mobile */
    .sidebar-overlay {
        display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.5);
        z-index: 995; opacity: 0; transition: opacity var(--smooth-transition);
        will-change: opacity;
    }
    .sidebar-overlay.active { display: block; opacity: 1; }

    /* ================= RESPONSIVE MOBILE ================= */
    @media (max-width: 768px) {
        #sidebar {
            left: 0; width: var(--sidebar-width) !important; min-width: var(--sidebar-width) !important;
            transform: translateX(-100%); transition: transform var(--smooth-transition);
            will-change: transform;
        }
        
        #sidebar.expand { transform: translateX(0); }
        .main, .wann { width: 100% !important; margin-left: 0 !important; padding: 1rem !important; }
        #toggle-btn { display: none; }
        .sidebar-header { padding-left: 20px; }
        #sidebar .sidebar-logo { opacity: 1; transform: translateX(0); }
        #sidebar a.sidebar-link span,
        #sidebar .sidebar-link[data-bs-toggle="collapse"]::after { display: inline-block !important; opacity: 1 !important; }
        
        /* FIX CỰC QUAN TRỌNG: XÓA CÁC THUỘC TÍNH PHÁ VỠ HOẠT ẢNH BOOTSTRAP Ở MOBILE */
        #sidebar .sidebar-item .sidebar-dropdown { 
            position: relative; left: 0; background: transparent; border: none; box-shadow: none; 
            /* Không ép display: none hay block ở đây để Bootstrap tự trượt */
        }
        
        #sidebar .sidebar-dropdown::before,
        #sidebar .sidebar-dropdown .sidebar-link::before { display: block; }
    }
</style>

<div class="sidebar-overlay" id="sidebar-overlay"></div>

    <aside id="sidebar">
        <div class="sidebar-header d-none d-md-flex">
            <button id="toggle-btn" type="button">
                <i class="bi bi-list"></i>
            </button>
            <div class="sidebar-logo">
                <a href="#">AZMEDIA<span> 247</span></a>
            </div>
        </div>
        
        <ul class="sidebar-nav">
            <li class="sidebar-item">
                <a href="dashboard.php" class="sidebar-link">
                    <i class="bi bi-grid-1x2"></i>
                    <span>Bảng điều khiển</span>
                </a>
            </li>
            
            <li class="sidebar-item">
                <a href="bangdieukhien_quanglyphong.php" class="sidebar-link">
                    <i class="bi bi-door-open"></i>
                    <span>Quản lý phòng</span>
                </a>
            </li>
            
            <li class="sidebar-item">
                <a href="#" class="sidebar-link collapsed" data-bs-toggle="collapse" data-bs-target="#khu-vuc-tro" aria-expanded="false">
                    <i class="bi bi-buildings"></i>
                    <span>Khu vực Trọ</span>
                </a>
                <ul id="khu-vuc-tro" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li><a href="quanlytro1.php" class="sidebar-link">Trọ Sinh Viên 1</a></li>
                    <li><a href="quanlytro2.php" class="sidebar-link">Trọ Sinh Viên 2</a></li>
                    <li><a href="quanlytro3.php" class="sidebar-link">Trọ Cao Cấp</a></li>
                </ul>
            </li>
            
            <li class="sidebar-item">
                <a href="#" class="sidebar-link collapsed" data-bs-toggle="collapse" data-bs-target="#cai-dat-tinh-nang" aria-expanded="false">
                    <i class="bi bi-gear"></i>
                    <span>Cài đặt Tính năng</span>
                </a>
                <ul id="cai-dat-tinh-nang" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li><a href="tinh_nang_dang_phat_trien.php" class="sidebar-link">Đang Phát Triển 1</a></li>
                    <li><a href="tinh_nang_dang_phat_trien.php" class="sidebar-link">Đang Phát Triển 2</a></li>
                    <li><a href="tinh_nang_dang_phat_trien.php" class="sidebar-link">Đang Phát Triển 3</a></li>
                </ul>
            </li>

            <li class="sidebar-item">
                <a href="thongtinhoadon.php" class="sidebar-link">
                    <i class="bi bi-receipt"></i>
                    <span>Thông tin Hóa đơn</span>
                </a>
            </li>

            <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <li class="sidebar-item">
                <a href="quanly_hoadon.php" class="sidebar-link">
                    <i class="bi bi-receipt-cutoff"></i>
                    <span>Quản lý hóa đơn</span>
                </a>
            </li>
            <?php endif; ?>

            <li class="sidebar-item">
                <a href="chat.php" class="sidebar-link">
                    <i class="bi bi-chat-dots"></i>
                    <span>Hỗ trợ (Chat)</span>
                    <?php 
                    try {
                        if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
                            if (!isset($pdo)) { require_once 'db.php'; }
                            $unread_total = $pdo->query("SELECT COUNT(*) FROM messages WHERE receiver_id = {$_SESSION['user_id']} AND is_read = 0")->fetchColumn();
                            if ($unread_total > 0) {
                                echo '<span class="badge bg-danger ms-auto rounded-pill" style="font-size: 0.6rem; padding: 0.35em 0.65em;">'.$unread_total.'</span>';
                            }
                        }
                    } catch (Exception $e) {}
                    ?>
                </a>
            </li>
        </ul>
        
        <div class="sidebar-footer">
            <a href="logout.php" class="sidebar-link">
                <i class="bi bi-box-arrow-right"></i>
                <span>Đăng xuất hệ thống</span>
            </a>
        </div>
    </aside>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const sidebar = document.querySelector("#sidebar");
        const desktopToggle = document.querySelector("#toggle-btn");
        const mobileToggle = document.querySelector("#mobile-nav-toggle"); 
        const overlay = document.querySelector("#sidebar-overlay");

        if (desktopToggle) {
            desktopToggle.addEventListener("click", () => {
                requestAnimationFrame(() => {
                    sidebar.classList.toggle("expand");
                });
            });
        }

        if (mobileToggle) {
            mobileToggle.addEventListener("click", () => {
                requestAnimationFrame(() => {
                    sidebar.classList.toggle("expand");
                    overlay.classList.toggle("active");
                });
            });
        }

        if (overlay) {
            overlay.addEventListener("click", () => {
                requestAnimationFrame(() => {
                    sidebar.classList.remove("expand");
                    overlay.classList.remove("active");
                });
            });
        }
    });
</script>