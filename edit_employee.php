<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';

// Check if employee ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: employees.php?error=invalid_id");
    exit();
}

$employee_id = (int)$_GET['id'];
$database = new Database();
$db = $database->getConnection();

// Prevent editing the default admin account
if ($employee_id === 1) {
    $_SESSION['error_message'] = "The default admin account cannot be edited.";
    header("Location: employees.php");
    exit();
}

// Get employee details
$query = "SELECT * FROM users WHERE id = :id AND role_id = 2";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $employee_id, PDO::PARAM_INT);
$stmt->execute();
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

// If employee not found
if (!$employee) {
    header("Location: employees.php?error=not_found");
    exit();
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($username)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Check if email already exists for another user
        $query = "SELECT id FROM users WHERE (email = :email OR username = :username) AND id != :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':id', $employee_id, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $error = "Email or username already exists.";
        } else {
            // Update employee
            $query = "UPDATE users SET 
                      first_name = :first_name, 
                      last_name = :last_name, 
                      email = :email,
                      username = :username,
                      is_active = :is_active
                      WHERE id = :id";
            
            $stmt = $db->prepare($query);
            $result = $stmt->execute([
                ':first_name' => $first_name,
                ':last_name' => $last_name,
                ':email' => $email,
                ':username' => $username,
                ':is_active' => $is_active,
                ':id' => $employee_id
            ]);
            
            if ($result) {
                $_SESSION['success_message'] = "Employee updated successfully.";
                header("Location: employees.php");
                exit();
            } else {
                $error = "Error updating employee. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee - Customer Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="dashboard">
    <div class="container">
        <header class="dashboard-header">
            <h1>Edit Employee</h1>
            <nav>
                <a href="dashboard.php">Dashboard</a>
                <a href="customers.php">Customers</a>
                <a href="employees.php" class="active">Employees</a>
                <a href="profile.php">Profile</a>
                <a href="logout.php" class="logout">Logout</a>
            </nav>
        </header>
        
        <main class="dashboard-content">
            <div class="page-header">
                <div>
                    <h2>Edit Employee: <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></h2>
                    <nav class="breadcrumb">
                        <a href="dashboard.php">Dashboard</a> &raquo;
                        <a href="employees.php">Employees</a> &raquo;
                        <span>Edit</span>
                    </nav>
                </div>
                <div class="actions">
                    <a href="employees.php" class="btn btn-secondary">Back to List</a>
                </div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <form action="edit_employee.php?id=<?php echo $employee_id; ?>" method="post">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($employee['first_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($employee['last_name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($employee['email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($employee['username']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-container">
                                <input type="checkbox" name="is_active" value="1" 
                                    <?php echo $employee['is_active'] ? 'checked' : ''; ?>>
                                <span class="checkmark"></span>
                                Active
                            </label>
                            <small class="text-muted">Deactivating will prevent this employee from logging in.</small>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Update Employee</button>
                            <a href="employees.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h3>Reset Password</h3>
                </div>
                <div class="card-body">
                    <form action="reset_password.php" method="post" onsubmit="return confirm('Are you sure you want to reset this employee\'s password? A temporary password will be emailed to them.');">
                        <input type="hidden" name="employee_id" value="<?php echo $employee_id; ?>">
                        <p>Reset this employee's password. They will receive an email with a temporary password.</p>
                        <button type="submit" class="btn btn-warning">Reset Password</button>
                    </form>
                </div>
            </div>
        </main>
        
        <footer class="dashboard-footer">
            <p>&copy; <?php echo date('Y'); ?> Customer Management System. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
