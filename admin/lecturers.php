<?php
/** @var PDO $pdo */

// ==========================================
// 1. XỬ LÝ LOGIC (THÊM / SỬA / XÓA)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO Lecturers (maGV, hoTenGV, email, sdt) VALUES (:id, :hoten, :email, :sdt)");
            $stmt->execute([
                'id' => $_POST['maGV'],
                'hoten' => $_POST['hoTenGV'],
                'email' => $_POST['email'],
                'sdt' => $_POST['sdt']
            ]);
            $_SESSION['msg'] = "Thêm giảng viên thành công!";
            $_SESSION['msg_type'] = "success";
        } elseif ($action === 'edit') {
            $sql = "UPDATE Lecturers SET hoTenGV=:hoten, email=:email, sdt=:sdt WHERE maGV=:id";
            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                'id' => $_POST['maGV'],
                'hoten' => $_POST['hoTenGV'],
                'email' => $_POST['email'],
                'sdt' => $_POST['sdt']
            ]);
            $_SESSION['msg'] = "Cập nhật thông tin giảng viên thành công!";
            $_SESSION['msg_type'] = "success";
        } elseif ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM Lecturers WHERE maGV = ?");
            $stmt->execute([$_POST['maGV']]);
            $_SESSION['msg'] = "Xóa giảng viên thành công!";
            $_SESSION['msg_type'] = "success";
        }
    } catch (PDOException $e) {
        $_SESSION['msg'] = "Lỗi CSDL: " . $e->getMessage();
        $_SESSION['msg_type'] = "danger";
    }

    header("Location: index.php?page=lecturers");
    exit;
}

// ==========================================
// 2. TRUY VẤN DỮ LIỆU HIỂN THỊ (TÌM KIẾM & PHÂN TRANG PHP)
// ==========================================
$keyword = trim($_GET['keyword'] ?? '');

// --- CẤU HÌNH PHÂN TRANG (Dùng biến $p, tuyệt đối không dùng $page) ---
$limit = 5;
$p = isset($_GET['p']) ? (int) $_GET['p'] : 1;
if ($p < 1)
    $p = 1;
$offset = ($p - 1) * $limit;

if ($keyword !== '') {
    $searchTerm = "%$keyword%";
    // Đếm tổng số dòng
    $countSql = "SELECT COUNT(*) FROM Lecturers WHERE maGV LIKE ? OR hoTenGV LIKE ? OR email LIKE ?";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    $totalRows = (int) $countStmt->fetchColumn();

    // Lấy dữ liệu
    $sql = "SELECT * FROM Lecturers WHERE maGV LIKE ? OR hoTenGV LIKE ? OR email LIKE ? LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
} else {
    // Đếm tổng số dòng khi không tìm kiếm
    $totalRows = (int) $pdo->query("SELECT COUNT(*) FROM Lecturers")->fetchColumn();

    // Lấy dữ liệu
    $sql = "SELECT * FROM Lecturers LIMIT $limit OFFSET $offset";
    $stmt = $pdo->query($sql);
}

