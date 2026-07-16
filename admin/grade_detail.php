<?php
/** @var PDO $pdo */

// 1. CHỐT CHẶN KIỂM TRA LỚP HỌC PHẦN
$maLHP = $_GET['maLHP'] ?? '';
if (empty($maLHP)) {
    header("Location: index.php?page=grades");
    exit;
}

// Truy vấn thông tin tổng quan của Lớp học phần kèm trạng thái isLocked
// ĐÃ SỬA: Viết thường toàn bộ tên bảng (courseclasses, courses, semesters, lecturers)
$infoSql = "SELECT cc.maLHP, c.tenMH, s.tenHK, t.hoTenGV, cc.isLocked 
            FROM courseclasses cc
            JOIN courses c ON cc.maMH = c.maMH
            JOIN semesters s ON cc.maHK = s.maHK
            JOIN lecturers t ON cc.maGV = t.maGV
            WHERE cc.maLHP = ? LIMIT 1";
$stmtInfo = $pdo->prepare($infoSql);
$stmtInfo->execute([$maLHP]);
$classInfo = $stmtInfo->fetch();

if (!$classInfo) {
    $_SESSION['msg'] = "Lỗi: Không tìm thấy thông tin Lớp học phần #$maLHP!";
    $_SESSION['msg_type'] = "danger";
    header("Location: index.php?page=grades");
    exit;
}

// XỬ LÝ HÀNH ĐỘNG MỞ KHÓA ĐIỂM CỦA ADMIN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'unlock_grades') {
    // ĐÃ SỬA: Viết thường courseclasses
    $pdo->prepare("UPDATE courseclasses SET isLocked = 0 WHERE maLHP = ?")->execute([$maLHP]);
    $_SESSION['msg'] = "Đã mở khoá thành công bảng điểm lớp #$maLHP. Giảng viên có thể tiếp tục nhập điểm!";
    $_SESSION['msg_type'] = "success";
    header("Location: index.php?page=grade_detail&maLHP=$maLHP");
    exit;
}

// ==========================================
// 2. TRUY VẤN DANH SÁCH SINH VIÊN (CÓ LỌC & TÌM KIẾM)
// ==========================================
$keyword = trim($_GET['keyword'] ?? '');
$status = $_GET['status'] ?? '';

// ĐÃ SỬA: Viết thường grades, students
$listSql = "SELECT g.*, s.hoTen, s.maLop 
            FROM grades g
            JOIN students s ON g.maSV = s.maSV
            WHERE g.maLHP = :maLHP";

$params = ['maLHP' => $maLHP];

if ($keyword !== '') {
    $listSql .= " AND (s.maSV LIKE :kw OR s.hoTen LIKE :kw)";
    $params['kw'] = "%$keyword%";
}

// Cấu trúc lọc tinh gọn theo yêu cầu mới
if ($status === 'passed') {
    $listSql .= " AND g.diemTong >= 4.0";
} elseif ($status === 'failed') {
    $listSql .= " AND g.diemTong < 4.0 AND g.diemTong IS NOT NULL";
}

$listSql .= " ORDER BY s.maSV ASC";

$stmtList = $pdo->prepare($listSql);
$stmtList->execute($params);
$studentsList = $stmtList->fetchAll();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Chi tiết Bảng điểm LHP #<?= htmlspecialchars($maLHP) ?></h1>
    <a href="index.php?page=grades" class="btn btn-secondary btn-sm shadow-sm">
        <i class="fas fa-arrow-left mr-1"></i> Quay lại danh sách lớp
    </a>
</div>

<?php if (isset($_SESSION['msg'])): ?>
    <div class="alert alert-<?= $_SESSION['msg_type'] ?> alert-dismissible fade show shadow-sm font-weight-bold">
        <?= $_SESSION['msg'] ?>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    <?php unset($_SESSION['msg'], $_SESSION['msg_type']); ?>
<?php endif; ?>

