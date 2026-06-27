<?php
/** @var PDO $pdo */
// ==========================================
// 0. XỬ LÝ LOGIC POST (SỬA / XÓA LHP / THÊM LỊCH)
// ==========================================
$message = '';
$messageType = ''; // 'success' hoặc 'danger'

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    try {
        if ($action === 'edit') {
            // Nhận và kiểm tra dữ liệu
            $maLHP_edit = $_POST['maLHP'] ?? '';
            $maMH_edit = $_POST['maMH'] ?? '';
            $maHK_edit = $_POST['maHK'] ?? '';
            $maGV_edit = $_POST['maGV'] ?? '';

            if (empty($maLHP_edit) || empty($maMH_edit) || empty($maHK_edit) || empty($maGV_edit)) {
                throw new Exception("Vui lòng điền đầy đủ thông tin để cập nhật.");
            }

            // Thực thi truy vấn an toàn bằng PDO Prepared Statement
            $sql_update = "UPDATE CourseClasses 
                           SET maMH = :maMH, maHK = :maHK, maGV = :maGV 
                           WHERE maLHP = :maLHP";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute([
                ':maMH' => $maMH_edit,
                ':maHK' => $maHK_edit,
                ':maGV' => $maGV_edit,
                ':maLHP' => $maLHP_edit
            ]);

            $message = "Cập nhật thành công Lớp học phần #$maLHP_edit.";
            $messageType = "success";

        } elseif ($action === 'delete') {
            $maLHP_del = $_POST['maLHP'] ?? '';
            if (empty($maLHP_del)) {
                throw new Exception("Mã Lớp học phần không hợp lệ.");
            }

            // Thực thi xóa
            $sql_delete = "DELETE FROM CourseClasses WHERE maLHP = :maLHP";
            $stmt_delete = $pdo->prepare($sql_delete);
            $stmt_delete->execute([':maLHP' => $maLHP_del]);

            $message = "Đã xóa Lớp học phần #$maLHP_del thành công.";
            $messageType = "success";

        } elseif ($action === 'add_schedule') {
            // Xử lý thêm lịch học mới
            $maLHP_sch = $_POST['maLHP'] ?? '';
            $room_id = $_POST['room_id'] ?? '';
            $day_of_week = $_POST['day_of_week'] ?? '';
            $start_period = $_POST['start_period'] ?? '';
            $num_periods = $_POST['num_periods'] ?? '';
            $start_date = $_POST['start_date'] ?? '';
            $end_date = $_POST['end_date'] ?? '';

            if (empty($maLHP_sch) || empty($room_id) || empty($day_of_week) || empty($start_period) || empty($num_periods) || empty($start_date) || empty($end_date)) {
                throw new Exception("Vui lòng điền đầy đủ thông tin lịch học.");
            }

            if (strtotime($start_date) > strtotime($end_date)) {
                throw new Exception("Ngày bắt đầu không thể lớn hơn ngày kết thúc.");
            }

            $sqlCheck = "SELECT schedule_id FROM class_schedules 
                         WHERE room_id = :room_id 
                         AND day_of_week = :day_of_week
                         AND (start_date <= :end_date AND end_date >= :start_date)
                         AND (start_period < (:new_start_1 + :new_num) AND :new_start_2 < (start_period + num_periods))
                         LIMIT 1";
            $stmtCheck = $pdo->prepare($sqlCheck);

            // Truyền đủ số lượng tham số tương ứng
            $stmtCheck->execute([
                ':room_id' => $room_id,
                ':day_of_week' => $day_of_week,
                ':start_date' => $start_date,
                ':end_date' => $end_date,
                ':new_start_1' => $start_period,
                ':new_start_2' => $start_period,
                ':new_num' => $num_periods
            ]);

            if ($stmtCheck->rowCount() > 0) {
                throw new Exception("Xung đột! Phòng học này đã được sử dụng vào thời gian này. Vui lòng chọn phòng hoặc thời gian khác.");
            }

            // Lưu dữ liệu nếu an toàn
            $sqlInsert = "INSERT INTO class_schedules (day_of_week, start_period, num_periods, start_date, end_date, maLHP, room_id) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmtInsert = $pdo->prepare($sqlInsert);
            $stmtInsert->execute([$day_of_week, $start_period, $num_periods, $start_date, $end_date, $maLHP_sch, $room_id]);

            $message = "Thêm lịch học cho Lớp học phần #$maLHP_sch thành công!";
            $messageType = "success";
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $message = "Thao tác thất bại: Lớp học phần này đang chứa dữ liệu liên kết. Vui lòng xóa dữ liệu liên kết trước.";
        } else {
            $message = "Lỗi hệ thống CSDL: " . $e->getMessage();
        }
        $messageType = "danger";
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = "danger";
    }
}

