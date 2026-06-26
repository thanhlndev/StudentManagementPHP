<?php
/** @var PDO $pdo */
$maSV = $_SESSION['user']['username'];

// Truy vấn lấy lịch học
$sql = "
    SELECT cs.day_of_week, cs.start_period, cs.num_periods, c.tenMH, r.room_name
    FROM class_schedules cs
    JOIN courseclasses cc ON cs.maLHP = cc.maLHP
    JOIN courses c ON cc.maMH = c.maMH
    JOIN rooms r ON cs.room_id = r.room_id
    JOIN grades g ON cs.maLHP = g.maLHP
    WHERE g.maSV = :maSV
";
$stmt = $pdo->prepare($sql);
$stmt->execute([':maSV' => $maSV]);
$schedules = $stmt->fetchAll();

// Xây dựng Grid
$grid = [];
foreach ($schedules as $s) {
    for ($i = 0; $i < $s['num_periods']; $i++) {
        $period = $s['start_period'] + $i;
        $grid[$s['day_of_week']][$period] = [
            'tenMH' => $s['tenMH'],
            'room' => $s['room_name']
        ];
    }
}
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Thời khóa biểu cá nhân</h6>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered mb-0" style="table-layout: fixed; min-height: 400px;">
            <thead class="bg-light">
                <tr>
                    <th width="80px">Tiết</th>
                    <?php for ($d = 2; $d <= 8; $d++): ?>
                        <th class="text-center"><?= $d == 8 ? 'CN' : 'Thứ ' . $d ?></th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody>
                <?php for ($p = 1; $p <= 12; $p++): ?>
                    <tr>
                        <td class="font-weight-bold text-center align-middle"><?= $p ?></td>
                        <?php for ($d = 2; $d <= 8; $d++): ?>
                            <td class="p-1 align-top" style="height: 60px;">
                                <?php if (isset($grid[$d][$p])): ?>
                                    <div class="bg-primary text-white rounded p-1" style="font-size: 0.75rem;">
                                        <strong><?= htmlspecialchars($grid[$d][$p]['tenMH']) ?></strong>
                                        <br><small><i class="fas fa-map-marker-alt"></i>
                                            <?= htmlspecialchars($grid[$d][$p]['room']) ?></small>
                                    </div>
                                <?php endif; ?>
                            </td>
                        <?php endfor; ?>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>
</div>