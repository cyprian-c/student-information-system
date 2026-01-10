<?php require_once '../includes/header.php'; ?>
<?php require_once '../models/Student.php'; ?>

<?php
$studentModel = new Student();
$stats = $studentModel->getStats();
$classes = $studentModel->getClasses();

// Get students per class
$classStats = [];
foreach ($classes as $class) {
    $students = $studentModel->readAll('', $class, '');
    $classStats[$class] = count($students);
}
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="bi bi-file-bar-graph"></i> Reports & Analytics</h2>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Students Overview</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td>Total Students:</td>
                            <td class="text-end"><strong><?php echo $stats['total']; ?></strong></td>
                        </tr>
                        <tr>
                            <td>Active:</td>
                            <td class="text-end"><span class="badge bg-success"><?php echo $stats['active']; ?></span></td>
                        </tr>
                        <tr>
                            <td>Inactive:</td>
                            <td class="text-end"><span class="badge bg-warning"><?php echo $stats['inactive']; ?></span></td>
                        </tr>
                        <tr>
                            <td>Total Classes:</td>
                            <td class="text-end"><strong><?php echo $stats['classes']; ?></strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Students per Class</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Class</th>
                                    <th>Number of Students</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($classStats as $class => $count): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($class); ?></td>
                                        <td><?php echo $count; ?></td>
                                        <td>
                                            <?php
                                            $percentage = $stats['total'] > 0 ? round(($count / $stats['total']) * 100, 1) : 0;
                                            echo $percentage . '%';
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Options -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Export Options</h5>
                </div>
                <div class="card-body">
                    <p>Generate and download reports in various formats:</p>
                    <div class="btn-group" role="group">
                        <button onclick="window.print()" class="btn btn-primary">
                            <i class="bi bi-printer"></i> Print Report
                        </button>
                        <button onclick="exportToCSV('studentsTable', 'students_report')" class="btn btn-success">
                            <i class="bi bi-file-earmark-excel"></i> Download CSV
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>