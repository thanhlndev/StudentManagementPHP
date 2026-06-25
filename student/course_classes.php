<?php
/** @var PDO $pdo */

$maSV = $_SESSION['user']['username'];

// ==========================================
// 1. XỬ LÝ LOGIC ĐĂNG KÝ HỌC PHẦN (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $maLHP_reg = (int) $_POST['maLHP'];

    try {
        $pdo->beginTransaction();

        $lockStmt = $pdo->prepare("SELECT maLHP FROM CourseClasses WHERE maLHP = ? FOR UPDATE");
        $lockStmt->execute([$maLHP_reg]);

        $checkReg = $pdo->prepare("SELECT COUNT(*) FROM Grades WHERE maLHP = ? AND maSV = ?");
        $checkReg->execute([$maLHP_reg, $maSV]);
        $isRegistered = $checkReg->fetchColumn();

        $checkCount = $pdo->prepare("SELECT COUNT(*) FROM Grades WHERE maLHP = ?");
        $checkCount->execute([$maLHP_reg]);
        $currentCount = $checkCount->fetchColumn();
        // Lấy lịch của lớp muốn đăng ký (new_sch)
        //         ↓
        // So sánh với tất cả lịch các lớp mà sinh viên đã đăng ký (old_sch)
        //         ↓
        // Chỉ xét cùng thứ trong tuần
        //         ↓
        // Kiểm tra hai khoảng tiết học có giao nhau hay không
        //         ↓
        // COUNT(*) > 0
        // => Bị trùng lịch

        // COUNT(*) = 0
        // => Không trùng lịch
        $sqlCheckCollision = "
            SELECT COUNT(*) 
            FROM class_schedules new_sch
            JOIN class_schedules old_sch ON new_sch.day_of_week = old_sch.day_of_week
            JOIN Grades g ON old_sch.maLHP = g.maLHP
            WHERE new_sch.maLHP = ? 
              AND g.maSV = ?
              AND new_sch.start_period < (old_sch.start_period + old_sch.num_periods)
              AND old_sch.start_period < (new_sch.start_period + new_sch.num_periods)
        ";
        $stmtCollision = $pdo->prepare($sqlCheckCollision);
        $stmtCollision->execute([$maLHP_reg, $maSV]);
        $isColliding = $stmtCollision->fetchColumn();

        if ($isRegistered > 0) {
            $_SESSION['msg'] = "Thất bại: Bạn đã đăng ký Lớp học phần #$maLHP_reg từ trước!";
            $_SESSION['msg_type'] = "warning";
        } elseif ($currentCount >= 50) {
            $_SESSION['msg'] = "Thất bại: Lớp học phần #$maLHP_reg đã đủ sĩ số!";
            $_SESSION['msg_type'] = "danger";
        } elseif ($isColliding > 0) {
            $_SESSION['msg'] = "Thất bại: Lịch của Lớp học phần #$maLHP_reg bị trùng với thời khóa biểu hiện tại của bạn!";
            $_SESSION['msg_type'] = "danger";
        } else {
            $stmtInsert = $pdo->prepare("INSERT INTO Grades (maSV, maLHP) VALUES (?, ?)");
            $stmtInsert->execute([$maSV, $maLHP_reg]);

            $_SESSION['msg'] = "Thành công: Đã ghi danh vào Lớp học phần #$maLHP_reg!";
            $_SESSION['msg_type'] = "success";
        }

        $pdo->commit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['msg'] = "Lỗi hệ thống: " . $e->getMessage();
        $_SESSION['msg_type'] = "danger";
    }

    header("Location: index.php?page=course_classes");
    exit;
}

// ==========================================
// 2. TRUY VẤN DANH SÁCH LỚP HỌC PHẦN 
// ==========================================
$keyword = trim($_GET['keyword'] ?? '');
$filter_hk = $_GET['filter_hk'] ?? '';
$hide_conflict = isset($_GET['hide_conflict']) ? 1 : 0;

