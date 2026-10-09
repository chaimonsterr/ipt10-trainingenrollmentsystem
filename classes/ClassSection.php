<?php
// classes/ClassSection.php
class ClassSection {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function allWithCourse() {
        $sql = "SELECT c.*, co.course_name, co.course_code 
                FROM classes c 
                JOIN courses co ON c.course_id = co.course_id 
                ORDER BY c.class_code ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM classes WHERE class_id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function create($course_id, $code, $schedule, $instructor, $slots) {
        $stmt = $this->db->prepare("INSERT INTO classes (course_id, class_code, schedule, instructor, slots) VALUES (:course_id, :code, :schedule, :instructor, :slots)");
        return $stmt->execute([
            ':course_id' => $course_id,
            ':code'      => $code,
            ':schedule'  => $schedule,
            ':instructor'=> $instructor,
            ':slots'     => $slots
        ]);
    }

    public function update($id, $course_id, $code, $schedule, $instructor, $slots) {
        $stmt = $this->db->prepare("UPDATE classes SET course_id = :course_id, class_code = :code, schedule = :schedule, instructor = :instructor, slots = :slots WHERE class_id = :id");
        return $stmt->execute([
            ':course_id' => $course_id,
            ':code'      => $code,
            ':schedule'  => $schedule,
            ':instructor'=> $instructor,
            ':slots'     => $slots,
            ':id'        => $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM classes WHERE class_id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function getSlots($class_id) {
        $stmt = $this->db->prepare("SELECT slots FROM classes WHERE class_id = :class_id");
        $stmt->execute([':class_id' => $class_id]);
        $res = $stmt->fetch();
        return $res ? (int)$res['slots'] : 0;
    }
}