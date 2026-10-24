<?php
require_once __DIR__ . '/includes/student_functions.php';

$pageTitle = 'All Students';
$activeNav = 'students';

$students = getAllStudents();

require __DIR__ . '/includes/header.php';
?>

<div class="toolbar">
    <div class="search-meta">
        <?= count($students) ?> record<?= count($students) === 1 ? '' : 's' ?> —
        <span class="mono">SELECT s.*, d.dept_name FROM students s JOIN departments d ON s.dept_id = d.dept_id</span>
    </div>
    <a href="add_student.php" class="btn btn-accent">＋ Add Student</a>
</div>

<div class="table-wrap">
<?php if ($students): ?>
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
            <?php foreach ($students as $s): ?>
            <?php
                $cgpa = (float) $s['cgpa'];
                $cgpaClass = $cgpa >= 3.5 ? 'cgpa-high' : ($cgpa >= 3.0 ? 'cgpa-mid' : 'cgpa-low');
            ?>
            <tr>
                <td><span class="pk-badge">PK</span><span class="cell-id"><?= htmlspecialchars($s['student_id']) ?></span></td>
                <td><?= htmlspecialchars($s['name']) ?></td>
                <td><?= htmlspecialchars($s['email']) ?></td>
                <td><span class="fk-badge">FK</span><span class="dept-tag"><?= htmlspecialchars($s['dept_name']) ?></span></td>
                <td><?= (int) $s['semester'] ?></td>
                <td><span class="cgpa-value <?= $cgpaClass ?>"><?= number_format($cgpa, 2) ?></span></td>
                <td>
                    <div class="row-actions">
                        <a href="edit_student.php?id=<?= urlencode($s['student_id']) ?>" class="btn btn-ghost btn-sm">Edit</a>
                        <form method="POST" action="delete_student.php"
                              onsubmit="return confirm('Delete student <?= htmlspecialchars($s['student_id']) ?>? This cannot be undone.');"
                              style="display:inline;">
                            <input type="hidden" name="student_id" value="<?= htmlspecialchars($s['student_id']) ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="empty-state">
        <div class="empty-state-title">No students yet</div>
        <p>Add your first student record to get started.</p>
        <a href="add_student.php" class="btn btn-accent" style="margin-top: 10px;">＋ Add Student</a>
    </div>
<?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
