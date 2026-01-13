<?php
require_once __DIR__ . '/../config/pdo.php';

class Academic
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = getPDO();
    }

    // ==================== SUBJECT METHODS ====================

    public function getAllSubjects($class = null)
    {
        $sql = "SELECT * FROM subjects WHERE status = 'active'";
        $params = [];

        if ($class) {
            $sql .= " AND class = :class";
            $params['class'] = $class;
        }

        $sql .= " ORDER BY class, subject_name";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function createSubject($data)
    {
        $sql = "INSERT INTO subjects (subject_code, subject_name, class, description, status)
                VALUES (:subject_code, :subject_name, :class, :description, :status)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    // ==================== EXAM TYPE METHODS ====================

    public function getAllExamTypes($academicYear = null)
    {
        $sql = "SELECT * FROM exam_types WHERE 1=1";
        $params = [];

        if ($academicYear) {
            $sql .= " AND academic_year = :academic_year";
            $params['academic_year'] = $academicYear;
        }

        $sql .= " ORDER BY academic_year DESC, term, start_date";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function createExamType($data)
    {
        $sql = "INSERT INTO exam_types 
                (exam_name, exam_code, academic_year, term, max_marks, passing_marks, 
                 weightage, start_date, end_date, status)
                VALUES 
                (:exam_name, :exam_code, :academic_year, :term, :max_marks, :passing_marks,
                 :weightage, :start_date, :end_date, :status)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    // ==================== GRADE METHODS ====================

    public function recordGrade($data)
    {
        $sql = "INSERT INTO student_grades 
                (student_id, subject_id, exam_type_id, academic_year, term, 
                 marks_obtained, max_marks, remarks, recorded_by)
                VALUES 
                (:student_id, :subject_id, :exam_type_id, :academic_year, :term,
                 :marks_obtained, :max_marks, :remarks, :recorded_by)
                ON DUPLICATE KEY UPDATE
                marks_obtained = :marks_obtained, max_marks = :max_marks, 
                remarks = :remarks, recorded_by = :recorded_by";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    public function getStudentGrades($studentId, $academicYear = null, $term = null)
    {
        $sql = "SELECT sg.*, s.subject_name, s.subject_code, et.exam_name, et.term,
                       gs.description as grade_description, gs.grade_point
                FROM student_grades sg
                JOIN subjects s ON sg.subject_id = s.id
                JOIN exam_types et ON sg.exam_type_id = et.id
                LEFT JOIN grade_scales gs ON sg.grade = gs.grade
                WHERE sg.student_id = :student_id";
        $params = ['student_id' => $studentId];

        if ($academicYear) {
            $sql .= " AND sg.academic_year = :academic_year";
            $params['academic_year'] = $academicYear;
        }

        if ($term) {
            $sql .= " AND sg.term = :term";
            $params['term'] = $term;
        }

        $sql .= " ORDER BY et.term, s.subject_name";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getClassGrades($class, $examTypeId)
    {
        $sql = "SELECT s.id, s.student_id, s.first_name, s.last_name,
                       sg.subject_id, sub.subject_name, sg.marks_obtained, 
                       sg.max_marks, sg.percentage, sg.grade
                FROM students s
                LEFT JOIN student_grades sg ON s.id = sg.student_id AND sg.exam_type_id = :exam_type_id
                LEFT JOIN subjects sub ON sg.subject_id = sub.id
                WHERE s.class = :class AND s.status = 'active'
                ORDER BY s.last_name, s.first_name, sub.subject_name";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['class' => $class, 'exam_type_id' => $examTypeId]);
        return $stmt->fetchAll();
    }

    // ==================== REPORT CARD METHODS ====================

    public function getStudentReportCard($studentId, $academicYear, $term)
    {
        // Get student info
        $studentSql = "SELECT * FROM students WHERE id = :id";
        $stmt = $this->pdo->prepare($studentSql);
        $stmt->execute(['id' => $studentId]);
        $student = $stmt->fetch();

        // Get grades
        $grades = $this->getStudentGrades($studentId, $academicYear, $term);

        // Calculate GPA and rank
        $gpaSql = "SELECT average_percentage, gpa, subjects_taken
                   FROM view_student_gpa
                   WHERE student_id = :student_id 
                   AND academic_year = :academic_year 
                   AND term = :term";
        $stmt = $this->pdo->prepare($gpaSql);
        $stmt->execute([
            'student_id' => $studentId,
            'academic_year' => $academicYear,
            'term' => $term
        ]);
        $performance = $stmt->fetch();

        // Get attendance
        $attendanceSql = "SELECT days_present, days_absent, attendance_percentage
                          FROM view_attendance_summary
                          WHERE student_id = :student_id 
                          AND year = YEAR(CURRENT_DATE)";
        $stmt = $this->pdo->prepare($attendanceSql);
        $stmt->execute(['student_id' => $studentId]);
        $attendance = $stmt->fetch();

        return [
            'student' => $student,
            'grades' => $grades,
            'performance' => $performance,
            'attendance' => $attendance
        ];
    }

    // ==================== ATTENDANCE METHODS ====================

    public function recordAttendance($data)
    {
        $sql = "INSERT INTO student_attendance 
                (student_id, attendance_date, status, subject_id, remarks, recorded_by)
                VALUES 
                (:student_id, :attendance_date, :status, :subject_id, :remarks, :recorded_by)
                ON DUPLICATE KEY UPDATE
                status = :status, remarks = :remarks, recorded_by = :recorded_by";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    public function getAttendanceByDate($date, $class = null)
    {
        $sql = "SELECT s.id, s.student_id, s.first_name, s.last_name, s.class,
                       sa.status, sa.remarks
                FROM students s
                LEFT JOIN student_attendance sa ON s.id = sa.student_id AND sa.attendance_date = :date
                WHERE s.status = 'active'";
        $params = ['date' => $date];

        if ($class) {
            $sql .= " AND s.class = :class";
            $params['class'] = $class;
        }

        $sql .= " ORDER BY s.class, s.last_name, s.first_name";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getStudentAttendanceSummary($studentId, $startDate, $endDate)
    {
        $sql = "SELECT 
                    COUNT(*) as total_days,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                    SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days,
                    ROUND((SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as attendance_percentage
                FROM student_attendance
                WHERE student_id = :student_id 
                AND attendance_date BETWEEN :start_date AND :end_date";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'student_id' => $studentId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        return $stmt->fetch();
    }
}
