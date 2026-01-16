<?php
require_once '../includes/header.php';
require_once '../models/Fee.php';

$fee = new Fee();
$academicYear = $_GET['academic_year'] ?? '2024-2025';
$feeStructures = $fee->getAllFeeStructures($academicYear);
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="bi bi-gear"></i> Fee Structure Management</h2>
            <p class="text-muted">Configure fees for each class</p>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success'];
                                            unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Fee Structures for <?php echo $academicYear; ?></h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addFeeModal">
                    <i class="bi bi-plus"></i> Add Fee Structure
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Class</th>
                            <th>Tuition</th>
                            <th>Library</th>
                            <th>Lab</th>
                            <th>Sports</th>
                            <th>Transport</th>
                            <th>Exam</th>
                            <th>Other</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($feeStructures as $fs): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($fs['class']); ?></strong></td>
                                <td>KSh <?php echo number_format($fs['tuition_fee'], 0); ?></td>
                                <td>KSh <?php echo number_format($fs['library_fee'], 0); ?></td>
                                <td>KSh <?php echo number_format($fs['lab_fee'], 0); ?></td>
                                <td>KSh <?php echo number_format($fs['sports_fee'], 0); ?></td>
                                <td>KSh <?php echo number_format($fs['transport_fee'], 0); ?></td>
                                <td>KSh <?php echo number_format($fs['exam_fee'], 0); ?></td>
                                <td>KSh <?php echo number_format($fs['other_fee'], 0); ?></td>
                                <td><strong>KSh <?php echo number_format($fs['total_fee'], 0); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Fee Structure Modal -->
<div class="modal fade" id="addFeeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add/Update Fee Structure</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../controllers/fee_controller.php?action=save_fee_structure" method="POST">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Class *</label>
                            <select name="class" class="form-select" required>
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                    <option value="Grade <?php echo $i; ?>">Grade <?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Academic Year *</label>
                            <input type="text" name="academic_year" class="form-control" value="<?php echo $academicYear; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tuition Fee</label>
                            <input type="number" name="tuition_fee" class="form-control" step="0.01" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Library Fee</label>
                            <input type="number" name="library_fee" class="form-control" step="0.01" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Lab Fee</label>
                            <input type="number" name="lab_fee" class="form-control" step="0.01" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sports Fee</label>
                            <input type="number" name="sports_fee" class="form-control" step="0.01" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Transport Fee</label>
                            <input type="number" name="transport_fee" class="form-control" step="0.01" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Exam Fee</label>
                            <input type="number" name="exam_fee" class="form-control" step="0.01" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Other Fee</label>
                            <input type="number" name="other_fee" class="form-control" step="0.01" value="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Fee Structure</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>