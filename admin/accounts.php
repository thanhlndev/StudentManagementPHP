<?php
/** @var PDO $pdo */

// ==========================================
// 1. XỬ LÝ LOGIC (THÊM / SỬA / XÓA)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $current_admin = $_SESSION['user']['username']; // Tài khoản Admin đang đăng nhập

    try {
        // --- CHỨC NĂNG THÊM TÀI KHOẢN ---
        if ($action === 'add') {
            $username = trim($_POST['username']);
            $password = $_POST['password'];
            $role = $_POST['role'];
            $isActive = isset($_POST['isActive']) ? 1 : 0;

            // Kiểm tra trùng lặp username trước khi tạo
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM Accounts WHERE username = ?");
            $checkStmt->execute([$username]);
            if ($checkStmt->fetchColumn() > 0) {
                throw new Exception("Tên đăng nhập này đã tồn tại trên hệ thống!");
            }

            // Băm mật khẩu bằng Bcrypt
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO Accounts (username, password, role, isActive) VALUES (:user, :pass, :role, :active)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'user'   => $username,
                'pass'   => $hashedPassword,
                'role'   => $role,
                'active' => $isActive
            ]);
            $_SESSION['msg'] = "Tạo tài khoản thành công!";
            $_SESSION['msg_type'] = "success";
        }
        // --- CHỨC NĂNG CẬP NHẬT TÀI KHOẢN ---
        elseif ($action === 'edit') {
            $username = trim($_POST['username']);
            $role = $_POST['role'];
            $isActive = isset($_POST['isActive']) ? 1 : 0;
            $new_password = $_POST['password'];

            // BẢO VỆ: Không cho phép Admin tự khóa chính mình
            if ($username === $current_admin && $isActive === 0) {
                throw new Exception("Bảo mật hệ thống: Bạn không thể tự khóa tài khoản của chính mình!");
            }
            // BẢO VỆ: Không cho phép Admin tự hạ quyền của chính mình
            if ($username === $current_admin && $role !== 'Admin') {
                throw new Exception("Bảo mật hệ thống: Bạn không thể tự thay đổi vai trò Admin của chính mình!");
            }

            if (!empty($new_password)) {
                // Nếu có nhập mật khẩu mới -> Cập nhật cả mật khẩu đã hash
                $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                $sql = "UPDATE Accounts SET password = :pass, role = :role, isActive = :active WHERE username = :user";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['pass' => $hashedPassword, 'role' => $role, 'active' => $isActive, 'user' => $username]);
            } else {
                // Nếu để trống mật khẩu -> Chỉ cập nhật quyền và trạng thái
                $sql = "UPDATE Accounts SET role = :role, isActive = :active WHERE username = :user";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['role' => $role, 'active' => $isActive, 'user' => $username]);
            }

            $_SESSION['msg'] = "Cập nhật tài khoản thành công!";
            $_SESSION['msg_type'] = "success";
        }
        // --- CHỨC NĂNG XÓA TÀI KHOẢN ---
        elseif ($action === 'delete') {
            $username = $_POST['username'];

            // BẢO VỆ: Không cho phép xóa chính mình
            if ($username === $current_admin) {
                throw new Exception("Bảo mật hệ thống: Bạn không thể xóa tài khoản đang đăng nhập!");
            }

            $stmt = $pdo->prepare("DELETE FROM Accounts WHERE username = ?");
            $stmt->execute([$username]);
            $_SESSION['msg'] = "Đã xóa tài khoản khỏi hệ thống!";
            $_SESSION['msg_type'] = "success";
        }
        // --- CHỨC NĂNG ĐỒNG BỘ TÀI KHOẢN CÒN THIẾU ---
        elseif ($action === 'sync') {
            $pdo->beginTransaction(); // Khởi tạo Transaction an toàn

            try {
                $count = 0;
                // Băm sẵn 1 mật khẩu mặc định (VD: '123456') để dùng chung nhằm tối ưu hiệu năng
                $defaultPassword = password_hash('123456', PASSWORD_DEFAULT);

                // 1. Quét Giảng viên chưa có tài khoản (Dùng NOT IN)
                $sqlTeacher = "SELECT maGV FROM Teachers WHERE maGV NOT IN (SELECT username FROM Accounts)";
                $missingTeachers = $pdo->query($sqlTeacher)->fetchAll(PDO::FETCH_COLUMN);
                
                if (!empty($missingTeachers)) {
                    $stmtTeacher = $pdo->prepare("INSERT INTO Accounts (username, password, role, isActive) VALUES (?, ?, 'Teacher', 1)");
                    foreach ($missingTeachers as $gv) {
                        $stmtTeacher->execute([$gv, $defaultPassword]);
                        $count++;
                    }
                }

                // 2. Quét Sinh viên chưa có tài khoản
                $sqlStudent = "SELECT maSV FROM Students WHERE maSV NOT IN (SELECT username FROM Accounts)";
                $missingStudents = $pdo->query($sqlStudent)->fetchAll(PDO::FETCH_COLUMN);
                
                if (!empty($missingStudents)) {
                    $stmtStudent = $pdo->prepare("INSERT INTO Accounts (username, password, role, isActive) VALUES (?, ?, 'Student', 1)");
                    foreach ($missingStudents as $sv) {
                        $stmtStudent->execute([$sv, $defaultPassword]);
                        $count++;
                    }
                }

                $pdo->commit();
                
                if ($count > 0) {
                    $_SESSION['msg'] = "Đã quét và tự động cấp phát tài khoản cho $count người dùng (Mật khẩu mặc định: 123456)!";
                    $_SESSION['msg_type'] = "success";
                } else {
                    $_SESSION['msg'] = "Hệ thống đã đồng bộ. Không phát hiện người dùng nào thiếu tài khoản!";
                    $_SESSION['msg_type'] = "info";
                }

            } catch (Exception $e) {
                $pdo->rollBack();
                $_SESSION['msg'] = "Lỗi đồng bộ: " . $e->getMessage();
                $_SESSION['msg_type'] = "danger";
            }
        }
        // --- CHỨC NĂNG ADMIN ÉP RESET MẬT KHẨU ---
        elseif ($action === 'reset_password') {
            $username = $_POST['username'];
            $role = $_POST['role']; // Truyền kèm role để biết đường tìm email
            
            // 1. Khởi tạo một mật khẩu ngẫu nhiên (8 ký tự gồm chữ và số)
            $newPassword = substr(str_shuffle('abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789!@#'), 0, 8);
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // 2. Tìm kiếm Email và Họ tên dựa vào Vai trò (Role)
            $email = '';
            $hoTen = '';

            if ($role === 'Student') {
                $stmtInfo = $pdo->prepare("SELECT email, hoTen FROM Students WHERE maSV = ?");
                $stmtInfo->execute([$username]);
                $info = $stmtInfo->fetch();
                if ($info) {
                    $email = $info['email'];
                    $hoTen = $info['hoTen'];
                }
            } elseif ($role === 'Teacher') {
                $stmtInfo = $pdo->prepare("SELECT email, hoTenGV AS hoTen FROM Teachers WHERE maGV = ?");
                $stmtInfo->execute([$username]);
                $info = $stmtInfo->fetch();
                if ($info) {
                    $email = $info['email'];
                    $hoTen = $info['hoTen'];
                }
            } elseif ($role === 'Admin') {
                throw new Exception("Bảo mật hệ thống: Không được phép reset mật khẩu của Admin qua chức năng này!");
            }

            // 3. Kiểm tra tính hợp lệ của Email
            if (empty($email)) {
                throw new Exception("Không thể reset: Hồ sơ của tài khoản [$username] chưa được cập nhật địa chỉ Email!");
            }

            // 4. Tiến hành gửi Email bằng hàm helper (Giả sử bạn đã tạo hàm sendSystemEmail)
            $subject = "Yêu cầu Cấp lại mật khẩu Hệ thống QLSV";
            $body = "<h3>Xin chào {$hoTen},</h3>
                     <p>Quản trị viên hệ thống đã tiến hành đặt lại mật khẩu cho tài khoản của bạn.</p>
                     <p><b>Thông tin truy cập mới:</b></p>
                     <ul>
                        <li><b>Tên đăng nhập:</b> {$username}</li>
                        <li><b>Mật khẩu mới:</b> <span style='color:red; font-size:18px; font-weight:bold;'>{$newPassword}</span></li>
                     </ul>
                     <p><i>Vui lòng đăng nhập và đổi lại mật khẩu cá nhân ngay lập tức để đảm bảo an toàn.</i></p>";

            // Gọi hàm gửi thư
            $isSent = sendSystemEmail($email, $hoTen, $subject, $body);

            if ($isSent) {
                // 5. CHỈ KHI GỬI EMAIL THÀNH CÔNG, mới thực sự cập nhật DB
                $stmtUpdate = $pdo->prepare("UPDATE Accounts SET password = ? WHERE username = ?");
                $stmtUpdate->execute([$hashedPassword, $username]);
                
                $_SESSION['msg'] = "Đã tạo mật khẩu mới và gửi thành công tới email: $email";
                $_SESSION['msg_type'] = "success";
            } else {
                throw new Exception("Lỗi máy chủ gửi mail. Mật khẩu trên hệ thống CHƯA bị thay đổi.");
            }
        }
    } catch (Exception $e) {
        $_SESSION['msg'] = $e->getMessage();
        $_SESSION['msg_type'] = "danger";
    }

    header("Location: index.php?page=accounts");
    exit;
}

