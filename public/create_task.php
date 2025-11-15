<?php
require 'auth_check.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }
if (!verify_csrf($_POST['csrf_token'] ?? '')) { die('CSRF token invalid'); }

$title = trim($_POST['title'] ?? '');
$desc = $_POST['description'] ?? null;
$due = $_POST['due_date'] ?: null;

if ($title === '') { $_SESSION['flash_error'] = 'Tiêu đề bắt buộc'; header('Location: index.php'); exit; }

$stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, due_date) VALUES (:uid, :title, :desc, :due)");
$stmt->execute([':uid'=>$_SESSION['user_id'], ':title'=>$title, ':desc'=>$desc, ':due'=>$due]);
header('Location: index.php');
exit;