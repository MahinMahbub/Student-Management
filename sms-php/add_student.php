<?php
require_once __DIR__ . '/includes/student_functions.php';
require_once __DIR__ . '/includes/flash.php';

$pageTitle = 'Add Student';
$activeNav = 'add';

$departments = getDepartments();
$form = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = $_POST;

    $studentId = trim($_POST['student_id'] ?? '');
    $name      = trim($_POST['name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $deptId    = $_POST['dept_id'] ?? '';
    $semester  = $_POST['semester'] ?? '';
    $cgpa      = $_POST['cgpa'] ?? '';

    $errors = [];
    if ($studentId === '') $errors[] = 'Student ID is required.';
    if ($name === '') $errors[] = 'Name is required.';
    if ($email === '' || !str_contains($email, '@')) $errors[] = 'A valid email is required.';
    if ($deptId === '') $errors[] = 'Department is required.';

    $semesterInt = filter_var($semester, FILTER_VALIDATE_INT);
    if ($semesterInt === false || $semesterInt < 1 || $semesterInt > 12) {
        $errors[] = 'Semester must be a number between 1 and 12.';
    }

    $cgpaFloat = filter_var($cgpa, FILTER_VALIDATE_FLOAT);
    if ($cgpaFloat === false || $cgpaFloat < 0.0 || $cgpaFloat > 4.0) {
        $errors[] = 'CGPA must be a number between 0.00 and 4.00.';
    }

    if (empty($errors)) {
        $result = addStudent($studentId, $name, $email, (int) $deptId, $semesterInt, $cgpaFloat);
        if ($result['success']) {
            setFlash('success', $result['message']);
            header('Location: students.php');
            exit;
        } else {
            $errors[] = $result['message'];
        }
    }

    foreach ($errors as $e) {
        setFlash('error', $e);
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="form-panel">
    <form method="POST" action="add_student.php">
        <div class="form-grid">
            <div class="field full">
                <label>Student ID <span class="pk-badge">PK</span></label>
                <input type="text" name="student_id" placeholder="e.g. CSE-2025-011" required
                       value="<?= htmlspecialchars($form['student_id'] ?? '') ?>">
                <span class="form-hint">Unique identifier — cannot be changed after creation.</span>
            </div>

            <div class="field full">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="e.g. Nabila Chowdhury" required
                       value="<?= htmlspecialchars($form['name'] ?? '') ?>">
            </div>

            <div class="field full">
                <label>Email</label>
                <input type="email" name="email" placeholder="student@example.com" required
                       value="<?= htmlspecialchars($form['email'] ?? '') ?>">
            </div>

            <div class="field">
                <label>Department <span class="fk-badge">FK</span></label>
                <select name="dept_id" required>
                    <option value="" disabled <?= empty($form['dept_id']) ? 'selected' : '' ?>>Select department</option>
                    <?php foreach ($departments as $d): ?>
                    <option value="<?= (int) $d['dept_id'] ?>" <?= (isset($form['dept_id']) && (string)$form['dept_id'] === (string)$d['dept_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($d['dept_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label>Semester</label>
                <input type="number" name="semester" min="1" max="12" placeholder="1–12" required
                       value="<?= htmlspecialchars($form['semester'] ?? '') ?>">
            </div>

            <div class="field">
                <label>CGPA</label>
                <input type="number" name="cgpa" min="0" max="4" step="0.01" placeholder="0.00 – 4.00" required
                       value="<?= htmlspecialchars($form['cgpa'] ?? '') ?>">
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-accent">Save Student</button>
            <a href="students.php" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