$Lecturers = $stmt->fetchAll();
$totalPages = ceil($totalRows / $limit);
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Quản lý Giảng viên</h1>
    <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal"
        data-target="#addModal">
        <i class="fas fa-plus fa-sm text-white-50"></i> Thêm Giảng viên Mới
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
    <div class="card-header py-3 row align-items-center">
        <div class="col-md-6 mt-2">
            <h6 class="font-weight-bold text-primary m-0">Danh sách các Giảng viên trong trường</h6>
        </div>
        <div class="col-md-6">
            <form method="GET" action="index.php" class="mb-0">
                <input type="hidden" name="page" value="lecturers">
                <div class="input-group w-100">
                    <input type="text" name="keyword" class="form-control" placeholder="Nhập mã, tên..."
                        value="<?= htmlspecialchars($keyword) ?>">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i> Tìm kiếm
                        </button>
                        <?php if ($keyword !== ''): ?>
                            <a href="index.php?page=lecturers" class="btn btn-danger">Hủy lọc</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        <th>Mã Giảng viên</th>
                        <th>Tên Giảng viên</th>
                        <th>Email</th>
                        <th>SĐT</th>
                        <th width="15%" class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($Lecturers)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">Không tìm thấy giảng viên nào.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($Lecturers as $f): ?>
                            <tr>
                                <td class="font-weight-bold text-primary"><?= htmlspecialchars($f['maGV']) ?></td>
                                <td class="font-weight-bold"><?= htmlspecialchars($f['hoTenGV']) ?></td>
                                <td><?= htmlspecialchars($f['email']) ?></td>
                                <td><?= htmlspecialchars($f['sdt']) ?></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-warning btn-sm shadow-sm"
                                        onclick='openEditModal(<?= json_encode($f, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>)'>
                                        <i class="fas fa-edit"></i> Sửa
                                    </button>

                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="maGV" value="<?= htmlspecialchars($f['maGV']) ?>">
                                        <button type="submit" class="btn btn-danger btn-sm shadow-sm"
                                            onclick="return confirm('Bạn chắc chắn muốn xóa Giảng viên này?')">
                                            <i class="fas fa-trash"></i> Xóa
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted text-sm font-weight-bold">
                Hiển thị <?= count($Lecturers) ?> / Tổng số <?= $totalRows ?> giảng viên (Trang
                <?= $p ?>/<?= $totalPages == 0 ? 1 : $totalPages ?>)
            </div>

            <?php if ($totalPages > 0): ?>
                <nav>
                    <ul class="pagination mb-0">
                        <li class="page-item <?= ($p <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link"
                                href="index.php?page=lecturers&keyword=<?= urlencode($keyword) ?>&p=<?= $p - 1 ?>">Trước</a>
                        </li>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= ($i == $p) ? 'active' : '' ?>">
                                <a class="page-link"
                                    href="index.php?page=lecturers&keyword=<?= urlencode($keyword) ?>&p=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?= ($p >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link"
                                href="index.php?page=lecturers&keyword=<?= urlencode($keyword) ?>&p=<?= $p + 1 ?>">Sau</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>

    </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form method="POST" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold">Thêm Giảng viên Mới</h5>
                <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body bg-light">
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label class="font-weight-bold">Mã Giảng viên</label>
                        <input type="text" class="form-control" name="maGV" required>
                    </div>
                    <div class="form-group col-md-8">
                        <label class="font-weight-bold">Tên Giảng viên</label>
                        <input type="text" class="form-control" name="hoTenGV" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="font-weight-bold">Email</label>
                        <input type="email" class="form-control" name="email">
                    </div>
                    <div class="form-group col-md-6">
                        <label class="font-weight-bold">Số điện thoại</label>
                        <input type="text" class="form-control" name="sdt">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Lưu dữ liệu</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form method="POST" class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title font-weight-bold">Cập nhật Thông tin Giảng viên</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body bg-light">
                <input type="hidden" name="action" value="edit">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label class="font-weight-bold">Mã Giảng viên</label>
                        <input type="text" class="form-control bg-white" name="maGV" id="edit_maGV" readonly required>
                    </div>
                    <div class="form-group col-md-8">
                        <label class="font-weight-bold">Tên Giảng viên</label>
                        <input type="text" class="form-control" name="hoTenGV" id="edit_hoTenGV" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="font-weight-bold">Email</label>
                        <input type="email" class="form-control" name="email" id="edit_email">
                    </div>
                    <div class="form-group col-md-6">
                        <label class="font-weight-bold">Số điện thoại</label>
                        <input type="text" class="form-control" name="sdt" id="edit_sdt">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-warning font-weight-bold text-dark"><i
                        class="fas fa-save mr-1"></i> Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

<script>
    window.openEditModal = function (data) {
        document.getElementById('edit_maGV').value = data.maGV;
        document.getElementById('edit_hoTenGV').value = data.hoTenGV;
        document.getElementById('edit_email').value = data.email;
        document.getElementById('edit_sdt').value = data.sdt;

        if (typeof $ !== 'undefined') {
            $('#editModal').modal('show');
        }
    }
</script>