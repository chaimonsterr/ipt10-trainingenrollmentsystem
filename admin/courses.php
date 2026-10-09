<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/Course.php';
require_once __DIR__ . '/../includes/header.php';

$courseObj = new Course($db);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $code = trim($_POST['course_code'] ?? '');
        $name = trim($_POST['course_name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        if ($code && $name) {
            $courseObj->create($code, $name, $desc);
            $message = "Course created successfully!";
        }
    } elseif ($_POST['action'] === 'edit') {
        $id   = (int)($_POST['course_id'] ?? 0);
        $code = trim($_POST['course_code'] ?? '');
        $name = trim($_POST['course_name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        if ($id && $code && $name) {
            $courseObj->update($id, $code, $name, $desc);
            $message = "Course updated successfully!";
        }
    } elseif ($_POST['action'] === 'delete') {
        $id = (int)($_POST['course_id'] ?? 0);
        $courseObj->delete($id);
        $message = "Course deleted successfully!";
    }
}

$courses = $courseObj->all();
?>

<h2>Manage Courses</h2>
<?php if ($message): ?><p style="color: green;"><?= htmlspecialchars($message) ?></p><?php endif; ?>

<!-- Form to Add Course -->
<form method="POST" style="margin-bottom: 24px;">
    <input type="hidden" name="action" value="add">
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <input type="text" name="course_code" placeholder="Course Code (e.g., IPT101)" required style="flex:1;">
        <input type="text" name="course_name" placeholder="Course Name" required style="flex:2;">
        <input type="text" name="description" placeholder="Description" style="flex:2;">
        <button type="submit">Add Course</button>
    </div>
</form>

<!-- Courses Table -->
<table>
    <thead>
        <tr>
            <th>ID</th><th>Code</th><th>Name</th><th>Description</th><th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($courses as $c): ?>
            <tr>
                <td><?= $c['course_id'] ?></td>
                <td><strong><?= htmlspecialchars($c['course_code']) ?></strong></td>
                <td><?= htmlspecialchars($c['course_name']) ?></td>
                <td><?= htmlspecialchars($c['description']) ?></td>
                <td>
                    <button type="button" style="background:#0284c7; margin-right:4px;" 
                        onclick="openEditModal(<?= $c['course_id'] ?>, '<?= htmlspecialchars($c['course_code'], ENT_QUOTES) ?>', '<?= htmlspecialchars($c['course_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($c['description'], ENT_QUOTES) ?>')">
                        Edit
                    </button>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="course_id" value="<?= $c['course_id'] ?>">
                        <button type="submit" onclick="return confirm('Delete this course?')">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Edit Modal Popup -->
<div id="editModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:200;">
    <div style="background:#fff; padding:24px; border-radius:8px; width:400px; max-width:90%;">
        <h3>Edit Course</h3>
        <form method="POST" style="margin-top:15px;">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="course_id" id="edit_id">
            
            <label>Course Code:</label>
            <input type="text" name="course_code" id="edit_code" required>
            
            <label>Course Name:</label>
            <input type="text" name="course_name" id="edit_name" required>
            
            <label>Description:</label>
            <input type="text" name="description" id="edit_desc">
            
            <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:15px;">
                <button type="button" style="background:#64748b;" onclick="closeEditModal()">Cancel</button>
                <button type="submit">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, code, name, desc) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_code').value = code;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_desc').value = desc;
    document.getElementById('editModal').style.display = 'flex';
}
function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>