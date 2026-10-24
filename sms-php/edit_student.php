<?php
require_once __DIR__ . '/includes/student_functions.php';
require_once __DIR__ . '/includes/flash.php';

$pageTitle = 'Edit Student';
$activeNav = 'students';

$studentId = $_GET['id'] ?? $_POST['student_id'] ?? '';
$student = getStudentById($studentId);

if ($student === null) {
    setFlash('error', "No student found with ID '{$studentId}'.");
    header('Location: students.php');
    exit;
}

$departments = getDepartments();
$form = $student; // pre-fill with existing values by default

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = $_POST;

    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $deptId   = $_POST['dept_id'] ?? '';
    $semester = $_POST['semester'] ?? '';
    $cgpa     = $_POST['cgpa'] ?? '';

    $errors = [];
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
        $result = updateStudent($studentId, $name, $email, (int) $deptId, $semesterInt, $cgpaFloat);
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
    <form method="POST" action="edit_student.php?id=<?= urlencode($student['student_id']) ?>">
        <input type="hidden" name="student_id" value="<?= htmlspecialchars($student['student_id']) ?>">
        <div class="form-grid">
            <div class="field full">
                <label>Student ID <span class="pk-badge">PK</span></label>
                <input type="text" value="<?= htmlspecialchars($student['student_id']) ?>" readonly>
                <span class="form-hint">Primary key — not editable.</span>
            </div>

            <div class="field full">
                <label>Full Name</label>
                <input type="text" name="name" required value="<?= htmlspecialchars($form['name'] ?? '') ?>">
            </div>

            <div class="field full">
                <label>Email</label>
                <input type="email" name="email" required value="<?= htmlspecialchars($form['email'] ?? '') ?>">
            </div>

            <div class="field">
                <label>Department <span class="fk-badge">FK</span></label>
                <select name="dept_id" required>
                    <?php foreach ($departments as $d): ?>
                    <option value="<?= (int) $d['dept_id'] ?>" <?= ((string)($form['dept_id'] ?? '') === (string)$d['dept_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($d['dept_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label>Semester</label>
                <input type="number" name="semester" min="1" max="12" required value="<?= htmlspecialchars((string) ($form['semester'] ?? '')) ?>">
            </div>

            <div class="field">
                <label>CGPA</label>
                <input type="number" name="cgpa" min="0" max="4" step="0.01" required
                       value="<?= htmlspecialchars(number_format((float) ($form['cgpa'] ?? 0), 2)) ?>">
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-accent">Update Student</button>
            <a href="students.php" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
