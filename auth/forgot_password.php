<?php
session_start();
require_once '../config/database.php';
// Nhúng file chứa hàm sendSystemEmail (Hãy đảm bảo bạn đã tạo file này như hướng dẫn trước)
require_once '../helpers/email.php'; 

$pdo = getDBConnection();
$msg = '';
$msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');

    if (!empty($username)) {
        try {
            // 1. Kéo thông tin tài khoản và JOIN chéo để lấy Email từ bảng tương ứng
            $sql = "SELECT a.username, a.role, 
                           s.email AS email_sv, s.hoTen AS name_sv,
                           t.email AS email_gv, t.hoTenGV AS name_gv
                    FROM Accounts a
                    LEFT JOIN Students s ON a.username = s.maSV AND a.role = 'Student'
                    LEFT JOIN Teachers t ON a.username = t.maGV AND a.role = 'Teacher'
                    WHERE a.username = :user";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['user' => $username]);
            $account = $stmt->fetch();

            if (!$account) {
                throw new Exception("Tên đăng nhập không tồn tại trên hệ thống!");
            }

            // Từ chối cấp lại mật khẩu cho Admin qua web để tránh rủi ro bảo mật cao nhất
            if ($account['role'] === 'Admin') {
                throw new Exception("Tài khoản Quản trị viên không thể dùng chức năng này. Vui lòng can thiệp trực tiếp vào CSDL.");
            }

            // Xác định Email và Tên người dùng
            $email = $account['email_sv'] ?? $account['email_gv'];
            $name = $account['name_sv'] ?? $account['name_gv'] ?? 'Người dùng';

            if (empty($email)) {
                throw new Exception("Tài khoản của bạn chưa được cập nhật địa chỉ Email trong hồ sơ. Vui lòng liên hệ Phòng Đào tạo.");
            }

            // 2. Sinh mật khẩu ngẫu nhiên (8 ký tự gồm chữ và số)
            $newPassword = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
            
            // 3. Băm mật khẩu và lưu vào Database
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateStmt = $pdo->prepare("UPDATE Accounts SET password = ? WHERE username = ?");
            $updateStmt->execute([$hashedPassword, $username]);

            // 4. Chuẩn bị và gửi Email
            $subject = "Cấp lại mật khẩu truy cập Hệ thống Quản lý Sinh viên";
            $body = "
                <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <h3 style='color: #4e73df;'>Xin chào {$name},</h3>
                    <p>Hệ thống đã nhận được yêu cầu đặt lại mật khẩu cho tài khoản <b>{$username}</b> của bạn.</p>
                    <div style='background-color: #f8f9fc; padding: 15px; border-left: 4px solid #4e73df; margin: 20px 0;'>
                        <p style='margin: 0;'>Mật khẩu mới của bạn là: <strong style='font-size: 18px; color: #e74a3b;'>{$newPassword}</strong></p>
                    </div>
                    <p><i>Lưu ý bảo mật: Vì lý do an toàn, vui lòng đăng nhập và đổi lại mật khẩu này ngay lập tức.</i></p>
                    <p>Trân trọng,<br><b>Phòng Đào tạo</b></p>
                </div>
            ";

            if (sendSystemEmail($email, $name, $subject, $body)) {
                $msg = "Thành công! Mật khẩu mới đã được gửi đến email: <b>" . hideEmail($email) . "</b>";
                $msg_type = "success";
            } else {
                // Nếu gửi mail thất bại, roll back bằng cách... không roll back được vì mật khẩu đã đổi. 
                // Thực tế nên dùng Transaction, nhưng việc gửi mail nằm ngoài phiên DB.
                $msg = "Đổi mật khẩu thành công nhưng gửi Email thất bại do lỗi cấu hình máy chủ SMTP.";
                $msg_type = "warning";
            }

        } catch (Exception $e) {
            $msg = $e->getMessage();
            $msg_type = "danger";
        }
    } else {
        $msg = "Vui lòng nhập Tên đăng nhập (Mã số)!";
        $msg_type = "warning";
    }
}

// Hàm phụ trợ ẩn một phần email (Bảo mật thông tin: nguyenvanA@gmail.com -> ngu***@gmail.com)
function hideEmail($email) {
    $parts = explode("@", $email);
    $name = $parts[0];
    $domain = $parts[1];
    $hidden_name = substr($name, 0, 3) . str_repeat('*', strlen($name) - 3);
    return $hidden_name . "@" . $domain;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Quên mật khẩu</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-primary">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-8 col-md-9">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <div class="p-5">
                            <div class="text-center">
                                <h1 class="h4 text-gray-900 mb-2 font-weight-bold">QUÊN MẬT KHẨU?</h1>
                                <p class="mb-4 text-muted small">Chúng tôi hiểu, thỉnh thoảng mọi người hay quên. Hãy nhập Tên đăng nhập (Mã Sinh viên/Giảng viên) của bạn, hệ thống sẽ cấp mật khẩu mới gửi vào email đăng ký.</p>
                            </div>
                            
                            <?php if (!empty($msg)): ?>
                                <div class="alert alert-<?= $msg_type ?> shadow-sm text-center small"><?= $msg ?></div>
                            <?php endif; ?>

                            <form class="user" method="POST" action="forgot_password.php">
                                <div class="form-group">
                                    <input type="text" name="username" class="form-control form-control-user" placeholder="Nhập mã số định danh..." required>
                                </div>
                                <button type="submit" class="btn btn-primary btn-user btn-block font-weight-bold shadow">
                                    GỬI MẬT KHẨU MỚI
                                </button>
                            </form>
                            <hr>
                            <div class="text-center">
                                <a class="small font-weight-bold" href="login.php">Đã nhớ mật khẩu? Quay lại Đăng nhập!</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>