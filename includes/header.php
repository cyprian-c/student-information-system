<?php
session_start();

// Check if user is logged in for admin pages
$current_page = basename($_SERVER['PHP_SELF']);
$admin_pages = ['dashboard.php', 'reports.php'];

if (in_array($current_page, $admin_pages) && !isset($_SESSION['admin_logged_in'])) {
    header('Location: ../admin-login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Information System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">
                <i class="bi bi-mortarboard-fill"></i> Student Information System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['admin_logged_in'])): ?>
                        <!-- Dashboard -->
                        <li class="nav-item">
                            <a class="nav-link" href="../admin/dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>

                        <!-- Students Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-people"></i> Students
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="../views/admission-form.php">
                                        <i class="bi bi-person-plus"></i> New Admission
                                    </a></li>
                                <li><a class="dropdown-item" href="../student-list.php">
                                        <i class="bi bi-list-ul"></i> All Students
                                    </a></li>
                                <li><a class="dropdown-item" href="../views/students-list.php">
                                        <i class="bi bi-table"></i> Student Records
                                    </a></li>
                            </ul>
                        </li>

                        <!-- Fees Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-cash-coin"></i> Fees
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="../views/fee-management.php">
                                        <i class="bi bi-wallet2"></i> Fee Management
                                    </a></li>
                                <li><a class="dropdown-item" href="../views/fee-structure.php">
                                        <i class="bi bi-gear"></i> Fee Structure
                                    </a></li>
                            </ul>
                        </li>

                        <!-- Academic Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-book"></i> Academic
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="../views/academic-dashboard.php">
                                        <i class="bi bi-grid"></i> Academic Dashboard
                                    </a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="../views/grade-entry.php">
                                        <i class="bi bi-pencil-square"></i> Enter Grades
                                    </a></li>
                                <li><a class="dropdown-item" href="../views/attendance-entry.php">
                                        <i class="bi bi-calendar-check"></i> Mark Attendance
                                    </a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="../views/subjects.php">
                                        <i class="bi bi-journal-text"></i> Manage Subjects
                                    </a></li>
                                <li><a class="dropdown-item" href="../views/exams.php">
                                        <i class="bi bi-clipboard-check"></i> Manage Exams
                                    </a></li>
                            </ul>
                        </li>

                        <!-- Reports Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-file-bar-graph"></i> Reports
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="../admin/reports.php">
                                        <i class="bi bi-graph-up"></i> System Reports
                                    </a></li>
                                <li><a class="dropdown-item" href="../views/academic-reports.php">
                                        <i class="bi bi-award"></i> Academic Reports
                                    </a></li>
                                <li><a class="dropdown-item" href="../views/fee-reports.php">
                                        <i class="bi bi-cash-stack"></i> Fee Reports
                                    </a></li>
                            </ul>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="../admin-logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../index.php">
                                <i class="bi bi-house"></i> Home
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container-fluid mt-4">