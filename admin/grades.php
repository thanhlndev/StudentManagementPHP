<?php
/** @var PDO $pdo */

// ==========================================
// 1. XỬ LÝ QUICK ACTION: KHÓA / MỞ KHÓA NHANH BẢNG ĐIỂM
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_lock') {
    $maLHP_toggle = (int)$_POST['maLHP'];
    $new_status = (int)$_POST['new_status']; // 1 là Khóa, 0 là Mở
    
    // Đã chuẩn hóa tên bảng thành in thường: courseclasses
    $stmt = $pdo->prepare("UPDATE courseclasses SET isLocked = ? WHERE maLHP = ?");
    $stmt->execute([$new_status, $maLHP_toggle]);
    
    $actionName = $new_status === 1 ? "KHOÁ" : "MỞ KHOÁ";
    $_SESSION['msg'] = "Đã $actionName thành công bảng điểm của Lớp học phần #$maLHP_toggle!";
    $_SESSION['msg_type'] = $new_status === 1 ? "warning" : "success";
    
    // Refresh lại trang để tránh lỗi nạp lại form
    header("Location: index.php?page=grades");
    exit;
}

// ==========================================
// 2. TRUY VẤN DỮ LIỆU & PHÂN TRANG (CÓ TÍCH HỢP isLocked)
// ==========================================
$keyword = trim($_GET['keyword'] ?? '');
$filter_hk = $_GET['filter_hk'] ?? '';

// Đã chuẩn hóa tên bảng thành in thường: semesters
$semestersList = $pdo->query("SELECT maHK, tenHK FROM semesters ORDER BY maHK DESC")->fetchAll();

// Cấu hình phân trang
$limit = 10;
$p = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($p < 1) $p = 1;
$offset = ($p - 1) * $limit;

// Câu lệnh Query thông minh sử dụng Subquery để đếm sĩ số
// Đã chuẩn hóa TOÀN BỘ tên bảng thành in thường, đặc biệt là JOIN lecturers
$baseSql = "SELECT cc.maLHP, c.tenMH, s.tenHK, t.hoTenGV, cc.isLocked,
            (SELECT COUNT(*) FROM grades g WHERE g.maLHP = cc.maLHP) AS soLuongSV
            FROM courseclasses cc
            JOIN courses c ON cc.maMH = c.maMH
            JOIN semesters s ON cc.maHK = s.maHK
            JOIN lecturers t ON cc.maGV = t.maGV
            WHERE 1=1";

$params = [];

if ($keyword !== '') {
    $baseSql .= " AND (cc.maLHP LIKE :kw OR c.tenMH LIKE :kw OR t.hoTenGV LIKE :kw)";
    $params[':kw'] = "%$keyword%";
}
if ($filter_hk !== '') {
    $baseSql .= " AND cc.maHK = :hk";
    $params[':hk'] = $filter_hk;
}

// Tính tổng số dòng
$countSql = "SELECT COUNT(*) FROM ($baseSql) AS temp";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalRows = $countStmt->fetchColumn();

// Lấy dữ liệu theo trang
$baseSql .= " ORDER BY cc.maLHP DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($baseSql);
$stmt->execute($params);
$classes = $stmt->fetchAll();

