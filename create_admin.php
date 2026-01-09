<?php
require_once 'config/database.php';

try {
    $conn = Database::getInstance()->getConnection();

    // Check if admin exists
    $stmt = $conn->query("SELECT COUNT(*) as count FROM admin_users WHERE username = 'admin'");
    $result = $stmt->fetch();

    if ($result['count'] == 0) {
        // Create admin user
        $username = 'admin';
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $email = 'admin@school.com';

        $sql = "INSERT INTO admin_users (username, password, email) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$username, $password, $email]);

        echo "<h2>✅ SUCCESS!</h2>";
        echo "<p>Admin user created successfully!</p>";
        echo "<p>Username: admin</p>";
        echo "<p>Password: admin123</p>";
        echo "<p><a href='index.php'>Go to Login Page</a></p>";
    } else {
        echo "<h2>ℹ️ Admin user already exists</h2>";
        echo "<p>Username: admin</p>";
        echo "<p>Password: admin123</p>";
        echo "<p><a href='index.php'>Go to Login Page</a></p>";
    }
} catch (PDOException $e) {
    echo "<h2>❌ ERROR:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Make sure the database and tables are created!</p>";
}
