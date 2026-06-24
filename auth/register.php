<?php
// auth/register.php
session_start();
require_once '../config/database.php';
$pdo = getDBConnection();

$msg = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        $msg = "Vui lòng nhập đầy đủ Tài khoản và Mật khẩu!";
        $msgType = "danger";
    } else {
        try {
            // Kiểm tra xem username đã tồn tại chưa
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM Accounts WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetchColumn() > 0) {
                $msg = "Tài khoản '$username' đã tồn tại trong hệ thống!";
                $msgType = "warning";
            } else {
                // Mã hóa Bcrypt theo chuẩn bảo mật PHP
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Chèn tài khoản mới với role cố định là Admin
                $insertSql = "INSERT INTO Accounts (username, password, role, isActive) VALUES (?, ?, 'Admin', 1)";
                $pdo->prepare($insertSql)->execute([$username, $hashedPassword]);
                
                $msg = "Tạo tài khoản Admin thành công! Bạn có thể đăng nhập ngay bây giờ.";
                $msgType = "success";
            }
        } catch (PDOException $e) {
            $msg = "Lỗi Database: " . $e->getMessage();
            $msgType = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Cấp cứu - Tạo tài khoản Admin</title>
    <link href="<?= BASE_URL ?>css/sb-admin-2.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
</head>
<body class="bg-gradient-dark">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-6 col-md-9 mt-5">
                
                <div class="alert alert-warning text-center font-weight-bold shadow-sm">
                    <i class="fas fa-exclamation-triangle"></i> FILE CẤP CỨU: HÃY XÓA FILE NÀY SAU KHI TẠO XONG TÀI KHOẢN!
                </div>

                <div class="card o-hidden border-0 shadow-lg my-4">
                    <div class="card-body p-0">
                        <div class="p-5">
                            <div class="text-center">
                                <h1 class="h4 text-gray-900 mb-4 font-weight-bold">Tạo Tài Khoản Admin</h1>
                            </div>
                            
                            <?php if ($msg): ?>
                                <div class="alert alert-<?= $msgType ?>"><?= $msg ?></div>
                            <?php endif; ?>

                            <form method="POST" action="register.php" class="user">
                                <div class="form-group">
                                    <input type="text" name="username" class="form-control form-control-user" placeholder="Nhập Username mới (vd: admin_vip)..." required>
                                </div>
                                <div class="form-group">
                                    <input type="password" name="password" class="form-control form-control-user" placeholder="Nhập Mật khẩu..." required>
                                </div>
                                <button type="submit" class="btn btn-danger btn-user btn-block font-weight-bold">
                                    <i class="fas fa-magic mr-1"></i> KHỞI TẠO TÀI KHOẢN ADMIN
                                </button>
                            </form>
                            
                            <hr>
                            <div class="text-center">
                                <a class="small font-weight-bold" href="login.php">Trở về trang Đăng nhập</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>