// ==========================================
// 2. TRUY VẤN DỮ LIỆU HIỂN THỊ (CÓ TÌM KIẾM & PHÂN TRANG)
// ==========================================
$keyword = $_GET['keyword'] ?? '';
$filter_role = $_GET['filter_role'] ?? '';

$limit = 10;
$p = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($p < 1) $p = 1;
$offset = (int)(($p - 1) * $limit);

$baseSelect = "SELECT username, role, isActive, createdAt FROM Accounts";
$whereClauses = [];
$params = [];

if ($keyword !== '') {
    $whereClauses[] = "username LIKE ?";
    $params[] = "%$keyword%";
}
if ($filter_role !== '') {
    $whereClauses[] = "role = ?";
    $params[] = $filter_role;
}

$whereString = !empty($whereClauses) ? " WHERE " . implode(" AND ", $whereClauses) : "";

// Đếm tổng dòng
$countSql = "SELECT COUNT(*) FROM Accounts" . $whereString;
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalRows = $countStmt->fetchColumn();

// Lấy danh sách tài khoản
$sql = $baseSelect . $whereString . " ORDER BY createdAt DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$accounts = $stmt->fetchAll();

$totalPages = ceil($totalRows / $limit);
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Hệ thống Tài khoản phân quyền</h1>
    
    <div> <form method="POST" class="d-inline mr-2">
            <input type="hidden" name="action" value="sync">
            <button type="submit" class="btn btn-sm btn-success shadow-sm" onclick="return confirm('Hệ thống sẽ quét và tạo tài khoản cho các SV/GV chưa có. Mật khẩu mặc định là 123456. Khuyến cáo thực hiện vào giờ thấp điểm. Tiếp tục?')">
                <i class="fas fa-sync-alt fa-sm text-white-50"></i> Đồng bộ tài khoản thiếu
            </button>
        </form>

        <button class="btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#addModal">
            <i class="fas fa-user-plus fa-sm text-white-50"></i> Cấp tài khoản mới
        </button>
    </div>
