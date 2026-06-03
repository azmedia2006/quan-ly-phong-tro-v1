<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';

// Redirection logic if already logged in properly
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    // Basic single session check here too to avoid unnecessary redirects
    $stmt = $pdo->prepare("SELECT session_id FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_db = $stmt->fetch();
    
    if ($user_db && $user_db['session_id'] === session_id()) {
        if ($_SESSION['role'] === 'admin') {
            header("Location: admin_dashboard.php");
            exit;
        } else {
            header("Location: user_dashboard.php");
            exit;
        }
    }
}

$error = ""; 
// Check for expiration error from cek-akses.php
if (isset($_GET['error']) && $_GET['error'] === 'expired') {
    $error = "Tài khoản của bạn đã được đăng nhập từ một trình duyệt hoặc thiết bị khác. Phiên làm việc này đã kết thúc.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Vui lòng nhập đầy đủ thông tin.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($user && password_verify($password, $user['password'])) {
                // SUCCESS - REGENERATE SESSION TO BE SECURE
                session_regenerate_id(true); // Create new unique ID
                $new_session_id = session_id();

                // UPDATE DATABASE WITH NEW SESSION ID (Forces others out)
                $update = $pdo->prepare("UPDATE users SET session_id = ? WHERE id = ?");
                $update->execute([$new_session_id, $user['id']]);

                // SETUP SESSION GLOBALS
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['login_success'] = true;
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: user_dashboard.php");
                }
                exit;
            } else {
                $error = "Tên đăng nhập hoặc mật khẩu không chính xác.";
            }
        } catch (PDOException $e) {
            $error = "Lỗi hệ thống: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Đăng nhập | Hệ Thống Quản Lý AZMEDIA247</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css">
    
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --bg-body: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --white: #ffffff;
            --radius: 20px;
            --shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }

        body {
            background-color: var(--bg-body);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background-image: radial-gradient(#e2e8f0 1px, transparent 1px);
            background-size: 30px 30px;
        }

        .login-card {
            display: flex;
            background: var(--white);
            width: 100%;
            max-width: 1000px;
            min-height: 600px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            animation: fadeInDown 0.8s ease-out;
        }

        .side-banner {
            flex: 1.2;
            position: relative;
            background: url('https://images.unsplash.com/photo-1560448204-61dc36dc98c8?auto=format&fit=crop&w=1000&q=80') center/cover no-repeat;
            display: block;
        }

        .side-banner::before {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.9), rgba(30, 27, 75, 0.8));
        }

        .banner-content {
            position: relative; z-index: 10; height: 100%; padding: 50px;
            display: flex; flex-direction: column; justify-content: flex-end; color: white;
        }

        .banner-content h2 { font-size: 2.5rem; font-weight: 800; line-height: 1.2; margin-bottom: 15px; }

        .feature-list { list-style: none; margin-top: 20px; }
        .feature-list li { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; font-size: 0.95rem; opacity: 0.9; }

        .form-section { flex: 1; padding: 50px; display: flex; flex-direction: column; justify-content: center; }

        .brand-logo { width: 56px; height: 56px; background: #eef2ff; color: var(--primary); border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 25px; }

        .form-header h1 { font-size: 1.75rem; color: var(--text-main); font-weight: 800; margin-bottom: 8px; }
        .form-header p { color: var(--text-muted); margin-bottom: 35px; }

        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; font-size: 0.875rem; font-weight: 600; color: var(--text-main); margin-bottom: 8px; }

        .input-box { position: relative; }
        .input-box i:not(.toggle-pwd) { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 1.1rem; }
        .input-box input { width: 100%; padding: 14px 16px 14px 48px; border: 2px solid #e2e8f0; border-radius: 12px; outline: none; font-size: 1rem; transition: all 0.3s; }
        .input-box input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1); }

        .toggle-pwd { position: absolute; right: 16px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #94a3b8; }

        .btn-login { width: 100%; padding: 15px; background: var(--primary); color: white; border: none; border-radius: 12px; font-size: 1rem; font-weight: 700; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .btn-login:hover { background: var(--primary-hover); transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4); }

        @media (max-width: 850px) { .side-banner { display: none; } .login-card { max-width: 480px; } .form-section { padding: 40px 30px; } }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="side-banner">
            <div class="banner-content">
                <div class="animate__animated animate__fadeInUp">
                    <h2>Quản lý tối ưu<br>Nhà trọ AZ</h2>
                    <ul class="feature-list">
                        <li><i class="bi bi-shield-lock"></i> Đăng nhập 1 tài khoản/1 thiết bị</li>
                        <li><i class="bi bi-graph-up"></i> Theo dõi doanh thu & phòng</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="brand-logo animate__animated animate__bounceIn"><i class="bi bi-person-badge"></i></div>
            
            <div class="form-header">
                <h1>Đăng nhập</h1>
                <p>Hệ thống Quản lý Phòng trọ Cao cấp</p>
            </div>

            <form action="" method="POST" id="mainLoginForm">
                <div class="input-group">
                    <label>Tên đăng nhập (Email)</label>
                    <div class="input-box">
                        <i class="bi bi-envelope"></i>
                        <input type="text" name="username" placeholder="admin@gmail.com" required>
                    </div>
                </div>

                <div class="input-group">
                    <label>Mật khẩu</label>
                    <div class="input-box">
                        <i class="bi bi-lock"></i>
                        <input type="password" name="password" id="passwordInput" placeholder="••••••••" required>
                        <i class="bi bi-eye-slash toggle-pwd" id="toggleIcon"></i>
                    </div>
                </div>

                <button type="submit" class="btn-login" id="submitBtn">
                    <span>Vào hệ thống</span>
                    <i class="bi bi-arrow-right"></i>
                </button>
                <div style="margin-top: 15px; text-align: center;">
                    <span style="font-size: 0.875rem; color: var(--text-muted);">Chưa có tài khoản? <a href="register.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">Đăng ký</a></span>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Toggle password
        const togglePwd = document.getElementById('toggleIcon');
        const pwdInput = document.getElementById('passwordInput');
        togglePwd.addEventListener('click', () => {
            const type = pwdInput.getAttribute('type') === 'password' ? 'text' : 'password';
            pwdInput.setAttribute('type', type);
            togglePwd.classList.toggle('bi-eye');
            togglePwd.classList.toggle('bi-eye-slash');
        });

        // Error message showing PHP errors or Session Expiration
        <?php if (!empty($error)): ?>
        Swal.fire({
            icon: 'error',
            title: 'Lỗi',
            text: '<?php echo addslashes($error); ?>',
            confirmButtonColor: '#4f46e5'
        });
        <?php endif; ?>
    </script>
</body>
</html>
