<?php
/** @var PDO $pdo */

$maSV = $_SESSION['user']['username'];
$filter_hk = $_GET['filter_hk'] ?? '';

// ==========================================
// 1. THUẬT TOÁN TÍNH ĐIỂM TRUNG BÌNH TÍCH LŨY (GPA)
// ==========================================

// Hàm quy đổi điểm hệ 10 sang hệ 4.0 chuẩn Đại học
function convertTo4Scale($diem10) {
    if ($diem10 >= 8.5) return 4.0; // A
    if ($diem10 >= 8.0) return 3.5; // B+
    if ($diem10 >= 7.0) return 3.0; // B
    if ($diem10 >= 6.5) return 2.5; // C+
    if ($diem10 >= 5.5) return 2.0; // C
    if ($diem10 >= 5.0) return 1.5; // D+
    if ($diem10 >= 4.0) return 1.0; // D
    return 0.0;                     // F
}

// Lấy TOÀN BỘ điểm đã có kết quả để tính tích lũy
$sqlAllGrades = "SELECT c.maMH, c.soTinChi, g.diemTong 
                 FROM Grades g
                 JOIN CourseClasses cc ON g.maLHP = cc.maLHP
                 JOIN Courses c ON cc.maMH = c.maMH
                 WHERE g.maSV = ? AND g.diemTong IS NOT NULL";
$stmtAll = $pdo->prepare($sqlAllGrades);
$stmtAll->execute([$maSV]);
$allGrades = $stmtAll->fetchAll();

$bestGrades = [];
// Lọc điểm cao nhất cho từng môn (Xử lý logic học lại/cải thiện)
foreach ($allGrades as $row) {
    $maMH = $row['maMH'];
    $diem = (float)$row['diemTong'];
    if (!isset($bestGrades[$maMH]) || $diem > $bestGrades[$maMH]['diem']) {
        $bestGrades[$maMH] = [
            'diem' => $diem,
            'tinChi' => (int)$row['soTinChi']
        ];
    }
}

$tongTinChiTichLuy = 0;
$tongDiem10xTinChi = 0;
$tongDiem4xTinChi  = 0;

foreach ($bestGrades as $mh) {
    // Chỉ cộng tín chỉ tích lũy cho môn ĐẬU (>= 4.0)
    if ($mh['diem'] >= 4.0) {
        $tongTinChiTichLuy += $mh['tinChi'];
    }
    // Vẫn phải cộng vào mẫu số để tính điểm Trung bình (dù rớt hay đậu)
    $tongDiem10xTinChi += ($mh['diem'] * $mh['tinChi']);
    $tongDiem4xTinChi  += (convertTo4Scale($mh['diem']) * $mh['tinChi']);
}

// Tổng số tín chỉ dùng để chia trung bình (bao gồm cả môn rớt)
$tongTinChiDaHoc = array_sum(array_column($bestGrades, 'tinChi'));

$gpa10 = $tongTinChiDaHoc > 0 ? round($tongDiem10xTinChi / $tongTinChiDaHoc, 2) : 0;
$gpa4  = $tongTinChiDaHoc > 0 ? round($tongDiem4xTinChi / $tongTinChiDaHoc, 2) : 0;

// Xếp loại học lực
$xepLoai = 'Chưa xếp loại';
if ($tongTinChiDaHoc > 0) {
    if ($gpa4 >= 3.6) $xepLoai = 'Xuất sắc';
    elseif ($gpa4 >= 3.2) $xepLoai = 'Giỏi';
    elseif ($gpa4 >= 2.5) $xepLoai = 'Khá';
    elseif ($gpa4 >= 2.0) $xepLoai = 'Trung bình';
    else $xepLoai = 'Yếu';
}

// ==========================================
// 2. TRUY VẤN DANH SÁCH BẢNG ĐIỂM CHI TIẾT
// ==========================================
$semestersList = $pdo->query("SELECT maHK, tenHK FROM Semesters ORDER BY maHK DESC")->fetchAll();

$sqlList = "SELECT cc.maLHP, c.tenMH, c.soTinChi, s.tenHK, g.diem1, g.diem2, g.diemThi, g.diemTong, g.diemTongChu
            FROM Grades g
            JOIN CourseClasses cc ON g.maLHP = cc.maLHP
            JOIN Courses c ON cc.maMH = c.maMH
            JOIN Semesters s ON cc.maHK = s.maHK
            WHERE g.maSV = :maSV";
