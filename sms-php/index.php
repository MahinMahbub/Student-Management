<?php
require_once __DIR__ . '/includes/student_functions.php';

$pageTitle = 'Dashboard';
$activeNav = 'dashboard';

$stats = getDashboardStats();

$maxDeptCount = 1;
foreach ($stats['by_department'] as $row) {
    $maxDeptCount = max($maxDeptCount, (int) $row['student_count']);
}
$maxSemCount = 1;
foreach ($stats['by_semester'] as $row) {
    $maxSemCount = max($maxSemCount, (int) $row['student_count']);
}

require __DIR__ . '/includes/header.php';
?>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-label">Total Students</div>
        <div class="stat-value"><?= (int) $stats['total_students'] ?></div>
        <div class="stat-sub">across <?= count($stats['by_department']) ?> departments</div>
    </div>
    <div class="stat-card teal">
        <div class="stat-label">Average CGPA</div>
        <div class="stat-value"><?= number_format((float) $stats['avg_cgpa'], 2) ?></div>
        <div class="stat-sub">scale of 4.00</div>
    </div>
    <div class="stat-card teal">
        <div class="stat-label">Highest CGPA</div>
        <div class="stat-value"><?= number_format((float) $stats['max_cgpa'], 2) ?></div>
        <div class="stat-sub">top performer</div>
    </div>
    <div class="stat-card red">
        <div class="stat-label">Lowest CGPA</div>
        <div class="stat-value"><?= number_format((float) $stats['min_cgpa'], 2) ?></div>
        <div class="stat-sub">needs attention</div>
    </div>
</div>

<div class="dashboard-grid">
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">Students by Department</span>
            <span class="panel-eyebrow">JOIN + GROUP BY</span>
        </div>
        <div class="bar-list">
            <?php foreach ($stats['by_department'] as $row): ?>
            <div class="bar-row">
                <div class="bar-row-label"><?= htmlspecialchars($row['dept_name']) ?></div>
                <div class="bar-track">
                    <div class="bar-fill" style="width: <?= round(((int)$row['student_count']) / $maxDeptCount * 100, 1) ?>%"></div>
                </div>
                <div class="bar-row-value"><?= (int) $row['student_count'] ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="padding: 0 20px 18px;">
            <div class="query-note"><span class="kw">SELECT</span> d.dept_name, <span class="kw">COUNT</span>(s.student_id)
<span class="kw">FROM</span> departments d <span class="kw">LEFT JOIN</span> students s <span class="kw">ON</span> s.dept_id = d.dept_id
<span class="kw">GROUP BY</span> d.dept_id <span class="kw">ORDER BY</span> student_count <span class="kw">DESC</span>;</div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">Top 5 by CGPA</span>
            <span class="panel-eyebrow">ORDER BY + LIMIT</span>
        </div>
        <ul class="top-list">
            <?php if ($stats['top_students']): ?>
                <?php foreach ($stats['top_students'] as $i => $s): ?>
                <li class="top-item">
                    <span class="top-rank"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                    <span class="top-name"><?= htmlspecialchars($s['name']) ?><br><span class="top-dept"><?= htmlspecialchars($s['dept_name']) ?></span></span>
                    <span class="top-cgpa"><?= number_format((float) $s['cgpa'], 2) ?></span>
                </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="top-item">No students yet.</li>
            <?php endif; ?>
        </ul>
        <div style="padding: 0 20px 18px;">
            <div class="query-note"><span class="kw">SELECT</span> s.name, s.cgpa, d.dept_name
<span class="kw">FROM</span> students s <span class="kw">JOIN</span> departments d <span class="kw">ON</span> s.dept_id = d.dept_id
<span class="kw">ORDER BY</span> s.cgpa <span class="kw">DESC LIMIT</span> 5;</div>
        </div>
    </div>
</div>

<div class="panel section-gap">
    <div class="panel-header">
        <span class="panel-title">Students by Semester</span>
        <span class="panel-eyebrow">GROUP BY semester</span>
    </div>
    <div class="bar-list">
        <?php if ($stats['by_semester']): ?>
            <?php foreach ($stats['by_semester'] as $row): ?>
            <div class="bar-row">
                <div class="bar-row-label">Semester <?= (int) $row['semester'] ?></div>
                <div class="bar-track">
                    <div class="bar-fill" style="width: <?= round(((int)$row['student_count']) / $maxSemCount * 100, 1) ?>%"></div>
                </div>
                <div class="bar-row-value"><?= (int) $row['student_count'] ?></div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">No student data yet.</div>
        <?php endif; ?>
    </div>
    <div style="padding: 0 20px 18px;">
        <div class="query-note"><span class="kw">SELECT</span> semester, <span class="kw">COUNT</span>(*) <span class="kw">AS</span> student_count
<span class="kw">FROM</span> students <span class="kw">GROUP BY</span> semester <span class="kw">ORDER BY</span> semester;</div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
