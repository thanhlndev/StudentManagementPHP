<?php
// teacher/index.php
require_once 'includes/header.php';
?>

<div id="wrapper">

    <?php require_once 'includes/sidebar.php'; ?>

    <?php require_once 'includes/topbar.php'; ?>

    <div class="container-fluid">
        <?php
        try {
            // Lấy tham số page, mặc định là dashboard
            $page = $_GET['page'] ?? 'dashboard';

            // Danh sách các trang được phép truy cập
            $allowed_pages = ['dashboard', 'grades', 'grade_detail', 'profile'];

            if (in_array($page, $allowed_pages) && file_exists($page . '.php')) {
                require_once $page . '.php';
            } else {
                echo "<div class='alert alert-info shadow-sm mt-4 text-center py-4'>
                        <i class='fas fa-info-circle fa-2x mb-2 text-info'></i><br>
                        Vui lòng chọn một chức năng trên Menu bên trái để bắt đầu làm việc.
                      </div>";
            }
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger shadow-sm mt-4 p-4 border-left-danger'>
                    <h5 class='font-weight-bold'><i class='fas fa-database'></i> Lỗi truy vấn Cơ sở dữ liệu!</h5>
                    <hr>
                    <p class='mb-0'>Hệ thống không thể lấy dữ liệu. Nguyên nhân: <b>" . htmlspecialchars($e->getMessage()) . "</b></p>
                  </div>";
        } catch (Exception $e) {
            // Bắt các lỗi hệ thống khác
            echo "<div class='alert alert-danger shadow-sm mt-4'>Lỗi hệ thống: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        ?>
    </div>
    <?php require_once 'includes/footer.php'; ?>