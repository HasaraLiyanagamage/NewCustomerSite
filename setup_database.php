<?php
/**
 * Database Setup Script for Customer Management System
 * 
 * This script will create the necessary database tables and insert default data.
 * WARNING: This will drop existing tables if they exist.
 */

// Prevent direct access to this file
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from the command line.');
}

require_once 'config/database.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Error: Could not connect to the database. Please check your database configuration in config/database.php\n");
}

echo "=== Customer Management System Database Setup ===\n";
echo "This script will set up the database for the Customer Management System.\n";
echo "WARNING: This will drop existing tables if they exist.\n\n";

// Ask for confirmation
if (PHP_OS == 'WINNT') {
    echo 'Continue? (y/N) ';
    $input = stream_get_line(STDIN, 1024, PHP_EOL);
} else {
    $input = readline('Continue? (y/N) ');
}

if (strtolower(trim($input)) !== 'y') {
    echo "Setup cancelled.\n";
    exit(0);
}

echo "\nSetting up database...\n";

try {
    // Start transaction
    $db->beginTransaction();
    
    // Drop tables if they exist
    echo "Dropping existing tables...\n";
    $dropTables = [
        'DROP TABLE IF EXISTS customers',
        'DROP TABLE IF EXISTS users',
        'DROP TABLE IF EXISTS roles',
        'DROP TABLE IF EXISTS password_resets',
        'DROP TABLE IF EXISTS login_attempts',
        'DROP TABLE IF EXISTS audit_logs'
    ];
    
    foreach ($dropTables as $sql) {
        $db->exec($sql);
    }
    
    // Create tables
    echo "Creating tables...\n";
    $createTables = [
        'CREATE TABLE IF NOT EXISTS roles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL UNIQUE,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
        
        'CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            role_id INT NOT NULL,
            is_active BOOLEAN DEFAULT TRUE,
            last_login TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
        
        'CREATE TABLE IF NOT EXISTS customers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL,
            phone VARCHAR(20),
            address TEXT,
            city VARCHAR(100),
            state VARCHAR(50),
            postal_code VARCHAR(20),
            country VARCHAR(100) DEFAULT "United States",
            date_of_birth DATE,
            notes TEXT,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
        
        'CREATE TABLE IF NOT EXISTS password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token VARCHAR(255) NOT NULL,
            expires_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX (token)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
        
        'CREATE TABLE IF NOT EXISTS login_attempts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL,
            username VARCHAR(255) NOT NULL,
            success BOOLEAN NOT NULL DEFAULT FALSE,
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (ip_address),
            INDEX (username)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
        
        'CREATE TABLE IF NOT EXISTS audit_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            action VARCHAR(50) NOT NULL,
            table_name VARCHAR(50) NOT NULL,
            record_id INT,
            old_values TEXT,
            new_values TEXT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            INDEX (action),
            INDEX (table_name, record_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    ];
    
    foreach ($createTables as $sql) {
        $db->exec($sql);
    }
    
    // Insert default roles
    echo "Inserting default roles...\n";
    $roles = [
        ['name' => 'admin', 'description' => 'Administrator with full access'],
        ['name' => 'employee', 'description' => 'Regular employee with limited access'],
        ['name' => 'customer', 'description' => 'Customer with read-only access']
    ];
    
    $roleStmt = $db->prepare('INSERT INTO roles (name, description) VALUES (:name, :description)');
    foreach ($roles as $role) {
        $roleStmt->execute($role);
    }
    
    // Insert default admin user
    echo "Creating default admin user...\n";
    $adminPassword = 'admin123'; // In a real app, generate a random password
    $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
    
    $adminUser = [
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password_hash' => $hashedPassword,
        'first_name' => 'Admin',
        'last_name' => 'User',
        'role_id' => 1, // admin role
        'is_active' => 1
    ];
    
    $userStmt = $db->prepare('INSERT INTO users (username, email, password_hash, first_name, last_name, role_id, is_active) ' .
                            'VALUES (:username, :email, :password_hash, :first_name, :last_name, :role_id, :is_active)');
    $userStmt->execute($adminUser);
    
    // Insert sample employee
    echo "Creating sample employee...\n";
    $employeePassword = 'employee123';
    $hashedEmployeePassword = password_hash($employeePassword, PASSWORD_DEFAULT);
    
    $employeeUser = [
        'username' => 'employee1',
        'email' => 'employee@example.com',
        'password_hash' => $hashedEmployeePassword,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'role_id' => 2, // employee role
        'is_active' => 1
    ];
    
    $userStmt->execute($employeeUser);
    
    // Insert sample customers
    echo "Creating sample customers...\n";
    $customers = [
        [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'phone' => '555-0101',
            'address' => '123 Main St',
            'city' => 'Anytown',
            'state' => 'CA',
            'postal_code' => '90210',
            'country' => 'United States',
            'date_of_birth' => '1985-07-15',
            'notes' => 'Sample customer 1',
            'created_by' => 1 // admin user
        ],
        [
            'first_name' => 'Robert',
            'last_name' => 'Johnson',
            'email' => 'robert.j@example.com',
            'phone' => '555-0102',
            'address' => '456 Oak Ave',
            'city' => 'Somewhere',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'United States',
            'date_of_birth' => '1990-11-22',
            'notes' => 'Sample customer 2',
            'created_by' => 2 // employee user
        ]
    ];
    
    $customerStmt = $db->prepare('INSERT INTO customers ' .
        '(first_name, last_name, email, phone, address, city, state, postal_code, country, date_of_birth, notes, created_by) ' .
        'VALUES (:first_name, :last_name, :email, :phone, :address, :city, :state, :postal_code, :country, :date_of_birth, :notes, :created_by)'
    );
    
    foreach ($customers as $customer) {
        $customerStmt->execute($customer);
    }
    
    // Commit transaction
    $db->commit();
    
    echo "\n=== Database setup completed successfully! ===\n\n";
    echo "Admin Login Details:\n";
    echo "Username: admin\n";
    echo "Password: admin123\n\n";
    
    echo "Employee Login Details:\n";
    echo "Username: employee1\n";
    echo "Password: employee123\n\n";
    
    echo "IMPORTANT: Change these default passwords immediately after first login!\n";
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    echo "\nError: " . $e->getMessage() . "\n";
    echo "Setup failed. Please check your database configuration and try again.\n";
    exit(1);
}
?>
