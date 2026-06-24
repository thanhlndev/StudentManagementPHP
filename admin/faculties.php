<?php
/** @var PDO $pdo */

// ==========================================
// 1. XỬ LÝ LOGIC (THÊM / SỬA / XÓA)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO Faculties (maKhoa, tenKhoa, diaChi, email, sdt) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$_POST['maKhoa'], $_POST['tenKhoa'], $_POST['diaChi'], $_POST['email'], $_POST['sdt']]);
            $_SESSION['msg'] = "Thêm khoa thành công!";
            $_SESSION['msg_type'] = "success";
        } 
        elseif ($action === 'edit') {
            // Sửa thông tin khoa (Khóa chính maKhoa được dùng làm điều kiện WHERE)
            $stmt = $pdo->prepare("UPDATE Faculties SET tenKhoa=?, diaChi=?, email=?, sdt=? WHERE maKhoa=?");
            $stmt->execute([$_POST['tenKhoa'], $_POST['diaChi'], $_POST['email'], $_POST['sdt'], $_POST['maKhoa']]);
            $_SESSION['msg'] = "Cập nhật thông tin khoa thành công!";
            $_SESSION['msg_type'] = "success";
        }
        elseif ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM Faculties WHERE maKhoa = ?");
            $stmt->execute([$_POST['maKhoa']]);
            $_SESSION['msg'] = "Xóa khoa thành công!";
            $_SESSION['msg_type'] = "success";
        }
    } catch (PDOException $e) {
        $_SESSION['msg'] = "Lỗi CSDL: " . $e->getMessage();
        $_SESSION['msg_type'] = "danger";
    }
    
    header("Location: index.php?page=faculties");
    exit;
}

// ==========================================
// 2. TRUY VẤN DỮ LIỆU HIỂN THỊ
// ==========================================

// $stmt = $pdo->query("SELECT * FROM Faculties");
// $faculties = $stmt->fetchAll();

// $keyword = $_GET['keyword'] ?? '';

// if ($keyword !== '') {
//     $sql = "SELECT * FROM Faculties WHERE maKhoa LIKE ? OR tenKhoa LIKE ? OR email LIKE ?";
//     $stmt = $pdo->prepare($sql);
    
//     $searchTerm = "%$keyword%";
    
//     $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
// } else {
//     $stmt = $pdo->query("SELECT * FROM Faculties");
// }

// $faculties = $stmt->fetchAll();
// ==========================================
// 2. TRUY VẤN DỮ LIỆU HIỂN THỊ (TÌM KIẾM & PHÂN TRANG PHP)
// ==========================================
$keyword = $_GET['keyword'] ?? '';

// --- CẤU HÌNH PHÂN TRANG ---
$limit = 5; // Số dòng muốn hiển thị trên 1 trang (VD: 5 khoa/trang)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1; // Không cho phép trang nhỏ hơn 1
$offset = ($page - 1) * $limit; // Công thức tính điểm bắt đầu lấy dữ liệu

if ($keyword !== '') {
    $searchTerm = "%$keyword%";
    
    //Đếm tổng số dòng vẽ số trang
    $countSql = "SELECT COUNT(*) FROM Faculties WHERE maKhoa LIKE ? OR tenKhoa LIKE ? OR email LIKE ?";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    $totalRows = $countStmt->fetchColumn();

    // Lấy dữ liệu dùng  LIMIT và OFFSET
    $sql = "SELECT * FROM Faculties WHERE maKhoa LIKE ? OR tenKhoa LIKE ? OR email LIKE ? LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);

} else {
    $totalRows = $pdo->query("SELECT COUNT(*) FROM Faculties")->fetchColumn();
    
    $sql = "SELECT * FROM Faculties LIMIT $limit OFFSET $offset";
    $stmt = $pdo->query($sql);
}

$faculties = $stmt->fetchAll();

