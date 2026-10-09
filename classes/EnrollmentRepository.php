<?php
// classes/EnrollmentRepository.php
class EnrollmentRepository {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function recordStudent($full_name, $email, $phone, $class_id) {
        try {
            $this->db->beginTransaction();

            // 1. Check available slots
            $stmt = $this->db->prepare("SELECT slots FROM classes WHERE class_id = :class_id FOR UPDATE");
            $stmt->execute([':class_id' => $class_id]);
            $cls = $stmt->fetch();

            if (!$cls || $cls['slots'] <= 0) {
                $this->db->rollBack();
                return false;
            }

            // 2. Insert Student
            $stmt = $this->db->prepare("INSERT INTO students (full_name, email, phone) VALUES (:name, :email, :phone)");
            $stmt->execute([
                ':name'  => $full_name,
                ':email' => $email,
                ':phone' => $phone
            ]);
            $student_id = $this->db->lastInsertId();

            // 3. Insert Enrollment
            $stmt = $this->db->prepare("INSERT INTO enrollments (student_id, class_id) VALUES (:student_id, :class_id)");
            $stmt->execute([
                ':student_id' => $student_id,
                ':class_id'   => $class_id
            ]);

            // 4. Decrement class slots
            $stmt = $this->db->prepare("UPDATE classes SET slots = slots - 1 WHERE class_id = :class_id");
            $stmt->execute([':class_id' => $class_id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function enroll($student_id, $class_id) {
        try {
            $this->db->beginTransaction();

            // Check if student is already enrolled in this class
            $stmt = $this->db->prepare("SELECT enrollment_id FROM enrollments WHERE student_id = :student_id AND class_id = :class_id AND status = 'active'");
            $stmt->execute([':student_id' => $student_id, ':class_id' => $class_id]);
            if ($stmt->fetch()) {
                $this->db->rollBack();
                return "already_enrolled";
            }

            // Check available slots
            $stmt = $this->db->prepare("SELECT slots FROM classes WHERE class_id = :class_id FOR UPDATE");
            $stmt->execute([':class_id' => $class_id]);
            $cls = $stmt->fetch();

            if (!$cls || $cls['slots'] <= 0) {
                $this->db->rollBack();
                return "no_slots";
            }

            // Insert enrollment
            $stmt = $this->db->prepare("INSERT INTO enrollments (student_id, class_id) VALUES (:student_id, :class_id)");
            $stmt->execute([':student_id' => $student_id, ':class_id' => $class_id]);

            // Decrement slots
            $stmt = $this->db->prepare("UPDATE classes SET slots = slots - 1 WHERE class_id = :class_id");
            $stmt->execute([':class_id' => $class_id]);

            $this->db->commit();
            return "success";
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function cancel($enrollment_id) {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("SELECT class_id, status FROM enrollments WHERE enrollment_id = :id FOR UPDATE");
            $stmt->execute([':id' => $enrollment_id]);
            $enrollment = $stmt->fetch();

            if (!$enrollment || $enrollment['status'] === 'cancelled') {
                $this->db->rollBack();
                return false;
            }

            // Update status to cancelled
            $stmt = $this->db->prepare("UPDATE enrollments SET status = 'cancelled' WHERE enrollment_id = :id");
            $stmt->execute([':id' => $enrollment_id]);

            // Restore slot
            $stmt = $this->db->prepare("UPDATE classes SET slots = slots + 1 WHERE class_id = :class_id");
            $stmt->execute([':class_id' => $enrollment['class_id']]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function update($enrollment_id, $new_class_id, $new_status) {
        try {
            $this->db->beginTransaction();

            // Fetch current enrollment details
            $stmt = $this->db->prepare("SELECT class_id, status FROM enrollments WHERE enrollment_id = :id FOR UPDATE");
            $stmt->execute([':id' => $enrollment_id]);
            $current = $stmt->fetch();

            if (!$current) {
                $this->db->rollBack();
                return false;
            }

            $old_class_id = $current['class_id'];
            $old_status   = $current['status'];

            // Handle slot adjustments if class section changed
            if ($old_class_id != $new_class_id && $old_status === 'active') {
                // Restore slot to old class
                $stmt = $this->db->prepare("UPDATE classes SET slots = slots + 1 WHERE class_id = :id");
                $stmt->execute([':id' => $old_class_id]);

                // Check and decrement slot from new class if active
                if ($new_status === 'active') {
                    $stmt = $this->db->prepare("SELECT slots FROM classes WHERE class_id = :id FOR UPDATE");
                    $stmt->execute([':id' => $new_class_id]);
                    $cls = $stmt->fetch();

                    if (!$cls || $cls['slots'] <= 0) {
                        $this->db->rollBack();
                        return false;
                    }

                    $stmt = $this->db->prepare("UPDATE classes SET slots = slots - 1 WHERE class_id = :id");
                    $stmt->execute([':id' => $new_class_id]);
                }
            } elseif ($old_status !== $new_status) {
                // Handle status toggling (Active <-> Cancelled)
                if ($new_status === 'cancelled' && $old_status === 'active') {
                    $stmt = $this->db->prepare("UPDATE classes SET slots = slots + 1 WHERE class_id = :id");
                    $stmt->execute([':id' => $new_class_id]);
                } elseif ($new_status === 'active' && $old_status === 'cancelled') {
                    $stmt = $this->db->prepare("SELECT slots FROM classes WHERE class_id = :id FOR UPDATE");
                    $stmt->execute([':id' => $new_class_id]);
                    $cls = $stmt->fetch();

                    if (!$cls || $cls['slots'] <= 0) {
                        $this->db->rollBack();
                        return false;
                    }

                    $stmt = $this->db->prepare("UPDATE classes SET slots = slots - 1 WHERE class_id = :id");
                    $stmt->execute([':id' => $new_class_id]);
                }
            }

            // Update enrollment row
            $stmt = $this->db->prepare("UPDATE enrollments SET class_id = :class_id, status = :status WHERE enrollment_id = :id");
            $stmt->execute([
                ':class_id' => $new_class_id,
                ':status'   => $new_status,
                ':id'       => $enrollment_id
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function allWithDetails() {
        $sql = "SELECT e.enrollment_id, e.enrollment_date, e.status, e.class_id,
                       s.full_name, s.email, s.phone,
                       c.class_code, c.schedule, c.instructor,
                       co.course_name, co.course_code
                FROM enrollments e
                JOIN students s ON e.student_id = s.student_id
                JOIN classes c ON e.class_id = c.class_id
                JOIN courses co ON c.course_id = co.course_id
                ORDER BY e.enrollment_id DESC";
        return $this->db->query($sql)->fetchAll();
    }
}