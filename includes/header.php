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
                        <li class="nav-item">
                            <a class="nav-link" href="../admin/dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../views/admission-form.php">
                                <i class="bi bi-person-plus"></i> New Admission
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../views/students-list.php">
                                <i class="bi bi-people"></i> Students
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../admin/reports.php">
                                <i class="bi bi-file-bar-graph"></i> Reports
                            </a>
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