$params = ['maSV' => $maSV];

if ($filter_hk !== '') {
    $sqlList .= " AND cc.maHK = :hk";
    $params['hk'] = $filter_hk;
}

$sqlList .= " ORDER BY s.maHK DESC, c.tenMH ASC";
$stmtList = $pdo->prepare($sqlList);
$stmtList->execute($params);
$myGrades = $stmtList->fetchAll();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Kết quả Học tập</h1>
</div>

<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Tín chỉ tích lũy</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $tongTinChiTichLuy ?> TC</div>
                    </div>
                    <div class="col-auto"><i class="fas fa-layer-group fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Trung bình (Hệ 10)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($gpa10, 2) ?></div>
                    </div>
                    <div class="col-auto"><i class="fas fa-calculator fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Điểm GPA (Hệ 4.0)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($gpa4, 2) ?></div>
                    </div>
                    <div class="col-auto"><i class="fas fa-star fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Xếp loại Học lực</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $xepLoai ?></div>
                    </div>
                    <div class="col-auto"><i class="fas fa-award fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between bg-light">
        <h6 class="m-0 font-weight-bold text-info mb-2 mb-lg-0"><i class="fas fa-list mr-1"></i> Bảng điểm chi tiết</h6>
        
        <form method="GET" action="index.php" class="form-inline mb-0">
            <input type="hidden" name="page" value="grades">
            <select name="filter_hk" class="form-control form-control-sm shadow-sm" onchange="this.form.submit()">
                <option value="">-- Tất cả Học kỳ --</option>
                <?php foreach ($semestersList as $sem): ?>
                    <option value="<?= $sem['maHK'] ?>" <?= ($filter_hk == $sem['maHK']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($sem['tenHK']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0 text-center" width="100%">
                <thead class="thead-light">
                    <tr>
                        <th width="8%">Mã LHP</th>
                        <th class="text-left">Môn học</th>
                        <th width="8%">Tín chỉ</th>
                        <th width="12%">Học kỳ</th>
                        <th width="10%">QT 1</th>
                        <th width="10%">QT 2</th>
                        <th width="10%">Thi</th>
                        <th width="10%" class="bg-info text-white">Tổng (10)</th>
                        <th width="8%" class="bg-info text-white">Điểm Chữ</th>
                        <th width="10%">Kết quả</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($myGrades)): ?>
                        <tr><td colspan="10" class="py-5 text-muted">Bạn chưa có dữ liệu điểm trong học kỳ này.</td></tr>
                    <?php else: ?>
                        <?php foreach ($myGrades as $g): ?>
                        <tr>
                            <td class="font-weight-bold text-dark">#<?= $g['maLHP'] ?></td>
                            <td class="text-left font-weight-bold text-info"><?= htmlspecialchars($g['tenMH']) ?></td>
                            <td><?= $g['soTinChi'] ?></td>
                            <td><?= htmlspecialchars($g['tenHK']) ?></td>
                            
                            <td class="text-secondary"><?= $g['diem1'] ?? '-' ?></td>
                            <td class="text-secondary"><?= $g['diem2'] ?? '-' ?></td>
                            <td class="text-secondary"><?= $g['diemThi'] ?? '-' ?></td>
                            
                            <td class="font-weight-bold text-dark table-light" style="font-size: 1.1rem;"><?= $g['diemTong'] ?? '-' ?></td>
                            <td class="font-weight-bold text-dark table-light" style="font-size: 1.1rem;"><?= $g['diemTongChu'] ?? '-' ?></td>
                            
                            <td>
                                <?php if ($g['diemTong'] === null): ?>
                                    <span class="badge badge-secondary p-2">Chưa có điểm</span>
                                <?php elseif ($g['diemTong'] >= 4.0): ?>
                                    <span class="badge badge-success p-2"><i class="fas fa-check mr-1"></i> Đạt</span>
                                <?php else: ?>
                                    <span class="badge badge-danger p-2"><i class="fas fa-times mr-1"></i> Trượt</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>