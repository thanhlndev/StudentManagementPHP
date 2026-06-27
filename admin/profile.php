<?php
/** @var PDO $pdo */

// 1. CHỐT CHẶN BẢO MẬT: Bắt buộc phải có username truyền vào
$username = $_GET['username'] ?? '';
if (empty($username)) {
    $_SESSION['msg'] = "Không tìm thấy thông tin tài khoản yêu cầu!";
    $_SESSION['msg_type'] = "warning";
    header("Location: index.php?page=accounts");
    exit;
}

// 2. TRUY VẤN LÕI: Lấy thông tin tài khoản cơ sở
$stmtAccount = $pdo->prepare("SELECT username, role, isActive, createdAt FROM Accounts WHERE username = ?");
$stmtAccount->execute([$username]);
$account = $stmtAccount->fetch();

if (!$account) {
    $_SESSION['msg'] = "Tài khoản không tồn tại trên hệ thống!";
    $_SESSION['msg_type'] = "danger";
    header("Location: index.php?page=accounts");
    exit;
}

$role = $account['role'];
$profileData = [];

// 3. ĐIỀU PHỐI DỮ LIỆU ĐA HÌNH (Polymorphic Query)
if ($role === 'Student') {
    // JOIN thêm Classes để lấy tên lớp thay vì chỉ hiện mã lớp
    $sql = "SELECT s.*, c.tenLop 
            FROM Students s 
            LEFT JOIN Classes c ON s.maLop = c.maLop 
            WHERE s.maSV = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $profileData = $stmt->fetch();

} elseif ($role === 'Teacher') {
    $sql = "SELECT * FROM Lecturers WHERE maGV = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $profileData = $stmt->fetch();
}
/**
 *  kiểm tra tí =)))))))
 */ elseif ($role === 'Admin') {
    $sql = "SELECT * FROM Lecturers WHERE maGV = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $profileData = $stmt->fetch();
}
// Nếu là Admin, không cần query thêm bảng phụ, chỉ dùng data từ mảng $account
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Hồ sơ Người dùng</h1>
    <a href="index.php?page=accounts" class="btn btn-secondary btn-sm shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay lại danh sách
    </a>
</div>