</div>

<?php if (isset($_SESSION['msg'])): ?>
    <div class="alert alert-<?= $_SESSION['msg_type'] ?> alert-dismissible fade show shadow-sm">
        <?= $_SESSION['msg'] ?>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    <?php unset($_SESSION['msg'], $_SESSION['msg_type']); ?>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3 row align-items-center m-0">
        <div class="col-md-4 mt-2 px-0">
            <h6 class="font-weight-bold text-primary m-0">Danh sách tài khoản đăng nhập</h6>
        </div>
        <div class="col-md-8 px-0">
            <form method="GET" action="index.php" class="mb-0 row no-gutters align-items-center">
                <input type="hidden" name="page" value="accounts">
                <div class="col-md-4 pr-md-2 mb-2 mb-md-0">
                    <select name="filter_role" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Tất cả Vai trò --</option>
                        <option value="Admin" <?= $filter_role === 'Admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="Teacher" <?= $filter_role === 'Teacher' ? 'selected' : '' ?>>Teacher (Giảng viên)</option>
                        <option value="Student" <?= $filter_role === 'Student' ? 'selected' : '' ?>>Student (Sinh viên)</option>
                    </select>
                </div>
                <div class="col-md-8">
                    <div class="input-group w-100">
                        <input type="text" name="keyword" class="form-control" placeholder="Tìm theo tên đăng nhập (mã số)..." value="<?= htmlspecialchars($keyword) ?>">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                            <?php if ($keyword !== '' || $filter_role !== ''): ?>
                                <a href="index.php?page=accounts" class="btn btn-danger">Hủy</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle text-center" width="100%">
                <thead class="thead-light">
                    <tr>
                        <th>Tên đăng nhập (Username)</th>
                        <th>Vai trò hệ thống</th>
                        <th>Trạng thái hoạt động</th>
                        <th>Ngày tạo</th>
                        <th width="15%">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accounts as $acc): ?>
                    <tr>
                        <td class="font-weight-bold text-primary"><?= htmlspecialchars($acc['username']) ?></td>
                        <td>
                            <?php if ($acc['role'] === 'Admin'): ?>
                                <span class="badge badge-danger p-2">Quản trị viên (Admin)</span>
                            <?php elseif ($acc['role'] === 'Teacher'): ?>
                                <span class="badge badge-warning p-2">Giảng viên</span>
                            <?php else: ?>
                                <span class="badge badge-success p-2">Sinh viên</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ((int)$acc['isActive'] === 1): ?>
                                <span class="text-success font-weight-bold"><i class="fas fa-check-circle"></i> Đang mở</span>
                            <?php else: ?>
                                <span class="text-danger font-weight-bold"><i class="fas fa-ban"></i> Đang khóa</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($acc['createdAt'])) ?></td>
                        <td class="text-nowrap">
                                <a href="index.php?page=profile&username=<?= urlencode($acc['username']) ?>" class="btn btn-info btn-sm shadow-sm mr-1">
                                    <i class="fas fa-id-card"></i> Hồ sơ
                                </a>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="reset_password">
                                <input type="hidden" name="username" value="<?= htmlspecialchars($acc['username']) ?>">
                                <input type="hidden" name="role" value="<?= htmlspecialchars($acc['role']) ?>">
                                <button type="submit" class="btn btn-info btn-sm shadow-sm mr-1" onclick="return confirm('Hệ thống sẽ tạo mật khẩu ngẫu nhiên và gửi thẳng vào email của người này. Bạn có chắc chắn?')">
                                    <i class="fas fa-key"></i> Reset MK
                                </button>
                            </form>

                            <button type="button" class="btn btn-warning btn-sm shadow-sm mr-1" 
                                    onclick='openEditModal(<?= json_encode($acc, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>

                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="username" value="<?= htmlspecialchars($acc['username']) ?>">
                                <button type="submit" class="btn btn-danger btn-sm shadow-sm" onclick="return confirm('Xóa tài khoản có thể làm lỗi lịch sử log. Bạn chắc chắn?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <nav class="mt-4"><ul class="pagination justify-content-end mb-0">
            <li class="page-item <?= ($p <= 1) ? 'disabled' : '' ?>"><a class="page-link" href="?page=accounts&keyword=<?= urlencode($keyword) ?>&filter_role=<?= urlencode($filter_role) ?>&p=<?= $p - 1 ?>">Trước</a></li>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= ($i == $p) ? 'active' : '' ?>"><a class="page-link" href="?page=accounts&keyword=<?= urlencode($keyword) ?>&filter_role=<?= urlencode($filter_role) ?>&p=<?= $i ?>"><?= $i ?></a></li>
            <?php endfor; ?>
            <li class="page-item <?= ($p >= $totalPages) ? 'disabled' : '' ?>"><a class="page-link" href="?page=accounts&keyword=<?= urlencode($keyword) ?>&filter_role=<?= urlencode($filter_role) ?>&p=<?= $p + 1 ?>">Sau</a></li>
        </ul></nav>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold">Cấp tài khoản mới</h5>
                <button class="close text-white" type="button" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label class="font-weight-bold">Tên đăng nhập (Mã số số định danh)</label>
                    <input type="text" class="form-control" name="username" required>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Mật khẩu khởi tạo</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Vai trò quyền hạn</label>
                    <select class="form-control" name="role" required>
                        <option value="Student">Student (Sinh viên)</option>
                        <option value="Teacher">Teacher (Giảng viên)</option>
                        <option value="Admin">Admin (Quản trị viên)</option>
                    </select>
                </div>
                <div class="form-group custom-control custom-checkbox ml-2 mt-4">
                    <input type="checkbox" class="custom-control-input" id="add_isActive" name="isActive" checked>
                    <label class="custom-control-label font-weight-bold" for="add_isActive">Kích hoạt tài khoản ngay lập tức</label>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-primary font-weight-bold">Tạo tài khoản</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title font-weight-bold">Cấu hình thông tin tài khoản</h5>
                <button class="close" type="button" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="edit">
                <div class="form-group">
                    <label class="font-weight-bold">Tên đăng nhập (Read-only)</label>
                    <input type="text" class="form-control bg-light font-weight-bold text-dark" name="username" id="edit_username" readonly>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold text-danger">Đặt lại mật khẩu mới</label>
                    <input type="password" class="form-control border-danger" name="password" placeholder="Bỏ trống nếu không muốn đổi mật khẩu...">
                    <small class="form-text text-muted"><i>An toàn bảo mật: Mật khẩu sẽ tự động băm Bcrypt khi gửi lên server.</i></small>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Vai trò hệ thống</label>
                    <select class="form-control" name="role" id="edit_role" required>
                        <option value="Student">Student (Sinh viên)</option>
                        <option value="Teacher">Teacher (Giảng viên)</option>
                        <option value="Admin">Admin (Quản trị viên)</option>
                    </select>
                </div>
                <div class="form-group custom-control custom-checkbox ml-2 mt-4">
                    <input type="checkbox" class="custom-control-input" id="edit_isActive" name="isActive">
                    <label class="custom-control-label font-weight-bold text-primary" for="edit_isActive">Trạng thái tài khoản hoạt động bình thường</label>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-warning font-weight-bold text-dark">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(data) {
    document.getElementById('edit_username').value = data.username;
    document.getElementById('edit_role').value = data.role;
    
    // Xử lý gán trạng thái checked cho checkbox dựa trên giá trị của isActive
    document.getElementById('edit_isActive').checked = (parseInt(data.isActive) === 1);
    
    $('#editModal').modal('show');
}
</script>