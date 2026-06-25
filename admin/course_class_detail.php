<?php
/** @var PDO $pdo */

// Kiểm tra ID Lớp học phần được truyền vào
$maLHP = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($maLHP === 0) {
    $_SESSION['msg'] = "Không tìm thấy mã Lớp học phần hợp lệ.";
    $_SESSION['msg_type'] = "danger";
    header("Location: index.php?page=course_classes");
    exit;
}

// Lấy thông tin cơ bản của Lớp học phần để dùng chung
$stmtInfo = $pdo->prepare("
    SELECT cc.*, c.tenMH, s.tenHK, t.hoTenGV, t.maGV 
    FROM courseclasses cc
    JOIN courses c ON cc.maMH = c.maMH
    JOIN semesters s ON cc.maHK = s.maHK
    JOIN teachers t ON cc.maGV = t.maGV
    WHERE cc.maLHP = ?
");
$stmtInfo->execute([$maLHP]);
$classInfo = $stmtInfo->fetch();

if (!$classInfo) {
    die("Dữ liệu Lớp học phần không tồn tại!");
}

// ==========================================
// 1. XỬ LÝ LOGIC THÊM/XÓA LỊCH HỌC (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    try {
        if ($action === 'add_schedule') {
            $room_id = (int) $_POST['room_id'];
            $day_of_week = (int) $_POST['day_of_week'];
            $start_period = (int) $_POST['start_period'];
            $num_periods = (int) $_POST['num_periods'];
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];

            // --- BƯỚC 1: KIỂM TRA TRÙNG PHÒNG HỌC ---
            $checkRoomSql = "
                SELECT cc.maLHP, c.tenMH 
                FROM class_schedules cs
                JOIN courseclasses cc ON cs.maLHP = cc.maLHP
                JOIN courses c ON cc.maMH = c.maMH
                WHERE cs.room_id = ? AND cs.day_of_week = ?
                  AND cs.start_period < (? + ?) 
                  AND ? < (cs.start_period + cs.num_periods)
                LIMIT 1
            ";
            $stmtRoom = $pdo->prepare($checkRoomSql);
            $stmtRoom->execute([$room_id, $day_of_week, $start_period, $num_periods, $start_period]);
            $roomConflict = $stmtRoom->fetch();

            // --- BƯỚC 2: KIỂM TRA TRÙNG LỊCH GIẢNG VIÊN ---
            $checkTeacherSql = "
                SELECT cc.maLHP, c.tenMH 
                FROM class_schedules cs
                JOIN courseclasses cc ON cs.maLHP = cc.maLHP
                JOIN courses c ON cc.maMH = c.maMH
                WHERE cc.maGV = ? AND cs.day_of_week = ?
                  AND cs.start_period < (? + ?) 
                  AND ? < (cs.start_period + cs.num_periods)
                LIMIT 1
            ";
            $stmtTeacher = $pdo->prepare($checkTeacherSql);
            $stmtTeacher->execute([$classInfo['maGV'], $day_of_week, $start_period, $num_periods, $start_period]);
            $teacherConflict = $stmtTeacher->fetch();

            // --- XỬ LÝ KẾT QUẢ ---
            if ($roomConflict) {
                $_SESSION['msg'] = "Lỗi: Phòng này đã được xếp cho lớp [{$roomConflict['tenMH']} - #{$roomConflict['maLHP']}] vào thời gian này!";
                $_SESSION['msg_type'] = "danger";
            } elseif ($teacherConflict) {
                $_SESSION['msg'] = "Lỗi: Giảng viên {$classInfo['hoTenGV']} đang bị kẹt lịch dạy lớp [{$teacherConflict['tenMH']} - #{$teacherConflict['maLHP']}] vào thời gian này!";
                $_SESSION['msg_type'] = "warning";
            } else {
                // An toàn tuyệt đối -> Chèn dữ liệu
                $insertSql = "INSERT INTO class_schedules (maLHP, room_id, day_of_week, start_period, num_periods, start_date, end_date) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)";
                $pdo->prepare($insertSql)->execute([$maLHP, $room_id, $day_of_week, $start_period, $num_periods, $start_date, $end_date]);
                $_SESSION['msg'] = "Đã xếp lịch thành công!";
                $_SESSION['msg_type'] = "success";
            }
        } elseif ($action === 'delete_schedule') {
            $schedule_id = (int) $_POST['schedule_id'];
            $pdo->prepare("DELETE FROM class_schedules WHERE schedule_id = ?")->execute([$schedule_id]);
            $_SESSION['msg'] = "Đã xóa buổi học!";
            $_SESSION['msg_type'] = "success";
        }
    } catch (PDOException $e) {
        $_SESSION['msg'] = "Lỗi hệ thống: " . $e->getMessage();
        $_SESSION['msg_type'] = "danger";
    }

    header("Location: index.php?page=course_class_detail&id=" . $maLHP);
    exit;
}