<div class="row">
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-primary">
                <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-user-circle"></i> Thẻ Tài Khoản</h6>
            </div>
            <div class="card-body text-center pt-5 pb-5">
                <img class="img-profile rounded-circle border shadow-sm mb-3" src="../img/undraw_profile.svg"
                    alt="Avatar" width="120" height="120">

                <h4 class="font-weight-bold text-dark mb-1"><?= htmlspecialchars($account['username']) ?></h4>

                <div class="mb-3">
                    <?php if ($role === 'Admin'): ?>
                        <span class="badge badge-danger px-3 py-2">Quản trị viên</span>
                    <?php elseif ($role === 'Teacher'): ?>
                        <span class="badge badge-warning px-3 py-2 text-dark">Giảng viên</span>
                    <?php else: ?>
                        <span class="badge badge-success px-3 py-2">Sinh viên</span>
                    <?php endif; ?>
                </div>

                <ul class="list-group list-group-flush text-left mt-4 border-top pt-3">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="font-weight-bold text-gray-700">Trạng thái:</span>
                        <?php if ((int) $account['isActive'] === 1): ?>
                            <span class="text-success font-weight-bold"><i class="fas fa-check-circle"></i> Đang hoạt
                                động</span>
                        <?php else: ?>
                            <span class="text-danger font-weight-bold"><i class="fas fa-ban"></i> Đã khóa</span>
                        <?php endif; ?>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="font-weight-bold text-gray-700">Ngày cấp:</span>
                        <span><?= date('d/m/Y H:i', strtotime($account['createdAt'])) ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-white border-bottom-primary">
                <h6 class="m-0 font-weight-bold text-primary">Thông tin Chi tiết</h6>
            </div>
            <div class="card-body">
                <?php if ($role === 'Admin'): ?>
                    <div class="alert alert-info text-center py-5">
                        <i class="fas fa-shield-alt fa-3x mb-3"></i>
                        <!-- <h5>Đây là tài khoản Quản trị viên cấp cao.</h5>
                        <p class="mb-0">Tài khoản này có toàn quyền vận hành hệ thống và không bị ràng buộc vào hồ sơ cá nhân.</p> -->
                        <?php if (!$profileData): ?>
                            <div class="alert alert-warning">Tài khoản này chưa được liên kết với hồ sơ Giảng viên nào trong
                                CSDL!</div>
                        <?php else: ?>
                            <table class="table table-borderless table-striped">
                                <tbody>
                                    <tr>
                                        <th width="30%" class="text-gray-800">Họ và Tên:</th>
                                        <td class="font-weight-bold text-primary text-uppercase">
                                            <?= htmlspecialchars($profileData['hoTenGV']) ?></td>
                                    </tr>
                                    <tr>
                                        <th class="text-gray-800">Mã Giảng viên:</th>
                                        <td><?= htmlspecialchars($profileData['maGV']) ?></td>
                                    </tr>
                                    <tr>
                                        <th class="text-gray-800">Email liên hệ:</th>
                                        <td><a
                                                href="mailto:<?= htmlspecialchars($profileData['email']) ?>"><?= htmlspecialchars($profileData['email'] ?? 'Chưa cập nhật') ?></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-gray-800">Số điện thoại:</th>
                                        <td><?= htmlspecialchars($profileData['sdt'] ?? 'Chưa cập nhật') ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>

                <?php elseif ($role === 'Teacher'): ?>
                    <?php if (!$profileData): ?>
                        <div class="alert alert-warning">Tài khoản này chưa được liên kết với hồ sơ Giảng viên nào trong CSDL!
                        </div>
                    <?php else: ?>
                        <table class="table table-borderless table-striped">
                            <tbody>
                                <tr>
                                    <th width="30%" class="text-gray-800">Họ và Tên:</th>
                                    <td class="font-weight-bold text-primary text-uppercase">
                                        <?= htmlspecialchars($profileData['hoTenGV']) ?></td>
                                </tr>
                                <tr>
                                    <th class="text-gray-800">Mã Giảng viên:</th>
                                    <td><?= htmlspecialchars($profileData['maGV']) ?></td>
                                </tr>
                                <tr>
                                    <th class="text-gray-800">Email liên hệ:</th>
                                    <td><a
                                            href="mailto:<?= htmlspecialchars($profileData['email']) ?>"><?= htmlspecialchars($profileData['email'] ?? 'Chưa cập nhật') ?></a>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-gray-800">Số điện thoại:</th>
                                    <td><?= htmlspecialchars($profileData['sdt'] ?? 'Chưa cập nhật') ?></td>
                                </tr>
                            </tbody>
                        </table>
                    <?php endif; ?>

                <?php elseif ($role === 'Student'): ?>
                    <?php if (!$profileData): ?>
                        <div class="alert alert-warning">Tài khoản này chưa được liên kết với hồ sơ Sinh viên nào trong CSDL!
                        </div>
                    <?php else: ?>
                        <table class="table table-borderless table-striped">
                            <tbody>
                                <tr>
                                    <th width="30%" class="text-gray-800">Họ và Tên:</th>
                                    <td class="font-weight-bold text-success text-uppercase">
                                        <?= htmlspecialchars($profileData['hoTen']) ?></td>
                                </tr>
                                <tr>
                                    <th class="text-gray-800">Mã Sinh viên:</th>
                                    <td><?= htmlspecialchars($profileData['maSV']) ?></td>
                                </tr>
                                <tr>
                                    <th class="text-gray-800">Lớp hành chính:</th>
                                    <td>
                                        <span class="badge badge-info p-2 font-weight-bold text-md">
                                            <?= htmlspecialchars($profileData['tenLop'] ?? 'Chưa phân lớp') ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-gray-800">Giới tính:</th>
                                    <td><?= htmlspecialchars($profileData['gioiTinh'] ?? 'Chưa cập nhật') ?></td>
                                </tr>
                                <tr>
                                    <th class="text-gray-800">Năm sinh:</th>
                                    <td><?= htmlspecialchars($profileData['namSinh'] ?? 'Chưa cập nhật') ?></td>
                                </tr>
                                <tr>
                                    <th class="text-gray-800">Email:</th>
                                    <td><a
                                            href="mailto:<?= htmlspecialchars($profileData['email']) ?>"><?= htmlspecialchars($profileData['email'] ?? 'Chưa cập nhật') ?></a>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-gray-800">Số điện thoại:</th>
                                    <td><?= htmlspecialchars($profileData['sdt'] ?? 'Chưa cập nhật') ?></td>
                                </tr>
                                <tr>
                                    <th class="text-gray-800">Địa chỉ:</th>
                                    <td><?= htmlspecialchars($profileData['diaChi'] ?? 'Chưa cập nhật') ?></td>
                                </tr>
                            </tbody>
                        </table>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>