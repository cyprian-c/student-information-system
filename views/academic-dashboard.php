<?php
require_once '../includes/header.php';
require_once '../models/Academic.php';
require_once '../config/pdo.php';

$pdo = getPDO();

// Get statistics
$totalSubjects = $pdo->query("SELECT COUNT(*) FROM subjects WHERE status='active'")->fetchColumn();
$totalExams = $pdo->query("SELECT COUNT(*) FROM exam_types WHERE academic_year='2024-2025'")->fetchColumn();
$gradesRecorded = $pdo->query("SELECT COUNT(*) FROM student_grades WHERE academic_year='2024-2025'")->fetchColumn();
$avgAttendance = $pdo->query("SELECT ROUND(AVG(attendance_percentage), 2) FROM view_attendance_summary")->fetchColumn();
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="bi bi-book"></i> Academic Dashboard</h2>
            <p class="text-muted">Overview of academic progress</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <h6 class="text-muted">Total Subjects</h6>
                    <h3 class="text-primary"><?php echo $totalSubjects; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <h6 class="text-muted">Total Exams</h6>
                    <h3 class="text-success"><?php echo $totalExams; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body">
                    <h6 class="text-muted">Grades Recorded</h6>
                    <h3 class="text-warning"><?php echo $gradesRecorded; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body">
                    <h6 class="text-muted">Avg Attendance</h6>
                    <h3 class="text-info"><?php echo $avgAttendance ?? 0; ?>%</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-journal-text"></i> Grade Management</h5>
                </div>
                <div class="card-body">
                    <a href="grade-entry.php" class="btn btn-primary mb-2 w-100">
                        <i class="bi bi-pencil"></i> Enter Grades
                    </a>
                    <a href="subjects.php" class="btn btn-outline-primary mb-2 w-100">
                        <i class="bi bi-book"></i> Manage Subjects
                    </a>
                    <a href="exams.php" class="btn btn-outline-primary w-100">
                        <i class="bi bi-clipboard-check"></i> Manage Exams
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Attendance</h5>
                </div>
                <div class="card-body">
                    <a href="attendance-entry.php" class="btn btn-success mb-2 w-100">
                        <i class="bi bi-check-circle"></i> Mark Attendance
                    </a>
                    <a href="attendance-report.php" class="btn btn-outline-success w-100">
                        <i class="bi bi-file-bar-graph"></i> View Reports
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>