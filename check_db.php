<?php
require_once 'config/database.php';

// Check database connection
try {
    $database = new Database();
    $db = $database->getConnection();
    echo "✅ Database connection successful!<br><br>";
    
    // Check users table structure
    $stmt = $db->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "📋 Users table columns: <br>";
    echo "<pre>";
    print_r($columns);
    echo "</pre><br>";
    
    // Check if there are any users
    $stmt = $db->query("SELECT username, LENGTH(password) as pass_length, password FROM users LIMIT 5");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "👥 Sample users (first 5): <br>";
    echo "<pre>";
    foreach ($users as $user) {
        echo "Username: " . htmlspecialchars($user['username']) . "\n";
        echo "Password length: " . $user['pass_length'] . " characters\n";
        echo "Password hash: " . htmlspecialchars(substr($user['password'] ?? 'NULL', 0, 60)) . "...\n";
        echo "Hash info: ";
        print_r(password_get_info($user['password'] ?? ''));
        echo "\n----------------------------------------\n";
    }
    echo "</pre>";
    
} catch (PDOException $e) {
    die("❌ Database error: " . $e->getMessage());
}

echo "<hr><h3>PHP Info:</h3>";
phpinfo();
?>
