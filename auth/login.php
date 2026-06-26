<?php
session_start();
require_once '../config/database.php';
$pdo = getDBConnection();

// Nếu đã đăng nhập rồi thì điều hướng thẳng về phân hệ tương ứng
if (isset($_SESSION['user'])) {
    redirectByRole($_SESSION['user']['role']);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // MỞ NGOẶC DÒNG 17 NẰM Ở ĐÂY
    if (!empty($username) && !empty($password)) {
        try {
            // Sử dụng Prepared Statement
            $stmt = $pdo->prepare("SELECT * FROM Accounts WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $account = $stmt->fetch();

            if ($account && password_verify($password, $account['password'])) {

                if ((int) $account['isActive'] === 1) {

                    // 1. Đổi ID session và lưu Session
                    session_regenerate_id(true);
                    $_SESSION['user'] = [
                        'username' => $account['username'],
                        'role' => $account['role']
                    ];

                    // 2. Lưu Cookie an toàn
                    $cookieData = [
                        'username' => $account['username'],
                        'role' => $account['role']
                    ];
                    $cookieValue = json_encode($cookieData);
                    $expireTime = time() + (30 * 24 * 3600);

                    setcookie('logged_in_user', $cookieValue, [
                        'expires' => $expireTime,
                        'path' => '/',
                        'domain' => '',
                        'secure' => false, // Đổi thành true khi chạy HTTPS thật
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]);

                    // Điều hướng
                    redirectByRole($account['role']);

                } else {
                    $error = 'Tài khoản của bạn hiện đang bị khóa!';
                }
            } else {
                $error = 'Tài khoản hoặc mật khẩu không chính xác!';
            }
        } catch (PDOException $e) {
            $error = 'Lỗi hệ thống: ' . $e->getMessage();
        }
    } else {
        $error = 'Vui lòng nhập đầy đủ thông tin!';
    }
} // ĐÓNG NGOẶC CHO IF($_SERVER['REQUEST_METHOD'] === 'POST')

// Hàm phụ trợ điều hướng theo vai trò
function redirectByRole($role)
{
    switch ($role) {
        case 'Admin':
            header("Location: ../admin/index.php");
            break;
        case 'Lecturer':
            header("Location: ../lecturer/index.php");
            break;
        case 'Student':
            header("Location: ../student/index.php");
            break;
        default:
            header("Location: login.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Đăng nhập hệ thống</title>
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
                                <h1 class="h4 text-gray-900 mb-4 font-weight-bold">CỔNG THÔNG TIN ĐÀO TẠO</h1>
                            </div>

                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger shadow-sm text-center small"><?= htmlspecialchars($error) ?>
                                </div>
                            <?php endif; ?>

                            <form class="user" method="POST" action="login.php">
                                <div class="form-group">
                                    <input type="text" name="username" class="form-control form-control-user"
                                        placeholder="Tên đăng nhập (Mã số)..." required autocomplete="username">
                                </div>
                                <div class="form-group">
                                    <input type="password" name="password" class="form-control form-control-user"
                                        placeholder="Mật khẩu..." required autocomplete="current-password">
                                </div>
                                <button type="submit"
                                    class="btn btn-primary btn-user btn-block font-weight-bold shadow">
                                    ĐĂNG NHẬP
                                </button>
                                <hr>
                                <div class="text-center">
                                    <a class="small font-weight-bold" href="forgot_password.php">Quên mật khẩu?</a>
                                </div>
                            </form>
                            <hr>
                            <div class="text-center">
                                <span class="small text-muted">Hệ thống Quản lý Sinh viên &copy; 2026</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>