<div class="card border-left-primary shadow mb-4">
    <div class="card-body py-3">
        <div class="row align-items-center">
            <div class="col-md-4">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Môn học / Học phần</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= htmlspecialchars($classInfo['tenMH']) ?></div>
            </div>
            <div class="col-md-4 border-left pl-4">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Giảng viên phụ trách</div>
                <div class="mb-0 font-weight-bold text-gray-700"><i class="fas fa-chalkboard-teacher mr-1"></i>
                    <?= htmlspecialchars($classInfo['hoTenGV']) ?></div>
            </div>
            <div class="col-md-4 border-left pl-4">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Học kỳ giảng dạy</div>
                <div class="mb-0 font-weight-bold text-gray-700"><?= htmlspecialchars($classInfo['tenHK']) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between bg-light">
        <h6 class="m-0 font-weight-bold text-primary mb-2 mb-lg-0"><i class="fas fa-list mr-1"></i> Danh sách Sinh viên
            & Điểm số</h6>

        <div class="d-flex align-items-center flex-column flex-md-row">
            <form method="GET" action="index.php" class="form-inline mb-2 mb-md-0 mr-md-3">
                <input type="hidden" name="page" value="grade_detail">
                <input type="hidden" name="maLHP" value="<?= htmlspecialchars($maLHP) ?>">

                <select name="status" class="form-control form-control-sm mr-2 shadow-sm" onchange="this.form.submit()">
                    <option value="">-- Tất cả kết quả --</option>
                    <option value="passed" <?= $status === 'passed' ? 'selected' : '' ?>>Qua Môn</option>
                    <option value="failed" <?= $status === 'failed' ? 'selected' : '' ?>>Trượt</option>
                </select>

                <div class="input-group input-group-sm shadow-sm">
                    <input type="text" name="keyword" class="form-control" placeholder="Mã SV, Tên..."
                        value="<?= htmlspecialchars($keyword) ?>">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                    </div>
                </div>
            </form>

            <?php if ((int) $classInfo['isLocked'] === 1): ?>
                <form method="POST" action="index.php?page=grade_detail&maLHP=<?= $maLHP ?>">
                    <input type="hidden" name="action" value="unlock_grades">
                    <button type="submit" class="btn btn-danger btn-sm font-weight-bold shadow-sm"
                        onclick="return confirm('Mở khóa đồng nghĩa với việc cho phép giảng viên sửa điểm trở lại. Chắc chắn mở?')">
                        <i class="fas fa-lock-open mr-1"></i> MỞ KHÓA BẢNG ĐIỂM
                    </button>
                </form>
            <?php else: ?>
                <span class="badge badge-success p-2 font-weight-bold" style="font-size: 13px;"><i
                        class="fas fa-unlock mr-1"></i> Đang mở (Chờ GV chốt)</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0 text-center" width="100%">
                <thead class="thead-light">
                    <tr>
                        <th width="15%">Mã Sinh viên</th>
                        <th class="text-left">Họ và Tên</th>
                        <th width="12%">Điểm QT 1 (40%)</th>
                        <th width="12%">Điểm QT 2 (40%)</th>
                        <th width="12%">Điểm Thi (60%)</th>
                        <th width="12%" class="bg-primary text-white">Tổng kết</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($studentsList)): ?>
                        <tr>
                            <td colspan="6" class="py-5 text-muted">Không có sinh viên nào khớp với điều kiện tìm kiếm/lọc.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($studentsList as $g): ?>
                            <tr>
                                <td class="font-weight-bold text-dark">
                                    <a href="index.php?page=profile&role=Student&username=<?= urlencode($g['maSV']) ?>"
                                        class="text-primary text-decoration-none"><?= htmlspecialchars($g['maSV']) ?></a>
                                </td>
                                <td class="text-left">
                                    <a href="index.php?page=profile&role=Student&username=<?= urlencode($g['maSV']) ?>"
                                        class="text-dark"><b><?= htmlspecialchars($g['hoTen']) ?></b></a><br>
                                    <small class="text-muted">Lớp: <?= htmlspecialchars($g['maLop']) ?></small>
                                </td>
                                <td class="font-weight-bold text-secondary"><?= $g['diem1'] ?? '-' ?></td>
                                <td class="font-weight-bold text-secondary"><?= $g['diem2'] ?? '-' ?></td>
                                <td class="font-weight-bold text-secondary"><?= $g['diemThi'] ?? '-' ?></td>

                                <?php
                                $diemColor = 'text-dark';
                                if ($g['diemTong'] !== null) {
                                    $diemColor = ($g['diemTong'] < 4.0) ? 'text-danger' : 'text-success';
                                }
                                ?>
                                <td class="font-weight-bold <?= $diemColor ?> table-gray" style="font-size: 1.1rem;">
                                    <?= $g['diemTong'] ?? '-' ?>
                                    <?php if (isset($g['diemTongChu']) && $g['diemTongChu']): ?><span
                                            class="badge badge-light border ml-1"><?= $g['diemTongChu'] ?></span><?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>