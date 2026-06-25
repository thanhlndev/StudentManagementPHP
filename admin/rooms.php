<?php
/** @var PDO $pdo */

// Cấp quyền Admin (Giả định bạn đã có cơ chế check role ở index.php)
// if ($_SESSION['user']['role'] !== 'Admin') { die("Access Denied"); }

// ==========================================
// 1. XỬ LÝ LOGIC CRUD (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    try {
        if ($action === 'add') {
            $room_name = trim($_POST['room_name']);
            $capacity = (int) $_POST['capacity'];
            $room_type = trim($_POST['room_type']);

            // Kiểm tra trùng tên phòng
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM rooms WHERE room_name = ?");
            $checkStmt->execute([$room_name]);
            if ($checkStmt->fetchColumn() > 0) {
                $_SESSION['msg'] = "Lỗi: Phòng học '$room_name' đã tồn tại!";
                $_SESSION['msg_type'] = "warning";
            } else {
                $stmt = $pdo->prepare("INSERT INTO rooms (room_name, capacity, room_type) VALUES (?, ?, ?)");
                $stmt->execute([$room_name, $capacity, $room_type]);
                $_SESSION['msg'] = "Thêm phòng học thành công!";
                $_SESSION['msg_type'] = "success";
            }
        } elseif ($action === 'edit') {
            $room_id = (int) $_POST['room_id'];
            $room_name = trim($_POST['room_name']);
            $capacity = (int) $_POST['capacity'];
            $room_type = trim($_POST['room_type']);

            // Kiểm tra trùng tên phòng (loại trừ chính nó)
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM rooms WHERE room_name = ? AND room_id != ?");
            $checkStmt->execute([$room_name, $room_id]);
            if ($checkStmt->fetchColumn() > 0) {
                $_SESSION['msg'] = "Lỗi: Tên phòng '$room_name' đã được sử dụng cho phòng khác!";
                $_SESSION['msg_type'] = "warning";
            } else {
                $stmt = $pdo->prepare("UPDATE rooms SET room_name = ?, capacity = ?, room_type = ? WHERE room_id = ?");
                $stmt->execute([$room_name, $capacity, $room_type, $room_id]);
                $_SESSION['msg'] = "Cập nhật phòng học thành công!";
                $_SESSION['msg_type'] = "success";
            }
        } elseif ($action === 'delete') {
            $room_id = (int) $_POST['room_id'];

            // Ràng buộc (Edge case): Tuyệt đối không xóa nếu phòng đang được lên lịch
            $checkSchedule = $pdo->prepare("SELECT COUNT(*) FROM class_schedules WHERE room_id = ?");
            $checkSchedule->execute([$room_id]);

            if ($checkSchedule->fetchColumn() > 0) {
                $_SESSION['msg'] = "Lỗi: Không thể xóa! Phòng học này đang được xếp lịch cho (các) Lớp học phần.";
                $_SESSION['msg_type'] = "danger";
            } else {
                $stmt = $pdo->prepare("DELETE FROM rooms WHERE room_id = ?");
                $stmt->execute([$room_id]);
                $_SESSION['msg'] = "Đã xóa phòng học khỏi hệ thống!";
                $_SESSION['msg_type'] = "success";
            }
        }

    } catch (PDOException $e) {
        $_SESSION['msg'] = "Lỗi cơ sở dữ liệu: " . $e->getMessage();
        $_SESSION['msg_type'] = "danger";
    }

    // Tránh lỗi nạp lại form (F5)
    header("Location: index.php?page=rooms");
    exit;
}

// ==========================================
// 2. TRUY VẤN DỮ LIỆU HIỂN THỊ (GET)
// ==========================================
$keyword = trim($_GET['keyword'] ?? '');

$sql = "SELECT * FROM rooms WHERE 1=1";
$params = [];

if ($keyword !== '') {
    $sql .= " AND (room_name LIKE :kw OR room_type LIKE :kw)";
    $params[':kw'] = "%$keyword%";
}

