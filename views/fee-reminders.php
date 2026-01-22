<?php
require_once '../includes/header.php';
require_once '../models/Fee.php';
require_once '../models/Student.php';
require_once '../models/SMSService.php';

$fee = new Fee();
$student = new Student();
$sms = new SMSService();

// Get all students with outstanding fees
$filters = ['academic_year' => '2024-2025', 'status' => 'partial'];
$studentsWithBalance = $fee->getAllStudentFees($filters);

// Add pending status
$filters['status'] = 'pending';
$pendingStudents = $fee->getAllStudentFees($filters);
$studentsWithBalance = array_merge($studentsWithBalance, $pendingStudents);

// Handle send reminders
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_reminders'])) {
    $selectedStudents = $_POST['students'] ?? [];
    $sentCount = 0;

    foreach ($selectedStudents as $studentId) {
        $studentData = $student->readOne($studentId);
        $feeData = $fee->getStudentFee($studentId, '2024-2025');

        if ($studentData && $feeData && $feeData['balance'] > 0) {
            $result = $sms->sendBalanceReminder(
                $studentData['parent_phone'],
                $studentData['first_name'] . ' ' . $studentData['last_name'],
                $feeData['total_fee'],
                $feeData['amount_paid'],
                $feeData['balance'],
                $feeData['due_date']
            );

            if ($result['status'] !== 'error') {
                $sentCount++;
            }
        }
    }

    $_SESSION['success'] = "Fee reminders sent to {$sentCount} parent(s)!";
    header('Location: fee-reminders.php');
    exit;
}
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="bi bi-bell"></i> Fee Balance Reminders</h2>
            <p class="text-muted">Send SMS reminders to parents with outstanding fees</p>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success'];
                                            unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">Students with Outstanding Fees</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">
                                    <input type="checkbox" id="selectAll" onclick="toggleAll(this)">
                                </th>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Class</th>
                                <th>Parent Phone</th>
                                <th>Total Fee</th>
                                <th>Paid</th>
                                <th>Balance</th>
                                <th>Due Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($studentsWithBalance)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        No students with outstanding fees
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($studentsWithBalance as $s): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="students[]" value="<?php echo $s['id']; ?>" class="student-checkbox">
                                        </td>
                                        <td><?php echo htmlspecialchars($s['student_id']); ?></td>
                                        <td><?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($s['class']); ?></td>
                                        <td><?php echo htmlspecialchars($s['parent_phone'] ?? 'N/A'); ?></td>
                                        <td>KSh <?php echo number_format($s['total_fee'], 2); ?></td>
                                        <td>KSh <?php echo number_format($s['amount_paid'], 2); ?></td>
                                        <td class="text-danger"><strong>KSh <?php echo number_format($s['balance'], 2); ?></strong></td>
                                        <td><?php echo date('d/m/Y', strtotime($s['due_date'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($studentsWithBalance)): ?>
                    <div class="mt-3">
                        <button type="submit" name="send_reminders" class="btn btn-warning" onclick="return confirm('Send fee reminders to selected parents?')">
                            <i class="bi bi-send"></i> Send SMS Reminders to Selected
                        </button>
                        <span class="text-muted ms-3">
                            <small>Cost: ~KSh 0.80 per SMS</small>
                        </span>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleAll(checkbox) {
        const checkboxes = document.querySelectorAll('.student-checkbox');
        checkboxes.forEach(cb => cb.checked = checkbox.checked);
    }
</script>

<?php require_once '../includes/footer.php'; ?>