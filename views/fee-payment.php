<?php
require_once '../includes/header.php';
require_once '../models/Fee.php';
require_once '../models/Student.php';

$studentId = $_GET['student_id'] ?? null;
if (!$studentId) {
    header('Location: fee-management.php');
    exit;
}

$fee = new Fee();
$student = new Student();
$academicYear = '2024-2025';

$studentData = $student->readOne($studentId);
$feeData = $fee->getStudentFee($studentId, $academicYear);
$paymentHistory = $fee->getPaymentHistory($studentId, $academicYear);
?>

<div class="container py-4">
    <div class="row">
        <!-- Student Info -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person"></i> Student Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong><br><?php echo htmlspecialchars($studentData['first_name'] . ' ' . $studentData['last_name']); ?></p>
                    <p><strong>Student ID:</strong><br><?php echo htmlspecialchars($studentData['student_id']); ?></p>
                    <p><strong>Class:</strong><br><?php echo htmlspecialchars($studentData['class']); ?></p>
                    <p><strong>Email:</strong><br><?php echo htmlspecialchars($studentData['email']); ?></p>
                </div>
            </div>

            <!-- Fee Summary -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-cash-stack"></i> Fee Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Fee:</span>
                        <strong>KSh <?php echo number_format($feeData['total_fee'] ?? 0, 2); ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Amount Paid:</span>
                        <strong class="text-success">KSh <?php echo number_format($feeData['amount_paid'] ?? 0, 2); ?></strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span><strong>Balance:</strong></span>
                        <strong class="text-danger">KSh <?php echo number_format($feeData['balance'] ?? 0, 2); ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Form -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-cash-coin"></i> Record Payment</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?php echo $_SESSION['success'];
                                                            unset($_SESSION['success']); ?></div>
                    <?php endif; ?>

                    <form action="../controllers/fee_controller.php?action=record_payment" method="POST">
                        <input type="hidden" name="student_fee_id" value="<?php echo $feeData['id'] ?? ''; ?>">
                        <input type="hidden" name="student_id" value="<?php echo $studentId; ?>">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Payment Date *</label>
                                <input type="date" name="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Amount *</label>
                                <input type="number" name="amount" class="form-control" step="0.01" min="0" max="<?php echo $feeData['balance'] ?? 0; ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Payment Method *</label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="">Select Method</option>
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="mobile_money">Mobile Money (M-Pesa)</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="card">Card</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Transaction ID</label>
                                <input type="text" name="transaction_id" class="form-control" placeholder="Optional">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Payment For</label>
                                <input type="text" name="payment_for" class="form-control" value="School Fees - <?php echo $academicYear; ?>">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" class="form-control" rows="2"></textarea>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle"></i> Record Payment
                                </button>
                                <a href="fee-management.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Payment History -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Payment History</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Receipt No.</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($paymentHistory)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-3 text-muted">No payment history</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($paymentHistory as $payment): ?>
                                        <tr>
                                            <td><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                                            <td><span class="badge bg-info"><?php echo $payment['receipt_number']; ?></span></td>
                                            <td>KSh <?php echo number_format($payment['amount'], 2); ?></td>
                                            <td><?php echo ucwords(str_replace('_', ' ', $payment['payment_method'])); ?></td>
                                            <td>
                                                <a href="fee-receipt.php?receipt=<?php echo $payment['receipt_number']; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                    <i class="bi bi-printer"></i> Print
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>