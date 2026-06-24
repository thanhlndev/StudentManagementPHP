<?php
session_start();
require_once __DIR__ . '/auth/session_check.php'; // Kiểm tra cookie trước
// 1. Nếu CHƯA ĐĂNG NHẬP -> Đẩy về trang Login
if (!isset($_SESSION['user'])) {
    header("Location: auth/login.php");
    exit;
}

// 2. Nếu ĐÃ ĐĂNG NHẬP -> Tự động đẩy về đúng phân hệ
$role = strtolower($_SESSION['user']['role']); // giả sử role là 'admin', 'teacher', 'student'

// Điều hướng dựa trên role
// Ví dụ: Student -> student/dashboard.php, Admin -> admin/dashboard.php
header("Location: " . $role);
exit;