// [MỚI] Lấy Mã Khoa của sinh viên hiện tại để làm màng lọc
$stmtKhoa = $pdo->prepare("
    SELECT cl.maKhoa 
    FROM students s 
    JOIN classes cl ON s.maLop = cl.maLop 
    WHERE s.maSV = ?
");
$stmtKhoa->execute([$maSV]);
$maKhoaSV = $stmtKhoa->fetchColumn();

$semestersList = $pdo->query("SELECT maHK, tenHK FROM Semesters ORDER BY maHK DESC")->fetchAll();

// Đưa maKhoaSV vào mảng tham số chuẩn
$params = [
    ':maSV' => $maSV,
    ':maSV_1' => $maSV,
    ':maKhoaSV' => $maKhoaSV
];

$baseSql = "SELECT 
    cc.maLHP, 
    c.tenMH, 
    s.tenHK, 
    t.hoTenGV,
    COALESCE(g_agg.soLuongSV, 0) AS soLuongSV,
    COALESCE(g_agg.daDangKy, 0) AS daDangKy,
    max_diem.diemCaoNhat,
    lich.thongTinLich
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
) g_agg ON cc.maLHP = g_agg.maLHP 

LEFT JOIN (
    SELECT 
        cc_sub.maMH, 
        MAX(g_sub.diemTong) AS diemCaoNhat
    FROM Grades g_sub
    JOIN CourseClasses cc_sub ON g_sub.maLHP = cc_sub.maLHP
    WHERE g_sub.maSV = :maSV_1
    GROUP BY cc_sub.maMH
) max_diem ON cc.maMH = max_diem.maMH

LEFT JOIN (
    SELECT 
        cs.maLHP,
        GROUP_CONCAT(
            CONCAT('<div class=\"mb-1\">Thứ ', cs.day_of_week, ' (T', cs.start_period, '-', (cs.start_period + cs.num_periods - 1), ')<br><small class=\"text-muted font-weight-bold\">P.', r.room_name, '</small></div>')
            SEPARATOR '<hr class=\"my-1\">'
        ) AS thongTinLich
    FROM class_schedules cs
    JOIN rooms r ON cs.room_id = r.room_id
    GROUP BY cs.maLHP
) lich ON cc.maLHP = lich.maLHP

WHERE 1=1 
  AND c.maKhoa = :maKhoaSV"; // [MỚI] Bắt buộc môn học phải thuộc Khoa của sinh viên

// Xử lý động (Dynamic Query Building)
if ($keyword !== '') {
    $baseSql .= " AND (cc.maLHP LIKE :kw1 OR c.tenMH LIKE :kw2)";
    $params[':kw1'] = "%$keyword%";
    $params[':kw2'] = "%$keyword%";
}

if ($filter_hk !== '') {
    $baseSql .= " AND cc.maHK = :hk";
    $params[':hk'] = $filter_hk;
}

