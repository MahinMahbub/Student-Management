<?php
require_once __DIR__ . '/includes/student_functions.php';

$pageTitle = 'Search Students';
$activeNav = 'search';

$criterion = $_GET['criterion'] ?? 'name';
$keyword   = trim($_GET['keyword'] ?? '');
$searched  = false;
$results   = [];

$allowedCriteria = ['id', 'name', 'department'];
if (!in_array($criterion, $allowedCriteria, true)) {
    $criterion = 'name';
}

if ($keyword !== '') {
    $results  = searchStudents($criterion, $keyword);
    $searched = true;
}

require __DIR__ . '/includes/header.php';
?>

<form method="GET" action="search.php" class="search-bar">
    <select name="criterion">
        <option value="id" <?= $criterion === 'id' ? 'selected' : '' ?>>Student ID</option>
        <option value="name" <?= $criterion === 'name' ? 'selected' : '' ?>>Name</option>
        <option value="department" <?= $criterion === 'department' ? 'selected' : '' ?>>Department</option>
    </select>
    <input type="text" name="keyword" placeholder="Type to search…" value="<?= htmlspecialchars($keyword) ?>" autofocus>
    <button type="submit" class="btn btn-accent">Search</button>
</form>

<?php if ($searched): ?>
    <div class="search-meta">
        <?= count($results) ?> result<?= count($results) === 1 ? '' : 's' ?> for
        <span class="mono"><?= htmlspecialchars($criterion) ?> LIKE '%<?= htmlspecialchars($keyword) ?>%'</span>
    </div>

    <div class="table-wrap">
    <?php if ($results): ?>
        <table>
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Semester</th>
                    <th>CGPA</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $s): ?>
                <?php
                    $cgpa = (float) $s['cgpa'];
                    $cgpaClass = $cgpa >= 3.5 ? 'cgpa-high' : ($cgpa >= 3.0 ? 'cgpa-mid' : 'cgpa-low');
                ?>
                <tr>
                    <td><span class="cell-id"><?= htmlspecialchars($s['student_id']) ?></span></td>
                    <td><?= htmlspecialchars($s['name']) ?></td>
                    <td><?= htmlspecialchars($s['email']) ?></td>
                    <td><span class="dept-tag"><?= htmlspecialchars($s['dept_name']) ?></span></td>
                    <td><?= (int) $s['semester'] ?></td>
                    <td><span class="cgpa-value <?= $cgpaClass ?>"><?= number_format($cgpa, 2) ?></span></td>
                    <td>
                        <div class="row-actions">
                            <a href="edit_student.php?id=<?= urlencode($s['student_id']) ?>" class="btn btn-ghost btn-sm">Edit</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-title">No matches</div>
            <p>Try a different keyword or search criterion.</p>
        </div>
    <?php endif; ?>
    </div>
<?php else: ?>
    <div class="empty-state">
        <div class="empty-state-title">Search the student database</div>
        <p>Choose a criterion and type a keyword to look up records.</p>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
