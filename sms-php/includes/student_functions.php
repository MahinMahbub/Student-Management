<?php
/**
 * includes/student_functions.php
 * --------------------------------
 * All database queries for the Student Management System.
 * Every statement uses PDO prepared statements (bound parameters),
 * so no SQL is ever built via string concatenation.
 */

require_once __DIR__ . '/../config/database.php';

// ------------------------------------------------------------------
// DEPARTMENTS
// ------------------------------------------------------------------

/** SELECT all departments (used for dropdowns). */
function getDepartments(): array {
    $pdo = getConnection();
    $stmt = $pdo->query("SELECT dept_id, dept_name FROM departments ORDER BY dept_name");
    return $stmt->fetchAll();
}

// ------------------------------------------------------------------
// CREATE (INSERT)
// ------------------------------------------------------------------

/**
 * INSERT a new student record.
 * Returns ['success' => bool, 'message' => string]
 */
function addStudent(string $studentId, string $name, string $email, int $deptId, int $semester, float $cgpa): array {
    $pdo = getConnection();

    $sql = "INSERT INTO students (student_id, name, email, dept_id, semester, cgpa)
            VALUES (:student_id, :name, :email, :dept_id, :semester, :cgpa)";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':student_id' => $studentId,
            ':name'       => $name,
            ':email'      => $email,
            ':dept_id'    => $deptId,
            ':semester'   => $semester,
            ':cgpa'       => $cgpa,
        ]);
        return ['success' => true, 'message' => 'Student added successfully.'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => friendlyDbError($e, $studentId, $email)];
    }
}

// ------------------------------------------------------------------
// READ (SELECT) — view all, with JOIN to departments
// ------------------------------------------------------------------

/**
 * SELECT all students, JOINed with departments to show the
 * department name (demonstrates PK/FK relational lookup).
 */
function getAllStudents(): array {
    $pdo = getConnection();
    $sql = "SELECT s.student_id, s.name, s.email, s.semester, s.cgpa,
                   d.dept_id, d.dept_name
            FROM students s
            JOIN departments d ON s.dept_id = d.dept_id
            ORDER BY s.student_id";
    return $pdo->query($sql)->fetchAll();
}

/** SELECT a single student by primary key. */
function getStudentById(string $studentId): ?array {
    $pdo = getConnection();
    $sql = "SELECT s.student_id, s.name, s.email, s.semester, s.cgpa,
                   d.dept_id, d.dept_name
            FROM students s
            JOIN departments d ON s.dept_id = d.dept_id
            WHERE s.student_id = :student_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':student_id' => $studentId]);
    $row = $stmt->fetch();
    return $row ?: null;
}

// ------------------------------------------------------------------
// SEARCH
// ------------------------------------------------------------------

/**
 * Search students by a chosen criterion: 'id', 'name', or 'department'.
 * Uses a parameterized LIKE query (safe from SQL injection).
 */
function searchStudents(string $criterion, string $keyword): array {
    $pdo = getConnection();
    $keywordLike = "%{$keyword}%";

    $base = "SELECT s.student_id, s.name, s.email, s.semester, s.cgpa,
                    d.dept_id, d.dept_name
             FROM students s
             JOIN departments d ON s.dept_id = d.dept_id";

    switch ($criterion) {
        case 'id':
            $sql = $base . " WHERE s.student_id LIKE :kw ORDER BY s.student_id";
            break;
        case 'name':
            $sql = $base . " WHERE s.name LIKE :kw ORDER BY s.student_id";
            break;
        case 'department':
            $sql = $base . " WHERE d.dept_name LIKE :kw ORDER BY s.student_id";
            break;
        default:
            return [];
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':kw' => $keywordLike]);
    return $stmt->fetchAll();
}

// ------------------------------------------------------------------
// UPDATE
// ------------------------------------------------------------------

/**
 * UPDATE an existing student's information.
 * Returns ['success' => bool, 'message' => string]
 */