if ($hide_conflict) {
    $baseSql .= " AND NOT EXISTS (
        SELECT 1 
        FROM class_schedules new_sch
        JOIN class_schedules old_sch ON new_sch.day_of_week = old_sch.day_of_week
        JOIN Grades g_conflict ON old_sch.maLHP = g_conflict.maLHP
        WHERE new_sch.maLHP = cc.maLHP 
          AND g_conflict.maSV = :maSV_conflict
          AND old_sch.maLHP != new_sch.maLHP 
          AND new_sch.start_period < (old_sch.start_period + old_sch.num_periods)
          AND old_sch.start_period < (new_sch.start_period + new_sch.num_periods)
    )";
    $params[':maSV_conflict'] = $maSV;
}

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
        <?php if ($_SESSION['msg_type'] === 'success')
            echo '<i class="fas fa-check-circle mr-1"></i>'; ?>
        <?= $_SESSION['msg'] ?>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    <?php unset($_SESSION['msg'], $_SESSION['msg_type']); ?>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between bg-light">
        <h6 class="m-0 font-weight-bold text-primary mb-3 mb-lg-0"><i class="fas fa-book mr-1"></i> Danh sách Học phần
            Khoa <?= htmlspecialchars($maKhoaSV) ?></h6>

        <form method="GET" action="index.php" class="form-inline mb-0">
            <input type="hidden" name="page" value="course_classes">

            <div class="custom-control custom-checkbox mr-3 mt-1 mt-md-0">
                <input type="checkbox" class="custom-control-input" id="hideConflictCheck" name="hide_conflict"
                    value="1" <?= $hide_conflict ? 'checked' : '' ?> onchange="this.form.submit()">
                <label class="custom-control-label font-weight-bold text-danger pt-1" for="hideConflictCheck"
                    style="cursor: pointer;">
                    <i class="fas fa-eye-slash"></i> Ẩn môn trùng lịch
                </label>
            </div>

            <select name="filter_hk" class="form-control form-control-sm mr-2 shadow-sm" onchange="this.form.submit()">
                <option value="">-- Tất cả Học kỳ --</option>
                <?php foreach ($semestersList as $sem): ?>
                    <option value="<?= $sem['maHK'] ?>" <?= ($filter_hk == $sem['maHK']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($sem['tenHK']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <div class="input-group input-group-sm shadow-sm mt-2 mt-md-0">
                <input type="text" name="keyword" class="form-control" placeholder="Tên môn, mã lớp..."
                    value="<?= htmlspecialchars($keyword) ?>">
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
                        <th width="15%">Lịch học</th>
                        <th width="10%">Sĩ số</th>
                        <th width="15%">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($classes)): ?>
                        <tr>
                            <td colspan="7" class="py-5 text-muted">Không có Lớp học phần nào đang mở hoặc phù hợp với tìm
                                kiếm/bộ lọc.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($classes as $c): ?>
                            <?php
                            $isFull = ($c['soLuongSV'] >= 50);
                            $isReg = ($c['daDangKy'] > 0);
                            ?>
                            <tr>
                                <td class="font-weight-bold text-dark align-middle">#<?= $c['maLHP'] ?></td>
                                <td class="text-left align-middle">
                                    <span class="font-weight-bold text-primary"><?= htmlspecialchars($c['tenMH']) ?></span>
                                    <?php
                                    if ($c['diemCaoNhat'] !== null) {
                                        if ($c['diemCaoNhat'] >= 4.0)
                                            echo ' <span class="badge badge-info">(học cải thiện)</span>';
                                        else
                                            echo ' <span class="badge badge-danger">(học lại)</span>';
                                    }
                                    ?>
                                </td>
                                <td class="align-middle"><?= htmlspecialchars($c['tenHK']) ?></td>
                                <td class="align-middle"><?= htmlspecialchars($c['hoTenGV']) ?></td>

                                <td class="align-middle text-sm" style="font-size: 0.85rem;">
                                    <?= $c['thongTinLich'] ?? '<span class="text-muted font-italic">Chưa có lịch</span>' ?>
                                </td>

                                <td class="align-middle">
                                    <span
                                        class="badge <?= $isFull ? 'badge-danger' : ($c['soLuongSV'] >= 40 ? 'badge-warning text-dark' : 'badge-success') ?> p-2"
                                        style="font-size:13px;">
                                        <i class="fas fa-users mr-1"></i> <?= $c['soLuongSV'] ?> / 50
                                    </span>
                                </td>

                                <td class="align-middle">
                                    <?php if ($isReg): ?>
                                        <div class="d-flex flex-column align-items-center">
                                            <span class="btn btn-secondary btn-sm font-weight-bold disabled shadow-sm w-100 mb-1"
                                                style="opacity: 0.8;">
                                                <i class="fas fa-check"></i> Đã Đk
                                            </span>
                                            <a class="btn btn-info btn-sm shadow-sm w-100"
                                                href="index.php?page=class_detail&maLHP=<?= $c['maLHP'] ?>">Xem lớp</a>
                                        </div>
                                    <?php elseif ($isFull): ?>
                                        <span class="btn btn-danger btn-sm font-weight-bold disabled w-100 shadow-sm"
                                            style="opacity: 0.8;">
                                            <i class="fas fa-ban"></i> Lớp đã đầy
                                        </span>
                                    <?php else: ?>
                                        <form method="POST" action="index.php?page=course_classes" class="mb-0">
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