<?php
/** @var PDO $pdo */

$maGV = $_SESSION['user']['username']; // Lấy mã số giảng viên từ Session
$keyword = trim($_GET['keyword'] ?? '');
$filter_hk = $_GET['filter_hk'] ?? '';

// 1. Lấy danh sách học kỳ cho bộ lọc Dropdown
$semestersList = $pdo->query("SELECT maHK, tenHK FROM Semesters ORDER BY maHK DESC")->fetchAll();

// 2. Cấu hình phân trang
$limit = 10;
$p = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($p < 1) $p = 1;
$offset = ($p - 1) * $limit;

// 3. SQL: Bổ sung cột cc.isLocked vào câu lệnh truy vấn
$baseSelect = "SELECT cc.maLHP, c.tenMH, s.tenHK, cc.isLocked, COUNT(g.maSV) AS soLuongSV 
               FROM CourseClasses cc
               JOIN Courses c ON cc.maMH = c.maMH
               JOIN Semesters s ON cc.maHK = s.maHK
               LEFT JOIN Grades g ON cc.maLHP = g.maLHP
               WHERE cc.maGV = :maGV";

$whereClauses = [];
$params = ['maGV' => $maGV];

if ($keyword !== '') {
    $baseSelect .= " AND (cc.maLHP LIKE :keyword OR c.tenMH LIKE :keyword)";
    $params['keyword'] = "%$keyword%";
}
if ($filter_hk !== '') {
    $baseSelect .= " AND cc.maHK = :hk";
    $params['hk'] = $filter_hk;
}

$groupString = " GROUP BY cc.maLHP, c.tenMH, s.tenHK, cc.isLocked ";

// Tính tổng số dòng để phân trang bằng Subquery
$countSql = "SELECT COUNT(*) FROM ($baseSelect $groupString) AS temp";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalRows = $countStmt->fetchColumn();

// Lấy danh sách hiển thị thực tế
$sql = $baseSelect . $groupString . " ORDER BY s.maHK DESC, cc.maLHP DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$myClasses = $stmt->fetchAll();

$totalPages = ceil($totalRows / $limit);
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Lịch dạy & Quản lý Điểm</h1>
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
        <h6 class="font-weight-bold text-success m-0 mb-2 mb-lg-0">
            <i class="fas fa-layer-group mr-1"></i> Các Lớp Học Phần Đang Đảm Nhiệm
        </h6>
        
        <form method="GET" action="index.php" class="form-inline mb-0">
            <input type="hidden" name="page" value="grades">
            <select name="filter_hk" class="form-control form-control-sm mr-2 shadow-sm" onchange="this.form.submit()">
                <option value="">-- Tất cả Học kỳ --</option>
                <?php foreach ($semestersList as $sem): ?>
                    <option value="<?= $sem['maHK'] ?>" <?= ($filter_hk == $sem['maHK']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($sem['tenHK']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <div class="input-group input-group-sm shadow-sm">
                <input type="text" name="keyword" class="form-control" placeholder="Tìm mã lớp, môn học..." value="<?= htmlspecialchars($keyword) ?>">
                <div class="input-group-append">
                    <button class="btn btn-success" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0" width="100%">
                <thead class="thead-light text-center">
                    <tr>
                        <th width="10%">Mã LHP</th>
                        <th class="text-left">Tên Môn Học / Học Phần</th>
                        <th>Học kỳ</th>
                        <th width="12%">Sĩ số</th>
                        <th width="15%">Trạng thái</th>
                        <th width="15%">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($myClasses)): ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted">Bạn không có lịch dạy nào phù hợp với bộ lọc.</td></tr>
                    <?php else: ?>
                        <?php foreach ($myClasses as $c): ?>
                            <?php $isLocked = ((int)$c['isLocked'] === 1); ?>
                            <tr class="text-center">
                                <td class="font-weight-bold text-success">#<?= $c['maLHP'] ?></td>
                                <td class="text-left font-weight-bold text-dark"><?= htmlspecialchars($c['tenMH']) ?></td>
                                <td><?= htmlspecialchars($c['tenHK']) ?></td>
                                <td>
                                    <span class="badge badge-light border p-2" style="font-size: 13px;">
                                        <i class="fas fa-user-graduate mr-1 text-success"></i> <?= $c['soLuongSV'] ?> SV
                                    </span>
                                </td>
                                
                                <td>
                                    <?php if ($isLocked): ?>
                                        <span class="badge badge-danger p-2"><i class="fas fa-lock mr-1"></i> Đã chốt điểm</span>
                                    <?php else: ?>
                                        <span class="badge badge-success p-2"><i class="fas fa-pen mr-1"></i> Đang mở</span>
                                    <?php endif; ?>
                                </td>
                                
                                <td>
                                    <a href="index.php?page=grade_detail&maLHP=<?= $c['maLHP'] ?>" 
                                       class="btn <?= $isLocked ? 'btn-secondary' : 'btn-success' ?> btn-sm font-weight-bold shadow-sm w-100">
                                        <?php if ($isLocked): ?>
                                            <i class="fas fa-eye mr-1"></i> Xem điểm
                                        <?php else: ?>
                                            <i class="fas fa-edit mr-1"></i> Nhập điểm
                                        <?php endif; ?>
                                    </a>
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