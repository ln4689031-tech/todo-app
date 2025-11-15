<?php
require 'db.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'CSRF token không hợp lệ';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') $errors[] = 'Nhập username và password';

        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :u LIMIT 1");
            $stmt->execute([':u'=>$username]);
            $user = $stmt->fetch();
            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header('Location: index.php');
                exit;
            } else {
                $errors[] = 'Username hoặc mật khẩu không đúng';
            }
        }
    }
}
$justRegistered = isset($_GET['registered']);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Đăng nhập — To-Do App</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    html,body { height:100%; }
    body {
      display:flex;
      align-items:center;
      justify-content:center;
      background: linear-gradient(135deg,#fbfafc,#fff7ed);
    }
    .card { width:100%; max-width:420px; padding:22px; border-radius:12px; box-shadow:0 8px 24px rgba(3,7,18,0.06); }
    .brand { color:#ef4444; font-weight:700; }
  </style>
</head>
<body>
  <div class="card">
    <div class="text-center mb-3">
	<img src="img/logo.jpg" 
         alt="Logo" 
         style="width:80px; height:80px; object-fit:contain;">
      <div class="brand">Simple To-Do</div>
      <div class="text-muted">Đăng nhập để quản lý công việc</div>
    </div>

    <?php if($justRegistered): ?><div class="alert alert-success small">Đăng ký thành công. Vui lòng đăng nhập.</div><?php endif; ?>
    <?php if(!empty($errors)): ?><div class="alert alert-danger small"><?php foreach($errors as $er) echo '<div>'.e($er).'</div>'; ?></div><?php endif; ?>

    <form method="post" autocomplete="off">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <div class="mb-2">
        <label class="form-label">Username</label>
        <input name="username" class="form-control" value="<?= e($_POST['username'] ?? '') ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Mật khẩu</label>
        <input name="password" type="password" class="form-control" required>
      </div>
      <div class="d-grid">
        <button class="btn btn-primary">Đăng nhập</button>
      </div>
      <div class="text-center mt-3"><a href="register.php">Chưa có tài khoản? Đăng ký</a></div>
    </form>
  </div>
</body>
</html>