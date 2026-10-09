<?php // includes/header.php ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training Enrollment System</title>
    <link rel="stylesheet" href="/training_enrollment/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<header class="app-header">
    <div class="header-container">
        <a href="/training_enrollment/index.php" class="brand-logo">
            <div class="logo-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
            </div>
            <span>Training<strong>Hub</strong></span>
        </a>
        <nav class="nav-links">
            <a href="/training_enrollment/index.php">Dashboard</a>
            <a href="/training_enrollment/admin/courses.php">Courses</a>
            <a href="/training_enrollment/admin/classes.php">Classes</a>
            <a href="/training_enrollment/admin/students.php">Record Student</a>
            <a href="/training_enrollment/admin/enroll.php">Enroll</a>
            <a href="/training_enrollment/admin/enrollments.php">Enrollments</a>
            <a href="/training_enrollment/admin/reports.php">Reports</a>
        </nav>
    </div>
</header>

<main class="main-container">