<?php
/** @var PDO $pdo */

// ==========================================
// 1. XỬ LÝ LOGIC (THÊM / SỬA / XÓA)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO Classes (maLop, tenLop, email, maKhoa, maGV) VALUES (:id, :ten, :email, :khoa, :gv)");
            $stmt->execute([
                'id' => $_POST['maLop'],
                'ten' => $_POST['tenLop'],
                'email' => $_POST['email'],
                'khoa' => $_POST['maKhoa'],
                'gv' => $_POST['maGV']
            ]);
            $_SESSION['msg'] = "Thêm Lớp học thành công!";
            $_SESSION['msg_type'] = "success";
        } elseif ($action === 'edit') {
            $sql = "UPDATE Classes SET tenLop=:ten, email=:email, maKhoa=:khoa, maGV=:gv WHERE maLop=:id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'id' => $_POST['maLop'],
                'ten' => $_POST['tenLop'],
                'email' => $_POST['email'],
                'khoa' => $_POST['maKhoa'],
                'gv' => $_POST['maGV']
            ]);
            $_SESSION['msg'] = "Cập nhật thông tin Lớp học thành công!";
            $_SESSION['msg_type'] = "success";
        } elseif ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM Classes WHERE maLop = ?");
            $stmt->execute([$_POST['maLop']]);
            $_SESSION['msg'] = "Xóa Lớp học thành công!";
            $_SESSION['msg_type'] = "success";
        }
    } catch (PDOException $e) {
        $_SESSION['msg'] = "Lỗi CSDL: " . $e->getMessage();
        $_SESSION['msg_type'] = "danger";
    }

    // Giữ lại bộ lọc sau khi Thêm/Sửa/Xóa để không bị văng khỏi trang hiện tại
    $current_filter = $_POST['current_filter_khoa'] ?? '';
    header("Location: index.php?page=classes&filter_khoa=" . urlencode($current_filter));
    exit;
}

// ==========================================
// 2. TRUY VẤN DỮ LIỆU HIỂN THỊ (JOIN, LỌC KHOA & PHÂN TRANG)
// ==========================================
$keyword = $_GET['keyword'] ?? '';
$filter_khoa = $_GET['filter_khoa'] ?? ''; // Bắt biến filter_khoa từ URL (do bảng Khoa truyền sang hoặc do chọn Dropdown)

// --- LẤY DỮ LIỆU CHO CÁC THẺ <SELECT> ---
$facultiesList = $pdo->query("SELECT maKhoa, tenKhoa FROM Faculties")->fetchAll();
$LecturersList = $pdo->query("SELECT maGV, hoTenGV FROM lecturers")->fetchAll();

// --- CẤU HÌNH PHÂN TRANG ---
$limit = 10;
$p = isset($_GET['p']) ? (int) $_GET['p'] : 1;
if ($p < 1)
    $p = 1;
$offset = (int) (($p - 1) * $limit);

$baseSelect = "SELECT c.maLop, c.tenLop, c.email, c.maKhoa, c.maGV, f.tenKhoa, t.hoTenGV 
               FROM Classes c
               LEFT JOIN Faculties f ON c.maKhoa = f.maKhoa
               LEFT JOIN Lecturers t ON c.maGV = t.maGV";

// Xây dựng điều kiện WHERE động
$whereClauses = [];
$params = [];

if ($keyword !== '') {
    $searchTerm = "%$keyword%";
    $whereClauses[] = "(c.maLop LIKE ? OR c.tenLop LIKE ? OR f.tenKhoa LIKE ? OR t.hoTenGV LIKE ?)";
    array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
}

// Nếu có lọc Khoa thì thêm điều kiện vào SQL
if ($filter_khoa !== '') {
    $whereClauses[] = "c.maKhoa = ?";
    array_push($params, $filter_khoa);
}

$whereString = "";
if (count($whereClauses) > 0) {
    $whereString = " WHERE " . implode(" AND ", $whereClauses);
}

