<?php
session_start();
?>
<!DOCTYPE html>
<html lang="vi" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AZMEDIA247 - Hệ Thống Quản Lý Phòng Trọ Cao Cấp</title>
    
    <!-- Meta SEO -->
    <meta name="description" content="Hệ thống quản lý phòng trọ thông minh AZMEDIA247. Tự động hóa hóa đơn, quản lý cư dân chuyên nghiệp, giao diện hiện đại bậc nhất.">
    <meta name="keywords" content="quản lý phòng trọ, phần mềm quản lý trọ, AZMEDIA247, smart home management">
    
    <!-- Fonts & Icons -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#6366f1',
                        secondary: '#4f46e5',
                        accent: '#10b981',
                        dark: '#0f172a',
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #ffffff;
        }

        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .gradient-text {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-glow {
            position: absolute;
            width: 50vw;
            height: 50vw;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, rgba(255, 255, 255, 0) 70%);
            z-index: -1;
            filter: blur(80px);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px -12px rgba(99, 102, 241, 0.15);
        }

        .nav-scrolled {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 30px rgba(0,0,0,0.05);
            padding-top: 1rem;
            padding-bottom: 1rem;
        }

        .blob {
            position: absolute;
            width: 500px;
            height: 500px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%;
            filter: blur(100px);
            opacity: 0.1;
            z-index: -1;
            animation: blob-animate 20s infinite alternate linear;
        }

        @keyframes blob-animate {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(100px, 100px) scale(1.2); }
        }
    </style>
