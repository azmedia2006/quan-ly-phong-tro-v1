<?php
/**
 * Base functions for boarding house pages
 * This file contains reusable functions used by quanlytro1.php, quanlytro2.php, and quanlytro3.php
 */

// Common processing code for all boarding house pages
function getKostData($pdo, $nama_kost, $search = '', $limit = 10) {
    // Fetch boarding house data with specified kost name
    if (!empty($search)) {
        $stmt = $pdo->prepare("SELECT * FROM data_kost WHERE 
                            nama_kost = ? AND
                            (jumlah_penghuni LIKE ? OR 
                            nama_penghuni LIKE ? OR 
                            no_hp LIKE ? OR
                            no_kamar LIKE ? OR
                            status_pembayaran LIKE ?)
                            ORDER BY id DESC");
        $searchParam = "%$search%";
        $stmt->execute([$nama_kost, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM data_kost WHERE nama_kost = ? ORDER BY id DESC");
        $stmt->execute([$nama_kost]);
    }

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Process form submission for adding data
function processAdd($pdo) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
        try {
            $nama_kost = $_POST['nama_kost'];
            $jumlah_penghuni = $_POST['jumlah_penghuni'];
            $nama_penghuni = $_POST['nama_penghuni'];
            $no_hp = $_POST['no_hp'];
            $no_kamar = $_POST['no_kamar'];
            $tanggal_masuk = $_POST['tanggal_masuk'];
            $status_pembayaran = $_POST['status_pembayaran'];
            
            // Handle file upload
            $foto_penghuni = null;
            if (isset($_FILES['foto_penghuni']) && $_FILES['foto_penghuni']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['foto_penghuni']['name'];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                if (in_array($ext, $allowed)) {
                    // Check file size (max 2MB)
                    if ($_FILES['foto_penghuni']['size'] <= 2 * 1024 * 1024) {
                        $upload_dir = 'uploads/kost/';
                        
                        // Create upload directory if not exists
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                        
                        $new_filename = uniqid() . '.' . $ext;
                        $upload_path = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($_FILES['foto_penghuni']['tmp_name'], $upload_path)) {
                            $foto_penghuni = $upload_path;
                        } else {
                            throw new Exception("Gagal mengupload file.");
                        }
                    } else {
                        throw new Exception("Ukuran file terlalu besar. Maksimum 2MB.");
                    }
                } else {
                    throw new Exception("Tipe file tidak diizinkan. Format yang diizinkan: JPG, JPEG, PNG, GIF.");
                }
            }
            
            // Update kamar status to Terisi
            $stmt_update_kamar = $pdo->prepare("UPDATE data_kamar SET status = 'Terisi' WHERE nama_kost = ? AND no_kamar = ?");
            $stmt_update_kamar->execute([$nama_kost, $no_kamar]);
            
            // Insert kost data
            $stmt = $pdo->prepare("INSERT INTO data_kost (nama_kost, foto_penghuni, jumlah_penghuni, nama_penghuni, no_hp, no_kamar, tanggal_masuk, status_pembayaran) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nama_kost, $foto_penghuni, $jumlah_penghuni, $nama_penghuni, $no_hp, $no_kamar, $tanggal_masuk, $status_pembayaran]);
            
            $_SESSION['flash_message'] = "Data kost berhasil ditambahkan!";
            $_SESSION['flash_type'] = "success";
        } catch (PDOException $e) {
            $_SESSION['flash_message'] = "Error: " . $e->getMessage();
            $_SESSION['flash_type'] = "danger";
        } catch (Exception $e) {
            $_SESSION['flash_message'] = $e->getMessage();
            $_SESSION['flash_type'] = "danger";
        }
        
        return true; // Indicate that a redirect should happen
    }
    
    return false;
}

// Process form submission for editing data
function processEdit($pdo) {
    
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {
        try {
            $id = $_POST['id'];
            $nama_kost = $_POST['nama_kost'];
            $jumlah_penghuni = $_POST['jumlah_penghuni'];
            $nama_penghuni = $_POST['nama_penghuni'];
            $no_hp = $_POST['no_hp'];
            $no_kamar = $_POST['no_kamar'];
            $no_kamar_lama = $_POST['no_kamar_lama'];
            $tanggal_masuk = $_POST['tanggal_masuk'];
            $status_pembayaran = $_POST['status_pembayaran'];
            
            // Check if room assignment changed
            $room_changed = ($no_kamar != $no_kamar_lama);
            
            // Handle file upload
            $foto_penghuni = $_POST['foto_existing'];
            if (isset($_FILES['foto_penghuni']) && $_FILES['foto_penghuni']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['foto_penghuni']['name'];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                if (in_array($ext, $allowed)) {
                    // Check file size (max 2MB)
                    if ($_FILES['foto_penghuni']['size'] <= 2 * 1024 * 1024) {
                        $upload_dir = 'uploads/kost/';
                        
                        // Create upload directory if not exists
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                        
                        $new_filename = uniqid() . '.' . $ext;
                        $upload_path = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($_FILES['foto_penghuni']['tmp_name'], $upload_path)) {
                            // Delete old photo if exists
                            if (!empty($_POST['foto_existing']) && file_exists($_POST['foto_existing'])) {
                                unlink($_POST['foto_existing']);
                            }
                            $foto_penghuni = $upload_path;
                        } else {
                            throw new Exception("Gagal mengupload file.");
                        }
                    } else {
                        throw new Exception("Ukuran file terlalu besar. Maksimum 2MB.");
                    }
                } else {
                    throw new Exception("Tipe file tidak diizinkan. Format yang diizinkan: JPG, JPEG, PNG, GIF.");
                }
            }
            
            if ($room_changed) {
                // Set old room to Tersedia
                $stmt_update_old_kamar = $pdo->prepare("UPDATE data_kamar SET status = 'Tersedia' WHERE nama_kost = ? AND no_kamar = ?");
                $stmt_update_old_kamar->execute([$nama_kost, $no_kamar_lama]);
                
                // Set new room to Terisi
                $stmt_update_new_kamar = $pdo->prepare("UPDATE data_kamar SET status = 'Terisi' WHERE nama_kost = ? AND no_kamar = ?");
                $stmt_update_new_kamar->execute([$nama_kost, $no_kamar]);
            }
            
            // Update kost data
            $stmt = $pdo->prepare("UPDATE data_kost SET foto_penghuni = ?, jumlah_penghuni = ?, nama_penghuni = ?, no_hp = ?, no_kamar = ?, tanggal_masuk = ?, status_pembayaran = ? WHERE id = ?");
            $stmt->execute([$foto_penghuni, $jumlah_penghuni, $nama_penghuni, $no_hp, $no_kamar, $tanggal_masuk, $status_pembayaran, $id]);
            
            $_SESSION['flash_message'] = "Data kost berhasil diperbarui!";
            $_SESSION['flash_type'] = "success";
        } catch (PDOException $e) {
            $_SESSION['flash_message'] = "Error: " . $e->getMessage();
            $_SESSION['flash_type'] = "danger";
        } catch (Exception $e) {
            $_SESSION['flash_message'] = $e->getMessage();
            $_SESSION['flash_type'] = "danger";
        }
        
        return true; // Indicate that a redirect should happen
    }
    
    return false;
}

// Process deletion
function processDelete($pdo) {
    if (isset($_GET['delete'])) {
        try {
            $id = $_GET['delete'];
            
            // Get room information before deleting
            $stmt_get_room = $pdo->prepare("SELECT nama_kost, no_kamar, foto_penghuni FROM data_kost WHERE id = ?");
            $stmt_get_room->execute([$id]);
            $room_info = $stmt_get_room->fetch(PDO::FETCH_ASSOC);
            
            if ($room_info) {
                // Set room status back to Tersedia
                $stmt_update_kamar = $pdo->prepare("UPDATE data_kamar SET status = 'Tersedia' WHERE nama_kost = ? AND no_kamar = ?");
                $stmt_update_kamar->execute([$room_info['nama_kost'], $room_info['no_kamar']]);
                
                // Delete photo if exists
                if (!empty($room_info['foto_penghuni']) && file_exists($room_info['foto_penghuni'])) {
                    unlink($room_info['foto_penghuni']);
                }
                
                // Delete kost data
                $stmt_delete = $pdo->prepare("DELETE FROM data_kost WHERE id = ?");
                $stmt_delete->execute([$id]);
                
                $_SESSION['flash_message'] = "Data kost berhasil dihapus!";
                $_SESSION['flash_type'] = "success";
            } else {
                $_SESSION['flash_message'] = "Data kost tidak ditemukan.";
                $_SESSION['flash_type'] = "warning";
            }
        } catch (PDOException $e) {
            $_SESSION['flash_message'] = "Error: " . $e->getMessage();
            $_SESSION['flash_type'] = "danger";
        }
        
        return true; // Indicate that a redirect should happen
    }
    
    return false;
}

// Get available rooms for a specific boarding house
function getAvailableRooms($pdo, $nama_kost) {
    $stmt = $pdo->prepare("SELECT no_kamar FROM data_kamar WHERE nama_kost = ? AND status = 'Tersedia' ORDER BY no_kamar");
    $stmt->execute([$nama_kost]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



// Function to check and mark overdue payments
function checkOverduePayments($pdo) {
    // Calculate due date (e.g., 5th of current month)
    $dueDate = date('Y-m-d', strtotime(date('Y-m') . '-05'));
    $graceperiod = 7; // 7 days grace period after due date
    $today = date('Y-m-d');
    
    // Only mark as overdue if we're past the due date plus grace period
    if ($today > date('Y-m-d', strtotime($dueDate . ' + ' . $graceperiod . ' days'))) {
        try {
            // First, get all unpaid residents
            $stmt = $pdo->prepare("UPDATE data_kost 
                                   SET status_pembayaran = 'Nunggak' 
                                   WHERE status_pembayaran = 'Belum Dibayar'
                                   AND tanggal_masuk < ?");
            $stmt->execute([$dueDate]);
            
            return $stmt->rowCount(); // Return number of affected records
        } catch (PDOException $e) {
            error_log("Error checking overdue payments: " . $e->getMessage());
            return 0;
        }
    }
    
    return 0;
}
?>