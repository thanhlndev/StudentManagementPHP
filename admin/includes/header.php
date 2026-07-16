<?php
ob_start();
session_start();
require_once '../config/database.php';
$pdo = getDBConnection();


// 1. TÁI TẠO SESSION TỪ COOKIE (Phải chạy trước chốt chặn)
if (!isset($_SESSION['user']) && isset($_COOKIE['logged_in_user'])) {
    $cookieData = json_decode($_COOKIE['logged_in_user'], true);

    if ($cookieData) {
        // Xác thực lại với CSDL: Tài khoản có còn tồn tại, đang là Admin và có bị khóa không?
        $stmt = $pdo->prepare("SELECT isActive FROM Accounts WHERE username = ? AND role = 'Admin' LIMIT 1");
        $stmt->execute([$cookieData['username']]);
        $account = $stmt->fetch();

        // Nếu hợp lệ, cấp lại Session ngay lập tức
        if ($account && (int) $account['isActive'] === 1) {
            $_SESSION['user'] = [
                'username' => $cookieData['username'],
                'role' => $cookieData['role']
            ];
        }
    }
}

// Nếu đến bước này mà vẫn không có Session (hoặc Cookie không hợp lệ), hất văng ra ngoài
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    unset($_SESSION['user']);
    header("Location: " . BASE_URL . "auth/login.php?error=Unauthorized");
    exit;
}


// // admin/includes/header.php
// ob_start();
// session_start();
// require_once '../config/database.php';
// $pdo = getDBConnection();

// // Khai báo hằng số đường dẫn gốc để không bị lỗi file CSS/JS khi thay đổi cấp thư mục
// define('BASE_URL', '/StudentManagement/');


?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Hệ thống Quản lý Sinh viên</title>
    <link href="<?= BASE_URL ?>vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <link href="<?= BASE_URL ?>css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body id="page-top">
    <div id="wrapper">