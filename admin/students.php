<?php
/** @var PDO $pdo */

// ==========================================
// 1. XỬ LÝ LOGIC (THÊM / SỬA / XÓA)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add') {
            $sql = "INSERT INTO Students (maSV, hoTen, gioiTinh, namSinh, diaChi, email, sdt, maLop) 
                    VALUES (:id, :hoten, :gioitinh, :namsinh, :diachi, :email, :sdt, :malop)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'id'       => $_POST['maSV'],
                'hoten'    => $_POST['hoTen'],
                'gioitinh' => $_POST['gioiTinh'],
                'namsinh'  => (int)$_POST['namSinh'],
                'diachi'   => $_POST['diaChi'],
                'email'    => $_POST['email'],
                'sdt'      => $_POST['sdt'],
                'malop'    => $_POST['maLop']
            ]);
            $_SESSION['msg'] = "Thêm Sinh viên thành công!";
            $_SESSION['msg_type'] = "success";
        } 
        elseif ($action === 'edit') {
            $sql = "UPDATE Students 
                    SET hoTen=:hoten, gioiTinh=:gioitinh, namSinh=:namsinh, diaChi=:diachi, email=:email, sdt=:sdt, maLop=:malop 
                    WHERE maSV=:id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'id'       => $_POST['maSV'],
                'hoten'    => $_POST['hoTen'],
                'gioitinh' => $_POST['gioiTinh'],
                'namsinh'  => (int)$_POST['namSinh'],
                'diachi'   => $_POST['diaChi'],
                'email'    => $_POST['email'],
                'sdt'      => $_POST['sdt'],
                'malop'    => $_POST['maLop']
            ]);
            $_SESSION['msg'] = "Cập nhật thông tin Sinh viên thành công!";
            $_SESSION['msg_type'] = "success";
        }
        elseif ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM Students WHERE maSV = ?");
            $stmt->execute([$_POST['maSV']]);
            $_SESSION['msg'] = "Xóa Sinh viên thành công!";
            $_SESSION['msg_type'] = "success";
        }
    } catch (PDOException $e) {
        $_SESSION['msg'] = "Lỗi CSDL: " . $e->getMessage();
        $_SESSION['msg_type'] = "danger";
    }
    
    header("Location: index.php?page=students");
    exit;
}

// ==========================================
// 2. TRUY VẤN DỮ LIỆU HIỂN THỊ (CÓ JOIN & PHÂN TRANG)
// ==========================================
$keyword = $_GET['keyword'] ?? '';

// --- LẤY DỮ LIỆU LỚP HỌC ĐỂ ĐỔ VÀO THẺ <SELECT> ---
$classesList = $pdo->query("SELECT maLop, tenLop FROM Classes")->fetchAll();

// --- CẤU HÌNH PHÂN TRANG ---
$limit = 10; // Bảng sinh viên thường dài, nên để 10 dòng/trang
$p = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($p < 1) $p = 1;
$offset = (int)(($p - 1) * $limit);

// Lệnh cơ sở sử dụng LEFT JOIN để lấy Tên Lớp
$baseSelect = "SELECT s.*, c.tenLop 
               FROM Students s
               LEFT JOIN Classes c ON s.maLop = c.maLop";

if ($keyword !== '') {
    $searchTerm = "%$keyword%";
    
    // Đếm tổng số dòng
    $countSql = "SELECT COUNT(*) FROM Students s
                 LEFT JOIN Classes c ON s.maLop = c.maLop
                 WHERE s.maSV LIKE ? OR s.hoTen LIKE ? OR s.email LIKE ? OR c.tenLop LIKE ?";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $totalRows = $countStmt->fetchColumn();

    // Lấy dữ liệu phân trang
    $sql = $baseSelect . " WHERE s.maSV LIKE ? OR s.hoTen LIKE ? OR s.email LIKE ? OR c.tenLop LIKE ? LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);

} else {
    $totalRows = $pdo->query("SELECT COUNT(*) FROM Students")->fetchColumn();
    $sql = $baseSelect . " LIMIT $limit OFFSET $offset";
    $stmt = $pdo->query($sql);
}