$totalPages = ceil($totalRows / $limit);
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Quản lý Bảng điểm Toàn trường</h1>
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
        <h6 class="m-0 font-weight-bold text-primary mb-2 mb-lg-0"><i class="fas fa-layer-group mr-1"></i> Danh sách Lớp học phần</h6>
        
        <form method="GET" action="index.php" class="form-inline mb-0">
            <input type="hidden" name="page" value="grades">
            <select name="filter_hk" class="form-control form-control-sm mr-2 shadow-sm" onchange="this.form.submit()">
                <option value="">-- Tất cả Học kỳ --</option>
                <?php foreach ($semestersList as $sem): ?>
                    <option value="<?= htmlspecialchars($sem['maHK']) ?>" <?= ($filter_hk == $sem['maHK']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($sem['tenHK']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="input-group input-group-sm shadow-sm">
                <input type="text" name="keyword" class="form-control" placeholder="Mã lớp, Môn, Giảng viên..." value="<?= htmlspecialchars($keyword) ?>">
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
                        <th width="8%">Mã LHP</th>
                        <th class="text-left">Môn học</th>
                        <th>Học kỳ</th>
                        <th>Giảng viên</th>
                        <th width="10%">Sĩ số</th>
                        <th width="12%">Trạng thái</th>
                        <th width="18%">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($classes)): ?>
                        <tr><td colspan="7" class="py-5 text-muted">Không tìm thấy Lớp học phần nào.</td></tr>
                    <?php else: ?>
                        <?php foreach ($classes as $c): ?>
                            <?php $isLocked = (int)$c['isLocked'] === 1; ?>
                            <tr>
                                <td class="font-weight-bold text-dark">#<?= $c['maLHP'] ?></td>
                                <td class="text-left font-weight-bold text-primary"><?= htmlspecialchars($c['tenMH']) ?></td>
                                <td><?= htmlspecialchars($c['tenHK']) ?></td>
                                <td><?= htmlspecialchars($c['hoTenGV']) ?></td>
                                <td><span class="badge badge-info p-2"><i class="fas fa-users mr-1"></i> <?= $c['soLuongSV'] ?></span></td>
                                
                                <td>
                                    <?php if ($isLocked): ?>
                                        <span class="badge badge-danger p-2"><i class="fas fa-lock mr-1"></i> Đã khóa</span>
                                    <?php else: ?>
                                        <span class="badge badge-success p-2"><i class="fas fa-unlock mr-1"></i> Đang mở</span>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="d-flex justify-content-center">
                                    <a href="index.php?page=grade_detail&maLHP=<?= $c['maLHP'] ?>" class="btn btn-primary btn-sm font-weight-bold shadow-sm mr-2" title="Xem chi tiết điểm">
                                        <i class="fas fa-eye"></i> Xem
                                    </a>
                                    
                                    <form method="POST" action="index.php?page=grades">
                                        <input type="hidden" name="action" value="toggle_lock">
                                        <input type="hidden" name="maLHP" value="<?= $c['maLHP'] ?>">
                                        <input type="hidden" name="new_status" value="<?= $isLocked ? 0 : 1 ?>">
                                        
                                        <?php if ($isLocked): ?>
                                            <button type="submit" class="btn btn-success btn-sm font-weight-bold shadow-sm" onclick="return confirm('Mở khóa sẽ cho phép giảng viên tiếp tục sửa điểm. Chắc chắn mở?');" title="Mở khóa bảng điểm">
                                                <i class="fas fa-lock-open"></i> Mở
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" class="btn btn-warning btn-sm font-weight-bold shadow-sm text-dark" onclick="return confirm('Khóa bảng điểm sẽ vô hiệu hóa quyền sửa điểm của giảng viên. Chắc chắn khóa?');" title="Khóa bảng điểm">
                                                <i class="fas fa-lock"></i> Khóa
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($totalPages > 1): ?>
        <nav class="mt-4 pr-3">
            <ul class="pagination justify-content-end mb-3">
                <li class="page-item <?= ($p <= 1) ? 'disabled' : '' ?>"><a class="page-link" href="?page=grades&keyword=<?= urlencode($keyword) ?>&filter_hk=<?= urlencode($filter_hk) ?>&p=<?= $p - 1 ?>">Trước</a></li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i == $p) ? 'active' : '' ?>"><a class="page-link" href="?page=grades&keyword=<?= urlencode($keyword) ?>&filter_hk=<?= urlencode($filter_hk) ?>&p=<?= $i ?>"><?= $i ?></a></li>
                <?php endfor; ?>
                <li class="page-item <?= ($p >= $totalPages) ? 'disabled' : '' ?>"><a class="page-link" href="?page=grades&keyword=<?= urlencode($keyword) ?>&filter_hk=<?= urlencode($filter_hk) ?>&p=<?= $p + 1 ?>">Sau</a></li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>