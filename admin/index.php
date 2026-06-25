<?php
// admin/index.php
// Quá trình kiểm tra Session/Cookie đã được đóng gói an toàn bên trong header.php
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
require_once 'includes/topbar.php';
?>

<div class="container-fluid">
    <?php
    // Bắt tham số page, mặc định hiển thị trang dashboard
    $page = $_GET['page'] ?? 'dashboard';

    // Router: Cấu hình các trang được phép truy cập
    $allowed_pages = [
        'dashboard',
        'faculties',
        'courses',
        'teachers',
        'semesters',
        'classes',
        'course_classes',
        'grades',
        'grade_detail',
        'students',
        'accounts',
        'profile',
        'rooms',
        'course_class_detail',
    ];

    if (in_array($page, $allowed_pages) && file_exists($page . '.php')) {
        require_once $page . '.php';
    } else {
        echo "<div class='alert alert-danger shadow-sm text-center font-weight-bold py-4 mt-4'>
                <i class='fas fa-exclamation-triangle fa-2x text-warning mb-2'></i><br>
                Trang bạn yêu cầu không tồn tại hoặc hệ thống đang bảo trì.
              </div>";
    }
    ?>
</div>

<?php
// Nạp Modal và các thẻ Script ở cuối trang
require_once 'includes/footer.php';
?>