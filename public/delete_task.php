<?php
require 'auth_check.php';
$id = intval($_GET['id'] ?? 0);
$csrf = $_GET['csrf'] ?? '';
if (!verify_csrf($csrf)) { die('CSRF token invalid'); }
if ($id) {
    $del = $pdo->prepare("DELETE FROM tasks WHERE id=:id AND user_id=:uid");
    $del->execute([':id'=>$id, ':uid'=>$_SESSION['user_id']]);
}
header('Location: index.php');
exit;