</head>
<body class="overflow-x-hidden">

    <!-- Navigation -->
    <nav id="navbar" class="fixed top-0 left-0 right-0 z-50 transition-all duration-500 py-6 px-4 md:px-12 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-200">
                <span class="font-black">AZ</span>
            </div>
            <a href="index.php" class="text-xl font-black text-slate-900 tracking-tighter">AZMEDIA<span class="text-indigo-600">247</span></a>
        </div>
        
        <div class="hidden md:flex items-center gap-10">
            <a href="#features" class="text-sm font-bold text-slate-500 hover:text-indigo-600 transition-colors uppercase tracking-widest">Tính năng</a>
            <a href="#stats" class="text-sm font-bold text-slate-500 hover:text-indigo-600 transition-colors uppercase tracking-widest">Thống kê</a>
            <a href="#rooms" class="text-sm font-bold text-slate-500 hover:text-indigo-600 transition-colors uppercase tracking-widest">Phòng trống</a>
        </div>

        <div class="flex items-center gap-4">
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php" class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-black text-sm shadow-xl shadow-indigo-200 hover:scale-105 active:scale-95 transition-all">DASHBOARD</a>
            <?php else: ?>
                <a href="login.php" class="text-sm font-black text-slate-900 px-6">LOGIN</a>
                <a href="register.php" class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-black text-sm shadow-xl shadow-indigo-200 hover:scale-105 active:scale-95 transition-all">BẮT ĐẦU NGAY</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 md:pt-48 md:pb-32 px-4 md:px-12">
        <div class="blob top-0 right-0 translate-x-1/2 -translate-y-1/2"></div>
        <div class="blob bottom-0 left-0 -translate-x-1/2 translate-y-1/2 bg-purple-500"></div>
        
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-20 items-center">
            <div class="animate__animated animate__fadeInLeft">
                <span class="inline-block px-4 py-2 bg-indigo-50 text-indigo-600 rounded-full text-xs font-black uppercase tracking-widest mb-6">
                    Hệ thống quản lý 4.0
                </span>
                <h1 class="text-5xl md:text-7xl font-black text-slate-900 tracking-tighter leading-[1.1] mb-8">
                    Quản lý trọ <br>
                    <span class="gradient-text italic">Thông minh hơn</span><br>
                    chuyên nghiệp hơn.
                </h1>
                <p class="text-lg md:text-xl text-slate-500 font-medium leading-relaxed max-w-xl mb-12">
                    Nâng tầm giá trị khu trọ của bạn với giải pháp quản lý tự động 100%. Tiết kiệm thời gian, minh bạch tài chính và hiện đại hóa trải nghiệm cư dân.
                </p>
                <div class="flex flex-col sm:flex-row gap-6">
                    <a href="register.php" class="flex items-center justify-center gap-3 bg-indigo-600 text-white px-10 py-5 rounded-[2rem] font-black text-lg shadow-2xl shadow-indigo-200 hover:scale-105 transition-all">
                        Trải nghiệm ngay <i class="bi bi-arrow-right"></i>
                    </a>
                    <div class="flex items-center gap-4 px-4 py-2 border border-slate-100 rounded-full md:glass">
                        <div class="flex -space-x-3">
                            <div class="w-10 h-10 rounded-full border-2 border-white bg-slate-200 overflow-hidden"><img src="https://i.pravatar.cc/100?u=1" alt=""></div>
                            <div class="w-10 h-10 rounded-full border-2 border-white bg-slate-200 overflow-hidden"><img src="https://i.pravatar.cc/100?u=2" alt=""></div>
                            <div class="w-10 h-10 rounded-full border-2 border-white bg-slate-200 overflow-hidden"><img src="https://i.pravatar.cc/100?u=3" alt=""></div>
                        </div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-tight">
                            Hơn <span class="text-indigo-600">500+</span> chủ trọ <br> tin dùng tại Việt Nam
                        </p>
                    </div>
                </div>
            </div>

            <div class="relative animate__animated animate__zoomIn">
                <!-- Mockup/Image Placeholder -->
                <div class="relative z-10 rounded-[3rem] overflow-hidden shadow-2xl border-8 border-white">
                    <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" alt="Dashboard Preview" class="w-full">
                </div>
                <!-- UI Cards Overlays -->
                <div class="absolute -top-10 -left-10 bg-white p-6 rounded-3xl shadow-2xl animate__animated animate__bounceIn animation-delay-500">
                    <div class="flex items-center gap-3">
                         <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center text-white"><i class="bi bi-wallet2 text-lg"></i></div>
                         <div>
                             <p class="text-[10px] font-black text-slate-400 uppercase">Thu tiền tháng này</p>
                             <p class="text-xl font-black text-slate-900">+125,4M VNĐ</p>
                         </div>
                    </div>
                </div>
                <div class="absolute -bottom-10 -right-10 bg-indigo-900 p-8 rounded-3xl shadow-2xl text-white animate__animated animate__fadeInUp">
                    <p class="text-sm font-bold opacity-60 mb-2">Trạng thái phòng</p>
                    <div class="flex items-center gap-4">
                        <div class="w-3 h-3 bg-indigo-400 rounded-full animate-pulse"></div>
                        <p class="text-2xl font-black italic">98% Full</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section id="stats" class="py-20 bg-slate-900 text-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-2 md:grid-cols-4 gap-12 text-center">
            <div class="group">
                <h3 class="text-5xl font-black mb-2 transition-transform group-hover:scale-110">100+</h3>
                <p class="text-indigo-300 font-bold uppercase tracking-widest text-[10px]">Tòa nhà quản lý</p>
            </div>
            <div class="group">
                <h3 class="text-5xl font-black mb-2 transition-transform group-hover:scale-110">2.5k+</h3>
                <p class="text-indigo-300 font-bold uppercase tracking-widest text-[10px]">Cư dân sinh sống</p>
            </div>
            <div class="group">
                <h3 class="text-5xl font-black mb-2 transition-transform group-hover:scale-110">99%</h3>
                <p class="text-indigo-300 font-bold uppercase tracking-widest text-[10px]">Độ hài lòng</p>
            </div>
            <div class="group">
                <h3 class="text-5xl font-black mb-2 transition-transform group-hover:scale-110">24/7</h3>
                <p class="text-indigo-300 font-bold uppercase tracking-widest text-[10px]">Hỗ trợ kỹ thuật</p>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section id="features" class="py-32 px-4 md:px-12">
        <div class="max-w-7xl mx-auto">
            <div class="text-center md:text-left mb-20">
                <h2 class="text-4xl md:text-5xl font-black text-slate-900 tracking-tighter mb-4">Tính năng <span class="text-indigo-600 italic underline decoration-wavy">vượt trội</span></h2>
                <p class="text-lg text-slate-500 font-medium">Được thiết kế để loại bỏ mọi gánh nặng quản lý của bạn.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                <!-- Feature 1 -->
                <div class="feature-card p-10 bg-white rounded-[2.5rem] border border-slate-100 transition-all duration-300">
                    <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-3xl mb-8">
                        <i class="bi bi-receipt-cutoff"></i>
                    </div>
                    <h4 class="text-2xl font-black text-slate-900 mb-4 transition-colors">Hóa đơn tự động</h4>
                    <p class="text-slate-500 leading-relaxed font-medium">Tự động tính toán tiền phòng, điện nước và dịch vụ. Xuất hóa đơn đẹp mắt gửi thẳng tới cư dân chỉ bằng 1 cú click.</p>
                </div>
                <!-- Feature 2 -->
                <div class="feature-card p-10 bg-white rounded-[2.5rem] border border-slate-100 transition-all duration-300 active">
                    <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-3xl mb-8">
                        <i class="bi bi-qr-code-scan"></i>
                    </div>
                    <h4 class="text-2xl font-black text-slate-900 mb-4 transition-colors">Quét mã VietQR</h4>
                    <p class="text-slate-500 leading-relaxed font-medium">Tích hợp VietQR MB Bank. Cư dân quét mã thanh toán tức thì, hệ thống tự động ghi nhận và thông báo cho admin.</p>
                </div>
                <!-- Feature 3 -->
                <div class="feature-card p-10 bg-white rounded-[2.5rem] border border-slate-100 transition-all duration-300">
                    <div class="w-16 h-16 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center text-3xl mb-8">
                        <i class="bi bi-chat-heart"></i>
                    </div>
                    <h4 class="text-2xl font-black text-slate-900 mb-4 transition-colors">Hỗ trợ trực tuyến</h4>
                    <p class="text-slate-500 leading-relaxed font-medium">Cổng giao tiếp nội bộ giữa chủ trọ và cư dân. Báo hỏng, gửi ý kiến và trò chuyện thời gian thực mượt mà.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Rooms Preview / CTA Area -->
    <section id="rooms" class="py-20 px-4 md:px-12">
        <div class="max-w-7xl mx-auto bg-indigo-600 rounded-[3rem] p-12 md:p-24 text-white relative overflow-hidden">
            <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute -left-20 -top-20 w-80 h-80 bg-indigo-400/20 rounded-full blur-3xl"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-12">
                <div class="max-w-xl">
                    <h2 class="text-4xl md:text-5xl font-black tracking-tighter mb-6 leading-tight uppercase">Sẵn sàng để chuyên nghiệp hóa khu trọ của bạn?</h2>
                    <p class="text-indigo-100 text-lg md:text-xl font-medium opacity-80">Tham gia cùng hàng trăm chủ trọ thông minh khác đang sử dụng AZMEDIA247 mỗi ngày.</p>
                </div>
                <div class="flex flex-col gap-4">
                    <a href="register.php" class="bg-white text-indigo-600 px-10 py-5 rounded-[2rem] font-black text-center shadow-2xl hover:scale-105 transition-all">ĐĂNG KÝ NGAY MIỄN PHÍ</a>
                    <p class="text-[10px] text-indigo-200 font-bold uppercase tracking-widest text-center italic">Không yêu cầu thẻ tín dụng</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="pt-24 pb-12 px-4 md:px-12 bg-white border-t border-slate-50">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-16 mb-20">
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white"><span class="font-black">AZ</span></div>
                        <span class="text-xl font-black text-slate-900 tracking-tighter">AZMEDIA<span class="text-indigo-600">247</span></span>
                    </div>
                    <p class="text-slate-400 font-medium leading-relaxed max-w-sm">
                        Nền tảng quản lý nhà trọ và căn hộ dịch vụ hàng đầu Thái Nguyên. Kiến tạo không gian sống hiện đại và quy trình quản lý tối ưu.
                    </p>
                </div>
                <div>
                    <h5 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-6">Liên kết</h5>
                    <ul class="space-y-4">
                        <li><a href="#" class="text-slate-500 font-bold text-sm hover:text-indigo-600 transition-colors">Về chúng tôi</a></li>
                        <li><a href="#" class="text-slate-500 font-bold text-sm hover:text-indigo-600 transition-colors">Điều khoản dịch vụ</a></li>
                        <li><a href="#" class="text-slate-500 font-bold text-sm hover:text-indigo-600 transition-colors">Chính sách bảo mật</a></li>
                    </ul>
                </div>
                <div>
                    <h5 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-6">Liên hệ</h5>
                    <ul class="space-y-4">
                        <li class="flex items-center gap-3 text-slate-500 font-bold text-sm"><i class="bi bi-geo-alt text-indigo-500"></i> TP. Thái Nguyên</li>
                        <li class="flex items-center gap-3 text-slate-500 font-bold text-sm"><i class="bi bi-envelope text-indigo-500"></i> admin@azmedia247.com</li>
                        <li class="flex items-center gap-3 text-slate-500 font-bold text-sm"><i class="bi bi-telephone text-indigo-500"></i> (+84) 333 222 111</li>
                    </ul>
                </div>
            </div>
            <div class="pt-12 border-t border-slate-50 flex flex-col md:flex-row justify-between items-center gap-6">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">© 2026 AZMEDIA247 . DEVELOPED BY TEAM QUANIT</p>
                <div class="flex items-center gap-6">
                    <a href="#" class="text-slate-400 hover:text-indigo-600 transition-colors"><i class="bi bi-facebook text-xl"></i></a>
                    <a href="#" class="text-slate-400 hover:text-indigo-600 transition-colors"><i class="bi bi-instagram text-xl"></i></a>
                    <a href="#" class="text-slate-400 hover:text-indigo-600 transition-colors"><i class="bi bi-linkedin text-xl"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Navbar scroll effect
        window.onscroll = function() {
            const navbar = document.getElementById('navbar');
            if (window.pageYOffset > 50) {
                navbar.classList.add('nav-scrolled');
                navbar.classList.remove('py-6');
                navbar.classList.add('py-3');
            } else {
                navbar.classList.remove('nav-scrolled');
                navbar.classList.add('py-6');
                navbar.classList.remove('py-3');
            }
        };
    </script>
</body>
</html>