// Đếm tổng số dòng
$countSql = "SELECT COUNT(*) FROM Classes c
             LEFT JOIN Faculties f ON c.maKhoa = f.maKhoa
             LEFT JOIN Lecturers t ON c.maGV = t.maGV" . $whereString;
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalRows = $countStmt->fetchColumn();

// Lấy dữ liệu phân trang
$sql = $baseSelect . $whereString . " ORDER BY c.maKhoa ASC, c.maLop ASC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$classes = $stmt->fetchAll();

$totalPages = ceil($totalRows / $limit);
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Quản lý Lớp Sinh hoạt</h1>
    <button href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal"
        data-target="#addModal">
        <i class="fas fa-plus fa-sm text-white-50"></i> Thêm Lớp Mới
    </button>
</div>

<?php if (isset($_SESSION['msg'])): ?>
    <div class="alert alert-<?= $_SESSION['msg_type'] ?> alert-dismissible fade show shadow-sm" role="alert">
        <?= $_SESSION['msg'] ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php unset($_SESSION['msg'], $_SESSION['msg_type']); ?>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3 row align-items-center m-0">
        <div class="col-xl-3 col-lg-2 mt-2 px-0">
            <h6 class="font-weight-bold text-primary m-0">Danh sách Lớp</h6>
        </div>
        <div class="col-xl-9 col-lg-10 px-0">
            <form method="GET" action="index.php" class="mb-0 row no-gutters align-items-center">
                <input type="hidden" name="page" value="classes">

                <div class="col-md-5 pr-md-2 mb-2 mb-md-0">
                    <select name="filter_khoa" class="form-control font-weight-bold" onchange="this.form.submit()">
                        <option value="">-- Tất cả các Khoa --</option>
                        <?php foreach ($facultiesList as $fac): ?>
                            <option value="<?= htmlspecialchars($fac['maKhoa']) ?>" <?= ($filter_khoa === $fac['maKhoa']) ? 'selected' : '' ?>>
                                Khoa <?= htmlspecialchars($fac['tenKhoa']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-7">
                    <div class="input-group w-100">
                        <input type="text" name="keyword" class="form-control"
                            placeholder="Tìm theo mã lớp, tên lớp hoặc GV..." value="<?= htmlspecialchars($keyword) ?>">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                            <?php if ($keyword !== '' || $filter_khoa !== ''): ?>
                                <a href="index.php?page=classes" class="btn btn-danger">Hủy lọc</a>
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
                        <th>Mã Lớp</th>
                        <th>Tên Lớp</th>
                        <th>Khoa Quản Lý</th>
                        <th>Cố vấn Học tập</th>
                        <th>Email Lớp</th>
                        <th width="15%" class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($classes)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">Không có lớp nào phù hợp</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($classes as $c): ?>
                            <tr>
                                <td class="font-weight-bold text-primary"><?= htmlspecialchars($c['maLop']) ?></td>
                                <td><?= htmlspecialchars($c['tenLop']) ?></td>
                                <td><span class="badge badge-info p-2"><?= htmlspecialchars($c['tenKhoa'] ?? 'Trống') ?></span>
                                </td>
                                <td><?= htmlspecialchars($c['hoTenGV'] ?? 'Trống') ?></td>
                                <td><?= htmlspecialchars($c['email']) ?></td>
                                <td class="text-center text-nowrap">
                                    <button type="button" class="btn btn-warning btn-sm mr-1 shadow-sm"
                                        onclick='openEditModal(<?= json_encode($c, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="current_filter_khoa"
                                            value="<?= htmlspecialchars($filter_khoa) ?>">
                                        <input type="hidden" name="maLop" value="<?= htmlspecialchars($c['maLop']) ?>">
                                        <button type="submit" class="btn btn-danger btn-sm shadow-sm"
                                            onclick="return confirm('Xóa lớp có thể ảnh hưởng sinh viên thuộc lớp. Xác nhận xóa?')">
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
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-end mb-0">
                    <li class="page-item <?= ($p <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link"
                            href="?page=classes&keyword=<?= urlencode($keyword) ?>&filter_khoa=<?= urlencode($filter_khoa) ?>&p=<?= $p - 1 ?>">Trước</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($i == $p) ? 'active' : '' ?>">
                            <a class="page-link"
                                href="?page=classes&keyword=<?= urlencode($keyword) ?>&filter_khoa=<?= urlencode($filter_khoa) ?>&p=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($p >= $totalPages) ? 'disabled' : '' ?>">
                        <a class="page-link"
                            href="?page=classes&keyword=<?= urlencode($keyword) ?>&filter_khoa=<?= urlencode($filter_khoa) ?>&p=<?= $p + 1 ?>">Sau</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form method="POST" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold">Thêm Lớp Mới</h5>
                <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="current_filter_khoa" value="<?= htmlspecialchars($filter_khoa) ?>">

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label class="font-weight-bold">Mã Lớp</label>
                        <input type="text" class="form-control" name="maLop" required>
                    </div>
                    <div class="form-group col-md-8">
                        <label class="font-weight-bold">Tên Lớp</label>
                        <input type="text" class="form-control" name="tenLop" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="font-weight-bold">Trực thuộc Khoa</label>
                        <select class="form-control" name="maKhoa" required>
                            <option value="">-- Chọn Khoa --</option>
                            <?php foreach ($facultiesList as $fac): ?>
                                <option value="<?= htmlspecialchars($fac['maKhoa']) ?>" <?= ($filter_khoa === $fac['maKhoa']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($fac['tenKhoa']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label class="font-weight-bold">Giảng viên Chủ nhiệm</label>
                        <select class="form-control" name="maGV" required>
                            <option value="">-- Chọn Giảng viên --</option>
                            <?php foreach ($LecturersList as $tea): ?>
                                <option value="<?= htmlspecialchars($tea['maGV']) ?>">
                                    <?= htmlspecialchars($tea['hoTenGV']) ?> (<?= htmlspecialchars($tea['maGV']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="font-weight-bold">Email Lớp (Nếu có)</label>
                    <input type="email" class="form-control" name="email">
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-primary">Lưu Lớp</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form method="POST" class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title font-weight-bold">Cập nhật Lớp</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="current_filter_khoa" value="<?= htmlspecialchars($filter_khoa) ?>">

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label class="font-weight-bold">Mã Lớp</label>
                        <input type="text" class="form-control bg-light" name="maLop" id="edit_maLop" readonly required>
                    </div>
                    <div class="form-group col-md-8">
                        <label class="font-weight-bold">Tên Lớp</label>
                        <input type="text" class="form-control" name="tenLop" id="edit_tenLop" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="font-weight-bold">Trực thuộc Khoa</label>
                        <select class="form-control" name="maKhoa" id="edit_maKhoa" required>
                            <?php foreach ($facultiesList as $fac): ?>
                                <option value="<?= htmlspecialchars($fac['maKhoa']) ?>">
                                    <?= htmlspecialchars($fac['tenKhoa']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label class="font-weight-bold">Giảng viên Chủ nhiệm</label>
                        <select class="form-control" name="maGV" id="edit_maGV" required>
                            <?php foreach ($LecturersList as $tea): ?>
                                <option value="<?= htmlspecialchars($tea['maGV']) ?>">
                                    <?= htmlspecialchars($tea['hoTenGV']) ?> (<?= htmlspecialchars($tea['maGV']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="font-weight-bold">Email Lớp</label>
                    <input type="email" class="form-control" name="email" id="edit_email">
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-warning font-weight-bold">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(data) {
        document.getElementById('edit_maLop').value = data.maLop;
        document.getElementById('edit_tenLop').value = data.tenLop;
        document.getElementById('edit_email').value = data.email;
        document.getElementById('edit_maKhoa').value = data.maKhoa;
        document.getElementById('edit_maGV').value = data.maGV;
        $('#editModal').modal('show');
    }
</script>