<?php
session_start();
require_once __DIR__ . '/../models/Student.php';

$student = new Student();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'create':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'first_name' => trim($_POST['first_name']),
                'last_name' => trim($_POST['last_name']),
                'gender' => $_POST['gender'],
                'date_of_birth' => $_POST['date_of_birth'],
                'class' => $_POST['class'],
                'parent_name' => trim($_POST['parent_name']),
                'parent_phone' => trim($_POST['parent_phone']),
                'address' => trim($_POST['address']),
                'enrollment_date' => $_POST['enrollment_date'],
                'status' => $_POST['status'] ?? 'active'
            ];

            if ($student->create($data)) {
                // ============ AUTO FEE ALLOCATION ============ //
                try {
                    // Get the newly created student ID
                    $pdo = getPDO();
                    $lastId = $pdo->lastInsertId();

                    // Get current academic year
                    $currentYear = date('Y');
                    $nextYear = $currentYear + 1;
                    $academicYear = $currentYear . '-' . $nextYear;

                    // Auto-assign fee for the student's class
                    require_once __DIR__ . '/../models/Fee.php';
                    $feeModel = new Fee();

                    $feeAssigned = $feeModel->assignFeeToStudent($lastId, $academicYear);

                    if ($feeAssigned) {
                        $_SESSION['success'] = 'Student added successfully! Fee automatically assigned for ' . $academicYear . '.';
                    } else {
                        $_SESSION['success'] = 'Student added successfully! Note: Fee structure not found for ' . $data['class'] . '. Please assign manually.';
                    }
                } catch (Exception $e) {
                    error_log("Fee allocation error: " . $e->getMessage());
                    $_SESSION['success'] = 'Student added successfully! Please assign fees manually.';
                }
                // ============ END AUTO FEE ALLOCATION ============ //

                header('Location: ../views/students-list.php');
            } else {
                $_SESSION['error'] = 'Failed to add student. Please try again.';
                header('Location: ../views/admission-form.php');
            }
        }
        break;

    case 'update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $data = [
                'first_name' => trim($_POST['first_name']),
                'last_name' => trim($_POST['last_name']),
                'gender' => $_POST['gender'],
                'date_of_birth' => $_POST['date_of_birth'],
                'class' => $_POST['class'],
                'parent_name' => trim($_POST['parent_name']),
                'parent_phone' => trim($_POST['parent_phone']),
                'address' => trim($_POST['address']),
                'enrollment_date' => $_POST['enrollment_date'],
                'status' => $_POST['status']
            ];

            if ($student->update($id, $data)) {
                $_SESSION['success'] = 'Student updated successfully!';
            } else {
                $_SESSION['error'] = 'Failed to update student.';
            }
            header('Location: ../views/students-list.php');
        }
        break;

    case 'delete':
        if (isset($_GET['id'])) {
            if ($student->delete($_GET['id'])) {
                $_SESSION['success'] = 'Student deleted successfully!';
            } else {
                $_SESSION['error'] = 'Failed to delete student.';
            }
        }
        header('Location: ../views/students-list.php');
        break;


    default:
        header('Location: ../index.php');
        break;
}
