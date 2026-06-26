<?php
/** @var PDO $pdo */

$maGV = $_SESSION['user']['username'];
$maLHP = $_GET['maLHP'] ?? '';
if (empty($maLHP)) {
    header("Location: index.php?page=grades"); 
    exit;
}

// Lấy thông tin lớp học phần kèm biến isLocked
$infoSql = "SELECT cc.maLHP, c.tenMH, s.tenHK, cc.isLocked 
            FROM CourseClasses cc
            JOIN Courses c ON cc.maMH = c.maMH
            JOIN Semesters s ON cc.maHK = s.maHK
            WHERE cc.maLHP = ? AND cc.maGV = ? LIMIT 1";
$stmtInfo = $pdo->prepare($infoSql);
$stmtInfo->execute([$maLHP, $maGV]);
$classInfo = $stmtInfo->fetch();

if (!$classInfo) {
    $_SESSION['msg'] = "Cảnh báo bảo mật: Bạn không có quyền truy cập dữ liệu lớp này!";
    $_SESSION['msg_type'] = "danger";
    header("Location: index.php?page=grades"); 
    exit;
}

$isClassLocked = ((int)$classInfo['isLocked'] === 1);

// ==========================================
// 2. XỬ LÝ LƯU ĐIỂM & KHOÁ ĐIỂM (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Hành động 1: KHOÁ ĐIỂM (Giảng viên chỉ khóa, không mở được)
    if ($action === 'lock_grades' && !$isClassLocked) {
        $pdo->prepare("UPDATE CourseClasses SET isLocked = 1 WHERE maLHP = ?")->execute([$maLHP]);
        $_SESSION['msg'] = "Đã chốt và khoá bảng điểm bản chính thành công! Quyền mở khóa thuộc về Giáo vụ (Admin).";
        $_SESSION['msg_type'] = "success";
    }
    
    // Hành động 2: LƯU ĐIỂM (Thuật toán so sánh sai lệch dữ liệu thực tế)
    elseif ($action === 'batch_update' && !$isClassLocked) {
        $selected_indexes = $_POST['selected'] ?? [];
        
        if (!empty($selected_indexes)) {
            // Lấy toàn bộ trạng thái dữ liệu cũ từ Database lên bộ nhớ để đối chiếu chéo
            $oldGradesStmt = $pdo->prepare("SELECT maSV, diem1, diem2, diemThi FROM Grades WHERE maLHP = ?");
            $oldGradesStmt->execute([$maLHP]);
            $oldData = [];
            while($row = $oldGradesStmt->fetch()) {
                $oldData[$row['maSV']] = $row;
            }

            $stmtUpdate = $pdo->prepare("UPDATE Grades SET diem1 = :d1, diem2 = :d2, diemThi = :dt WHERE maSV = :sv AND maLHP = :lhp");
            $countUpdated = 0; 

            // Hàm vô danh so sánh điểm an toàn (Float Precision Handling & Null value check)
            $isChanged = function($new, $old) {
                if ($new === null && $old === null) return false;
                if ($new === null || $old === null) return true;
                return abs((float)$new - (float)$old) > 0.0001;
            };

            foreach ($selected_indexes as $idx) {
                $maSV = $_POST['maSV'][$idx];
                $newD1 = ($_POST['diem1'][$idx] !== '') ? (float)$_POST['diem1'][$idx] : null;
                $newD2 = ($_POST['diem2'][$idx] !== '') ? (float)$_POST['diem2'][$idx] : null;
                $newDT = ($_POST['diemThi'][$idx] !== '') ? (float)$_POST['diemThi'][$idx] : null;

                $oldD1 = isset($oldData[$maSV]) && $oldData[$maSV]['diem1'] !== null ? (float)$oldData[$maSV]['diem1'] : null;
                $oldD2 = isset($oldData[$maSV]) && $oldData[$maSV]['diem2'] !== null ? (float)$oldData[$maSV]['diem2'] : null;
                $oldDT = isset($oldData[$maSV]) && $oldData[$maSV]['diemThi'] !== null ? (float)$oldData[$maSV]['diemThi'] : null;

                if ($isChanged($newD1, $oldD1) || $isChanged($newD2, $oldD2) || $isChanged($newDT, $oldDT)) {
                    $stmtUpdate->execute([
                        'd1' => $newD1, 'd2' => $newD2, 'dt' => $newDT,
                        'sv' => $maSV, 'lhp' => $maLHP
                    ]);
                    $countUpdated++;
                }
            }

            if ($countUpdated > 0) {
                $_SESSION['msg'] = "Lưu thành công: Đã cập nhật dữ liệu điểm mới cho $countUpdated sinh viên!";
                $_SESSION['msg_type'] = "success";
            } else {
                $_SESSION['msg'] = "Điểm nhập mới không có sự khác biệt so với dữ liệu trên hệ thống!";
                $_SESSION['msg_type'] = "info"; 
            }
        } else {
            $_SESSION['msg'] = "Vui lòng tích chọn hàng sinh viên trước khi ấn Lưu điểm!";
            $_SESSION['msg_type'] = "warning";
        }
    }
    
    header("Location: index.php?page=grade_detail&maLHP=$maLHP");
    exit;
}

