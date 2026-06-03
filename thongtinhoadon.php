<?php
include 'cek-akses.php';
require 'cookie.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin Hóa đơn - Quản lý Trọ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="shortcut icon" href="uploads/asset/favicon.ico" type="image/x-icon">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        :root {
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --primary: #38bdf8;
            --navbar-height: 70px;
            --sidebar-collapsed: 85px;
            --sidebar-expanded: 280px;
            
            --pln-color: #f59e0b; 
            --pam-color: #0ea5e9; 
            --wifi-color: #ef4444; 
            --map-color: #8b5cf6; 
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        a { text-decoration: none; }
        li { list-style: none; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            overflow-x: hidden;
            padding-top: var(--navbar-height); 
        }

        /* ================= 3. CONTENT ================= */
        .wann {
            margin-top: 80px;
        }

        .container-fluid { max-width: 1400px; margin: 0 auto; }
        
        .page-header {
            position: relative; padding: 40px 30px; margin-bottom: 40px;
            background: linear-gradient(135deg, #38bdf8 0%, #0284c7 100%);
            color: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(56, 189, 248, 0.2);
            overflow: hidden;
        }
        .page-title { font-size: 2.2rem; font-weight: 800; margin-bottom: 10px; position: relative; z-index: 2; }
        .page-subtitle { font-size: 1.05rem; opacity: 0.9; max-width: 600px; position: relative; z-index: 2; }
        
        .section-title { display: flex; align-items: center; font-size: 1.4rem; font-weight: 800; margin-bottom: 20px; }
        .section-title i { margin-right: 12px; font-size: 1.5rem; padding: 10px; border-radius: 12px; background: #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }

        /* Lưới Card PC */
        .card-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px; margin-bottom: 40px; }
        
        .modern-card {
            background-color: var(--card-bg); border-radius: 20px; padding: 25px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04); border: 1px solid rgba(0,0,0,0.03);
            transition: all 0.3s ease; position: relative; overflow: hidden;
        }
        .modern-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08); }

        .pln-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: var(--pln-color); }
        .pam-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: var(--pam-color); }
        .indihome-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: var(--wifi-color); }
        .address-card-wrap::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: var(--map-color); }
        
        .mc-header { display: flex; align-items: center; margin-bottom: 20px; }
        .mc-icon {
            width: 50px; height: 50px; border-radius: 14px; display: flex; align-items: center; justify-content: center;
            margin-right: 15px; font-size: 1.4rem; transition: transform 0.3s ease;
        }
        .mc-title { font-size: 1.1rem; font-weight: 700; margin: 0; line-height: 1.3; color: var(--text-main); }
        
        .mc-body {
            display: flex; align-items: center; justify-content: space-between;
            background: #f8fafc; padding: 12px 16px; border-radius: 12px; border: 1px dashed #cbd5e1;
        }
        .mc-data { font-family: 'Courier New', Courier, monospace; font-size: 1.15rem; font-weight: 700; word-break: break-all; color: var(--text-main); }
        
        .mc-copy {
            background: white; border: 1px solid #e2e8f0; color: var(--text-muted);
            width: 38px; height: 38px; border-radius: 10px; cursor: pointer;
            display: flex; align-items: center; justify-content: center; transition: all 0.2s;
        }
        .mc-copy:hover { color: var(--primary); border-color: var(--primary); background: #f0f9ff; }
        .mc-copy:active { transform: scale(0.95); }
        
        /* Chỉnh nút bấm Bản đồ */
        .mc-map-link {
            display: inline-flex; align-items: center; gap: 8px; color: var(--map-color);
            text-decoration: none; font-weight: 600; padding: 10px 18px;
            background-color: rgba(139, 92, 246, 0.1); border-radius: 10px;
            margin-top: 15px; transition: all 0.3s; font-size: 0.95rem; width: fit-content;
        }
        .mc-map-link:hover { background-color: var(--map-color); color: white; transform: translateY(-2px); }

        .pln-card .mc-icon { background: rgba(245, 158, 11, 0.15); color: var(--pln-color); }
        .pam-card .mc-icon { background: rgba(14, 165, 233, 0.15); color: var(--pam-color); }
        .indihome-card .mc-icon { background: rgba(239, 68, 68, 0.15); color: var(--wifi-color); }
        .address-card-wrap .mc-icon { background: rgba(139, 92, 246, 0.15); color: var(--map-color); }

        /* ================= TỐI ƯU MOBILE (LIST VIEW APP) ================= */
        @media (max-width: 768px) {
            .page-header { padding: 25px 20px; border-radius: 16px; margin-bottom: 25px; }
            .page-title { font-size: 1.6rem; }
            
            .card-grid { display: flex; flex-direction: column; gap: 10px; margin-bottom: 30px; }
            
            .modern-card {
                display: grid;
                grid-template-columns: auto 1fr auto;
                grid-template-rows: auto auto;
                grid-template-areas: "icon title btn" "icon data btn";
                align-items: center; padding: 14px 16px; border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            }
            .modern-card::before { display: none; } 
            .mc-header, .mc-body { display: contents; }
            .mc-icon { grid-area: icon; width: 44px; height: 44px; margin-right: 12px; font-size: 1.2rem; border-radius: 12px; }
            .mc-title { grid-area: title; font-size: 0.95rem; align-self: end; margin-bottom: 2px; }
            .mc-data { grid-area: data; align-self: start; font-size: 0.9rem; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 500; color: var(--text-muted); background: transparent; border: none; padding: 0; }
            .mc-copy { grid-area: btn; width: 36px; height: 36px; border-radius: 50%; border: none; margin-left: 10px; }
            
            .modern-card.address-card-wrap {
                grid-template-rows: auto auto auto;
                grid-template-areas: "icon title btn" "icon data btn" "map map map";
            }
            .address-card-wrap .mc-data { font-size: 0.85rem; line-height: 1.4; }
            .mc-map-link { grid-area: map; width: 100%; justify-content: center; margin-top: 12px; padding: 8px; font-size: 0.85rem; }

            .pln-card .mc-copy { background: rgba(245, 158, 11, 0.1); color: var(--pln-color); }
            .pam-card .mc-copy { background: rgba(14, 165, 233, 0.1); color: var(--pam-color); }
            .indihome-card .mc-copy { background: rgba(239, 68, 68, 0.1); color: var(--wifi-color); }
            .address-card-wrap .mc-copy { background: rgba(139, 92, 246, 0.1); color: var(--map-color); }
        }

        .toast-container { position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); z-index: 9999; display: flex; flex-direction: column; gap: 10px; pointer-events: none; }
        .toast-modern { background: #1e293b; color: #fff; padding: 12px 24px; border-radius: 50px; font-weight: 600; font-size: 0.9rem; display: flex; align-items: center; gap: 10px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2); opacity: 0; transform: translateY(20px); transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55); white-space: nowrap; }
        .toast-modern.show { opacity: 1; transform: translateY(0); }
        .toast-icon { color: #4ade80; font-size: 1.2rem; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>
    <?php include 'navbar.php'; ?>

    <div class="wann">
        <div class="container-fluid">
            <div class="page-header">
                <h1 class="page-title">Hóa đơn & Tiện ích</h1>
                <p class="page-subtitle">Dữ liệu khách hàng điện (PLN), nước (PAM) và mạng (INDIHOME) cho các khu trọ.</p>
            </div>
            
            <div class="section">
                <h2 class="section-title"><i class="bi bi-lightning-charge-fill" style="color: var(--pln-color);"></i> Điện năng (PLN)</h2>
                <div class="card-grid">
                    
                    <div class="modern-card pln-card">
                        <div class="mc-header"><div class="mc-icon"><i class="bi bi-building"></i></div><h3 class="mc-title">Trọ Cao Cấp</h3></div>
                        <div class="mc-body"><div class="mc-data">5432 1109 8271</div><button class="mc-copy" onclick="copyToClipboard('543211098271')"><i class="bi bi-clipboard"></i></button></div>
                    </div>
                    
                    <div class="modern-card pln-card">
                        <div class="mc-header"><div class="mc-icon"><i class="bi bi-building"></i></div><h3 class="mc-title">Trọ Sinh Viên 1</h3></div>
                        <div class="mc-body"><div class="mc-data">5432 2209 8272</div><button class="mc-copy" onclick="copyToClipboard('543222098272')"><i class="bi bi-clipboard"></i></button></div>
                    </div>
                    
                    <div class="modern-card pln-card">
                        <div class="mc-header"><div class="mc-icon"><i class="bi bi-building"></i></div><h3 class="mc-title">Trọ Sinh Viên 2 - Khu 1</h3></div>
                        <div class="mc-body"><div class="mc-data">5432 3309 8273</div><button class="mc-copy" onclick="copyToClipboard('543233098273')"><i class="bi bi-clipboard"></i></button></div>
                    </div>

                    <div class="modern-card pln-card">
                        <div class="mc-header"><div class="mc-icon"><i class="bi bi-building"></i></div><h3 class="mc-title">Trọ Sinh Viên 2 - Tầng 1</h3></div>
                        <div class="mc-body"><div class="mc-data">5432 3309 8274</div><button class="mc-copy" onclick="copyToClipboard('543233098274')"><i class="bi bi-clipboard"></i></button></div>
                    </div>

                    <div class="modern-card pln-card">
                        <div class="mc-header"><div class="mc-icon"><i class="bi bi-building"></i></div><h3 class="mc-title">Trọ Sinh Viên 2 - Máy bơm</h3></div>
                        <div class="mc-body"><div class="mc-data">5432 3309 8275</div><button class="mc-copy" onclick="copyToClipboard('543233098275')"><i class="bi bi-clipboard"></i></button></div>
                    </div>

                    <div class="modern-card pln-card">
                        <div class="mc-header"><div class="mc-icon"><i class="bi bi-building"></i></div><h3 class="mc-title">Trọ Sinh Viên 3 - Tòa A</h3></div>
                        <div class="mc-body"><div class="mc-data">5432 4409 8276</div><button class="mc-copy" onclick="copyToClipboard('543244098276')"><i class="bi bi-clipboard"></i></button></div>
                    </div>

                    <div class="modern-card pln-card">
                        <div class="mc-header"><div class="mc-icon"><i class="bi bi-building"></i></div><h3 class="mc-title">Trọ Sinh Viên 3 - Tòa B</h3></div>
                        <div class="mc-body"><div class="mc-data">5432 4409 8277</div><button class="mc-copy" onclick="copyToClipboard('543244098277')"><i class="bi bi-clipboard"></i></button></div>
                    </div>

                    <div class="modern-card pln-card">
                        <div class="mc-header"><div class="mc-icon"><i class="bi bi-car-front"></i></div><h3 class="mc-title">Khu vực Nhà Xe</h3></div>
                        <div class="mc-body"><div class="mc-data">5432 5509 8278</div><button class="mc-copy" onclick="copyToClipboard('543255098278')"><i class="bi bi-clipboard"></i></button></div>
                    </div>

                    <div class="modern-card pln-card">
                        <div class="mc-header"><div class="mc-icon"><i class="bi bi-lightbulb"></i></div><h3 class="mc-title">Điện Chiếu Sáng Chung</h3></div>
                        <div class="mc-body"><div class="mc-data">5432 9909 8299</div><button class="mc-copy" onclick="copyToClipboard('543299098299')"><i class="bi bi-clipboard"></i></button></div>
                    </div>

                </div>
            </div>
            
            <div class="section">
                <h2 class="section-title"><i class="bi bi-droplet-fill" style="color: var(--pam-color);"></i> Nước sạch (PAM)</h2>
                <div class="card-grid">
                    <div class="modern-card pam-card">
                        <div class="mc-header"><div class="mc-icon"><i class="bi bi-water"></i></div><h3 class="mc-title">Trọ Sinh Viên 1</h3></div>
                        <div class="mc-body"><div class="mc-data">1002 9384 55</div><button class="mc-copy" onclick="copyToClipboard('1002938455')"><i class="bi bi-clipboard"></i></button></div>
                    </div>

                    <div class="modern-card pam-card">
                        <div class="mc-header"><div class="mc-icon"><i class="bi bi-water"></i></div><h3 class="mc-title">Trọ Sinh Viên 2</h3></div>
                        <div class="mc-body"><div class="mc-data">1002 9384 56</div><button class="mc-copy" onclick="copyToClipboard('1002938456')"><i class="bi bi-clipboard"></i></button></div>
                    </div>

                    <div class="modern-card pam-card">
                        <div class="mc-header"><div class="mc-icon"><i class="bi bi-water"></i></div><h3 class="mc-title">Trọ Sinh Viên 3</h3></div>
                        <div class="mc-body"><div class="mc-data">1002 9384 57</div><button class="mc-copy" onclick="copyToClipboard('1002938457')"><i class="bi bi-clipboard"></i></button></div>
                    </div>

                    <div class="modern-card pam-card">
                        <div class="mc-header"><div class="mc-icon"><i class="bi bi-water"></i></div><h3 class="mc-title">Trọ Cao Cấp</h3></div>
                        <div class="mc-body"><div class="mc-data">1002 9384 99</div><button class="mc-copy" onclick="copyToClipboard('1002938499')"><i class="bi bi-clipboard"></i></button></div>
                    </div>

                    <div class="modern-card pam-card">
                        <div class="mc-header"><div class="mc-icon"><i class="bi bi-moisture"></i></div><h3 class="mc-title">Nước Sinh Hoạt Chung</h3></div>
                        <div class="mc-body"><div class="mc-data">1002 9384 00</div><button class="mc-copy" onclick="copyToClipboard('1002938400')"><i class="bi bi-clipboard"></i></button></div>
                    </div>
                </div>
            </div>
            
            <div class="section">
                <h2 class="section-title"><i class="bi bi-router-fill" style="color: var(--wifi-color);"></i> Mạng (INDIHOME)</h2>
                <div class="card-grid">
                    
                    <div class="modern-card indihome-card">
                        <div class="mc-header"><div class="mc-icon"><i class="bi bi-router"></i></div><h3 class="mc-title">Trọ Sinh Viên 1</h3></div>
                        <div class="mc-body"><div class="mc-data">1225 0192 8374</div><button class="mc-copy" onclick="copyToClipboard('122501928374')"><i class="bi bi-clipboard"></i></button></div>
                    </div>
                    
                    <div class="modern-card indihome-card">
                        <div class="mc-header"><div class="mc-icon"><i class="bi bi-router"></i></div><h3 class="mc-title">Trọ Sinh Viên 2</h3></div>
                        <div class="mc-body"><div class="mc-data">1225 0192 8375</div><button class="mc-copy" onclick="copyToClipboard('122501928375')"><i class="bi bi-clipboard"></i></button></div>
                    </div>

                    <div class="modern-card indihome-card">
                        <div class="mc-header"><div class="mc-icon"><i class="bi bi-router"></i></div><h3 class="mc-title">Trọ Sinh Viên 3</h3></div>
                        <div class="mc-body"><div class="mc-data">1225 0192 8376</div><button class="mc-copy" onclick="copyToClipboard('122501928376')"><i class="bi bi-clipboard"></i></button></div>
                    </div>

                    <div class="modern-card indihome-card">
                        <div class="mc-header"><div class="mc-icon"><i class="bi bi-router"></i></div><h3 class="mc-title">Trọ Cao Cấp</h3></div>
                        <div class="mc-body"><div class="mc-data">1225 0192 8399</div><button class="mc-copy" onclick="copyToClipboard('122501928399')"><i class="bi bi-clipboard"></i></button></div>
                    </div>

                </div>
            </div>

            <div class="section">
                <h2 class="section-title"><i class="bi bi-wifi" style="color: var(--wifi-color);"></i> Mật khẩu WiFi</h2>
                <div class="card-grid">

                    <div class="modern-card indihome-card">
                        <div class="mc-header"><div class="mc-icon"><i class="bi bi-router"></i></div><h3 class="mc-title">Tro_Sinh_Vien02</h3></div>
                        <div class="mc-body"><div class="mc-data">TroSV1@2026</div><button class="mc-copy" onclick="copyToClipboard('TroSV1@2026')"><i class="bi bi-clipboard"></i></button></div>
                    </div>

                    <div class="modern-card indihome-card">
                        <div class="mc-header"><div class="mc-icon"><i class="bi bi-router"></i></div><h3 class="mc-title">Tro_Sinh_Vien03</h3></div>
                        <div class="mc-body"><div class="mc-data">TroSV1@vip99</div><button class="mc-copy" onclick="copyToClipboard('TroSV1@vip99')"><i class="bi bi-clipboard"></i></button></div>
                    </div>

                    <div class="modern-card indihome-card">
                        <div class="mc-header"><div class="mc-icon"><i class="bi bi-router"></i></div><h3 class="mc-title">Griya_Tara01</h3></div>
                        <div class="mc-body"><div class="mc-data">GriyaTara@01</div><button class="mc-copy" onclick="copyToClipboard('GriyaTara@01')"><i class="bi bi-clipboard"></i></button></div>
                    </div>

                    <div class="modern-card indihome-card">
                        <div class="mc-header"><div class="mc-icon"><i class="bi bi-router"></i></div><h3 class="mc-title">Griya_Tara02</h3></div>
                        <div class="mc-body"><div class="mc-data">GriyaTara@02</div><button class="mc-copy" onclick="copyToClipboard('GriyaTara@02')"><i class="bi bi-clipboard"></i></button></div>
                    </div>

                    <div class="modern-card indihome-card">
                        <div class="mc-header"><div class="mc-icon"><i class="bi bi-router"></i></div><h3 class="mc-title">Griya_Tara03</h3></div>
                        <div class="mc-body"><div class="mc-data">GriyaTara@03</div><button class="mc-copy" onclick="copyToClipboard('GriyaTara@03')"><i class="bi bi-clipboard"></i></button></div>
                    </div>

                    <div class="modern-card indihome-card">
                        <div class="mc-header"><div class="mc-icon"><i class="bi bi-router"></i></div><h3 class="mc-title">Trọ Cao Cấp</h3></div>
                        <div class="mc-body"><div class="mc-data">VipCaoCap@888</div><button class="mc-copy" onclick="copyToClipboard('VipCaoCap@888')"><i class="bi bi-clipboard"></i></button></div>
                    </div>

                    <div class="modern-card indihome-card">
                        <div class="mc-header"><div class="mc-icon"><i class="bi bi-router"></i></div><h3 class="mc-title">Tro_Sinh_Vien04</h3></div>
                        <div class="mc-body"><div class="mc-data">TroSV4@2027</div><button class="mc-copy" onclick="copyToClipboard('TroSV4@2027')"><i class="bi bi-clipboard"></i></button></div>
                    </div>

                    <div class="modern-card indihome-card">
                        <div class="mc-header"><div class="mc-icon"><i class="bi bi-globe"></i></div><h3 class="mc-title">Khu_Vuc_Chung</h3></div>
                        <div class="mc-body"><div class="mc-data">FreeWiFi@123</div><button class="mc-copy" onclick="copyToClipboard('FreeWiFi@123')"><i class="bi bi-clipboard"></i></button></div>
                    </div>

                </div>
            </div>

            <div class="section">
                <h2 class="section-title"><i class="bi bi-geo-alt-fill" style="color: var(--map-color);"></i> ĐỊA CHỈ</h2>
                <div class="card-grid">
                    
                    <div class="modern-card address-card-wrap">
                        <div class="mc-header"><div class="mc-icon"><i class="bi bi-geo-alt"></i></div><h3 class="mc-title" style="color: var(--map-color);">TRỌ SINH VIÊN 1</h3></div>
                        <div class="mc-body"><div class="mc-data">Đường Z115, Tân Thịnh, Thái Nguyên</div><button class="mc-copy" onclick="copyToClipboard('Đường Z115, Tân Thịnh, Thái Nguyên')"><i class="bi bi-clipboard"></i></button></div>
                        <a href="https://www.google.com/maps/search/Đường+Z115,+Tân+Thịnh,+Thái+Nguyên" target="_blank" class="mc-map-link"><i class="bi bi-map"></i> Xem trên Google Maps</a>
                    </div>
                    
                    <div class="modern-card address-card-wrap">
                        <div class="mc-header"><div class="mc-icon"><i class="bi bi-geo-alt"></i></div><h3 class="mc-title" style="color: var(--map-color);">TRỌ CAO CẤP</h3></div>
                        <div class="mc-body"><div class="mc-data">Đường Quang Trung, Thành phố Thái Nguyên</div><button class="mc-copy" onclick="copyToClipboard('Đường Quang Trung, Thành phố Thái Nguyên')"><i class="bi bi-clipboard"></i></button></div>
                        <a href="https://www.google.com/maps/search/Đường+Quang+Trung,+Thái+Nguyên" target="_blank" class="mc-map-link"><i class="bi bi-map"></i> Xem trên Google Maps</a>
                    </div>
                    
                    <div class="modern-card address-card-wrap">
                        <div class="mc-header"><div class="mc-icon"><i class="bi bi-geo-alt"></i></div><h3 class="mc-title" style="color: var(--map-color);">TRỌ SINH VIÊN 2</h3></div>
                        <div class="mc-body"><div class="mc-data">Đại học Công nghệ Thông tin và Truyền thông Thái Nguyên (ICTU)</div><button class="mc-copy" onclick="copyToClipboard('Đại học Công nghệ Thông tin và Truyền thông Thái Nguyên')"><i class="bi bi-clipboard"></i></button></div>
                        <a href="https://www.google.com/maps/search/Đại+học+Công+nghệ+Thông+tin+và+Truyền+thông+Thái+Nguyên" target="_blank" class="mc-map-link"><i class="bi bi-map"></i> Xem trên Google Maps</a>
                    </div>

                </div>
            </div>

            <div class="footer">
                <p>© <?php echo date('Y'); ?> Quản lý Trọ. Đã đăng ký bản quyền.</p>
            </div>
            
        </div>
        
        <div class="toast-container" id="toastBox"></div>
    </div>
    
    <script>
        // COPY VÀ THÔNG BÁO TOAST
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                showToast("Đã sao chép ID thành công!");
            }, function() {
                showToast("Sao chép ID thất bại.");
            });
        }
        
        function showToast(message) {
            const toastBox = document.getElementById('toastBox');
            const toast = document.createElement('div');
            toast.className = 'toast-modern';
            toast.innerHTML = `<i class="bi bi-check-circle-fill toast-icon"></i> <span>${message}</span>`;
            toastBox.appendChild(toast);
            
            setTimeout(() => toast.classList.add('show'), 10);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 400); 
            }, 3000);
        }
    </script>
</body>
</html>