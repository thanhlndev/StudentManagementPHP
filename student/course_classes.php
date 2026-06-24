<?php
/** @var PDO $pdo */

$maSV = $_SESSION['user']['username'];

// ==========================================
// 1. XỬ LÝ LOGIC ĐĂNG KÝ HỌC PHẦN (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $maLHP_reg = (int)$_POST['maLHP'];
    
    try {
        // Khởi tạo Transaction để đảm bảo tính toàn vẹn dữ liệu
        $pdo->beginTransaction();

        // [SENIOR KNOWLEDGE]: Dùng FOR UPDATE để khóa dòng (Lock Row) của Lớp học phần này
        // Ngăn chặn tình trạng 2 sinh viên cùng đăng ký 1 slot cuối cùng tại cùng 1 mili-giây
        $lockStmt = $pdo->prepare("SELECT maLHP FROM CourseClasses WHERE maLHP = ? FOR UPDATE");
        $lockStmt->execute([$maLHP_reg]);

        // 1. Kiểm tra xem sinh viên đã đăng ký môn này chưa
        $checkReg = $pdo->prepare("SELECT COUNT(*) FROM Grades WHERE maLHP = ? AND maSV = ?");
        $checkReg->execute([$maLHP_reg, $maSV]);
        $isRegistered = $checkReg->fetchColumn();

        // 2. Đếm số lượng sinh viên hiện tại trong lớp
        $checkCount = $pdo->prepare("SELECT COUNT(*) FROM Grades WHERE maLHP = ?");
        $checkCount->execute([$maLHP_reg]);
        $currentCount = $checkCount->fetchColumn();

        if ($isRegistered > 0) {
            $_SESSION['msg'] = "Thất bại: Bạn đã đăng ký Lớp học phần #$maLHP_reg từ trước!";
            $_SESSION['msg_type'] = "warning";
        } elseif ($currentCount >= 50) {
            $_SESSION['msg'] = "Thất bại: Lớp học phần #$maLHP_reg đã đủ sĩ số (50/50)!";
            $_SESSION['msg_type'] = "danger";
        } else {
            // Đủ điều kiện -> Insert vào bảng Grades
            $stmtInsert = $pdo->prepare("INSERT INTO Grades (maSV, maLHP) VALUES (?, ?)");
            $stmtInsert->execute([$maSV, $maLHP_reg]);
            
            $_SESSION['msg'] = "Thành công: Đã ghi danh vào Lớp học phần #$maLHP_reg!";
            $_SESSION['msg_type'] = "success";
        }

        // Commit lưu dữ liệu và mở khóa (Unlock Row)
        $pdo->commit();

    } catch (Exception $e) {
        $pdo->rollBack(); // Nếu có lỗi mạng/CSDL thì hủy bỏ toàn bộ thao tác
        $_SESSION['msg'] = "Lỗi hệ thống: " . $e->getMessage();
        $_SESSION['msg_type'] = "danger";
    }
    
    // Điều hướng lại trang để tránh lỗi nạp lại Form (F5 Form Resubmission)
    header("Location: index.php?page=course_classes");
    exit;
}

// // ==========================================
// // 2. TRUY VẤN DANH SÁCH LỚP HỌC PHẦN ĐỂ HIỂN THỊ
// // ==========================================
// $keyword = trim($_GET['keyword'] ?? '');
// $filter_hk = $_GET['filter_hk'] ?? '';

// // Truy vấn danh sách học kỳ cho Dropdown lọc
// $semestersList = $pdo->query("SELECT maHK, tenHK FROM Semesters ORDER BY maHK DESC")->fetchAll();

// // Câu lệnh Query thông minh: Dùng Subquery để lấy sĩ số và trạng thái đăng ký ngay trong 1 lần quét
// $baseSql = "SELECT cc.maLHP, c.tenMH, s.tenHK, t.hoTenGV,
//             (SELECT COUNT(*) FROM Grades g WHERE g.maLHP = cc.maLHP) AS soLuongSV,
//             (SELECT COUNT(*) FROM Grades g2 WHERE g2.maLHP = cc.maLHP AND g2.maSV = :maSV) AS daDangKy
//             FROM CourseClasses cc
//             JOIN Courses c ON cc.maMH = c.maMH
//             JOIN Semesters s ON cc.maHK = s.maHK
//             JOIN Teachers t ON cc.maGV = t.maGV
//             WHERE 1=1";

