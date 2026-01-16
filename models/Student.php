<?php
require_once __DIR__ . '/../config/pdo.php';

class Student
{
    private $pdo;
    private $table = 'students';

    public function __construct()
    {
        $this->pdo = getPDO();
    }

    // Create new student
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (student_id, first_name, last_name, gender, date_of_birth, class,parent_name, parent_phone, address, enrollment_date, status) 
                VALUES 
                (:student_id, :first_name, :last_name, :gender, :date_of_birth, 
                 :class, :parent_name, :parent_phone, :address, :enrollment_date, :status)";
        $stmt = $this->pdo->prepare($sql);

        // Generate unique student ID
        $data['student_id'] = $this->generateStudentId();

        return $stmt->execute($data);
    }

    // Read all students with optional filters
    public function readAll($search = '', $class = '', $status = '')
    {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (first_name LIKE :search OR last_name LIKE :search OR student_id LIKE :search)";
            $params['search'] = "%{$search}%";
        }

        if (!empty($class)) {
            $sql .= " AND class = :class";
            $params['class'] = $class;
        }

        if (!empty($status)) {
            $sql .= " AND status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Read single student
    public function readOne($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    // Update student
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} 
                SET first_name = :first_name,
                    last_name = :last_name,
                    gender = :gender,
                    date_of_birth = :date_of_birth,
                    class = :class,
                    parent_name = :parent_name,
                    parent_phone = :parent_phone,
                    address = :address,
                    enrollment_date = :enrollment_date,
                    status = :status
                WHERE id = :id";

        $data['id'] = $id;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    // Delete student
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    // Get statistics
    public function getStats()
    {
        $stats = [];

        // Total students
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM {$this->table}");
        $stats['total'] = $stmt->fetch()['total'];

        // Active students
        $stmt = $this->pdo->query("SELECT COUNT(*) as active FROM {$this->table} WHERE status = 'active'");
        $stats['active'] = $stmt->fetch()['active'];

        // Inactive students
        $stmt = $this->pdo->query("SELECT COUNT(*) as inactive FROM {$this->table} WHERE status = 'inactive'");
        $stats['inactive'] = $stmt->fetch()['inactive'];

        // Total classes
        $stmt = $this->pdo->query("SELECT COUNT(DISTINCT class) as classes FROM {$this->table}");
        $stats['classes'] = $stmt->fetch()['classes'];

        return $stats;
    }

    // Get all unique classes
    public function getClasses()
    {
        $sql = "SELECT DISTINCT class FROM {$this->table} ORDER BY class";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Generate unique student ID
    private function generateStudentId()
    {
        $year = date('Y');
        $db = $this->pdo;
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("SELECT last_sequence FROM student_sequences WHERE year = ? FOR UPDATE");
            $stmt->execute([$year]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $nextSequence = $result['last_sequence'] + 1;
                $update = $db->prepare("UPDATE student_sequences SET last_sequence = ? WHERE year = ?");
                $update->execute([$nextSequence, $year]);
            } else {
                $nextSequence = 1;
                $insert = $db->prepare("INSERT INTO student_sequences (year, last_sequence) VALUES (?, ?)");
                $insert->execute([$year, $nextSequence]);
            }
            $db->commit();
            return "SMA{$year}" . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
