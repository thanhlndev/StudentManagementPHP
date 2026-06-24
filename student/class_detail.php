<?php
/** @var PDO $pdo */

// 1. Lấy và kiểm tra ID từ URL
$maLHP = isset($_GET['maLHP']) ? (int)$_GET['maLHP'] : 0;

// 2. Truy vấn thông tin Lớp học phần
// JOIN chuẩn theo schema: CourseClasses -> Courses, Teachers, Semesters
$stmt = $pdo->prepare("
    SELECT cc.maLHP, c.tenMH, t.hoTenGV, t.email AS emailGV, s.tenHK
    FROM CourseClasses cc
    JOIN Courses c ON cc.maMH = c.maMH
    JOIN Teachers t ON cc.maGV = t.maGV
    JOIN Semesters s ON cc.maHK = s.maHK
    WHERE cc.maLHP = ?
");
$stmt->execute([$maLHP]);
$classInfo = $stmt->fetch();

if (!$classInfo) {
    echo '<div class="alert alert-danger">Lớp học phần không tồn tại hoặc dữ liệu bị lỗi.</div>';
    exit;
}

// 3. Truy vấn danh sách sinh viên
// JOIN bảng Students để lấy họ tên và email
$stmtSv = $pdo->prepare("
    SELECT s.maSV, s.hoTen, s.email
    FROM Grades g
    JOIN Students s ON g.maSV = s.maSV
    WHERE g.maLHP = ?
");
$stmtSv->execute([$maLHP]);
$students = $stmtSv->fetchAll();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Chi tiết Lớp: <?= htmlspecialchars($classInfo['tenMH']) ?></h1>
    <a href="index.php?page=course_classes" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 font-weight-bold text-primary">Thông tin lớp</div>
            <div class="card-body">
                <p><strong>Môn học:</strong> <?= htmlspecialchars($classInfo['tenMH']) ?></p>
                <p><strong>Học kỳ:</strong> <?= htmlspecialchars($classInfo['tenHK']) ?></p>
                <hr>
                <h6 class="font-weight-bold text-info"><i class="fas fa-chalkboard-teacher mr-1"></i> Giảng viên</h6>
                <p class="mb-1"><?= htmlspecialchars($classInfo['hoTenGV']) ?></p>
                <p class="mb-0"><small><?= htmlspecialchars($classInfo['emailGV']) ?></small></p>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 font-weight-bold text-success">
                Danh sách sinh viên (<?= count($students) ?>)
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>STT</th>
                            <th>Mã SV</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $k => $sv): ?>
                        <tr>
                            <td><?= $k + 1 ?></td>
                            <td class="font-weight-bold"><?= $sv['maSV'] ?></td>
                            <td><?= htmlspecialchars($sv['hoTen']) ?></td>
                            <td><?= htmlspecialchars($sv['email']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>