// $params = [':maSV' => $maSV];

// if ($keyword !== '') {
//     $baseSql .= " AND (cc.maLHP LIKE :kw OR c.tenMH LIKE :kw)";
//     $params[':kw'] = "%$keyword%";
// }
// if ($filter_hk !== '') {
//     $baseSql .= " AND cc.maHK = :hk";
//     $params[':hk'] = $filter_hk;
// }

// $baseSql .= " ORDER BY s.maHK DESC, c.tenMH ASC";
// $stmt = $pdo->prepare($baseSql);
// $stmt->execute($params);
// $classes = $stmt->fetchAll();

// ==========================================
// 2. TRUY VẤN DANH SÁCH LỚP HỌC PHẦN (ĐÃ TỐI ƯU)
// ==========================================
$keyword = trim($_GET['keyword'] ?? '');
$filter_hk = $_GET['filter_hk'] ?? '';
$semestersList = $pdo->query("SELECT maHK, tenHK FROM Semesters ORDER BY maHK DESC")->fetchAll();

// Khởi tạo mảng tham số chuẩn
$params = [
    ':maSV' => $maSV,
    ':maSV_1' => $maSV
];

$baseSql = "SELECT 
    cc.maLHP, 
    c.tenMH, 
    s.tenHK, 
    t.hoTenGV,
    COALESCE(g_agg.soLuongSV, 0) AS soLuongSV,
    COALESCE(g_agg.daDangKy, 0) AS daDangKy,
    max_diem.diemCaoNhat
FROM CourseClasses cc
JOIN Courses c ON cc.maMH = c.maMH
JOIN Semesters s ON cc.maHK = s.maHK
JOIN Teachers t ON cc.maGV = t.maGV

LEFT JOIN (
    SELECT 
        maLHP,
        COUNT(maSV) AS soLuongSV,
        SUM(CASE WHEN maSV = :maSV THEN 1 ELSE 0 END) AS daDangKy
    FROM Grades
    GROUP BY maLHP
) g_agg
 ON cc.maLHP = g_agg.maLHP

LEFT JOIN (
    SELECT 
        cc_sub.maMH, 
        MAX(g_sub.diemTong) AS diemCaoNhat
    FROM Grades g_sub
    JOIN CourseClasses cc_sub ON g_sub.maLHP = cc_sub.maLHP
    WHERE g_sub.maSV = :maSV_1
    GROUP BY cc_sub.maMH
) max_diem ON cc.maMH = max_diem.maMH

WHERE 1=1";

// Xử lý động (Dynamic Query Building) - Bulletproof
if ($keyword !== '') { 
    // Tách biệt kw1 và kw2 để tránh lỗi Native Prepare Statement
    $baseSql .= " AND (cc.maLHP LIKE :kw1 OR c.tenMH LIKE :kw2)"; 
    $params[':kw1'] = "%$keyword%"; 
    $params[':kw2'] = "%$keyword%"; 
}

if ($filter_hk !== '') { 
    $baseSql .= " AND cc.maHK = :hk"; 
    $params[':hk'] = $filter_hk; 
}

// Bổ sung lại ORDER BY để UX được nhất quán
$baseSql .= " ORDER BY s.maHK DESC, c.tenMH ASC";

$stmt = $pdo->prepare($baseSql);
$stmt->execute($params);
$classes = $stmt->fetchAll();
?>


<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Đăng ký Học phần</h1>
</div>