// ==========================================
// 1. TRUY VẤN DỮ LIỆU ĐỔ VÀO SELECT
// ==========================================
$coursesList = $pdo->query("SELECT maMH, tenMH FROM Courses")->fetchAll();
$semestersList = $pdo->query("SELECT maHK, tenHK FROM Semesters")->fetchAll();
$LecturersList = $pdo->query("SELECT maGV, hoTenGV FROM lecturers")->fetchAll();
$RoomsList = $pdo->query("SELECT room_id, room_name, capacity FROM rooms ORDER BY room_name ASC")->fetchAll();

// ==========================================
// 2. CẤU HÌNH PHÂN TRANG VÀ LẤY DỮ LIỆU
// ==========================================
$keyword = $_GET['keyword'] ?? '';
$filter_hk = $_GET['filter_hk'] ?? '';
$limit = 10;
$p = isset($_GET['p']) ? (int) $_GET['p'] : 1;
if ($p < 1)
    $p = 1;
$offset = (int) (($p - 1) * $limit);

// Xây dựng Subquery nối chuỗi Lịch học (GROUP_CONCAT)
$lichHocSubquery = "
    (SELECT GROUP_CONCAT(
        CONCAT(
            CASE WHEN cs.day_of_week = 1 THEN 'CN' ELSE CONCAT('Thứ ', cs.day_of_week) END,
            ' (T', cs.start_period, '-', cs.start_period + cs.num_periods - 1, ') - ',
            r.room_name
        ) SEPARATOR '<br>'
    )
    FROM class_schedules cs 
    JOIN rooms r ON cs.room_id = r.room_id 
    WHERE cs.maLHP = cc.maLHP) AS lichHoc
";

$baseSelect = "SELECT cc.maLHP, cc.maMH, cc.maHK, cc.maGV, 
                      c.tenMH, s.tenHK, t.hoTenGV,
                      $lichHocSubquery 
               FROM CourseClasses cc
               LEFT JOIN Courses c ON cc.maMH = c.maMH
               LEFT JOIN Semesters s ON cc.maHK = s.maHK
               LEFT JOIN lecturers t ON cc.maGV = t.maGV";

$whereClauses = [];
$params = [];

if ($keyword !== '') {
    $searchTerm = "%$keyword%";
    $whereClauses[] = "(cc.maLHP LIKE ? OR c.tenMH LIKE ? OR s.tenHK LIKE ? OR t.hoTenGV LIKE ?)";
    array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
}

if ($filter_hk !== '') {
    $whereClauses[] = "cc.maHK = ?";
    array_push($params, $filter_hk);
}

$whereString = count($whereClauses) > 0 ? " WHERE " . implode(" AND ", $whereClauses) : "";

// BƯỚC A: Đếm tổng số dòng
$countSql = "SELECT COUNT(*) FROM CourseClasses cc
             LEFT JOIN Courses c ON cc.maMH = c.maMH
             LEFT JOIN Semesters s ON cc.maHK = s.maHK
             LEFT JOIN lecturers t ON cc.maGV = t.maGV" . $whereString;
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalRows = $countStmt->fetchColumn();

// BƯỚC B: Lấy dữ liệu
$sql = $baseSelect . $whereString . " ORDER BY cc.maLHP DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$courseClasses = $stmt->fetchAll();

$totalPages = ceil($totalRows / $limit);
?>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show shadow-sm" role="alert">
        <strong><?= $messageType === 'success' ? '<i class="fas fa-check-circle"></i> Thành công!' : '<i class="fas fa-exclamation-triangle"></i> Lỗi!' ?></strong>
        <?= htmlspecialchars($message) ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

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
                        <th>Giảng viên</th>
                        <th>Lịch học</th>
                        <th width="15%" class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($courseClasses)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-3">Không tìm thấy dữ liệu phù hợp</td>
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
                                <td>
                                    <?php if (!empty($cc['lichHoc'])): ?>
                                        <small><?= $cc['lichHoc'] ?></small>
                                    <?php else: ?>
                                        <span class="text-muted font-italic"><i class="fas fa-info-circle"></i> Chưa có lịch</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-info btn-sm shadow-sm mb-1" title="Thêm lịch mới"
                                        onclick="openScheduleModal('<?= $cc['maLHP'] ?>', '<?= htmlspecialchars(addslashes($cc['tenMH'])) ?>')">
                                        <i class="fas fa-calendar-plus"></i>
                                    </button>

                                    <button type="button" class="btn btn-warning btn-sm mb-1" title="Sửa LHP"
                                        onclick='openEditModal(<?= json_encode($cc, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="maLHP" value="<?= htmlspecialchars($cc['maLHP']) ?>">
                                        <button type="submit" class="btn btn-danger btn-sm mb-1" title="Xóa LHP"
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

