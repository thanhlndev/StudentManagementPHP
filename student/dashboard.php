<?php
/** @var PDO $pdo */
$maSV = $_SESSION['user']['username'];

// Tầng 1: Lấy số liệu cá nhân
$stats = [
    'tin_chi' => $pdo->prepare("SELECT SUM(c.soTinChi) FROM Grades g JOIN CourseClasses cc ON g.maLHP = cc.maLHP JOIN Courses c ON cc.maMH = c.maMH WHERE g.maSV = ? AND g.diemTong >= 4.0"),
    'gpa' => $pdo->prepare("SELECT diemTong FROM Grades WHERE maSV = ?"), // Đơn giản hóa: lấy điểm trung bình hiện tại
    'dang_ky' => $pdo->prepare("SELECT COUNT(*) FROM Grades g JOIN CourseClasses cc ON g.maLHP = cc.maLHP WHERE g.maSV = ? AND cc.isLocked = 0")
];
$stats['tin_chi']->execute([$maSV]); $tinChi = $stats['tin_chi']->fetchColumn() ?? 0;
$stats['dang_ky']->execute([$maSV]); $dangKy = $stats['dang_ky']->fetchColumn();

// Tầng 2: Dữ liệu GPA qua các kỳ để vẽ biểu đồ
$gpaHistory = $pdo->prepare("
    SELECT s.tenHK, AVG(g.diemTong) as avgDiem
    FROM Grades g
    JOIN CourseClasses cc ON g.maLHP = cc.maLHP
    JOIN Semesters s ON cc.maHK = s.maHK
    WHERE g.maSV = ?
    GROUP BY s.maHK
    ORDER BY s.maHK ASC
");
$gpaHistory->execute([$maSV]);
$history = $gpaHistory->fetchAll();
?>

<div class="row">
    <div class="col-xl-4 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col"><div class="text-xs font-weight-bold text-success text-uppercase">Tín chỉ tích lũy</div>
                    <div class="h5 font-weight-bold"><?= $tinChi ?> / 120 TC</div></div>
                    <div class="col-auto"><i class="fas fa-graduation-cap fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col"><div class="text-xs font-weight-bold text-info text-uppercase">Học kỳ này</div>
                    <div class="h5 font-weight-bold"><?= $dangKy ?> lớp</div></div>
                    <div class="col-auto"><i class="fas fa-book-open fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body text-center">
                <div class="h6 font-weight-bold text-primary">Cố vấn học tập: GV01</div>
                <div class="small text-muted">Liên hệ: gv01@university.edu.vn</div>
            </div>
        </div>
    </div>
</div>


<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Biểu đồ tiến độ GPA qua các học kỳ</h6>
    </div>
    <div class="card-body">
        <div style="height: 300px; width: 100%;">
            <canvas id="gpaChart"></canvas>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('gpaChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: [<?php foreach($history as $h) echo "'" . addslashes(substr($h['tenHK'], 0, 7)) . "', "; ?>],
            datasets: [{
                label: 'Điểm trung bình học kỳ',
                data: [<?php foreach($history as $h) echo round($h['avgDiem'], 2) . ", "; ?>],
                borderColor: '#4e73df',
                tension: 0.1, // Thêm đường cong nhẹ cho đẹp
                fill: false
            }]
        },
        // Thay đổi 2: Cấu hình options để không bị giãn nở vô tội vạ
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
});
</script>