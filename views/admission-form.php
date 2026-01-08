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
                        <?php echo $isEdit ? 'Edit Student' : 'New Student Admission';
