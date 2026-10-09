<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/EnrollmentRepository.php';
require_once __DIR__ . '/../classes/ClassSection.php';
require_once __DIR__ . '/../includes/header.php';

$repo = new EnrollmentRepository($db);
$classes = new ClassSection($db);
$classList = $classes->allWithCourse();
$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $class_id  = (int)($_POST['class_id'] ?? 0);

    if (empty($full_name) || $class_id <= 0) {
        $error = "Please fill in required fields.";
    } else {
        $success = $repo->recordStudent($full_name, $email, $phone, $class_id);
        if ($success) {
            $msg = "Student recorded and enrolled successfully!";
        } else {
            $error = "No slots available for this class section.";
        }
    }
}
?>

<h2>Record New Student & Enroll</h2>
<?php if ($msg): ?><p style="color: green;"><?= htmlspecialchars($msg) ?></p><?php endif; ?>
<?php if ($error): ?><p style="color: red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>

<form method="POST">
    <label>Full Name:</label><br>
    <input type="text" name="full_name" required><br>
    <label>Email:</label><br>
    <input type="email" name="email"><br>
    <label>Phone:</label><br>
    <input type="text" name="phone"><br>
    <label>Select Class:</label><br>
    <select name="class_id" required>
        <option value="">-- Choose Class Section --</option>
        <?php foreach ($classList as $c): ?>
            <option value="<?= $c['class_id'] ?>">
                <?= htmlspecialchars($c['course_name']) ?> (<?= htmlspecialchars($c['class_code']) ?>) - Slots: <?= $c['slots'] ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>
    <button type="submit">Submit Registration</button>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>