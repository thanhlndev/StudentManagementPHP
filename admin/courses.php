<?php
/** @var PDO $pdo */

// ==========================================
// 1. XỬ LÝ LOGIC (THÊM / SỬA / XÓA)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO Courses (maMH, tenMH, soTinChi) VALUES (:id, :tenmh, :soTinChi)");
             $stmt->execute([
                'id'    => $_POST['maMH'],
                'tenmh' => $_POST['tenMH'],
                'soTinChi'   => $_POST['soTinChi']
            ]);
            $_SESSION['msg'] = "Thêm môn học thành công!";
            $_SESSION['msg_type'] = "success";
        } 
        elseif ($action === 'edit') {
            $sql = "UPDATE Courses SET tenMH=:tenmh, soTinChi=:soTinChi WHERE maMH=:id";
            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                'id'    => $_POST['maMH'],
                'tenmh' => $_POST['tenMH'],
                'soTinChi'   => $_POST['soTinChi']
            ]);
            $_SESSION['msg'] = "Cập nhật thông tin môn học thành công!";
            $_SESSION['msg_type'] = "success";
        }
        elseif ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM Courses WHERE maMH = ?");
            $stmt->execute([$_POST['maMH']]);
            $_SESSION['msg'] = "Xóa môn học thành công!";
            $_SESSION['msg_type'] = "success";
        }
    } catch (PDOException $e) {
        $_SESSION['msg'] = "Lỗi CSDL: " . $e->getMessage();
        $_SESSION['msg_type'] = "danger";
    }
    
    header("Location: index.php?page=courses");
    exit;
}

// ==========================================
// 2. TRUY VẤN DỮ LIỆU HIỂN THỊ
// ==========================================

// $stmt = $pdo->query("SELECT * FROM Courses");
// $Courses = $stmt->fetchAll();

// $keyword = $_GET['keyword'] ?? '';

// if ($keyword !== '') {
//     $sql = "SELECT * FROM Courses WHERE maMH LIKE ? OR tenMH LIKE ? OR  LIKE ?";
//     $stmt = $pdo->prepare($sql);
    
//     $searchTerm = "%$keyword%";
    
//     $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
// } else {
//     $stmt = $pdo->query("SELECT * FROM Courses");
// }

// $Courses = $stmt->fetchAll();
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
    $countSql = "SELECT COUNT(*) FROM Courses WHERE maMH LIKE ? OR tenMH LIKE ? OR soTinChi LIKE ?";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    $totalRows = $countStmt->fetchColumn();

    // Lấy dữ liệu dùng  LIMIT và OFFSET
    $sql = "SELECT * FROM Courses WHERE maMH LIKE ? OR tenMH LIKE ? OR soTinChi LIKE ? LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);

} else {
    $totalRows = $pdo->query("SELECT COUNT(*) FROM Courses")->fetchColumn();
    
    $sql = "SELECT * FROM Courses LIMIT $limit OFFSET $offset";
    $stmt = $pdo->query($sql);
}

$Courses = $stmt->fetchAll();

$totalPages = ceil($totalRows / $limit);
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Quản lý môn học</h1>
    <button href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#addModal">
        <i class="fas fa-plus fa-sm text-white-50"></i> Thêm môn học Mới
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
            <h6 class="font-weight-bold text-primary">Danh sách các môn học trong trường</h6>
        </div>
        <div class="col-4">
        </div>
        <div class="col-4">
            <form method="GET" action="index.php">
                <input type="hidden" name="page" value="courses">
                
                <div class="input-group w-100">
                    <input type="text" name="keyword" class="form-control " placeholder="Nhập mã, tên môn học hoặc số tín chỉ để tìm kiếm..." value="<?= htmlspecialchars($keyword) ?>">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i> Tìm kiếm
                        </button>
                        <?php if ($keyword !== ''): ?>
                            <a href="index.php?page=courses" class="btn btn-hite">Reset</a>
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
                        <th>Mã môn học</th>
                        <th>Tên môn học</th>
                        <th>Số tín chỉ</th>
                        <th width="15%" class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($Courses as $f): ?>
                    <tr>
                        <td class="font-weight-bold text-primary"><?= htmlspecialchars($f['maMH']) ?></td>
                        <td><?= htmlspecialchars($f['tenMH']) ?></td>
                        <td><?= htmlspecialchars($f['soTinChi']) ?></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-warning btn-sm" 
                                    onclick='openEditModal(<?= json_encode($f, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>)'>
                                <i class="fas fa-edit"></i> Sửa
                            </button>
                            
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="maMH" value="<?= htmlspecialchars($f['maMH']) ?>">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Bạn chắc chắn muốn xóa môn học này?')">
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
                <h5 class="modal-title">Thêm môn học Mới</h5>
                <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label class="font-weight-bold">Mã môn học</label>
                        <input type="text" class="form-control" name="maMH" required>
                    </div>
                    <div class="form-group col-md-8">
                        <label class="font-weight-bold">Tên môn học</label>
                        <input type="text" class="form-control" name="tenMH" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="font-weight-bold">Số tín chỉ</label>
                        <input type="text" class="form-control" name="soTinChi">
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
                <h5 class="modal-title font-weight-bold">Cập nhật Thông tin môn học</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="edit">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label class="font-weight-bold">Mã môn học</label>
                        <input type="text" class="form-control bg-light" name="maMH" id="edit_maMH" readonly required>
                    </div>
                    <div class="form-group col-md-8">
                        <label class="font-weight-bold">Tên môn học</label>
                        <input type="text" class="form-control" name="tenMH" id="edit_tenMH" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="font-weight-bold">Số tín chỉ</label>
                        <input type="text" class="form-control" name="soTinChi" id="edit_soTinChi">
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
    document.getElementById('edit_maMH').value = data.maMH;
    document.getElementById('edit_tenMH').value = data.tenMH;
    document.getElementById('edit_soTinChi').value = data.soTinChi;
    
    // Dùng jQuery (SB Admin 2) để gọi Modal hiện lên
    $('#editModal').modal('show');
}
</script>