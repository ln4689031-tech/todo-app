<?php
require 'auth_check.php';
$id = intval($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) { die('CSRF token invalid'); }
    $id = intval($_POST['id']);
    $title = trim($_POST['title']);
    $desc = $_POST['description'] ?: null;
    $due = $_POST['due_date'] ?: null;
    $status = $_POST['status'] ?? 'pending';
    $upd = $pdo->prepare("UPDATE tasks SET title=:t, description=:d, due_date=:due, status=:s WHERE id=:id AND user_id=:uid");
    $upd->execute([':t'=>$title, ':d'=>$desc, ':due'=>$due, ':s'=>$status, ':id'=>$id, ':uid'=>$_SESSION['user_id']]);
    header('Location: index.php'); exit;
}

$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id=:id AND user_id=:uid");
$stmt->execute([':id'=>$id, ':uid'=>$_SESSION['user_id']]);
$task = $stmt->fetch();
if (!$task) { header('Location: index.php'); exit; }
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Sửa</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{display:flex;align-items:center;justify-content:center;height:100vh;background:#f6f8fb}.card{width:460px;padding:20px;border-radius:12px}</style>
</head>
<body>
  <div class="card">
    <h5>Sửa công việc</h5>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="id" value="<?= e($task['id']) ?>">
      <div class="mb-2"><input name="title" class="form-control" value="<?= e($task['title']) ?>" required></div>
      <div class="mb-2"><textarea name="description" class="form-control"><?= e($task['description']) ?></textarea></div>
      <div class="mb-2"><input type="date" name="due_date" class="form-control" value="<?= e($task['due_date']) ?>"></div>
      <div class="mb-2">
        <select name="status" class="form-select">
          <option value="pending" <?= $task['status']=='pending'?'selected':'' ?>>pending</option>
          <option value="in_progress" <?= $task['status']=='in_progress'?'selected':'' ?>>in_progress</option>
          <option value="completed" <?= $task['status']=='completed'?'selected':'' ?>>completed</option>
        </select>
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-primary">Lưu</button>
        <a href="index.php" class="btn btn-outline-secondary">Hủy</a>
      </div>
    </form>
  </div>
</body></html>