<?php
/** @var PDO $pdo */

// 1. NHẬN DIỆN MỤC TIÊU CẦN XEM
$roleView = $_GET['role'] ?? 'Teacher';
$idView = $_GET['id'] ?? $_SESSION['user']['username'];
$profileData = [];

// 2. TRUY VẤN ĐA HÌNH (Polymorphic Query)
if ($roleView === 'Student') {
    // A. Lấy thông tin Sinh viên (Kèm tên lớp)
    $sql = "SELECT s.*, c.tenLop 
            FROM Students s 
            LEFT JOIN Classes c ON s.maLop = c.maLop 
            WHERE s.maSV = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idView]);
    $profileData = $stmt->fetch();

    if (!$profileData) {
        echo "<div class='alert alert-warning shadow-sm mt-4'>Không tìm thấy dữ liệu Sinh viên này trên hệ thống!</div>";
        return; // Dừng chạy file nếu không có data
    }
} else {
    // B. Lấy thông tin Giảng viên
    // CHỐT CHẶN BẢO MẬT: Ép buộc idView phải là mã của CHÍNH GIẢNG VIÊN ĐÓ, chống xem trộm đồng nghiệp
    $idView = $_SESSION['user']['username']; 
    $roleView = 'Teacher'; 

    $sql = "SELECT * FROM Teachers WHERE maGV = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idView]);
    $profileData = $stmt->fetch();
}
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <?= ($roleView === 'Student') ? 'Hồ sơ Sinh viên' : 'Hồ sơ Của bạn' ?>
    </h1>
    <button onclick="history.back()" class="btn btn-secondary btn-sm shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay lại
    </button>
</div>

<div class="row">
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4 border-bottom-<?= ($roleView === 'Student') ? 'primary' : 'success' ?>">
            <div class="card-body text-center pt-5 pb-4">
                <img class="img-profile rounded-circle border shadow-sm mb-3" 
                     src="<?= BASE_URL ?>img/undraw_profile<?= ($roleView === 'Student') ? '_2' : '' ?>.svg" 
                     alt="Avatar" width="130" height="130">
                
                <h4 class="font-weight-bold text-dark mb-1">
                    <?= htmlspecialchars($profileData[($roleView === 'Student') ? 'hoTen' : 'hoTenGV']) ?>
                </h4>
                
                <div class="mb-3">
                    <?php if ($roleView === 'Student'): ?>
                        <span class="badge badge-primary px-3 py-2">Sinh viên</span>
                    <?php else: ?>
                        <span class="badge badge-success px-3 py-2">Giảng viên</span>
                    <?php endif; ?>
                </div>
                
                <div class="text-muted font-weight-bold">
                    Mã số: <?= htmlspecialchars($idView) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-white">
                <h6 class="m-0 font-weight-bold text-<?= ($roleView === 'Student') ? 'primary' : 'success' ?>">
                    Chi tiết Thông tin liên lạc
                </h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-striped">
                    <tbody>
                        <?php if ($roleView === 'Student'): ?>
                            <tr>
                                <th width="30%" class="text-gray-800">Lớp sinh hoạt:</th>
                                <td><span class="badge badge-info p-2" style="font-size: 14px;"><?= htmlspecialchars($profileData['tenLop'] ?? 'Chưa phân lớp') ?></span></td>
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
                                <th class="text-gray-800">Địa chỉ:</th>
                                <td><?= htmlspecialchars($profileData['diaChi'] ?? 'Chưa cập nhật') ?></td>
                            </tr>
                        <?php endif; ?>

                        <tr>
                            <th width="30%" class="text-gray-800">Email:</th>
                            <td>
                                <?php if(!empty($profileData['email'])): ?>
                                    <a href="mailto:<?= htmlspecialchars($profileData['email']) ?>" class="text-decoration-none">
                                        <i class="fas fa-envelope mr-1 text-danger"></i> <?= htmlspecialchars($profileData['email']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">Chưa cập nhật</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-gray-800">Số điện thoại:</th>
                            <td>
                                <?php if(!empty($profileData['sdt'])): ?>
                                    <i class="fas fa-phone mr-1 text-success"></i> <?= htmlspecialchars($profileData['sdt']) ?>
                                <?php else: ?>
                                    <span class="text-muted">Chưa cập nhật</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>