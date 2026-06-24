<?php
/** @var PDO $pdo */

// CHỐT CHẶN BẢO MẬT: Luôn luôn lấy mã SV từ phiên đăng nhập hiện tại
$maSV = $_SESSION['user']['username'];

// Truy vấn thông tin Sinh viên kèm thông tin Lớp sinh hoạt
$sql = "SELECT s.*, c.tenLop, f.tenKhoa 
        FROM Students s
        LEFT JOIN Classes c ON s.maLop = c.maLop
        LEFT JOIN Faculties f ON c.maKhoa = f.maKhoa
        WHERE s.maSV = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$maSV]);
$profileData = $stmt->fetch();

if (!$profileData) {
    echo "<div class='alert alert-danger'>Hồ sơ của bạn chưa được thiết lập dữ liệu trong CSDL! Báo ngay cho Admin.</div>";
    return;
}
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Hồ sơ Cá nhân</h1>
</div>

<div class="row">
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4 border-bottom-info">
            <div class="card-body text-center pt-5 pb-4">
                <img class="img-profile rounded-circle border shadow-sm mb-3" 
                     src="<?= BASE_URL ?>img/undraw_profile_2.svg" 
                     alt="Avatar" width="130" height="130">
                
                <h4 class="font-weight-bold text-dark mb-1">
                    <?= htmlspecialchars($profileData['hoTen']) ?>
                </h4>
                
                <div class="mb-3">
                    <span class="badge badge-info px-3 py-2 text-uppercase">Sinh viên</span>
                </div>
                
                <div class="text-muted font-weight-bold mb-2">
                    Mã số: <?= htmlspecialchars($maSV) ?>
                </div>
                <div class="text-muted">
                    <?= htmlspecialchars($profileData['tenKhoa'] ?? 'Khoa chưa xác định') ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-white">
                <h6 class="m-0 font-weight-bold text-info">
                    <i class="fas fa-info-circle mr-1"></i> Chi tiết Thông tin liên lạc
                </h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-striped align-middle">
                    <tbody>
                        <tr>
                            <th width="30%" class="text-gray-800">Lớp sinh hoạt:</th>
                            <td>
                                <span class="badge badge-primary p-2" style="font-size: 14px;">
                                    <?= htmlspecialchars($profileData['tenLop'] ?? 'Chưa được phân lớp') ?>
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
                            <td>
                                <?php if(!empty($profileData['email'])): ?>
                                    <span class="text-info font-weight-bold"><i class="fas fa-envelope mr-1"></i> <?= htmlspecialchars($profileData['email']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">Chưa cập nhật</span>
                                <?php endif; ?>
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
            </div>
        </div>
    </div>
</div>