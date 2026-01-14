<?php
require_once '../includes/header.php';
require_once '../models/Academic.php';

$studentId = $_GET['student_id'] ?? null;
$academicYear = $_GET['academic_year'] ?? '2024-2025';
$term = $_GET['term'] ?? 'Term 1';

if (!$studentId) {
    header('Location: ../student-list.php');
    exit;
}

$academic = new Academic();
$reportData = $academic->getStudentReportCard($studentId, $academicYear, $term);
$student = $reportData['student'];
$grades = $reportData['grades'];
$performance = $reportData['performance'];
$attendance = $reportData['attendance'];
?>

<style>
    @media print {
        .no-print {
            display: none !important;
        }

        body {
            background: white;
        }
    }
</style>

<div class="container py-4">
    <div class="no-print mb-3">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer"></i> Print Report Card
        </button>
        <a href="../student-list.php" class="btn btn-secondary">Back to Students</a>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Header -->
            <div class="text-center mb-4">
                <h2>STUDENT REPORT CARD</h2>
                <p class="mb-0">Academic Year: <?php echo $academicYear; ?></p>
                <p>Term: <?php echo $term; ?></p>
            </div>

            <hr>

            <!-- Student Information -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <p><strong>Name:</strong> <?php echo $student['first_name'] . ' ' . $student['last_name']; ?></p>
                    <p><strong>Student ID:</strong> <?php echo $student['student_id']; ?></p>
                    <p><strong>Class:</strong> <?php echo $student['class']; ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Date of Birth:</strong> <?php echo date('M d, Y', strtotime($student['date_of_birth'])); ?></p>
                    <p><strong>Gender:</strong> <?php echo $student['gender']; ?></p>
                </div>
            </div>

            <!-- Academic Performance -->
            <h5 class="mb-3">Academic Performance</h5>
            <div class="table-responsive mb-4">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Subject</th>
                            <th>Marks Obtained</th>
                            <th>Max Marks</th>
                            <th>Percentage</th>
                            <th>Grade</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($grades)): ?>
                            <?php foreach ($grades as $grade): ?>
                                <tr>
                                    <td><?php echo $grade['subject_name']; ?></td>
                                    <td><?php echo $grade['marks_obtained']; ?></td>
                                    <td><?php echo $grade['max_marks']; ?></td>
                                    <td><?php echo number_format($grade['percentage'], 2); ?>%</td>
                                    <td><strong><?php echo $grade['grade']; ?></strong></td>
                                    <td><?php echo $grade['remarks'] ?? '-'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No grades recorded for this term</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Overall Performance -->
            <?php if ($performance): ?>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6>Average Percentage</h6>
                                <h3 class="text-primary"><?php echo number_format($performance['average_percentage'], 2); ?>%</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6>GPA</h6>
                                <h3 class="text-success"><?php echo number_format($performance['gpa'], 2); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6>Subjects Taken</h6>
                                <h3 class="text-info"><?php echo $performance['subjects_taken']; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Attendance -->
            <?php if ($attendance): ?>
                <h5 class="mb-3">Attendance Summary</h5>
                <div class="row mb-4">
                    <div class="col-md-3">
                        <p><strong>Days Present:</strong> <?php echo $attendance['days_present'] ?? 0; ?></p>
                    </div>
                    <div class="col-md-3">
                        <p><strong>Days Absent:</strong> <?php echo $attendance['days_absent'] ?? 0; ?></p>
                    </div>
                    <div class="col-md-3">
                        <p><strong>Attendance %:</strong> <?php echo number_format($attendance['attendance_percentage'] ?? 0, 2); ?>%</p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Grading Scale -->
            <h6 class="mb-2">Grading Scale</h6>
            <p class="small">A+ (90-100): Excellent | A (80-89): Very Good | B+ (70-79): Good | B (60-69): Above Average | C+ (50-59): Average | C (40-49): Pass | D (30-39): Below Average | F (0-29): Fail</p>

            <!-- Footer -->
            <div class="row mt-5">
                <div class="col-md-6">
                    <p>______________________</p>
                    <p>Class Teacher</p>
                </div>
                <div class="col-md-6 text-end">
                    <p>______________________</p>
                    <p>Principal</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>