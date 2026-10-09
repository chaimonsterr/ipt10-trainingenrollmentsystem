<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';

// Fetch quick statistics for the dashboard
$totalCourses = $db->getRow("SELECT COUNT(*) as count FROM courses")['count'] ?? 0;
$totalClasses = $db->getRow("SELECT COUNT(*) as count FROM classes")['count'] ?? 0;
$totalStudents = $db->getRow("SELECT COUNT(*) as count FROM students")['count'] ?? 0;
$activeEnrollments = $db->getRow("SELECT COUNT(*) as count FROM enrollments WHERE status = 'active'")['count'] ?? 0;

// Fetch recent enrollments
$recentEnrollments = $db->getRows("
    SELECT e.enrollment_date, s.full_name, co.course_code, c.class_code
    FROM enrollments e
    JOIN students s ON e.student_id = s.student_id
    JOIN classes c ON e.class_id = c.class_id
    JOIN courses co ON c.course_id = co.course_id
    ORDER BY e.enrollment_id DESC LIMIT 4
");
?>

<!-- Welcome Banner -->
<div class="welcome-banner">
    <div>
        <h1>Welcome Back, Administrator 👋</h1>
        <p>Here is an overview of your training catalog, active classes, and student enrollments today.</p>
    </div>
    <div class="banner-actions">
        <a href="/training_enrollment/admin/students.php" class="btn btn-hero">+ Record Student</a>
        <a href="/training_enrollment/admin/enroll.php" class="btn btn-hero-secondary">Enroll Existing</a>
    </div>
</div>

<!-- Key Performance Metrics -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon icon-blue">📚</div>
        <div class="stat-data">
            <span class="stat-number"><?= $totalCourses ?></span>
            <span class="stat-label">Active Courses</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-purple">🗓️</div>
        <div class="stat-data">
            <span class="stat-number"><?= $totalClasses ?></span>
            <span class="stat-label">Class Sections</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-green">👥</div>
        <div class="stat-data">
            <span class="stat-number"><?= $totalStudents ?></span>
            <span class="stat-label">Registered Students</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-orange">🎓</div>
        <div class="stat-data">
            <span class="stat-number"><?= $activeEnrollments ?></span>
            <span class="stat-label">Active Enrollments</span>
        </div>
    </div>
</div>

<!-- Main Dashboard Layout Grid -->
<div class="dashboard-split">
    <!-- Navigation & Core Actions -->
    <div class="section-card">
        <h2>Quick System Actions</h2>
        <div class="action-list">
            <a href="/training_enrollment/admin/courses.php" class="action-item">
                <div class="action-icon">📖</div>
                <div class="action-info">
                    <strong>Manage Courses</strong>
                    <span>Add, view, or remove training offerings in the catalog</span>
                </div>
                <span class="arrow">→</span>
            </a>

            <a href="/training_enrollment/admin/classes.php" class="action-item">
                <div class="action-icon">🕒</div>
                <div class="action-info">
                    <strong>Manage Class Sections</strong>
                    <span>Set up schedules, assign instructors, and update slots</span>
                </div>
                <span class="arrow">→</span>
            </a>

            <a href="/training_enrollment/admin/enrollments.php" class="action-item">
                <div class="action-icon">📋</div>
                <div class="action-info">
                    <strong>Manage Enrollments</strong>
                    <span>Review current registrations and process cancellations</span>
                </div>
                <span class="arrow">→</span>
            </a>

            <a href="/training_enrollment/admin/reports.php" class="action-item">
                <div class="action-icon">📊</div>
                <div class="action-info">
                    <strong>Summary Reports</strong>
                    <span>View course occupancy rates and slot breakdown</span>
                </div>
                <span class="arrow">→</span>
            </a>
        </div>
    </div>

    <!-- Recent Activity Side Panel -->
    <div class="section-card">
        <h2>Recent Registrations</h2>
        <?php if (!empty($recentEnrollments)): ?>
            <div class="activity-feed">
                <?php foreach ($recentEnrollments as $item): ?>
                    <div class="activity-item">
                        <div class="activity-avatar"><?= strtoupper(substr($item['full_name'], 0, 1)) ?></div>
                        <div class="activity-details">
                            <strong><?= htmlspecialchars($item['full_name']) ?></strong>
                            <span>Enrolled in <strong><?= htmlspecialchars($item['course_code']) ?></strong> (<?= htmlspecialchars($item['class_code']) ?>)</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="color: var(--text-muted); font-size: 0.9rem;">No student registrations recorded yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>