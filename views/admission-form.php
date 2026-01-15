<?php require_once '../includes/header.php'; ?>
<?php require_once '../models/Student.php'; ?>

<?php
$studentModel = new Student();
$student = null;
$isEdit = false;

if (isset($_GET['id'])) {
    $student = $studentModel->readOne($_GET['id']);
    $isEdit = true;
}
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-person-plus"></i>
                        <?php echo $isEdit ? 'Edit Student' : 'New Student Admission'; ?>
                    </h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php echo $_SESSION['error'];
                            unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form action="../controllers/student_controller.php?action=<?php echo $isEdit ? 'update' : 'create'; ?>"
                        method="POST" class="needs-validation" novalidate>

                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                        <?php endif; ?>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label required">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name"
                                    value="<?php echo $student['first_name'] ?? ''; ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label for="last_name" class="form-label required">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name"
                                    value="<?php echo $student['last_name'] ?? ''; ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label for="gender" class="form-label required">Gender</label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php echo ($student['gender'] ?? '') == 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo ($student['gender'] ?? '') == 'Female' ? 'selected' : ''; ?>>Female</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="date_of_birth" class="form-label required">Date of Birth</label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth"
                                    value="<?php echo $student['date_of_birth'] ?? ''; ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label for="class" class="form-label required">Class</label>
                                <select class="form-select" id="class" name="class" required>
                                    <option value="">Select Class</option>
                                    <?php for ($i = 1; $i <= 12; $i++): ?>
                                        <option value="Grade <?php echo $i; ?>"
                                            <?php echo ($student['class'] ?? '') == "Grade $i" ? 'selected' : ''; ?>>
                                            Grade <?php echo $i; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="parent_name" class="form-label required">Parent/Guardian Name</label>
                                <input type="text" class="form-control" id="parent_name" name="parent_name"
                                    value="<?php echo $student['parent_name'] ?? ''; ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label for="parent_phone" class="form-label required">Parent/Guardian Phone</label>
                                <input type="tel" class="form-control" id="parent_phone" name="parent_phone"
                                    value="<?php echo $student['parent_phone'] ?? ''; ?>" required>
                            </div>

                            <div class="col-12">
                                <label for="address" class="form-label required">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="2"><?php echo $student['address'] ?? ''; ?></textarea>
                            </div>



                            <div class="col-md-6">
                                <label for="enrollment_date" class="form-label required">Enrollment Date</label>
                                <input type="date" class="form-control" id="enrollment_date" name="enrollment_date"
                                    value="<?php echo $student['enrollment_date'] ?? date('Y-m-d'); ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label for="status" class="form-label required">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active" <?php echo ($student['status'] ?? 'active') == 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($student['status'] ?? '') == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> <?php echo $isEdit ? 'Update Student' : 'Save Student'; ?>
                            </button>
                            <a href="students-list.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>