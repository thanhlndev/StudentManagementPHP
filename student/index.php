<?php
// student/index.php
require_once 'includes/header.php';
?>

<div id="wrapper">
    <?php require_once 'includes/sidebar.php'; ?>
    <?php require_once 'includes/topbar.php'; ?>
    <div class="container-fluid">
        <?php
        try {
            $page = $_GET['page'] ?? 'dashboard';
            $allowed_pages = ['dashboard', 'course_classes', 'class_detail', 'grades', 'timetable', 'profile'];

            if (in_array($page, $allowed_pages) && file_exists($page . '.php')) {
                require_once $page . '.php';
            } else {
                echo "<div class='alert alert-info shadow-sm text-center font-weight-bold py-5 mt-4'>
                        <i class='fas fa-graduation-cap fa-3x mb-3'></i><br>
                        <h5>Chào mừng bạn đến với Cổng thông tin Sinh viên!</h5>
                        <p class='mb-0'>Sử dụng thanh công cụ bên trái để tra cứu điểm và đăng ký học phần.</p>
                      </div>";
            }
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger shadow-sm border-left-danger p-4 mt-4'>
                    <h5 class='font-weight-bold'><i class='fas fa-database'></i> Lỗi truy xuất dữ liệu</h5>
                    <p class='mb-0'>Hệ thống đang gặp sự cố. Nguyên nhân: <b>" . htmlspecialchars($e->getMessage()) . "</b></p>
                  </div>";
        }
        ?>
    </div>
    <?php require_once 'includes/footer.php'; ?>