<?php
require_once '../includes/header.php';
require_once '../models/Academic.php';
require_once '../models/Student.php';

$academic = new Academic();
$student = new Student();

$class = $_GET['class'] ?? 'Grade 1';
$examTypeId = $_GET['exam_type_id'] ?? null;

$subjects = $academic->getAllSubjects($class);
$examTypes = $academic->getAllExamTypes('2024-2025');
$students = $student->readAll('', $class, 'active');
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="bi bi-journal-text"></i> Grade Entry</h2>
            <p class="text-muted">Enter student grades and marks</p>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success'];
                                            unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Class</label>
                    <select name="class" class="form-select" onchange="this.form.submit()">
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="Grade <?php echo $i; ?>" <?php echo $class == "Grade $i" ? 'selected' : ''; ?>>Grade <?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Exam Type</label>
                    <select name="exam_type_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Select Exam</option>
                        <?php foreach ($examTypes as $et): ?>
                            <option value="<?php echo $et['id']; ?>" <?php echo $examTypeId == $et['id'] ? 'selected' : ''; ?>>
                                <?php echo $et['exam_name'] . ' - ' . $et['term']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Grade Entry Form -->
    <?php if ($examTypeId): ?>
        <?php
        $examType = null;
        foreach ($examTypes as $et) {
            if ($et['id'] == $examTypeId) {
                $examType = $et;
                break;
            }
        }
        ?>
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Enter Grades - <?php echo $examType['exam_name']; ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Student ID</th>
                                <th>Student Name</th>
                                <?php foreach ($subjects as $subject): ?>
                                    <th><?php echo $subject['subject_code']; ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $stu): ?>
                                <tr>
                                    <td><?php echo $stu['student_id']; ?></td>
                                    <td><?php echo $stu['first_name'] . ' ' . $stu['last_name']; ?></td>
                                    <?php foreach ($subjects as $subject): ?>
                                        <td>
                                            <form action="../controllers/academic_controller.php?action=record_grade" method="POST" class="d-inline">
                                                <input type="hidden" name="student_id" value="<?php echo $stu['id']; ?>">
                                                <input type="hidden" name="subject_id" value="<?php echo $subject['id']; ?>">
                                                <input type="hidden" name="exam_type_id" value="<?php echo $examTypeId; ?>">
                                                <input type="hidden" name="academic_year" value="<?php echo $examType['academic_year']; ?>">
                                                <input type="hidden" name="term" value="<?php echo $examType['term']; ?>">
                                                <input type="hidden" name="max_marks" value="<?php echo $examType['max_marks']; ?>">
                                                <input type="number" name="marks_obtained" class="form-control form-control-sm"
                                                    style="width:70px; display:inline-block"
                                                    min="0" max="<?php echo $examType['max_marks']; ?>"
                                                    placeholder="0"
                                                    onchange="this.form.submit()">
                                            </form>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Please select an exam type to enter grades.
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>