$sql .= " ORDER BY room_name ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rooms = $stmt->fetchAll();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Quản lý Phòng học</h1>
    <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal"
        data-target="#addRoomModal">
        <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Thêm phòng mới
    </button>
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
        <h6 class="m-0 font-weight-bold text-primary mb-2 mb-lg-0"><i class="fas fa-building mr-1"></i> Danh sách không
            gian học tập</h6>

        <form method="GET" action="index.php" class="form-inline mb-0">
            <input type="hidden" name="page" value="rooms">
            <div class="input-group input-group-sm shadow-sm">
                <input type="text" name="keyword" class="form-control" placeholder="Tên phòng, loại phòng..."
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
                        <th width="10%">ID</th>
                        <th class="text-left">Tên phòng</th>
                        <th>Loại phòng</th>
                        <th width="15%">Sức chứa (SV)</th>
                        <th width="20%">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rooms)): ?>
                        <tr>
                            <td colspan="5" class="py-5 text-muted">Không tìm thấy phòng học nào phù hợp.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rooms as $r): ?>
                            <tr>
                                <td class="align-middle">
                                    <?= $r['room_id'] ?>
                                </td>
                                <td class="text-left font-weight-bold text-dark align-middle">
                                    <?= htmlspecialchars($r['room_name']) ?>
                                </td>
                                <td class="align-middle">
                                    <span
                                        class="badge badge-<?= $r['room_type'] === 'Lý thuyết' ? 'info' : ($r['room_type'] === 'Hội trường' ? 'warning' : 'success') ?>">
                                        <?= htmlspecialchars($r['room_type']) ?>
                                    </span>
                                </td>
                                <td class="align-middle font-weight-bold">
                                    <?= $r['capacity'] ?>
                                </td>
                                <td class="align-middle">
                                    <div class="d-flex justify-content-center">
                                        <button type="button" class="btn btn-sm btn-primary shadow-sm mr-2" data-toggle="modal"
                                            data-target="#editRoomModal<?= $r['room_id'] ?>">
                                            <i class="fas fa-edit"></i> Sửa
                                        </button>

                                        <form method="POST" action="index.php?page=rooms"
                                            onsubmit="return confirm('Bạn có chắc chắn muốn xóa phòng <?= htmlspecialchars($r['room_name']) ?> không?');"
                                            class="mb-0">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="room_id" value="<?= $r['room_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger shadow-sm">
                                                <i class="fas fa-trash"></i> Xóa
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <div class="modal fade text-left" id="editRoomModal<?= $r['room_id'] ?>" tabindex="-1" role="dialog"
                                aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <form method="POST" action="index.php?page=rooms">
                                            <div class="modal-header bg-light">
                                                <h5 class="modal-title font-weight-bold text-primary">Sửa thông tin phòng</h5>
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="action" value="edit">
                                                <input type="hidden" name="room_id" value="<?= $r['room_id'] ?>">

                                                <div class="form-group">
                                                    <label class="font-weight-bold">Tên phòng học <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" name="room_name" class="form-control"
                                                        value="<?= htmlspecialchars($r['room_name']) ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Sức chứa (Số sinh viên) <span
                                                            class="text-danger">*</span></label>
                                                    <input type="number" name="capacity" class="form-control"
                                                        value="<?= $r['capacity'] ?>" min="1" max="500" required>
                                                </div>
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Loại phòng</label>
                                                    <select name="room_type" class="form-control">
                                                        <option value="Lý thuyết" <?= $r['room_type'] === 'Lý thuyết' ? 'selected' : '' ?>>Lý thuyết</option>
                                                        <option value="Thực hành máy tính"
                                                            <?= $r['room_type'] === 'Thực hành máy tính' ? 'selected' : '' ?>>Thực
                                                            hành máy tính</option>
                                                        <option value="Hội trường" <?= $r['room_type'] === 'Hội trường' ? 'selected' : '' ?>>Hội trường</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-dismiss="modal">Hủy</button>
                                                <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addRoomModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="index.php?page=rooms">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold">Thêm phòng học mới</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">

                    <div class="form-group">
                        <label class="font-weight-bold">Tên phòng học <span class="text-danger">*</span></label>
                        <input type="text" name="room_name" class="form-control" placeholder="VD: A1-101" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Sức chứa (Số sinh viên) <span
                                class="text-danger">*</span></label>
                        <input type="number" name="capacity" class="form-control" value="50" min="1" max="500" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Loại phòng</label>
                        <select name="room_type" class="form-control">
                            <option value="Lý thuyết">Lý thuyết</option>
                            <option value="Thực hành máy tính">Thực hành máy tính</option>
                            <option value="Hội trường">Hội trường</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Thêm phòng</button>
                </div>
            </form>
        </div>
    </div>
</div>