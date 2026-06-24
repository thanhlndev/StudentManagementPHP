<?php
// auth/logout.php
session_start();

// 1. Xóa sạch Session trên Server
$_SESSION = array();
session_destroy();

// 2. XÓA COOKIE DỮ LIỆU USER (Đặt thời gian hết hạn về 1 tiếng trước)
if (isset($_COOKIE['logged_in_user'])) {
    setcookie('logged_in_user', '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

// 3. Điều hướng về trang login
header("Location: login.php");
exit;