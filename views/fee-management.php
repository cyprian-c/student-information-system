<?php
require_once '../includes/header.php';
require_once '../models/Fee.php';

$fee = new Fee();
$academicYear = $_GET['academic_year'] ?? '2024-2025';
$class = $_GET['class'] ?? '';
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$filters = [
    'academic_year' => $academicYear,
    'class' => $class,
    'status' => $status,
    'search' => $search
];

$studentFees = $fee->getAllStudentFees($filters);
$statistics = $fee->getFeeStatistics($academicYear);
?>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="bi bi-cash-coin"></i> Fee Management</h2>
            <p class="text-muted">Manage student fees and payments</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <h6 class="text-muted">Total Expected</h6>
                    <h3 class="text-primary">KSh <?php echo number_format($statistics['total_expected'] ?? 0, 2); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <h6 class="text-muted">Total Collected</h6>
                    <h3 class="text-success">KSh <?php echo number_format($statistics['total_collected'] ?? 0, 2); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body">
                    <h6 class="text-muted">Outstanding</h6>
                    <h3 class="text-warning">KSh <?php echo number_format($statistics['total_outstanding'] ?? 0, 2); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body">
                    <h6 class="text-muted">Fully Paid</h6>
                    <h3 class="text-info"><?php echo $statistics['fully_paid_count'] ?? 0; ?> Students</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search student..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-2">
                    <select name="class" class="form-select">
                        <option value="">All Classes</option>
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="Grade <?php echo $i; ?>" <?php echo $class == "Grade $i" ? 'selected' : ''; ?>>Grade <?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="paid" <?php echo $status == 'paid' ? 'selected' : ''; ?>>Paid</option>
                        <option value="partial" <?php echo $status == 'partial' ? 'selected' : ''; ?>>Partial</option>
                        <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="overdue" <?php echo $status == 'overdue' ? 'selected' : ''; ?>>Overdue</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="academic_year" class="form-select">
                        <option value="2024-2025" selected>2024-2025</option>
                        <option value="2023-2024">2023-2024</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="fee-management.php" class="btn btn-secondary">
                        <i class="bi bi-x"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Fee Table -->
    <div class="card">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Student Fee Records</h5>
                <a href="fee-structure.php" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-gear"></i> Manage Fee Structure
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Class</th>
                            <th>Total Fee</th>
                            <th>Paid</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($studentFees)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">No records found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($studentFees as $sf): ?>
                                <tr>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($sf['student_id']); ?></span></td>
                                    <td><?php echo htmlspecialchars($sf['first_name'] . ' ' . $sf['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($sf['class']); ?></td>
                                    <td>KSh <?php echo number_format($sf['total_fee'], 2); ?></td>
                                    <td>KSh <?php echo number_format($sf['amount_paid'], 2); ?></td>
                                    <td>KSh <?php echo number_format($sf['balance'], 2); ?></td>
                                    <td>
                                        <?php
                                        $statusClass = [
                                            'paid' => 'success',
                                            'partial' => 'warning',
                                            'pending' => 'secondary',
                                            'overdue' => 'danger'
                                        ];
                                        ?>
                                        <span class="badge bg-<?php echo $statusClass[$sf['status']] ?? 'secondary'; ?>">
                                            <?php echo ucfirst($sf['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($sf['due_date'])); ?></td>
                                    <td>
                                        <a href="fee-payment.php?student_id=<?php echo $sf['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-cash"></i> Payment
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

<?php require_once '../includes/footer.php'; ?>