// ==========================================
// 3. TRUY VẤN DANH SÁCH (CÓ LỌC TÌM KIẾM THEO CHUẨN MỚI)
// ==========================================
$keyword = trim($_GET['keyword'] ?? '');
$status  = $_GET['status'] ?? '';

$listSql = "SELECT g.*, s.hoTen, s.maLop 
            FROM Grades g
            JOIN Students s ON g.maSV = s.maSV
            WHERE g.maLHP = :maLHP";

$params = ['maLHP' => $maLHP];

if ($keyword !== '') {
    $listSql .= " AND (s.maSV LIKE :kw OR s.hoTen LIKE :kw)";
    $params['kw'] = "%$keyword%";
}

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
    <h1 class="h3 mb-0 text-gray-800">Cập nhật bảng điểm LHP #<?= htmlspecialchars($maLHP) ?></h1>
    <a href="index.php?page=grades" class="btn btn-secondary btn-sm shadow-sm"><i class="fas fa-arrow-left mr-1"></i> Trở lại danh sách lớp</a>
</div>

<?php if (isset($_SESSION['msg'])): ?>
    <div class="alert alert-<?= $_SESSION['msg_type'] ?> alert-dismissible fade show shadow-sm font-weight-bold">
        <?= $_SESSION['msg'] ?>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    <?php unset($_SESSION['msg'], $_SESSION['msg_type']); ?>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between bg-light">
        <h6 class="m-0 font-weight-bold text-success mb-2 mb-lg-0"><i class="fas fa-table mr-1"></i> Sổ điểm điện tử</h6>
        
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
                    <input type="text" name="keyword" class="form-control" placeholder="Mã SV, Tên..." value="<?= htmlspecialchars($keyword) ?>">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                    </div>
                </div>
            </form>

            <?php if (!$isClassLocked): ?>
                <div class="d-flex">
                    <form method="POST" action="index.php?page=grade_detail&maLHP=<?= $maLHP ?>" class="mr-2">
                        <input type="hidden" name="action" value="lock_grades">
                        <button type="submit" class="btn btn-warning btn-sm font-weight-bold shadow-sm" onclick="return confirm('CẢNH BÁO: Sau khi khoá, bạn không thể tự mở. Quyền chỉnh sửa sẽ bị đóng băng cho đến khi Giáo vụ mở lại. Chắc chắn khóa?');">
                            <i class="fas fa-lock mr-1"></i> KHOÁ ĐIỂM
                        </button>
                    </form>
                    <button type="submit" form="formBatchUpdate" class="btn btn-success btn-sm font-weight-bold shadow-sm"><i class="fas fa-save mr-1"></i> LƯU ĐIỂM</button>
                </div>
            <?php else: ?>
                <span class="badge badge-danger p-2 font-weight-bold" style="font-size: 13px;"><i class="fas fa-lock mr-1"></i> BẢNG ĐIỂM ĐÃ KHÓA BẢN CHÍNH</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="card-body p-0">
        <form id="formBatchUpdate" method="POST" action="index.php?page=grade_detail&maLHP=<?= htmlspecialchars($maLHP) ?>">
            <input type="hidden" name="action" value="batch_update">
            
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0 text-center" width="100%">
                    <thead class="thead-light">
                        <tr>
                            <th width="5%"><input type="checkbox" id="checkAll" style="transform: scale(1.2);" <?= $isClassLocked ? 'disabled' : '' ?>></th>
                            <th width="15%">Mã Sinh viên</th>
                            <th class="text-left">Họ và Tên</th>
                            <th width="12%">Điểm QT 1 (40%)</th>
                            <th width="12%">Điểm QT 2 (40%)</th>
                            <th width="12%">Điểm Thi (60%)</th>
                            <th width="10%" class="bg-success text-white">Tổng kết</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($studentsList)): ?>
                            <tr><td colspan="7" class="py-5 text-muted">Không có sinh viên nào khớp với điều kiện tìm kiếm/lọc.</td></tr>
                        <?php else: ?>
                            <?php foreach ($studentsList as $index => $g): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="selected[]" value="<?= $index ?>" class="row-checkbox" style="transform: scale(1.2);" onchange="highlightRow(this)" <?= $isClassLocked ? 'disabled' : '' ?>>
                                    <input type="hidden" name="maSV[<?= $index ?>]" value="<?= htmlspecialchars($g['maSV']) ?>">
                                </td>
                                <td class="font-weight-bold text-dark">
                                    <a href="index.php?page=profile&role=Student&id=<?= urlencode($g['maSV']) ?>" class="text-success text-decoration-none"><?= htmlspecialchars($g['maSV']) ?></a>
                                </td>
                                <td class="text-left">
                                    <a href="index.php?page=profile&role=Student&id=<?= urlencode($g['maSV']) ?>" class="text-dark"><b><?= htmlspecialchars($g['hoTen']) ?></b></a><br>
                                    <small class="text-muted">Lớp: <?= htmlspecialchars($g['maLop']) ?></small>
                                </td>
                                
                                <td><input type="number" step="0.01" min="0" max="10" name="diem1[<?= $index ?>]" class="form-control form-control-sm text-center font-weight-bold" value="<?= $g['diem1'] ?>" oninput="markChecked(this)" <?= $isClassLocked ? 'disabled readonly' : '' ?>></td>
                                <td><input type="number" step="0.01" min="0" max="10" name="diem2[<?= $index ?>]" class="form-control form-control-sm text-center font-weight-bold" value="<?= $g['diem2'] ?>" oninput="markChecked(this)" <?= $isClassLocked ? 'disabled readonly' : '' ?>></td>
                                <td><input type="number" step="0.01" min="0" max="10" name="diemThi[<?= $index ?>]" class="form-control form-control-sm text-center font-weight-bold border-success" value="<?= $g['diemThi'] ?>" oninput="markChecked(this)" <?= $isClassLocked ? 'disabled readonly' : '' ?>></td>
                                
                                <?php 
                                    $diemColor = 'text-dark';
                                    if ($g['diemTong'] !== null) {
                                        $diemColor = ($g['diemTong'] < 4.0) ? 'text-danger' : 'text-success';
                                    }
                                ?>
                                <td class="font-weight-bold <?= $diemColor ?> table-gray" style="font-size: 1.1rem;">
                                    <?= $g['diemTong'] ?? '-' ?> <?php if($g['diemTongChu']): ?><span class="badge badge-light border ml-1"><?= $g['diemTongChu'] ?></span><?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

<script>
function highlightRow(cb) { 
    cb.closest('tr').style.backgroundColor = cb.checked ? '#f4f9f4' : ''; 
}
function markChecked(inp) { 
    const cb = inp.closest('tr').querySelector('.row-checkbox'); 
    if (!cb.checked) { cb.checked = true; highlightRow(cb); } 
}
<?php if (!$isClassLocked): ?>
document.getElementById('checkAll').addEventListener('change', function() {
    document.querySelectorAll('.row-checkbox').forEach(cb => { cb.checked = this.checked; highlightRow(cb); });
});
<?php endif; ?>
</script>