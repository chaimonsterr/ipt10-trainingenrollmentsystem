<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/EnrollmentRepository.php';
require_once __DIR__ . '/../classes/Student.php';
require_once __DIR__ . '/../classes/ClassSection.php';
require_once __DIR__ . '/../includes/header.php';

$repo = new EnrollmentRepository($db);
$studentObj = new Student($db);
$classObj = new ClassSection($db);

$students = $studentObj->all();
$classList = $classObj->allWithCourse();
$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = (int)($_POST['student_id'] ?? 0);
    $class_id   = (int)($_POST['class_id'] ?? 0);

    if ($student_id > 0 && $class_id > 0) {
        $res = $repo->enroll($student_id, $class_id);
        if ($res === 'success') {
            $msg = "Student enrolled successfully!";
        } elseif ($res === 'already_enrolled') {
            $error = "Student is already actively enrolled in this class.";
        } else {
            $error = "No available slots remaining.";
        }
    }
}
?>

<h2>Enroll Existing Student</h2>
<?php if ($msg): ?><p style="color: green;"><?= htmlspecialchars($msg) ?></p><?php endif; ?>
<?php if ($error): ?><p style="color: red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>

<form method="POST">
    <label>Select Student:</label><br>
    <select name="student_id" required>
        <option value="">-- Choose Student --</option>
        <?php foreach ($students as $s): ?>
            <option value="<?= $s['student_id'] ?>"><?= htmlspecialchars($s['full_name']) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Select Class Section:</label><br>
    <select name="class_id" required>
        <option value="">-- Choose Class Section --</option>
        <?php foreach ($classList as $c): ?>
            <option value="<?= $c['class_id'] ?>">
                <?= htmlspecialchars($c['course_name']) ?> (<?= htmlspecialchars($c['class_code']) ?>) - Slots: <?= $c['slots'] ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">Enroll</button>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>