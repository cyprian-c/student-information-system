<?php
session_start();
require_once __DIR__ . '/../config/pdo.php';

$action = $_GET['action'] ?? '';

switch ($action) {

    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                $_SESSION['error'] = 'Please enter both username and password.';
                header('Location: ../admin-login.php');
                exit;
            }

            try {
                $pdo = getPDO();

                $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = :username LIMIT 1");
                $stmt->execute(['username' => $username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['password'])) {
                    // Regenerate session ID for security
                    session_regenerate_id(true);

                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];

                    header('Location: ../admin/dashboard.php');
                    exit;
                } else {
                    $_SESSION['error'] = 'Invalid username or password.';
                    header('Location: ../admin-login.php');
                    exit;
                }
            } catch (PDOException $e) {
                error_log("Admin Login Error: " . $e->getMessage());
                $_SESSION['error'] = 'Database error. Please try again later.';
                header('Location: ../admin-login.php');
                exit;
            }
        } else {
            // If not POST, redirect to login
            header('Location: ../admin-login.php');
            exit;
        }
        break;

    case 'logout':
        // Clear all session data
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();

        // Start a fresh session for logout message
        session_start();
        $_SESSION['success'] = 'You have been logged out successfully.';
        header('Location: ../admin-logout.php');
        exit;

    default:
        header('Location: ../admin-logout.php');
        exit;
}
