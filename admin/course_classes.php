<?php
/** @var PDO $pdo */

// ==========================================
// 2. TRUY VẤN DỮ LIỆU HIỂN THỊ (TÌM KIẾM, LỌC HỌC KỲ & PHÂN TRANG PHP)
// ==========================================
$keyword = $_GET['keyword'] ?? '';
$filter_hk = $_GET['filter_hk'] ?? '';

// --- FETCH PRE-DATA CHO CÁC THẺ <SELECT> ---
$coursesList = $pdo->query("SELECT maMH, tenMH FROM Courses")->fetchAll();
$semestersList = $pdo->query("SELECT maHK, tenHK FROM Semesters")->fetchAll();
$teachersList = $pdo->query("SELECT maGV, hoTenGV FROM Teachers")->fetchAll();

// --- CẤU HÌNH PHÂN TRANG ---
$limit = 10;
$p = isset($_GET['p']) ? (int) $_GET['p'] : 1;
if ($p < 1)
    $p = 1;
$offset = (int) (($p - 1) * $limit);

// Lệnh cơ sở kết nối 3 bảng
$baseSelect = "SELECT cc.maLHP, cc.maMH, cc.maHK, cc.maGV, 
                      c.tenMH, s.tenHK, t.hoTenGV 
               FROM CourseClasses cc
               LEFT JOIN Courses c ON cc.maMH = c.maMH
               LEFT JOIN Semesters s ON cc.maHK = s.maHK
               LEFT JOIN Teachers t ON cc.maGV = t.maGV";

// Xây dựng điều kiện WHERE động dựa trên việc có lọc học kỳ hay không
$whereClauses = [];
$params = [];

// 1. Nếu có từ khóa tìm kiếm
if ($keyword !== '') {
    $searchTerm = "%$keyword%";
    $whereClauses[] = "(cc.maLHP LIKE ? OR c.tenMH LIKE ? OR s.tenHK LIKE ? OR t.hoTenGV LIKE ?)";
    array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
}

// 2. Nếu có chọn học kỳ cụ thể (khác rỗng '')
if ($filter_hk !== '') {
    $whereClauses[] = "cc.maHK = ?";
    array_push($params, $filter_hk);
}

// Gộp các điều kiện lại bằng từ khóa AND
$whereString = "";
if (count($whereClauses) > 0) {
    $whereString = " WHERE " . implode(" AND ", $whereClauses);
}

// BƯỚC A: Đếm tổng số dòng thỏa mãn bộ lọc để phân trang
$countSql = "SELECT COUNT(*) FROM CourseClasses cc
             LEFT JOIN Courses c ON cc.maMH = c.maMH
             LEFT JOIN Semesters s ON cc.maHK = s.maHK
             LEFT JOIN Teachers t ON cc.maGV = t.maGV" . $whereString;
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalRows = $countStmt->fetchColumn();

// BƯỚC B: Lấy dữ liệu phân trang thực tế
$sql = $baseSelect . $whereString . " ORDER BY cc.maLHP DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$courseClasses = $stmt->fetchAll();

$totalPages = ceil($totalRows / $limit);
?>


<div class="card shadow mb-4">
    <div class="card-header py-3 row align-items-center m-0">
        <div class="col-xl-4 col-lg-3 mt-2 px-0">
            <h6 class="font-weight-bold text-primary m-0">Danh sách Lớp Học phần</h6>
        </div>
        <div class="col-xl-8 col-lg-9 px-0">
            <form method="GET" action="index.php" class="mb-0 row no-gutters align-items-center">
                <input type="hidden" name="page" value="course_classes">

                <div class="col-md-4 pr-md-2 mb-2 mb-md-0">
                    <select name="filter_hk" class="form-control" onchange="this.form.submit()">
                        <option value="">Tất cả học kỳ</option>
                        <?php foreach ($semestersList as $sem): ?>
                            <option value="<?= htmlspecialchars($sem['maHK']) ?>" <?= ($filter_hk === $sem['maHK']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sem['tenHK']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-8">
                    <div class="input-group w-100">
                        <input type="text" name="keyword" class="form-control"
                            placeholder="Tìm mã lớp, môn học, giảng viên..." value="<?= htmlspecialchars($keyword) ?>">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i> Tìm
                            </button>
                            <?php if ($keyword !== '' || $filter_hk !== ''): ?>
                                <a href="index.php?page=course_classes" class="btn btn-danger">Hủy lọc</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        <th width="8%" class="text-center">Mã LHP</th>
                        <th>Tên Môn Học</th>
                        <th>Học kỳ</th>
                        <th>Giảng viên Phụ trách</th>
                        <th width="15%" class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($courseClasses)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-3">Không tìm thấy dữ liệu phù hợp</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($courseClasses as $cc): ?>
                            <tr>
                                <td class="font-weight-bold text-danger text-center">#<?= htmlspecialchars($cc['maLHP']) ?></td>
                                <td class="font-weight-bold"><?= htmlspecialchars($cc['tenMH'] ?? '[Lỗi/Xóa]') ?></td>
                                <td><span
                                        class="badge badge-success p-2"><?= htmlspecialchars($cc['tenHK'] ?? '[Lỗi/Xóa]') ?></span>
                                </td>
                                <td><?= htmlspecialchars($cc['hoTenGV'] ?? '[Lỗi/Xóa]') ?></td>
                                <td class="text-center">
                                    <a href="index.php?page=course_class_detail&id=<?= htmlspecialchars($cc['maLHP']) ?>"
                                        class="btn btn-info btn-sm shadow-sm" title="Xếp lịch & Chi tiết">
                                        <i class="fas fa-calendar-alt"></i> Lịch
                                    </a>
                                    <button type="button" class="btn btn-warning btn-sm"
                                        onclick='openEditModal(<?= json_encode($cc, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="maLHP" value="<?= htmlspecialchars($cc['maLHP']) ?>">
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Xóa Lớp HP có thể ảnh hưởng tới điểm số SV đã đăng ký. Bạn chắc chắn?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-end mb-0">
                    <li class="page-item <?= ($p <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link"
                            href="?page=course_classes&keyword=<?= urlencode($keyword) ?>&filter_hk=<?= urlencode($filter_hk) ?>&p=<?= $p - 1 ?>">Trước</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($i == $p) ? 'active' : '' ?>">
                            <a class="page-link"
                                href="?page=course_classes&keyword=<?= urlencode($keyword) ?>&filter_hk=<?= urlencode($filter_hk) ?>&p=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($p >= $totalPages) ? 'disabled' : '' ?>">
                        <a class="page-link"
                            href="?page=course_classes&keyword=<?= urlencode($keyword) ?>&filter_hk=<?= urlencode($filter_hk) ?>&p=<?= $p + 1 ?>">Sau</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>

    </div>
</div>

<script>
    function openEditModal(data) {
        // Đổ ID vào ô Text
        document.getElementById('edit_maLHP').value = data.maLHP;

        // Javascript tự động tìm thẻ <option> có value tương ứng để gán Selected
        document.getElementById('edit_maMH').value = data.maMH;
        document.getElementById('edit_maHK').value = data.maHK;
        document.getElementById('edit_maGV').value = data.maGV;

        $('#editModal').modal('show');
    }
</script>