$students = $stmt->fetchAll();
$totalPages = ceil($totalRows / $limit);
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Quản lý Sinh viên</h1>
    <button href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#addModal">
        <i class="fas fa-user-plus fa-sm text-white-50"></i> Thêm Sinh viên
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
        <div class="col-md-5 mt-2">
            <h6 class="font-weight-bold text-primary">Danh sách Sinh viên toàn trường</h6>
        </div>
        <div class="col-md-7">
            <form method="GET" action="index.php" class="mb-0">
                <input type="hidden" name="page" value="students">
                <div class="input-group w-100">
                    <input type="text" name="keyword" class="form-control" placeholder="Tìm theo Mã SV, Tên, Email hoặc Tên Lớp..." value="<?= htmlspecialchars($keyword) ?>">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i> Tìm
                        </button>
                        <?php if ($keyword !== ''): ?>
                            <a href="index.php?page=students" class="btn btn-danger">Hủy</a>
                        <?php endif; ?>
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
                        <th>Mã SV</th>
                        <th>Họ và Tên</th>
                        <th>Giới tính</th>
                        <th>Năm sinh</th>
                        <th>Lớp</th>
                        <th>Email</th>
                        <th width="15%" class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($students)): ?>
                        <tr><td colspan="7" class="text-center py-3">Không có dữ liệu</td></tr>
                    <?php else: ?>
                        <?php foreach ($students as $s): ?>
                        <tr>
                            <td class="font-weight-bold text-primary"><?= htmlspecialchars($s['maSV']) ?></td>
                            <td class="font-weight-bold"><?= htmlspecialchars($s['hoTen']) ?></td>
                            <td><?= htmlspecialchars($s['gioiTinh']) ?></td>
                            <td><?= htmlspecialchars($s['namSinh']) ?></td>
                            <td><span class="badge badge-info p-2"><?= htmlspecialchars($s['tenLop'] ?? 'Chưa phân lớp') ?></span></td>
                            <td><?= htmlspecialchars($s['email']) ?></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-warning btn-sm" 
                                        onclick='openEditModal(<?= json_encode($s, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>)'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="maSV" value="<?= htmlspecialchars($s['maSV']) ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Bạn chắc chắn muốn xóa Sinh viên này?')">
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
                    <a class="page-link" href="?page=students&keyword=<?= urlencode($keyword) ?>&p=<?= $p - 1 ?>">Trước</a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i == $p) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=students&keyword=<?= urlencode($keyword) ?>&p=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($p >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=students&keyword=<?= urlencode($keyword) ?>&p=<?= $p + 1 ?>">Sau</a>
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
                <h5 class="modal-title font-weight-bold">Thêm Sinh viên Mới</h5>
                <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="add">
                
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label class="font-weight-bold">Mã Sinh viên</label>
                        <input type="text" class="form-control" name="maSV" required>
                    </div>
                    <div class="form-group col-md-8">
                        <label class="font-weight-bold">Họ và Tên</label>
                        <input type="text" class="form-control" name="hoTen" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label class="font-weight-bold">Giới tính</label>
                        <select class="form-control" name="gioiTinh">
                            <option value="Nam">Nam</option>
                            <option value="Nữ">Nữ</option>
                            <option value="Khác">Khác</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label class="font-weight-bold">Năm sinh</label>
                        <input type="number" class="form-control" name="namSinh" min="1990" max="<?= date('Y') ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label class="font-weight-bold">Trực thuộc Lớp</label>
                        <select class="form-control" name="maLop" required>
                            <option value="">-- Chọn Lớp --</option>
                            <?php foreach ($classesList as $c): ?>
                                <option value="<?= htmlspecialchars($c['maLop']) ?>"><?= htmlspecialchars($c['tenLop']) ?></option>
                            <?php endforeach; ?>
                        </select>
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
                <button type="submit" class="btn btn-primary">Lưu Sinh viên</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form method="POST" class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title font-weight-bold">Cập nhật Thông tin Sinh viên</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="edit">
                
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label class="font-weight-bold">Mã Sinh viên</label>
                        <input type="text" class="form-control bg-light" name="maSV" id="edit_maSV" readonly required>
                    </div>
                    <div class="form-group col-md-8">
                        <label class="font-weight-bold">Họ và Tên</label>
                        <input type="text" class="form-control" name="hoTen" id="edit_hoTen" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label class="font-weight-bold">Giới tính</label>
                        <select class="form-control" name="gioiTinh" id="edit_gioiTinh">
                            <option value="Nam">Nam</option>
                            <option value="Nữ">Nữ</option>
                            <option value="Khác">Khác</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label class="font-weight-bold">Năm sinh</label>
                        <input type="number" class="form-control" name="namSinh" id="edit_namSinh" min="1990" max="<?= date('Y') ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label class="font-weight-bold">Trực thuộc Lớp</label>
                        <select class="form-control" name="maLop" id="edit_maLop" required>
                            <?php foreach ($classesList as $c): ?>
                                <option value="<?= htmlspecialchars($c['maLop']) ?>"><?= htmlspecialchars($c['tenLop']) ?></option>
                            <?php endforeach; ?>
                        </select>
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
    document.getElementById('edit_maSV').value = data.maSV;
    document.getElementById('edit_hoTen').value = data.hoTen;
    document.getElementById('edit_namSinh').value = data.namSinh;
    document.getElementById('edit_email').value = data.email;
    document.getElementById('edit_sdt').value = data.sdt;
    document.getElementById('edit_diaChi').value = data.diaChi;
    
    // Gán selected cho thẻ select
    document.getElementById('edit_gioiTinh').value = data.gioiTinh;
    document.getElementById('edit_maLop').value = data.maLop;
    
    $('#editModal').modal('show');
}
</script>