<?php
require 'db.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'CSRF token không hợp lệ';
    } else {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';

        if ($username === '') $errors[] = 'Username bắt buộc';
        if ($password === '' || $password !== $password2) $errors[] = 'Mật khẩu rỗng hoặc không khớp';
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ';
        if (strlen($password) < 6) $errors[] = 'Mật khẩu tối thiểu 6 ký tự';

        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :u OR (email IS NOT NULL AND email = :e)");
            $stmt->execute([':u'=>$username, ':e'=>$email]);
            if ($stmt->fetch()) {
                $errors[] = 'Username hoặc email đã tồn tại';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $ins = $pdo->prepare("INSERT INTO users (username, password, email) VALUES (:u, :p, :e)");
                $ins->execute([':u'=>$username, ':p'=>$hash, ':e'=>($email ?: null)]);
                header('Location: login.php?registered=1');
                exit;
            }
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Đăng ký — To-Do App</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* Center card vertically and horizontally */
    html,body { height:100%; }
    body {
      display:flex;
      align-items:center;
      justify-content:center;
      background: linear-gradient(135deg,#f8fafc,#eef2ff);
    }
    .card { width: 100%; max-width: 420px; border-radius:12px; box-shadow:0 10px 30px rgba(2,6,23,0.08); }
    .brand { font-weight:700; color:#4f46e5; letter-spacing:0.4px; }
    footer { text-align:center; margin-top:12px; color:#666; font-size:13px; }
  </style>
</head>
<body>
  <div class="card p-4">
    <div class="text-center mb-3">
	<img src="img/logo.jpg" 
         alt="Logo" 
         style="width:80px; height:80px; object-fit:contain;">
      <div class="brand">Simple To-Do</div>
      <div class="text-muted">Tạo tài khoản mới</div>
    </div>

    <?php if(!empty($errors)): ?>
      <div class="alert alert-danger small">
        <?php foreach($errors as $er) echo '<div>'.e($er).'</div>'; ?>
      </div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <div class="mb-2">
        <label class="form-label">Username</label>
        <input name="username" class="form-control" value="<?= e($_POST['username'] ?? '') ?>" required>
      </div>
      <div class="mb-2">
<label class="form-label">Email (tùy)</label>
        <input name="email" class="form-control" value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <div class="mb-2">
        <label class="form-label">Mật khẩu</label>
        <input name="password" type="password" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Nhập lại mật khẩu</label>
        <input name="password2" type="password" class="form-control" required>
      </div>
      <div class="d-grid">
        <button class="btn btn-primary">Đăng ký</button>
      </div>
      <div class="text-center mt-3">
        <a href="login.php">Đã có tài khoản? Đăng nhập</a>
      </div>
    </form>

  </div>
</body>
</html>
