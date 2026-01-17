<?php require_once '../includes/header.php'; ?>
<?php require_once '../models/Student.php'; ?>

<?php
$studentModel = new Student();
$stats = $studentModel->getStats();
$recentStudents = $studentModel->readAll('', '', '');
$recentStudents = array_slice($recentStudents, 0, 5);
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4"><i class="bi bi-speedometer2"></i> Dashboard</h2>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Students</p>
                            <h3 class="mb-0"><?php echo $stats['total']; ?></h3>
                        </div>
                        <i class="bi bi-people stat-icon text-primary"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Active Students</p>
                            <h3 class="mb-0 text-success"><?php echo $stats['active']; ?></h3>
                        </div>
                        <i class="bi bi-person-check stat-icon text-success"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Inactive Students</p>
                            <h3 class="mb-0 text-warning"><?php echo $stats['inactive']; ?></h3>
                        </div>
                        <i class="bi bi-person-x stat-icon text-warning"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Classes</p>
                            <h3 class="mb-0 text-info"><?php echo $stats['classes']; ?></h3>
                        </div>
                        <i class="bi bi-buildings stat-icon text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Admissions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Admissions</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recentStudents)): ?>
                        <p class="text-muted text-center py-4">No students enrolled yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Student ADM</th>
                                        <th>Name</th>
                                        <th>Class</th>
                                        <th>Parent</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                        <th>Enrollment Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentStudents as $student): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($student['student_id']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($student['class']); ?></td>
                                            <td><?php echo htmlspecialchars($student['parent_name']); ?></td>
                                            <td><?php echo htmlspecialchars($student['parent_phone']); ?></td>
                                            <td>
                                                <?php if ($student['status'] === 'active'): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($student['enrollment_date'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>