<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" class="modal-content" action="">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold">Sửa Lớp Học Phần</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="edit">

                <div class="form-group">
                    <label class="font-weight-bold">Mã LHP</label>
                    <input type="text" id="edit_maLHP" name="maLHP" class="form-control" readonly>
                </div>

                <div class="form-group">
                    <label class="font-weight-bold">Môn học</label>
                    <select id="edit_maMH" name="maMH" class="form-control" required>
                        <?php foreach ($coursesList as $c): ?>
                            <option value="<?= htmlspecialchars($c['maMH']) ?>"><?= htmlspecialchars($c['tenMH']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="font-weight-bold">Học kỳ</label>
                    <select id="edit_maHK" name="maHK" class="form-control" required>
                        <?php foreach ($semestersList as $s): ?>
                            <option value="<?= htmlspecialchars($s['maHK']) ?>"><?= htmlspecialchars($s['tenHK']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="font-weight-bold">Giảng viên phụ trách</label>
                    <select id="edit_maGV" name="maGV" class="form-control" required>
                        <?php foreach ($LecturersList as $l): ?>
                            <option value="<?= htmlspecialchars($l['maGV']) ?>"><?= htmlspecialchars($l['hoTenGV']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="scheduleModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" class="modal-content" action="">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-calendar-alt"></i> Thêm Lịch Học</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="add_schedule">
                <input type="hidden" id="sch_maLHP" name="maLHP" value="">

                <div class="alert alert-secondary text-center font-weight-bold" id="sch_tenMH_display"></div>

                <div class="form-group">
                    <label class="font-weight-bold">Phòng học</label>
                    <select name="room_id" class="form-control" required>
                        <option value="">-- Chọn phòng --</option>
                        <?php foreach ($RoomsList as $r): ?>
                            <option value="<?= $r['room_id'] ?>"><?= htmlspecialchars($r['room_name']) ?> (Sức chứa:
                                <?= $r['capacity'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label class="font-weight-bold">Thứ</label>
                        <select name="day_of_week" class="form-control" required>
                            <option value="2">Thứ 2</option>
                            <option value="3">Thứ 3</option>
                            <option value="4">Thứ 4</option>
                            <option value="5">Thứ 5</option>
                            <option value="6">Thứ 6</option>
                            <option value="7">Thứ 7</option>
                            <option value="1">Chủ nhật</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label class="font-weight-bold">Tiết BĐ</label>
                        <input type="number" name="start_period" class="form-control" min="1" max="15" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label class="font-weight-bold">Số tiết</label>
                        <input type="number" name="num_periods" class="form-control" min="1" max="5" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="font-weight-bold">Từ ngày (Bắt đầu tuần)</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label class="font-weight-bold">Đến ngày (Kết thúc tuần)</label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-info"><i class="fas fa-save"></i> Lưu Lịch Học</button>
            </div>
        </form>
    </div>
</div>

<script>
    window.openEditModal = function (data) {
        const fieldMapping = {
            'edit_maLHP': data.maLHP,
            'edit_maMH': data.maMH,
            'edit_maHK': data.maHK,
            'edit_maGV': data.maGV
        };

        for (const [id, value] of Object.entries(fieldMapping)) {
            const element = document.getElementById(id);
            if (element) element.value = value;
        }

        if (typeof $ !== 'undefined') {
            $('#editModal').modal('show');
        }
    };

    window.openScheduleModal = function (maLHP, tenMH) {
        // Gán mã LHP vào input ẩn
        document.getElementById('sch_maLHP').value = maLHP;
        // Hiển thị tên MH để Admin biết đang xếp lịch cho môn nào
        document.getElementById('sch_tenMH_display').innerText = "Môn: " + tenMH + " (Mã LHP: #" + maLHP + ")";

        if (typeof $ !== 'undefined') {
            $('#scheduleModal').modal('show');
        }
    };
</script>