$totalPages = ceil($totalRows / $limit);
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Quản lý Khoa</h1>
    <button href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#addModal">
        <i class="fas fa-plus fa-sm text-white-50"></i> Thêm Khoa Mới
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
            <h6 class="font-weight-bold text-primary">Danh sách các khoa trong trường</h6>
        </div>
        <div class="col-4">
        </div>
        <div class="col-4">
            <form method="GET" action="index.php">
                <input type="hidden" name="page" value="faculties">
                
                <div class="input-group w-100">
                    <input type="text" name="keyword" class="form-control " placeholder="Nhập mã, tên khoa hoặc email để tìm kiếm..." value="<?= htmlspecialchars($keyword) ?>">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i> Tìm kiếm
                        </button>
                        <?php if ($keyword !== ''): ?>
                            <a href="index.php?page=faculties" class="btn btn-hite">Reset</a>
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
                        <th>Mã Khoa</th>
                        <th>Tên Khoa</th>
                        <th>Email</th>
                        <th>SĐT</th>
                        <th>Địa chỉ</th>
                        <th width="15%" class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($faculties as $f): ?>
                    <tr>
                        <td class="font-weight-bold text-primary"><?= htmlspecialchars($f['maKhoa']) ?></td>
                        <td><?= htmlspecialchars($f['tenKhoa']) ?></td>
                        <td><?= htmlspecialchars($f['email']) ?></td>
                        <td><?= htmlspecialchars($f['sdt']) ?></td>
                        <td><?= htmlspecialchars($f['diaChi']) ?></td>
                        <td class="text-center text-nowrap">
                            <a href="index.php?page=classes&filter_khoa=<?= urlencode($f['maKhoa']) ?>" class="btn btn-info btn-sm shadow-sm mr-1">
                                <i class="fas fa-users"></i> Xem Lớp
                            </a>
                            
                            <button type="button" class="btn btn-warning btn-sm shadow-sm mr-1" 
                                    onclick='openEditModal(<?= json_encode($f, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>)'>
                                <i class="fas fa-edit"></i> Sửa
                            </button>
                            
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="maKhoa" value="<?= htmlspecialchars($f['maKhoa']) ?>">
                                <button type="submit" class="btn btn-danger btn-sm shadow-sm" onclick="return confirm('Bạn chắc chắn muốn xóa khoa này?')">
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
                <h5 class="modal-title">Thêm Khoa Mới</h5>
                <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label class="font-weight-bold">Mã Khoa</label>
                        <input type="text" class="form-control" name="maKhoa" required>
                    </div>
                    <div class="form-group col-md-8">
                        <label class="font-weight-bold">Tên Khoa</label>
                        <input type="text" class="form-control" name="tenKhoa" required>
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
                <div class="form-group">
                    <label class="font-weight-bold">Địa chỉ</label>
                    <input type="text" class="form-control" name="diaChi">
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
                <h5 class="modal-title font-weight-bold">Cập nhật Thông tin Khoa</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="edit">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label class="font-weight-bold">Mã Khoa</label>
                        <input type="text" class="form-control bg-light" name="maKhoa" id="edit_maKhoa" readonly required>
                    </div>
                    <div class="form-group col-md-8">
                        <label class="font-weight-bold">Tên Khoa</label>
                        <input type="text" class="form-control" name="tenKhoa" id="edit_tenKhoa" required>
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
                <div class="form-group">
                    <label class="font-weight-bold">Địa chỉ</label>
                    <input type="text" class="form-control" name="diaChi" id="edit_diaChi">
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
    document.getElementById('edit_maKhoa').value = data.maKhoa;
    document.getElementById('edit_tenKhoa').value = data.tenKhoa;
    document.getElementById('edit_email').value = data.email;
    document.getElementById('edit_sdt').value = data.sdt;
    document.getElementById('edit_diaChi').value = data.diaChi;
    
    // Dùng jQuery (SB Admin 2) để gọi Modal hiện lên
    $('#editModal').modal('show');
}
</script>