// ==========================================
// 2. TRUY VẤN DỮ LIỆU HIỂN THỊ (GET)
// ==========================================

// Đếm số lượng sinh viên hiện tại
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM grades WHERE maLHP = ?");
$stmtCount->execute([$maLHP]);
$soLuongSV = $stmtCount->fetchColumn();

// Lấy danh sách lịch học của lớp này
$stmtSchedules = $pdo->prepare("
    SELECT cs.*, r.room_name, r.capacity 
    FROM class_schedules cs
    JOIN rooms r ON cs.room_id = r.room_id
    WHERE cs.maLHP = ?
    ORDER BY cs.day_of_week ASC, cs.start_period ASC
");
$stmtSchedules->execute([$maLHP]);
$schedules = $stmtSchedules->fetchAll();

// Lấy danh sách phòng học để đổ vào Form thêm lịch
$roomsList = $pdo->query("SELECT room_id, room_name, capacity, room_type FROM rooms ORDER BY room_name ASC")->fetchAll();
?>



<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Chi tiết & Xếp lịch học</h1>
    <a href="index.php?page=course_classes" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Quay lại danh sách
    </a>
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

<div class="row">
    <div class="col-lg-4">
        <div class="card shadow mb-4 border-left-primary">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Thông tin chung #
                    <?= $classInfo['maLHP'] ?>
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item px-0">
                        <small class="text-muted d-block">Môn học</small>
                        <strong class="text-dark" style="font-size: 1.1rem;">
                            <?= htmlspecialchars($classInfo['tenMH']) ?>
                        </strong>
                    </li>
                    <li class="list-group-item px-0">
                        <small class="text-muted d-block">Học kỳ</small>
                        <span class="badge badge-success p-2">
                            <?= htmlspecialchars($classInfo['tenHK']) ?>
                        </span>
                    </li>
                    <li class="list-group-item px-0">
                        <small class="text-muted d-block">Giảng viên phụ trách</small>
                        <strong><i class="fas fa-chalkboard-teacher text-primary mr-1"></i>
                            <?= htmlspecialchars($classInfo['hoTenGV']) ?>
                        </strong>
                    </li>
                    <li class="list-group-item px-0">
                        <small class="text-muted d-block">Tình trạng ghi danh</small>
                        <div class="progress mt-2 mb-1" style="height: 20px;">
                            <?php $percent = ($soLuongSV / 50) * 100; ?>
                            <div class="progress-bar <?= $percent >= 100 ? 'bg-danger' : 'bg-info' ?>"
                                role="progressbar" style="width: <?= $percent ?>%;" aria-valuenow="<?= $soLuongSV ?>"
                                aria-valuemin="0" aria-valuemax="50">
                                <?= $soLuongSV ?>/50 SV
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-light">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-calendar-alt mr-1"></i> Ma trận Lịch học
                </h6>
                <button class="btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#addScheduleModal">
                    <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Thêm buổi học
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0 text-center">
                        <thead class="thead-light">
                            <tr>
                                <th width="15%">Thứ</th>
                                <th width="20%">Tiết học</th>
                                <th>Phòng học</th>
                                <th width="25%">Giai đoạn</th>
                                <th width="10%">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($schedules)): ?>
                                <tr>
                                    <td colspan="5" class="py-5 text-muted">
                                        <i class="fas fa-info-circle mr-1"></i> Lớp học phần này chưa được xếp lịch.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($schedules as $sch): ?>
                                    <tr>
                                        <td class="font-weight-bold text-danger">Thứ
                                            <?= $sch['day_of_week'] ?>
                                        </td>
                                        <td class="font-weight-bold text-dark">
                                            Tiết
                                            <?= $sch['start_period'] ?> -
                                            <?= $sch['start_period'] + $sch['num_periods'] - 1 ?>
                                            <small class="d-block text-muted">(
                                                <?= $sch['num_periods'] ?> tiết)
                                            </small>
                                        </td>
                                        <td>
                                            <span class="text-primary font-weight-bold">
                                                <?= htmlspecialchars($sch['room_name']) ?>
                                            </span>
                                            <br><small class="text-muted">Sức chứa:
                                                <?= $sch['capacity'] ?> SV
                                            </small>
                                            <?php if ($sch['capacity'] < $soLuongSV): ?>
                                                <br><span class="badge badge-danger mt-1"><i
                                                        class="fas fa-exclamation-triangle"></i> Quá tải</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-sm">
                                            <?= date('d/m/Y', strtotime($sch['start_date'])) ?> <br> đến <br>
                                            <?= date('d/m/Y', strtotime($sch['end_date'])) ?>
                                        </td>
                                        <td>
                                            <form method="POST" class="m-0"
                                                onsubmit="return confirm('Bạn có chắc chắn xóa buổi học này?');">
                                                <input type="hidden" name="action" value="delete_schedule">
                                                <input type="hidden" name="schedule_id" value="<?= $sch['schedule_id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger shadow-sm">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade text-left" id="addScheduleModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0 shadow-lg">
            <form method="POST" action="index.php?page=course_class_detail&id=<?= $maLHP ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold">Thêm buổi học mới</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body bg-light">
                    <input type="hidden" name="action" value="add_schedule">

                    <div class="form-group">
                        <label class="font-weight-bold text-dark">Phòng học <span class="text-danger">*</span></label>
                        <select name="room_id" class="form-control shadow-sm" required>
                            <option value="">-- Chọn phòng học --</option>
                            <?php foreach ($roomsList as $r): ?>
                                <option value="<?= $r['room_id'] ?>">
                                    <?= htmlspecialchars($r['room_name']) ?> (Sức chứa:
                                    <?= $r['capacity'] ?> -
                                    <?= htmlspecialchars($r['room_type']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold text-dark">Thứ <span class="text-danger">*</span></label>
                            <select name="day_of_week" class="form-control shadow-sm" required>
                                <?php for ($i = 2; $i <= 8; $i++): ?>
                                    <option value="<?= $i ?>">
                                        <?= $i == 8 ? 'Chủ nhật' : 'Thứ ' . $i ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label class="font-weight-bold text-dark">Tiết BĐ <span class="text-danger">*</span></label>
                            <input type="number" name="start_period" class="form-control shadow-sm" min="1" max="15"
                                value="1" required>
                        </div>
                        <div class="form-group col-md-3">
                            <label class="font-weight-bold text-dark">Số tiết <span class="text-danger">*</span></label>
                            <input type="number" name="num_periods" class="form-control shadow-sm" min="1" max="5"
                                value="3" required>
                        </div>
                    </div>

                    <div class="form-row mt-2">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold text-dark">Ngày bắt đầu</label>
                            <input type="date" name="start_date" class="form-control shadow-sm" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold text-dark">Ngày kết thúc</label>
                            <input type="date" name="end_date" class="form-control shadow-sm" required>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Lưu Lịch Học</button>
                </div>
            </form>
        </div>
    </div>
</div>