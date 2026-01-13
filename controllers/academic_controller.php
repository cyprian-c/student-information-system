<?php
session_start();
require_once __DIR__ . '/../models/Academic.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../admin-login.php');
    exit;
}

$academic = new Academic();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'record_grade':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'student_id' => $_POST['student_id'],
                'subject_id' => $_POST['subject_id'],
                'exam_type_id' => $_POST['exam_type_id'],
                'academic_year' => $_POST['academic_year'],
                'term' => $_POST['term'],
                'marks_obtained' => $_POST['marks_obtained'],
                'max_marks' => $_POST['max_marks'],
                'remarks' => $_POST['remarks'] ?? null,
                'recorded_by' => $_SESSION['admin_id']
            ];

            if ($academic->recordGrade($data)) {
                $_SESSION['success'] = "Grade recorded successfully!";
            } else {
                $_SESSION['error'] = "Failed to record grade.";
            }

            header('Location: ../views/grade-entry.php');
            exit;
        }
        break;

    case 'record_attendance':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $students = $_POST['students'] ?? [];
            $date = $_POST['attendance_date'];
            $subjectId = $_POST['subject_id'] ?? null;
            $recordedBy = $_SESSION['admin_id'];

            $successCount = 0;
            foreach ($students as $studentId => $status) {
                $data = [
                    'student_id' => $studentId,
                    'attendance_date' => $date,
                    'status' => $status,
                    'subject_id' => $subjectId,
                    'remarks' => $_POST['remarks'][$studentId] ?? null,
                    'recorded_by' => $recordedBy
                ];

                if ($academic->recordAttendance($data)) {
                    $successCount++;
                }
            }

            $_SESSION['success'] = "Attendance recorded for {$successCount} students!";
            header('Location: ../views/attendance-entry.php');
            exit;
        }
        break;

    case 'create_subject':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'subject_code' => $_POST['subject_code'],
                'subject_name' => $_POST['subject_name'],
                'class' => $_POST['class'],
                'description' => $_POST['description'] ?? null,
                'status' => 'active'
            ];

            if ($academic->createSubject($data)) {
                $_SESSION['success'] = "Subject created successfully!";
            } else {
                $_SESSION['error'] = "Failed to create subject.";
            }

            header('Location: ../views/subjects.php');
            exit;
        }
        break;

    case 'create_exam':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'exam_name' => $_POST['exam_name'],
                'exam_code' => $_POST['exam_code'],
                'academic_year' => $_POST['academic_year'],
                'term' => $_POST['term'],
                'max_marks' => $_POST['max_marks'],
                'passing_marks' => $_POST['passing_marks'],
                'weightage' => $_POST['weightage'] ?? 100.00,
                'start_date' => $_POST['start_date'],
                'end_date' => $_POST['end_date'],
                'status' => $_POST['status'] ?? 'scheduled'
            ];

            if ($academic->createExamType($data)) {
                $_SESSION['success'] = "Exam created successfully!";
            } else {
                $_SESSION['error'] = "Failed to create exam.";
            }

            header('Location: ../views/exams.php');
            exit;
        }
        break;

    default:
        header('Location: ../views/academic-dashboard.php');
        exit;
}