function updateStudent(string $studentId, string $name, string $email, int $deptId, int $semester, float $cgpa): array {
    $pdo = getConnection();

    $sql = "UPDATE students
            SET name = :name, email = :email, dept_id = :dept_id,
                semester = :semester, cgpa = :cgpa
            WHERE student_id = :student_id";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':name'       => $name,
            ':email'      => $email,
            ':dept_id'    => $deptId,
            ':semester'   => $semester,
            ':cgpa'       => $cgpa,
            ':student_id' => $studentId,
        ]);

        if ($stmt->rowCount() === 0 && getStudentById($studentId) === null) {
            return ['success' => false, 'message' => "No student found with ID '{$studentId}'."];
        }
        return ['success' => true, 'message' => 'Student updated successfully.'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => friendlyDbError($e, $studentId, $email)];
    }
}

// ------------------------------------------------------------------
// DELETE
// ------------------------------------------------------------------

/**
 * DELETE a student record by primary key.
 * Returns ['success' => bool, 'message' => string]
 */
function deleteStudent(string $studentId): array {
    $pdo = getConnection();
    try {
        $stmt = $pdo->prepare("DELETE FROM students WHERE student_id = :student_id");
        $stmt->execute([':student_id' => $studentId]);

        if ($stmt->rowCount() === 0) {
            return ['success' => false, 'message' => "No student found with ID '{$studentId}'."];
        }
        return ['success' => true, 'message' => 'Student deleted successfully.'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

// ------------------------------------------------------------------
// DASHBOARD (multiple aggregate SQL queries)
// ------------------------------------------------------------------

/**
 * Runs several SQL aggregate queries to power the dashboard:
 *   1. Total number of students          -> COUNT(*)
 *   2. Students per department            -> COUNT(*) + GROUP BY + JOIN
 *   3. Average / highest / lowest CGPA    -> AVG / MAX / MIN
 *   4. Students per semester              -> COUNT(*) + GROUP BY
 *   5. Top 5 students by CGPA             -> ORDER BY ... LIMIT
 */
function getDashboardStats(): array {
    $pdo = getConnection();

    $totalStudents = (int) $pdo->query("SELECT COUNT(*) AS total FROM students")
                                ->fetch()['total'];

    $byDepartment = $pdo->query(
        "SELECT d.dept_name, COUNT(s.student_id) AS student_count
         FROM departments d
         LEFT JOIN students s ON s.dept_id = d.dept_id
         GROUP BY d.dept_id, d.dept_name
         ORDER BY student_count DESC"
    )->fetchAll();

    $cgpaStats = $pdo->query(
        "SELECT
            ROUND(AVG(cgpa), 2) AS avg_cgpa,
            MAX(cgpa) AS max_cgpa,
            MIN(cgpa) AS min_cgpa
         FROM students"
    )->fetch();

    $bySemester = $pdo->query(
        "SELECT semester, COUNT(*) AS student_count
         FROM students
         GROUP BY semester
         ORDER BY semester"
    )->fetchAll();

    $topStudents = $pdo->query(
        "SELECT s.student_id, s.name, s.cgpa, d.dept_name
         FROM students s
         JOIN departments d ON s.dept_id = d.dept_id
         ORDER BY s.cgpa DESC
         LIMIT 5"
    )->fetchAll();

    return [
        'total_students' => $totalStudents,
        'by_department'  => $byDepartment,
        'avg_cgpa'       => $cgpaStats['avg_cgpa'] ?? 0,
        'max_cgpa'       => $cgpaStats['max_cgpa'] ?? 0,
        'min_cgpa'       => $cgpaStats['min_cgpa'] ?? 0,
        'by_semester'    => $bySemester,
        'top_students'   => $topStudents,
    ];
}

// ------------------------------------------------------------------
// HELPERS
// ------------------------------------------------------------------

/** Translate common PDO/MySQL integrity errors into friendly messages. */
function friendlyDbError(PDOException $e, string $studentId, string $email): string {
    $msg = $e->getMessage();

    if (str_contains($msg, 'students.PRIMARY') || (str_contains($msg, 'Duplicate') && str_contains($msg, 'PRIMARY'))) {
        return "Student ID '{$studentId}' already exists.";
    }
    if (str_contains($msg, 'email')) {
        return "Email '{$email}' is already registered to another student.";
    }
    if (str_contains($msg, 'fk_student_department') || str_contains($msg, 'FOREIGN KEY')) {
        return "Invalid department selected.";
    }
    if (str_contains($msg, 'chk_cgpa')) {
        return "CGPA must be between 0.00 and 4.00.";
    }
    if (str_contains($msg, 'chk_semester')) {
        return "Semester must be between 1 and 12.";
    }
    return "Database error: " . $msg;
}
