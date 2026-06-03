<style>
  #neubar {
    z-index: 990; /* Đặt dưới sidebar một chút để sidebar đè lên trên PC */
    background: rgb(19, 48, 90);
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 70px;
    box-shadow: 4px 6px 12px rgba(0, 0, 0, 0.4);
  }
  
  /* Chữ TEAM MINH QUÂN DEV */
  .team-name {
    color: white; 
    font-weight: 700; 
    font-size: 1.5rem;
    margin: 0;
  }

  /* Tùy chỉnh cho điện thoại */
  @media (max-width: 768px) {
    .team-name { font-size: 1.1rem; }
  }
</style>

<nav class="navbar navbar-expand-lg navbar-dark" id="neubar">
  <div class="container-fluid px-3">
    
    <button class="navbar-toggler border-0 shadow-none d-md-none" type="button" id="mobile-nav-toggle" style="background-color: rgba(255,255,255,0.1);">
        <span class="navbar-toggler-icon"></span>
    </button>

    <a class="navbar-brand ms-2 d-inline-block d-md-none" href="#">
        <img src="uploads/asset/favicon.ico" height="40" style="border-radius: 8px;" onerror="this.src='https://cdn-icons-png.flaticon.com/512/25/25694.png'" />
    </a>

    <div class="ms-auto d-flex align-items-center gap-3">
      <?php 
      if (isset($_SESSION['user_id'])) {
          try {
              if (!isset($pdo)) { require_once 'db.php'; }
              $chat_badge = $pdo->query("SELECT COUNT(*) FROM messages WHERE receiver_id = {$_SESSION['user_id']} AND is_read = 0")->fetchColumn();
              echo '<a href="chat.php" class="text-white position-relative hover:opacity-75 transition-all">
                      <i class="bi bi-chat-dots-fill fs-4"></i>';
              if ($chat_badge > 0) {
                  echo '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.55rem; padding: 0.35em 0.5em;">' . $chat_badge . '</span>';
              }
              echo '</a>';
          } catch (Exception $e) {
              // Ignore if table doesn't exist
          }
      }
      ?>
      <h1 class="team-name">TEAM MINH QUÂN DEV</h1>
    </div>

  </div>
</nav>