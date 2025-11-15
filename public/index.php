<?php
require 'auth_check.php';

// Filter
$status = $_GET['status'] ?? '';
$order = $_GET['order'] ?? 'due_date ASC';
$allowedOrders = ['due_date ASC','due_date DESC','created_at DESC','created_at ASC'];
if (!in_array($order, $allowedOrders)) $order = 'due_date ASC';

$params = [':uid' => $_SESSION['user_id']];
$where = "WHERE user_id = :uid";
if ($status && in_array($status, ['pending','in_progress','completed'])) {
    $where .= " AND status = :status";
    $params[':status'] = $status;
}

$stmt = $pdo->prepare("SELECT * FROM tasks $where ORDER BY $order");
$stmt->execute($params);
$tasks = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Dashboard — To-Do</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
      min-height: 100vh;
      padding: 30px;
      font-family: "Segoe UI", Roboto, Arial;
    }

    .container-box {
      max-width: 1100px;
      margin: auto;
    }

    .header-area {
      background: white;
      padding: 20px 30px;
      border-radius: 14px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.06);
      margin-bottom: 25px;
    }

    .task-card {
      border-radius: 14px;
      padding: 18px;
      background: #ffffff;
      box-shadow: 0 5px 18px rgba(0,0,0,0.05);
      transition: 0.2s;
    }
    .task-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.07);
    }

    .left-panel {
      background: #ffffff;
      padding: 20px;
      border-radius: 14px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.06);
      height: fit-content;
    }

    .status-badge {
      padding: 4px 10px;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 600;
    }
    .pending { background: #fff4d4; color: #9a6700; }
    .in_progress { background: #dbeafe; color: #1e3a8a; }
    .completed { background: #dcfce7; color: #065f46; }

    .task-title { font-size: 18px; font-weight: 600; }
    .task-desc { color: #444; font-size: 14px; margin-top: 4px; }
    .empty-msg { text-align: center; padding: 40px; color: #666; }
  </style>

</head>
<body>

<div class="container-box">

  <!-- Header -->
  <!-- Header -->
<div class="header-area d-flex justify-content-between align-items-center">

    <div class="d-flex align-items-center gap-3">

        <!-- ⭐ LOGO ⭐ -->
        <img src="img/logo.jpg" 
             alt="Logo" 
             style="width:50px; height:50px; object-fit:contain; border-radius:10px;">

        <div>
            <h3 class="fw-bold m-0">To-Do của <?= e($_SESSION['username']) ?></h3>
            <small class="text-muted">Quản lý công việc cá nhân</small>
        </div>

    </div>

    <a class="btn btn-outline-danger" href="logout.php">Đăng xuất</a>
</div>

    <!-- Left box -->
    <div class="col-md-4">
      <div class="left-panel">
        <h5 class="fw-bold mb-3">➕ Thêm công việc mới</h5>
        <form method="post" action="create_task.php">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

          <label class="form-label">Tiêu đề</label>
          <input class="form-control mb-3" name="title" placeholder="Nhập tiêu đề…" required>

          <label class="form-label">Mô tả</label>
          <textarea class="form-control mb-3" name="description" placeholder="Nhập mô tả…" rows="3"></textarea>

          <label class="form-label">Ngày hết hạn</label>
          <input type="date" class="form-control mb-3" name="due_date">

          <button class="btn btn-primary w-100">Thêm công việc</button>
        </form>
      </div>
    </div>

    <!-- Right content -->
    <div class="col-md-8">

      <div class="mb-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold">📋 Danh sách công việc</h5>
        <span class="text-muted">Tổng: <?= count($tasks) ?></span>
      </div>

      <!-- Filter -->
      <form class="d-flex gap-2 mb-3" method="get">
        <select name="status" class="form-select">
          <option value="">Tất cả trạng thái</option>
          <option value="pending" <?= $status==="pending"?'selected':'' ?>>Pending</option>
          <option value="in_progress" <?= $status==="in_progress"?'selected':'' ?>>In Progress</option>
          <option value="completed" <?= $status==="completed"?'selected':'' ?>>Completed</option>
        </select>

        <select name="order" class="form-select">
          <option value="due_date ASC" <?= $order==="due_date ASC"?'selected':'' ?>>Due ↑</option>
          <option value="due_date DESC" <?= $order==="due_date DESC"?'selected':'' ?>>Due ↓</option>
          <option value="created_at DESC" <?= $order==="created_at DESC"?'selected':'' ?>>Mới nhất</option>
        </select>

        <button class="btn btn-secondary">Lọc</button>
      </form>

      <!-- Task list -->
      <?php if(empty($tasks)): ?>
        <div class="empty-msg">🌱 Bạn chưa có công việc nào — thêm ngay để bắt đầu!</div>
      <?php else: ?>
        <div class="d-flex flex-column gap-3">
          <?php foreach($tasks as $t): ?>
            <div class="task-card d-flex justify-content-between">

              <div>
                <div class="task-title"><?= e($t['title']) ?></div>
                <?php if($t['description']): ?>
                  <div class="task-desc"><?= e($t['description']) ?></div>
                <?php endif; ?>
                <div class="text-muted small mt-2">📅 Hạn: <?= e($t['due_date'] ?: '—') ?></div>
              </div>

              <div class="text-end">
                <div class="status-badge <?= e($t['status']) ?>">
                  <?= e($t['status']) ?>
                </div>

                <div class="mt-2 d-flex gap-2 justify-content-end">
                  <a class="btn btn-sm btn-outline-primary" href="edit_task.php?id=<?= $t['id'] ?>">Sửa</a>
<a class="btn btn-sm btn-outline-danger"
                     href="delete_task.php?id=<?= $t['id'] ?>&csrf=<?= e(csrf_token()) ?>"
                     onclick="return confirm('Bạn chắc muốn xóa?')">
                     Xóa
                  </a>
                </div>
              </div>

            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>

  </div>
</div>

</body>
</html>