<?php
require_once __DIR__ . '/../config/pdo.php';

class Fee {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getPDO();
    }
    
    // ==================== FEE STRUCTURE METHODS ====================
    
    /**
     * Get fee structure for a class and academic year
     */
    public function getFeeStructure($class, $academicYear) {
        $sql = "SELECT * FROM fee_structures 
                WHERE class = :class AND academic_year = :academic_year AND status = 'active' 
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['class' => $class, 'academic_year' => $academicYear]);
        return $stmt->fetch();
    }
    
    /**
     * Get all fee structures
     */
    public function getAllFeeStructures($academicYear = null) {
        $sql = "SELECT * FROM fee_structures WHERE status = 'active'";
        $params = [];
        
        if ($academicYear) {
            $sql .= " AND academic_year = :academic_year";
            $params['academic_year'] = $academicYear;
        }
        
        $sql .= " ORDER BY class, academic_year";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Create or update fee structure
     */
    public function saveFeeStructure($data) {
        $sql = "INSERT INTO fee_structures 
                (class, academic_year, tuition_fee, library_fee, lab_fee, sports_fee, 
                 transport_fee, exam_fee, other_fee, status)
                VALUES 
                (:class, :academic_year, :tuition_fee, :library_fee, :lab_fee, :sports_fee,
                 :transport_fee, :exam_fee, :other_fee, :status)
                ON DUPLICATE KEY UPDATE
                tuition_fee = :tuition_fee, library_fee = :library_fee, lab_fee = :lab_fee,
                sports_fee = :sports_fee, transport_fee = :transport_fee, exam_fee = :exam_fee,
                other_fee = :other_fee, status = :status";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }
    
    // ==================== STUDENT FEE METHODS ====================
    
    /**
     * Assign fee to student
     */
    public function assignFeeToStudent($studentId, $academicYear) {
        try {
            // Get student's class
            $studentSql = "SELECT class FROM students WHERE id = :id";
            $stmt = $this->pdo->prepare($studentSql);
            $stmt->execute(['id' => $studentId]);
            $student = $stmt->fetch();
            
            if (!$student) {
                return false;
            }
            
            // Get fee structure
            $feeStructure = $this->getFeeStructure($student['class'], $academicYear);
            
            if (!$feeStructure) {
                return false;
            }
            
            // Check if already assigned
            $checkSql = "SELECT id FROM student_fees 
                        WHERE student_id = :student_id AND academic_year = :academic_year";
            $stmt = $this->pdo->prepare($checkSql);
            $stmt->execute(['student_id' => $studentId, 'academic_year' => $academicYear]);
            
            if ($stmt->fetch()) {
                return true; // Already assigned
            }
            
            // Assign fee
            $sql = "INSERT INTO student_fees 
                    (student_id, academic_year, fee_structure_id, total_fee, due_date)
                    VALUES 
                    (:student_id, :academic_year, :fee_structure_id, :total_fee, :due_date)";
            
            $dueDate = date('Y-m-d', strtotime('+30 days'));
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'student_id' => $studentId,
                'academic_year' => $academicYear,
                'fee_structure_id' => $feeStructure['id'],
                'total_fee' => $feeStructure['total_fee'],
                'due_date' => $dueDate
            ]);
        } catch (PDOException $e) {
            error_log("Error assigning fee: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get student fee details
     */
    public function getStudentFee($studentId, $academicYear) {
        $sql = "SELECT sf.*, fs.* 
                FROM student_fees sf
                JOIN fee_structures fs ON sf.fee_structure_id = fs.id
                WHERE sf.student_id = :student_id AND sf.academic_year = :academic_year
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['student_id' => $studentId, 'academic_year' => $academicYear]);
        return $stmt->fetch();
    }
    
    /**
     * Get all student fees with student details
     */
    public function getAllStudentFees($filters = []) {
        $sql = "SELECT 
                    s.id, s.student_id, s.first_name, s.last_name, s.class,
                    sf.academic_year, sf.total_fee, sf.amount_paid, sf.balance, 
                    sf.status, sf.due_date
                FROM students s
                JOIN student_fees sf ON s.id = sf.student_id
                WHERE 1=1";
        $params = [];
        
        if (!empty($filters['academic_year'])) {
            $sql .= " AND sf.academic_year = :academic_year";
            $params['academic_year'] = $filters['academic_year'];
        }
        
        if (!empty($filters['class'])) {
            $sql .= " AND s.class = :class";
            $params['class'] = $filters['class'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND sf.status = :status";
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (s.first_name LIKE :search OR s.last_name LIKE :search OR s.student_id LIKE :search)";
            $params['search'] = "%{$filters['search']}%";
        }
        
        $sql .= " ORDER BY s.class, s.last_name, s.first_name";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // ==================== PAYMENT METHODS ====================
    
    /**
     * Record fee payment
     */
    public function recordPayment($data) {
        try {
            $this->pdo->beginTransaction();
            
            // Generate receipt number
            $receiptNumber = $this->generateReceiptNumber();
            
            $sql = "INSERT INTO fee_payments 
                    (student_fee_id, student_id, payment_date, amount, payment_method,
                     transaction_id, receipt_number, payment_for, remarks, recorded_by)
                    VALUES 
                    (:student_fee_id, :student_id, :payment_date, :amount, :payment_method,
                     :transaction_id, :receipt_number, :payment_for, :remarks, :recorded_by)";
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                'student_fee_id' => $data['student_fee_id'],
                'student_id' => $data['student_id'],
                'payment_date' => $data['payment_date'],
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'transaction_id' => $data['transaction_id'] ?? null,
                'receipt_number' => $receiptNumber,
                'payment_for' => $data['payment_for'] ?? 'School Fees',
                'remarks' => $data['remarks'] ?? null,
                'recorded_by' => $data['recorded_by']
            ]);
            
            $this->pdo->commit();
            return $receiptNumber;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error recording payment: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get payment history for a student
     */
    public function getPaymentHistory($studentId, $academicYear = null) {
        $sql = "SELECT fp.*, sf.academic_year, CONCAT(au.username) as recorded_by_name
                FROM fee_payments fp
                JOIN student_fees sf ON fp.student_fee_id = sf.id
                LEFT JOIN admin_users au ON fp.recorded_by = au.id
                WHERE fp.student_id = :student_id";
        $params = ['student_id' => $studentId];
        
        if ($academicYear) {
            $sql .= " AND sf.academic_year = :academic_year";
            $params['academic_year'] = $academicYear;
        }
        
        $sql .= " ORDER BY fp.payment_date DESC, fp.created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Generate unique receipt number
     */
    private function generateReceiptNumber() {
        $year = date('Y');
        $prefix = "RCP{$year}";
        
        $sql = "SELECT MAX(CAST(SUBSTRING(receipt_number, 8) AS UNSIGNED)) as max_num 
                FROM fee_payments 
                WHERE receipt_number LIKE :prefix";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['prefix' => $prefix . '%']);
        $result = $stmt->fetch();
        
        $nextNum = ($result['max_num'] ?? 0) + 1;
        return $prefix . str_pad($nextNum, 6, '0', STR_PAD_LEFT);
    }
    
    // ==================== STATISTICS METHODS ====================
    
    /**
     * Get fee collection statistics
     */
    public function getFeeStatistics($academicYear) {
        $sql = "SELECT 
                    COUNT(DISTINCT sf.student_id) as total_students,