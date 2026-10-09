<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/header.php';

$summary = $db->getRows("
    SELECT co.course_name, c.class_code, c.slots,
           COUNT(e.enrollment_id) as total_enrollments
    FROM classes c
    JOIN courses co ON c.course_id = co.course_id
    LEFT JOIN enrollments e ON c.class_id = e.class_id AND e.status = 'active'
    GROUP BY c.class_id
");
?>

<h2>Summary Reports</h2>

<table border="1">
    <thead>
        <tr><th>Course</th><th>Class Code</th><th>Active Enrollments</th><th>Remaining Slots</th></tr>
    </thead>
    <tbody>
        <?php foreach ($summary as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['course_name']) ?></td>
                <td><?= htmlspecialchars($s['class_code']) ?></td>
                <td><?= $s['total_enrollments'] ?></td>
                <td><?= $s['slots'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>