<?php if (isset($_SESSION['msg'])): ?>
    <div class="alert alert-<?= $_SESSION['msg_type'] ?> alert-dismissible fade show shadow-sm font-weight-bold">
        <?php if($_SESSION['msg_type'] === 'success') echo '<i class="fas fa-check-circle mr-1"></i>'; ?>
        <?= $_SESSION['msg'] ?>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    <?php unset($_SESSION['msg'], $_SESSION['msg_type']); ?>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between bg-light">
        <h6 class="m-0 font-weight-bold text-primary mb-2 mb-lg-0"><i class="fas fa-book mr-1"></i> Danh sách Lớp học phần mở trong kỳ</h6>
        
        <form method="GET" action="index.php" class="form-inline mb-0">
            <input type="hidden" name="page" value="course_classes">
            <select name="filter_hk" class="form-control form-control-sm mr-2 shadow-sm" onchange="this.form.submit()">
                <option value="">-- Tất cả Học kỳ --</option>
                <?php foreach ($semestersList as $sem): ?>
                    <option value="<?= $sem['maHK'] ?>" <?= ($filter_hk == $sem['maHK']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($sem['tenHK']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="input-group input-group-sm shadow-sm">
                <input type="text" name="keyword" class="form-control" placeholder="Tên môn, mã lớp..." value="<?= htmlspecialchars($keyword) ?>">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0 text-center" width="100%">
                <thead class="thead-light">
                    <tr>
                        <th width="10%">Mã LHP</th>
                        <th class="text-left">Môn học</th>
                        <th>Học kỳ</th>
                        <th>Giảng viên</th>
                        <th width="12%">Sĩ số</th>
                        <th width="15%">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($classes)): ?>
                        <tr><td colspan="6" class="py-5 text-muted">Không có Lớp học phần nào đang mở hoặc phù hợp với tìm kiếm.</td></tr>
                    <?php else: ?>
                        <?php foreach ($classes as $c): ?>
                            <?php 
                                $isFull = ($c['soLuongSV'] >= 50);
                                $isReg = ($c['daDangKy'] > 0);
                            ?>
                            <tr>
                                <td>#<?= $c['maLHP'] ?></td>
                                <td>
                                    <?= htmlspecialchars($c['tenMH']) ?>
                                    <?php 
                                    if ($c['diemCaoNhat'] !== null) {
                                        if ($c['diemCaoNhat'] >= 4.0) echo ' <span class="badge badge-info">(học cải thiện)</span>';
                                        else echo ' <span class="badge badge-danger">(học lại)</span>';
                                    }
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($c['tenHK']) ?></td>
                                <td><?= htmlspecialchars($c['hoTenGV']) ?></td>
                                
                                <td>
                                    <span class="badge <?= $isFull ? 'badge-danger' : ($c['soLuongSV'] >= 40 ? 'badge-warning text-dark' : 'badge-success') ?> p-2" style="font-size:13px;">
                                        <i class="fas fa-users mr-1"></i> <?= $c['soLuongSV'] ?> / 50
                                    </span>
                                </td>
                                
                                <td>
                                    <?php if ($isReg): ?>
                                        <div class="d-flex flex-row align-items-center">
                                            <span class="btn btn-secondary btn-sm font-weight-bold disabled shadow-sm w-50" style="opacity: 0.8;">
                                                <i class="fas fa-check"></i> Đã Đk
                                            </span>
                                            <a class="btn btn-info btn-sm shadow-sm w-50" href="index.php?page=class_detail&maLHP=<?= $c['maLHP'] ?>">Xem lớp</a>
                                        </div>
                                    <?php elseif ($isFull): ?>
                                        <span class="btn btn-danger btn-sm font-weight-bold disabled w-100 shadow-sm" style="opacity: 0.8;">
                                            <i class="fas fa-ban"></i> Lớp đã đầy
                                        </span>
                                    <?php else: ?>
                                        <form method="POST" action="index.php?page=course_classes">
                                            <input type="hidden" name="action" value="register">
                                            <input type="hidden" name="maLHP" value="<?= $c['maLHP'] ?>">
                                            <button type="submit" class="btn btn-primary btn-sm font-weight-bold shadow-sm w-100">
                                                <i class="fas fa-plus-circle"></i> Đăng ký
                                            </button>
                                        </form>
                                        
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