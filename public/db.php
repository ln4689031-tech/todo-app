<?php
// db.php = Kết nối DB, session, helper, CSRF
session_start();

// --- Cấu hình DB ---
$DB_HOST = "127.0.0.1";
$DB_NAME = "todo_app";
$DB_USER = "root";
$DB_PASS = ""; // XAMPP không có password

$dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

// --- Tạo kết nối PDO ---
try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}

// --- Escape output (chống XSS) ---
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// --- Tạo CSRF token ---
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// --- Kiểm tra CSRF ---
function verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}