<?php
require_once '../includes/header.php';
require_once '../models/Academic.php';

$academic = new Academic();
$date = $_GET['date'] ?? date('Y-m-d');
$class = $_GET['class'] ?? 'Grade 1';

$students = $academic->getAttendanceByDate($date, $class);
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="bi bi-calendar-check"></i> Attendance Entry</h2>
            <p class="text-muted">Mark student attendance</p>
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
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-control" value="<?php echo $date; ?>" onchange="this.form.submit()">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Class</label>
                    <select name="class" class="form-select" onchange="this.form.submit()">
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="Grade <?php echo $i; ?>" <?php echo $class == "Grade $i" ? 'selected' : ''; ?>>Grade <?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Attendance Form -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Attendance for <?php echo $class; ?> - <?php echo date('F d, Y', strtotime($date)); ?></h5>
        </div>
        <div class="card-body">
            <form action="../controllers/academic_controller.php?action=record_attendance" method="POST">
                <input type="hidden" name="attendance_date" value="<?php echo $date; ?>">

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Present</th>
                                <th>Absent</th>
                                <th>Late</th>
                                <th>Excused</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo $student['student_id']; ?></td>
                                    <td><?php echo $student['first_name'] . ' ' . $student['last_name']; ?></td>
                                    <td>
                                        <input type="radio" name="students[<?php echo $student['id']; ?>]" value="present"
                                            <?php echo ($student['status'] ?? '') == 'present' ? 'checked' : ''; ?> required>
                                    </td>
                                    <td>
                                        <input type="radio" name="students[<?php echo $student['id']; ?>]" value="absent"
                                            <?php echo ($student['status'] ?? '') == 'absent' ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <input type="radio" name="students[<?php echo $student['id']; ?>]" value="late"
                                            <?php echo ($student['status'] ?? '') == 'late' ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <input type="radio" name="students[<?php echo $student['id']; ?>]" value="excused"
                                            <?php echo ($student['status'] ?? '') == 'excused' ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <input type="text" name="remarks[<?php echo $student['id']; ?>]"
                                            class="form-control form-control-sm"
                                            value="<?php echo $student['remarks'] ?? ''; ?>"
                                            placeholder="Optional">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Save Attendance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>