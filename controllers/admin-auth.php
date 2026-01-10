<?php
session_start();
require_once __DIR__ . '/../config/pdo.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username']);
            $password = $_POST['password'];

            // Validate input
            if (empty($username) || empty($password)) {
                $_SESSION['error'] = 'Please enter both username and password.';
                header('Location: ../admin-login.php');
                exit;
            }

            try {
                // Get PDO connection
                $pdo = getPDO();

                // Fetch user from database
                $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = :username LIMIT 1");
                $stmt->execute(['username' => $username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                // Verify user exists and password matches
                if ($user && password_verify($password, $user['password'])) {
                    // Set session variables
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];

                    // Redirect to dashboard
                    header('Location: ../admin/dashboard.php');
                    exit;
                } else {
                    $_SESSION['error'] = 'Invalid username or password.';
                    header('Location: ../admin-login.php');
                    exit;
                }
            } catch (PDOException $e) {
                error_log("Login Error: " . $e->getMessage());
                $_SESSION['error'] = 'Database error. Please try again later.';
                header('Location: ../admin-login.php');
                exit;
            }
        }
        break;

    case 'logout':
        // Destroy session
        session_unset();
        session_destroy();

        // Start new session for success message
        session_start();
        $_SESSION['success'] = 'You have been logged out successfully.';

        // Redirect to login page
        header('Location: ../admin-login.php');
        exit;
        break;

    default:
        header('Location: ../admin-login.php');
        exit;
        break;
}
