<?php
ob_start();
require_once 'db.php';
require_once 'cek-akses.php';
ob_clean();

$action = $_GET['action'] ?? '';
$my_id = $_SESSION['user_id'] ?? null;

if (!$my_id) {
    header('Content-Type: application/json');
    exit(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

// Ensure table exists
try {
    $pdo->query("SELECT 1 FROM messages LIMIT 1");
} catch (Exception $e) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `messages` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `sender_id` int(11) NOT NULL,
      `receiver_id` int(11) NOT NULL,
      `message` text NOT NULL,
      `is_read` tinyint(1) DEFAULT 0,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      INDEX (sender_id), INDEX (receiver_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}

header('Content-Type: application/json');

if ($action === 'list_users') {
    try {
        // Find all users who are NOT admins and NOT current user
        // and fetch their last communication with Admin
        $stmt = $pdo->prepare("SELECT u.id, u.fullname, u.avatar, 
                              (SELECT m1.message FROM messages m1 
                               WHERE (m1.sender_id = u.id AND m1.receiver_id = :myid1) 
                               OR (m1.sender_id = :myid2 AND m1.receiver_id = u.id) 
                               ORDER BY m1.created_at DESC LIMIT 1) as last_msg,
                              (SELECT m2.created_at FROM messages m2 
                               WHERE (m2.sender_id = u.id AND m2.receiver_id = :myid3) 
                               OR (m2.sender_id = :myid4 AND m2.receiver_id = u.id) 
                               ORDER BY m2.created_at DESC LIMIT 1) as last_time,
                              (SELECT COUNT(*) FROM messages m3 
                               WHERE m3.sender_id = u.id AND m3.receiver_id = :myid5 AND m3.is_read = 0) as unread_count
                              FROM users u 
                              WHERE u.id != :myid6
                              ORDER BY last_time DESC, u.fullname ASC");
        $stmt->execute([
            'myid1' => $my_id, 'myid2' => $my_id, 
            'myid3' => $my_id, 'myid4' => $my_id, 
            'myid5' => $my_id, 'myid6' => $my_id
        ]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (Exception $e) {
        echo json_encode([]);
    }
}

elseif ($action === 'fetch') {
    $other_id = $_GET['other_id'] ?? $_GET['with_id'] ?? null;
    if (!$other_id) exit(json_encode([]));
    
    try {
        // Mark messages as read where I am the receiver and the other is sender
        $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0");
        $stmt->execute([$other_id, $my_id]);
        
        // Fetch all messages in the conversation
        $stmt = $pdo->prepare("SELECT * FROM messages 
                               WHERE (sender_id = ? AND receiver_id = ?) 
                               OR (sender_id = ? AND receiver_id = ?) 
                               ORDER BY created_at ASC");
        $stmt->execute([$my_id, $other_id, $other_id, $my_id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (isset($_GET['with_id'])) {
            echo json_encode(['status' => 'success', 'data' => $messages]);
        } else {
            echo json_encode($messages);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

elseif ($action === 'send') {
    $receiver_id = $_POST['receiver_id'] ?? null;
    $message = trim($_POST['message'] ?? '');
    
    if (!$receiver_id || empty($message)) {
        exit(json_encode(['status' => 'error', 'message' => 'Missing data']));
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$my_id, $receiver_id, $message]);
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

elseif ($action === 'delete_history') {
    $other_id = $_POST['other_id'] ?? null;
    if (!$other_id) exit(json_encode(['status' => 'error', 'message' => 'Missing ID']));
    
    try {
        $stmt = $pdo->prepare("DELETE FROM messages 
                               WHERE (sender_id = ? AND receiver_id = ?) 
                               OR (sender_id = ? AND receiver_id = ?)");
        $stmt->execute([$my_id, $other_id, $other_id, $my_id]);
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

elseif ($action === 'get_admin') {
    try {
        // Get the first user with 'admin' role, or default to ID 1
        $stmt = $pdo->query("SELECT id FROM users WHERE role = 'admin' ORDER BY id ASC LIMIT 1");
        $admin = $stmt->fetch();
        echo json_encode(['status' => 'success', 'admin_id' => $admin['id'] ?? 1]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'success', 'admin_id' => 1]);
    }
}


