<?php require_once '../includes/header.php'; ?>
<?php require_once '../models/Student.php'; ?>

<?php
$studentModel = new Student();
$search = $_GET['search'] ?? '';
$filterClass = $_GET['class'] ?? '';
$filterStatus = $_GET['status'] ?? '';

$students = $studentModel->readAll($search, $filterClass, $filterStatus);
$classes = $studentModel->getClasses();
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="bi bi-people"></i> Students List</h2>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $_SESSION['success'];
            unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $_SESSION['error'];
            unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search"
                        placeholder="Search by name, email, or ID..."
                        value="<?php echo htmlspecialchars($search); ?>"
                        onkeyup="handleSearch(this)">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="class" onchange="this.form.submit()">
                        <option value="">All Classes</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class; ?>" <?php echo $filterClass == $class ? 'selected' : ''; ?>>
                                <?php echo $class; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="active" <?php echo $filterStatus == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $filterStatus == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Total Students: <?php echo count($students); ?></h5>
                <div>
                    <button onclick="exportToCSV('studentsTable', 'students')" class="btn btn-success btn-sm">
                        <i class="bi bi-file-earmark-excel"></i> Export CSV
                    </button>
                    <a href="admission-form.php" class="btn btn-primary btn-sm">
                        <i class="bi bi-person-plus"></i> Add New Student
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover" id="studentsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Class</th>
                            <th>Guardian</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No students found
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($student['student_id']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                                    <td><?php echo htmlspecialchars($student['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($student['class']); ?></td>
                                    <td>
                                        <div><?php echo htmlspecialchars($student['guardian_name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($student['guardian_phone']); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($student['status'] === 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="table-actions">
                                        <a href="edit-student.php?id=<?php echo $student['id']; ?>"
                                            class="btn btn-sm btn-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="../controllers/student_controller.php?action=delete&id=<?php echo $student['id']; ?>"
                                            class="btn btn-sm btn-danger btn-delete" title="Delete">
                                            <i class="bi bi-trash"></i>
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