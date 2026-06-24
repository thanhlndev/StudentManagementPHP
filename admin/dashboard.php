<?php
/** @var PDO $pdo */
global $pdo;
// Tầng 1: Lấy dữ liệu Macro
$stats = [
    'sv' => $pdo->query("SELECT COUNT(*) FROM Students")->fetchColumn(),
    'gv' => $pdo->query("SELECT COUNT(*) FROM Teachers")->fetchColumn(),
    'lhp' => $pdo->query("SELECT COUNT(*) FROM CourseClasses")->fetchColumn(),
    'can_chot' => $pdo->query("SELECT COUNT(*) FROM CourseClasses WHERE isLocked = 0")->fetchColumn()
];

// Tầng 2: Lấy dữ liệu Phân bổ Khoa (cho Biểu đồ)
$facultyData = $pdo->query("
    SELECT f.tenKhoa, COUNT(s.maSV) as total 
    FROM Faculties f 
    JOIN Classes c ON f.maKhoa = c.maKhoa 
    JOIN Students s ON c.maLop = s.maLop 
    GROUP BY f.tenKhoa
")->fetchAll();

// Tầng 3: Lấy dữ liệu Cảnh báo (Lớp trượt > 30%)
$alerts = $pdo->query("
    SELECT cc.maLHP, c.tenMH, COUNT(g.maSV) as total, 
           SUM(CASE WHEN g.diemTong < 4.0 THEN 1 ELSE 0 END) as failCount
    FROM CourseClasses cc
    JOIN Grades g ON cc.maLHP = g.maLHP
    JOIN Courses c ON cc.maMH = c.maMH
    GROUP BY cc.maLHP
    HAVING (failCount / total) > 0.3
")->fetchAll();
?>

<div class="row">
    <?php 
    $widgets = [
        ['Tổng SV', $stats['sv'], 'primary', 'user-graduate'],
        ['Tổng GV', $stats['gv'], 'success', 'chalkboard-teacher'],
        ['Lớp HP', $stats['lhp'], 'info', 'book'],
        ['Chưa chốt', $stats['can_chot'], 'warning', 'exclamation-triangle']
    ];
    foreach($widgets as $w): ?>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-<?= $w[2] ?> shadow h-100 py-2">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col"><div class="text-xs font-weight-bold text-<?= $w[2] ?> text-uppercase"><?= $w[0] ?></div>
                    <div class="h5 font-weight-bold text-gray-800"><?= $w[1] ?></div></div>
                    <div class="col-auto"><i class="fas fa-<?= $w[3] ?> fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>


<div class="row">
    <div class="col-xl-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Phân bổ Sinh viên theo Khoa</h6></div>
            <div class="card-body"><canvas id="facultyChart" height="100"></canvas></div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-danger">Cảnh báo: Lớp tỷ lệ trượt cao</h6></div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php foreach($alerts as $a): ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <?= $a['tenMH'] ?> <span class="badge badge-danger"><?= round(($a['failCount']/$a['total'])*100) ?>% trượt</span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
// Chờ DOM load xong mới vẽ
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('facultyChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [<?php foreach($facultyData as $f) echo "'" . addslashes($f['tenKhoa']) . "', "; ?>],
            datasets: [{
                label: 'Số sinh viên',
                data: [<?php foreach($facultyData as $f) echo $f['total'] . ", "; ?>],
                backgroundColor: '#4e73df'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
});
</script>