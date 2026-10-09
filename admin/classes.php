<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/Course.php';
require_once __DIR__ . '/../classes/ClassSection.php';
require_once __DIR__ . '/../includes/header.php';

$classObj = new ClassSection($db);
$courseObj = new Course($db);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $course_id  = (int)($_POST['course_id'] ?? 0);
        $class_code = trim($_POST['class_code'] ?? '');
        $schedule   = trim($_POST['schedule'] ?? '');
        $instructor = trim($_POST['instructor'] ?? '');
        $slots      = (int)($_POST['slots'] ?? 0);

        if ($course_id && $class_code) {
            $classObj->create($course_id, $class_code, $schedule, $instructor, $slots);
            $message = "Class section created successfully!";
        }
    } elseif ($_POST['action'] === 'edit') {
        $class_id   = (int)($_POST['class_id'] ?? 0);
        $course_id  = (int)($_POST['course_id'] ?? 0);
        $class_code = trim($_POST['class_code'] ?? '');
        $schedule   = trim($_POST['schedule'] ?? '');
        $instructor = trim($_POST['instructor'] ?? '');
        $slots      = (int)($_POST['slots'] ?? 0);

        if ($class_id && $course_id && $class_code) {
            $classObj->update($class_id, $course_id, $class_code, $schedule, $instructor, $slots);
            $message = "Class section updated successfully!";
        }
    } elseif ($_POST['action'] === 'delete') {
        $class_id = (int)($_POST['class_id'] ?? 0);
        $classObj->delete($class_id);
        $message = "Class section deleted successfully!";
    }
}

$classesList = $classObj->allWithCourse();
$courses = $courseObj->all();
?>

<h2>Manage Class Sections</h2>
<?php if ($message): ?><p style="color: green;"><?= htmlspecialchars($message) ?></p><?php endif; ?>

<!-- Form to Add Class Section -->
<form method="POST" style="margin-bottom: 24px;">
    <input type="hidden" name="action" value="add">
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <select name="course_id" required style="flex:1;">
            <option value="">Select Course</option>
            <?php foreach ($courses as $c): ?>
                <option value="<?= $c['course_id'] ?>"><?= htmlspecialchars($c['course_name']) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="class_code" placeholder="Class Code (e.g. SEC-A)" required style="flex:1;">
        <input type="text" name="schedule" placeholder="Schedule (e.g. Mon/Wed 9AM)" style="flex:1;">
        <input type="text" name="instructor" placeholder="Instructor" style="flex:1;">
        <input type="number" name="slots" placeholder="Slots" min="0" required style="width:100px;">
        <button type="submit">Add Class</button>
    </div>
</form>

<!-- Class Sections Table -->
<table>
    <thead>
        <tr>
            <th>ID</th><th>Course</th><th>Class Code</th><th>Schedule</th><th>Instructor</th><th>Slots</th><th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($classesList as $cl): ?>
            <tr>
                <td><?= $cl['class_id'] ?></td>
                <td><?= htmlspecialchars($cl['course_name']) ?></td>
                <td><strong><?= htmlspecialchars($cl['class_code']) ?></strong></td>
                <td><?= htmlspecialchars($cl['schedule']) ?></td>
                <td><?= htmlspecialchars($cl['instructor']) ?></td>
                <td><strong><?= $cl['slots'] ?></strong></td>
                <td>
                    <button type="button" style="background:#0284c7; margin-right:4px;" 
                        onclick="openEditClassModal(<?= $cl['class_id'] ?>, <?= $cl['course_id'] ?>, '<?= htmlspecialchars($cl['class_code'], ENT_QUOTES) ?>', '<?= htmlspecialchars($cl['schedule'], ENT_QUOTES) ?>', '<?= htmlspecialchars($cl['instructor'], ENT_QUOTES) ?>', <?= $cl['slots'] ?>)">
                        Edit
                    </button>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="class_id" value="<?= $cl['class_id'] ?>">
                        <button type="submit" onclick="return confirm('Delete this class section?')">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Edit Class Modal Popup -->
<div id="editClassModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:200;">
    <div style="background:#fff; padding:24px; border-radius:8px; width:420px; max-width:90%;">
        <h3>Edit Class Section</h3>
        <form method="POST" style="margin-top:15px;">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="class_id" id="edit_class_id">
            
            <label>Course:</label>
            <select name="course_id" id="edit_course_id" required>
                <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['course_id'] ?>"><?= htmlspecialchars($c['course_name']) ?></option>
                <?php endforeach; ?>
            </select>
            
            <label>Class Code:</label>
            <input type="text" name="class_code" id="edit_class_code" required>
            
            <label>Schedule:</label>
            <input type="text" name="schedule" id="edit_schedule">

            <label>Instructor:</label>
            <input type="text" name="instructor" id="edit_instructor">

            <label>Available Slots:</label>
            <input type="number" name="slots" id="edit_slots" min="0" required>
            
            <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:15px;">
                <button type="button" style="background:#64748b;" onclick="closeEditClassModal()">Cancel</button>
                <button type="submit">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditClassModal(id, courseId, code, schedule, instructor, slots) {
    document.getElementById('edit_class_id').value = id;
    document.getElementById('edit_course_id').value = courseId;
    document.getElementById('edit_class_code').value = code;
    document.getElementById('edit_schedule').value = schedule;
    document.getElementById('edit_instructor').value = instructor;
    document.getElementById('edit_slots').value = slots;
    document.getElementById('editClassModal').style.display = 'flex';
}
function closeEditClassModal() {
    document.getElementById('editClassModal').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>