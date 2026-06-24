<?php
/** @var PDO $pdo */

// ==========================================
// 1. XỬ LÝ LOGIC (THÊM / SỬA / XÓA)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO Semesters (maHK, tenHK) VALUES (:id, :ten)");
             $stmt->execute([
                'id'    => $_POST['maHK'],
                'ten' => $_POST['tenHK']
            ]);
            $_SESSION['msg'] = "Thêm học kỳ thành công!";
            $_SESSION['msg_type'] = "success";
        } 
        elseif ($action === 'edit') {
            $sql = "UPDATE Semesters SET tenHK=:ten WHERE maHK=:id";
            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                'id'    => $_POST['maHK'],
                'ten' => $_POST['tenHK'],
            ]);
            $_SESSION['msg'] = "Cập nhật thông tin học kỳ thành công!";
            $_SESSION['msg_type'] = "success";
        }
        elseif ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM Semesters WHERE maHK = ?");
            $stmt->execute([$_POST['maHK']]);
            $_SESSION['msg'] = "Xóa học kỳ thành công!";
            $_SESSION['msg_type'] = "success";
        }
    } catch (PDOException $e) {
        $_SESSION['msg'] = "Lỗi CSDL: " . $e->getMessage();
        $_SESSION['msg_type'] = "danger";
    }
    
    header("Location: index.php?page=semesters");
    exit;
}

// ==========================================
// 2. TRUY VẤN DỮ LIỆU HIỂN THỊ (TÌM KIẾM & PHÂN TRANG PHP)
// ==========================================
$keyword = $_GET['keyword'] ?? '';

// --- CẤU HÌNH PHÂN TRANG ---
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset =(int) ($page - 1) * $limit;

if ($keyword !== '') {
    $searchTerm = "%$keyword%";
    
    //Đếm tổng số dòng vẽ số trang
    $countSql = "SELECT COUNT(*) FROM Semesters WHERE maHK LIKE ? OR tenHK LIKE ?";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    $totalRows = $countStmt->fetchColumn();

    // Lấy dữ liệu dùng  LIMIT và OFFSET
    $sql = "SELECT * FROM Semesters WHERE maHK LIKE ? OR tenHK LIKE ? LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);

} else {
    $totalRows = $pdo->query("SELECT COUNT(*) FROM Semesters")->fetchColumn();
    
    $sql = "SELECT * FROM Semesters LIMIT $limit OFFSET $offset";
    $stmt = $pdo->query($sql);
}

$Semesters = $stmt->fetchAll();

$totalPages = ceil($totalRows / $limit);
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Quản lý học kỳ</h1>
    <button href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#addModal">
        <i class="fas fa-plus fa-sm text-white-50"></i> Thêm học kỳ Mới
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
    <div class="card-header py-3 row">
        <div class="col-4 mt-2">
            <h6 class="font-weight-bold text-primary">Danh sách các học kỳ trong trường</h6>
        </div>
        <div class="col-4">
        </div>
        <div class="col-4">
            <form method="GET" action="index.php">
                <input type="hidden" name="page" value="Semesters">
                
                <div class="input-group w-100">
                    <input type="text" name="keyword" class="form-control " placeholder="Nhập mã, tên học kỳ hoặc email để tìm kiếm..." value="<?= htmlspecialchars($keyword) ?>">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i> Tìm kiếm
                        </button>
                        <?php if ($keyword !== ''): ?>
                            <a href="index.php?page=semesters" class="btn btn-hite">Reset</a>
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
                        <th>Mã học kỳ</th>
                        <th>Tên học kỳ</th>
                        <th width="15%" class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($Semesters as $f): ?>
                    <tr>
                        <td class="font-weight-bold text-primary"><?= htmlspecialchars($f['maHK']) ?></td>
                        <td><?= htmlspecialchars($f['tenHK']) ?></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-warning btn-sm" 
                                    onclick='openEditModal(<?= json_encode($f, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>)'>
                                <i class="fas fa-edit"></i> Sửa
                            </button>
                            
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="maHK" value="<?= htmlspecialchars($f['maHK']) ?>">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Bạn chắc chắn muốn xóa học kỳ này?')">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form method="POST" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Thêm học kỳ Mới</h5>
                <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label class="font-weight-bold">Mã học kỳ</label>
                        <input type="text" class="form-control" name="maHK" required>
                    </div>
                    <div class="form-group col-md-8">
                        <label class="font-weight-bold">Tên học kỳ</label>
                        <input type="text" class="form-control" name="tenHK" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-primary">Lưu dữ liệu</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form method="POST" class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title font-weight-bold">Cập nhật Thông tin học kỳ</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="edit">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label class="font-weight-bold">Mã học kỳ</label>
                        <input type="text" class="form-control bg-light" name="maHK" id="edit_maHK" readonly required>
                    </div>
                    <div class="form-group col-md-8">
                        <label class="font-weight-bold">Tên học kỳ</label>
                        <input type="text" class="form-control" name="tenHK" id="edit_tenHK" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-warning font-weight-bold">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(data) {
    // Đổ dữ liệu JSON vào các input tương ứng theo ID
    document.getElementById('edit_maHK').value = data.maHK;
    document.getElementById('edit_tenHK').value = data.tenHK;
    
    // Dùng jQuery (SB Admin 2) để gọi Modal hiện lên
    $('#editModal').modal('show');
}
</script>