<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/EnrollmentRepository.php';
require_once __DIR__ . '/../classes/ClassSection.php';
require_once __DIR__ . '/../includes/header.php';

$repo = new EnrollmentRepository($db);
$classObj = new ClassSection($db);
$classList = $classObj->allWithCourse();
$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'cancel') {
        $cancel_id = (int)($_POST['cancel_id'] ?? 0);
        if ($repo->cancel($cancel_id)) {
            $msg = "Enrollment cancelled and slot restored!";
        } else {
            $error = "Unable to cancel enrollment.";
        }
    } elseif ($_POST['action'] === 'edit') {
        $enrollment_id = (int)($_POST['enrollment_id'] ?? 0);
        $class_id      = (int)($_POST['class_id'] ?? 0);
        $status        = trim($_POST['status'] ?? 'active');

        if ($enrollment_id > 0 && $class_id > 0) {
            if ($repo->update($enrollment_id, $class_id, $status)) {
                $msg = "Enrollment updated successfully!";
            } else {
                $error = "Failed to update enrollment. Selected class section may be full.";
            }
        }
    }
}

$rows = $repo->allWithDetails();
?>

<h2>All Enrollments</h2>
<?php if ($msg): ?><p style="color: green;"><?= htmlspecialchars($msg) ?></p><?php endif; ?>
<?php if ($error): ?><p style="color: red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>

<table>
    <thead>
        <tr>
            <th>ID</th><th>Student</th><th>Course</th><th>Class</th><th>Date</th><th>Status</th><th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= $r['enrollment_id'] ?></td>
                <td><strong><?= htmlspecialchars($r['full_name']) ?></strong></td>
                <td><?= htmlspecialchars($r['course_name']) ?></td>
                <td><?= htmlspecialchars($r['class_code']) ?></td>
                <td><?= $r['enrollment_date'] ?></td>
                <td>
                    <span style="display:inline-block; padding:4px 8px; border-radius:4px; font-weight:600; font-size:0.8rem; background:<?= $r['status'] === 'active' ? '#dcfce7' : '#fee2e2' ?>; color:<?= $r['status'] === 'active' ? '#15803d' : '#b91c1c' ?>;">
                        <?= ucfirst($r['status']) ?>
                    </span>
                </td>
                <td style="white-space:nowrap;">
                    <button type="button" class="btn btn-sm btn-edit"
                        onclick="openEditEnrollmentModal(<?= $r['enrollment_id'] ?>, '<?= htmlspecialchars($r['full_name'], ENT_QUOTES) ?>', <?= $r['class_id'] ?? 0 ?>, '<?= $r['status'] ?>')">
                        Edit
                    </button>
                    <?php if ($r['status'] === 'active'): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="cancel">
                            <input type="hidden" name="cancel_id" value="<?= $r['enrollment_id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Cancel enrollment?')">Cancel</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Edit Enrollment Modal -->
<div id="editEnrollmentModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:200;">
    <div style="background:#fff; padding:24px; border-radius:8px; width:400px; max-width:90%;">
        <h3>Edit Enrollment Record</h3>
        <form method="POST" style="margin-top:15px;">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="enrollment_id" id="edit_enrollment_id">

            <label>Student Name:</label>
            <input type="text" id="edit_student_name" disabled style="background:#f1f5f9;">

            <label>Class Section:</label>
            <select name="class_id" id="edit_class_id" required>
                <?php foreach ($classList as $c): ?>
                    <option value="<?= $c['class_id'] ?>">
                        <?= htmlspecialchars($c['course_name']) ?> (<?= htmlspecialchars($c['class_code']) ?>) - Slots: <?= $c['slots'] ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Enrollment Status:</label>
            <select name="status" id="edit_status" required>
                <option value="active">Active</option>
                <option value="cancelled">Cancelled</option>
            </select>

            <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:15px;">
                <button type="button" class="btn btn-secondary" onclick="closeEditEnrollmentModal()">Cancel</button>
                <button type="submit" class="btn">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditEnrollmentModal(id, name, classId, status) {
    document.getElementById('edit_enrollment_id').value = id;
    document.getElementById('edit_student_name').value = name;
    document.getElementById('edit_class_id').value = classId;
    document.getElementById('edit_status').value = status;
    document.getElementById('editEnrollmentModal').style.display = 'flex';
}
function closeEditEnrollmentModal() {
    document.getElementById('editEnrollmentModal').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>