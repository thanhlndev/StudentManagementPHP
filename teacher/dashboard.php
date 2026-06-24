<?php
/** @var PDO $pdo */
$maGV = $_SESSION['user']['username'];

// Tầng 1: Lấy chỉ số
$stats = [
    'lop' => $pdo->prepare("SELECT COUNT(*) FROM CourseClasses WHERE maGV = ?"),
    'sv' => $pdo->prepare("SELECT COUNT(DISTINCT maSV) FROM Grades g JOIN CourseClasses cc ON g.maLHP = cc.maLHP WHERE cc.maGV = ?"),
    'chua_khoa' => $pdo->prepare("SELECT COUNT(*) FROM CourseClasses WHERE maGV = ? AND isLocked = 0")
];
$stats['lop']->execute([$maGV]); $lopCount = $stats['lop']->fetchColumn();
$stats['sv']->execute([$maGV]); $svCount = $stats['sv']->fetchColumn();
$stats['chua_khoa']->execute([$maGV]); $chuaKhoa = $stats['chua_khoa']->fetchColumn();

// Tầng 3: Lấy danh sách lớp cần xử lý
$myClasses = $pdo->prepare("
    SELECT cc.maLHP, c.tenMH, cc.isLocked, COUNT(g.maSV) as totalSV
    FROM CourseClasses cc
    JOIN Courses c ON cc.maMH = c.maMH
    LEFT JOIN Grades g ON cc.maLHP = g.maLHP
    WHERE cc.maGV = ?
    GROUP BY cc.maLHP
    ORDER BY cc.isLocked ASC, cc.maLHP DESC
");
$myClasses->execute([$maGV]);
$list = $myClasses->fetchAll();
?>

<div class="row">
    <div class="col-xl-4 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col"><div class="text-xs font-weight-bold text-info text-uppercase">Số lớp phụ trách</div>
                    <div class="h5 font-weight-bold"><?= $lopCount ?> lớp</div></div>
                    <div class="col-auto"><i class="fas fa-chalkboard fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col"><div class="text-xs font-weight-bold text-primary text-uppercase">Tổng sinh viên</div>
                    <div class="h5 font-weight-bold"><?= $svCount ?> SV</div></div>
                    <div class="col-auto"><i class="fas fa-users fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col"><div class="text-xs font-weight-bold text-warning text-uppercase">Lớp chưa chốt điểm</div>
                    <div class="h5 font-weight-bold"><?= $chuaKhoa ?> lớp</div></div>
                    <div class="col-auto"><i class="fas fa-pen-nib fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-info">Danh sách lớp học phần</h6></div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>Mã LHP</th><th>Môn học</th><th>Sĩ số</th><th>Trạng thái</th><th>Hành động</th></tr>
            </thead>
            <tbody>
                <?php foreach($list as $c): ?>
                <tr>
                    <td>#<?= $c['maLHP'] ?></td>
                    <td class="font-weight-bold"><?= htmlspecialchars($c['tenMH']) ?></td>
                    <td><?= $c['totalSV'] ?> SV</td>
                    <td>
                        <?= $c['isLocked'] ? '<span class="badge badge-danger">Đã khóa</span>' : '<span class="badge badge-success">Đang mở</span>' ?>
                    </td>
                    <td>
                        <a href="index.php?page=grade_detail&maLHP=<?= $c['maLHP'] ?>" class="btn btn-sm btn-<?= $c['isLocked'] ? 'secondary' : 'primary' ?>">
                            <?= $c['isLocked'] ? 'Xem điểm' : 'Nhập điểm' ?>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>