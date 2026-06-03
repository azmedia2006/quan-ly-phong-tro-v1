<?php
/**
 * File: cookie.php
 * Deskripsi: Mengelola cookie untuk menjaga sesi login user pada aplikasi MyKost
 */

// Fungsi untuk membuat cookie persisten saat login berhasil dan "Remember Me" dicentang
if (!function_exists('createPersistentCookie')) {
    function createPersistentCookie($user_id, $email) {
        // Membuat token unik untuk authentikasi
        $token = bin2hex(random_bytes(32)); // Membuat token acak yang aman
        
        // Set waktu kedaluwarsa (30 hari dari sekarang)
        $expiry = time() + (30 * 24 * 60 * 60);
        
        // Simpan token di database untuk validasi nanti
        global $pdo;
        
        try {
            // Hapus token lama untuk user ini (jika ada)
            $stmt = $pdo->prepare("DELETE FROM user_tokens WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $user_id]);
            
            // Simpan token baru
            $stmt = $pdo->prepare("INSERT INTO user_tokens (user_id, token, expiry) VALUES (:user_id, :token, :expiry)");
            $stmt->execute([
                'user_id' => $user_id,
                'token' => $token,
                'expiry' => $expiry
            ]);
            
            // Set cookie di browser pengguna (HTTP-only untuk keamanan)
            setcookie('mykost_session', $token, $expiry, '/', '', isset($_SERVER['HTTPS']), true);
            
            return true;
        } catch (PDOException $e) {
            // Log error jika perlu
            error_log("Cookie creation error: " . $e->getMessage());
            return false;
        }
    }
}

// Fungsi untuk memeriksa dan memvalidasi cookie saat pengguna mengakses situs
if (!function_exists('validateUserCookie')) {
    function validateUserCookie() {
        // Cek apakah cookie tersedia
        if (isset($_COOKIE['mykost_session'])) {
            $token = $_COOKIE['mykost_session'];
            
            // Cek apakah sudah ada session aktif
            if (isset($_SESSION['user_id'])) {
                // User sudah login via session, tidak perlu validasi cookie
                return true;
            }
            
            // Validasi token dari cookie dengan database
            global $pdo;
            
            try {
                $stmt = $pdo->prepare("SELECT ut.*, u.email 
                                      FROM user_tokens ut 
                                      JOIN user u ON ut.user_id = u.id 
                                      WHERE ut.token = :token AND ut.expiry > :current_time");
                $current_time = time();
                $stmt->execute([
                    'token' => $token,
                    'current_time' => $current_time
                ]);
                
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result) {
                    // Set session berdasarkan data dari cookie
                    $_SESSION['user_id'] = $result['user_id'];
                    $_SESSION['user_email'] = $result['email'];
                    
                    return true;
                } else {
                    // Token tidak valid atau kedaluwarsa, hapus cookie
                    clearAuthCookie();
                    return false;
                }
            } catch (PDOException $e) {
                // Log error jika perlu
                error_log("Cookie validation error: " . $e->getMessage());
                return false;
            }
        }
        
        return false;
    }
}

// Fungsi untuk menghapus cookie saat logout
if (!function_exists('clearAuthCookie')) {
    function clearAuthCookie() {
        // Hapus cookie dengan mengatur waktu kedaluwarsa ke masa lalu
        setcookie('mykost_session', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
        
        // Hapus token dari database jika session user_id tersedia
        if (isset($_SESSION['user_id'])) {
            global $pdo;
            
            try {
                $user_id = $_SESSION['user_id'];
                $stmt = $pdo->prepare("DELETE FROM user_tokens WHERE user_id = :user_id");
                $stmt->execute(['user_id' => $user_id]);
            } catch (PDOException $e) {
                // Log error jika perlu
                error_log("Token deletion error: " . $e->getMessage());
            }
        }
    }
}

// Panggil fungsi validasi secara otomatis saat file ini diinclude
if (!isset($_SESSION['user_id'])) {
    validateUserCookie();
}
?>