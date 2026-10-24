<?php
/**
 * includes/header.php
 * Shared page shell: <head>, sidebar navigation, flash messages.
 * Expects $pageTitle and $activeNav to be set by the including page.
 */
$pageTitle = $pageTitle ?? 'Student Management System';
$activeNav = $activeNav ?? '';

// Start session for flash messages (add/edit/delete confirmations)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?> · Student Management System</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-mark">SM</div>
            <div class="brand-text">
                <span class="brand-title">Student MS</span>
                <span class="brand-sub">PHP · MySQL</span>
            </div>
        </div>
        <nav class="nav">
            <a href="index.php" class="nav-link <?= $activeNav === 'dashboard' ? 'active' : '' ?>">
                <span class="nav-icon">▦</span> Dashboard
            </a>
            <a href="students.php" class="nav-link <?= $activeNav === 'students' ? 'active' : '' ?>">
                <span class="nav-icon">▤</span> All Students
            </a>
            <a href="add_student.php" class="nav-link <?= $activeNav === 'add' ? 'active' : '' ?>">
                <span class="nav-icon">＋</span> Add Student
            </a>
            <a href="search.php" class="nav-link <?= $activeNav === 'search' ? 'active' : '' ?>">
                <span class="nav-icon">⌕</span> Search
            </a>
        </nav>
        <div class="sidebar-footer">
            <span>MySQL · PDO</span>
        </div>
    </aside>

    <main class="content">
        <header class="topbar">
            <h1 class="page-heading"><?= htmlspecialchars($pageTitle) ?></h1>
        </header>

        <?php if (!empty($_SESSION['flash'])): ?>
            <div class="flash-stack">
                <?php foreach ($_SESSION['flash'] as $flash): ?>
                    <div class="flash flash-<?= htmlspecialchars($flash['type']) ?>">
                        <?= htmlspecialchars($flash['message']) ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>
