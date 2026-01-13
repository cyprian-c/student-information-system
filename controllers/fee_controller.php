<?php
session_start();
require_once __DIR__ . '/../models/Fee.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../admin-login.php');
    exit;
}

$fee = new Fee();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'record_payment':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'student_fee_id' => $_POST['student_fee_id'],
                'student_id' => $_POST['student_id'],
                'payment_date' => $_POST['payment_date'],
                'amount' => $_POST['amount'],
                'payment_method' => $_POST['payment_method'],
                'transaction_id' => $_POST['transaction_id'] ?? null,
                'payment_for' => $_POST['payment_for'] ?? 'School Fees',
                'remarks' => $_POST['remarks'] ?? null,
                'recorded_by' => $_SESSION['admin_id']
            ];

            $receiptNumber = $fee->recordPayment($data);

            if ($receiptNumber) {
                $_SESSION['success'] = "Payment recorded successfully! Receipt No: {$receiptNumber}";
            } else {
                $_SESSION['error'] = "Failed to record payment. Please try again.";
            }

            header('Location: ../views/fee-payment.php?student_id=' . $_POST['student_id']);
            exit;
        }
        break;

    case 'assign_fee':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $studentId = $_POST['student_id'];
            $academicYear = $_POST['academic_year'];

            if ($fee->assignFeeToStudent($studentId, $academicYear)) {
                $_SESSION['success'] = "Fee assigned successfully!";
            } else {
                $_SESSION['error'] = "Failed to assign fee. Fee structure may not exist.";
            }

            header('Location: ../views/fee-management.php');
            exit;
        }
        break;

    case 'save_fee_structure':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'class' => $_POST['class'],
                'academic_year' => $_POST['academic_year'],
                'tuition_fee' => $_POST['tuition_fee'],
                'library_fee' => $_POST['library_fee'],
                'lab_fee' => $_POST['lab_fee'],
                'sports_fee' => $_POST['sports_fee'],
                'transport_fee' => $_POST['transport_fee'],
                'exam_fee' => $_POST['exam_fee'],
                'other_fee' => $_POST['other_fee'],
                'status' => $_POST['status'] ?? 'active'
            ];

            if ($fee->saveFeeStructure($data)) {
                $_SESSION['success'] = "Fee structure saved successfully!";
            } else {
                $_SESSION['error'] = "Failed to save fee structure.";
            }

            header('Location: ../views/fee-structure.php');
            exit;
        }
        break;

    default:
        header('Location: ../views/fee-management.php');
        exit;
}
