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
                'email' => trim($_POST['email']),
                'phone' => trim($_POST['phone']),
                'date_of_birth' => $_POST['date_of_birth'],
                'gender' => $_POST['gender'],
                'address' => trim($_POST['address']),
                'guardian_name' => trim($_POST['guardian_name']),
                'guardian_phone' => trim($_POST['guardian_phone']),
                'class' => $_POST['class'],
                'enrollment_date' => $_POST['enrollment_date'],
                'status' => $_POST['status'] ?? 'active'
            ];

            if ($student->create($data)) {
                $_SESSION['success'] = 'Student added successfully!';
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
                'email' => trim($_POST['email']),
                'phone' => trim($_POST['phone']),
                'date_of_birth' => $_POST['date_of_birth'],
                'gender' => $_POST['gender'],
                'address' => trim($_POST['address']),
                'guardian_name' => trim($_POST['guardian_name']),
                'guardian_phone' => trim($_POST['guardian_phone']),
                'class' => $_POST['class'],
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
