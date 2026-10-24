<?php
require_once __DIR__ . '/includes/student_functions.php';
require_once __DIR__ . '/includes/flash.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: students.php');
    exit;
}

$studentId = $_POST['student_id'] ?? '';

if ($studentId === '') {
    setFlash('error', 'No student ID provided.');
} else {
    $result = deleteStudent($studentId);
    setFlash($result['success'] ? 'success' : 'error', $result['message']);
}

header('Location: students.php');
exit;
