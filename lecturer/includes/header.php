<?php
ob_start();
session_start();
require_once '../config/database.php';
$pdo = getDBConnection();

// Đề phòng trường hợp database.php chưa định nghĩa BASE_URL
if (!defined('BASE_URL')) {
    define('BASE_URL', '/StudentManagement/');
}

// ==========================================
// CHỐT CHẶN BẢO MẬT DÀNH RIÊNG CHO GIẢNG VIÊN
// ==========================================
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Lecturer') {
    unset($_SESSION['user']);
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Cổng Giảng Viên - Quản lý Sinh viên</title>

    <link href="<?= BASE_URL ?>vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">

    <link href="<?= BASE_URL ?>css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body id="page-top">