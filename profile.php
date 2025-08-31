<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Get user details
$query = "SELECT * FROM users WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $error = "First name, last name, and email are required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Check if email already exists for another user
        $query = "SELECT id FROM users WHERE email = :email AND id != :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $error = "This email is already registered to another account.";
        } else {
            // If changing password
            if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
                // Verify current password
                if (empty($current_password) || !password_verify($current_password, $user['password_hash'])) {
                    $error = "Current password is incorrect.";
                } elseif (strlen($new_password) < 8) {
                    $error = "New password must be at least 8 characters long.";
                } elseif ($new_password !== $confirm_password) {
                    $error = "New password and confirmation do not match.";
                } else {
                    // Update password
                    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $query = "UPDATE users SET password_hash = :password_hash WHERE id = :id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':password_hash', $password_hash);
                    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
                    
                    if (!$stmt->execute()) {
                        $error = "Error updating password. Please try again.";
                    } else {
                        $success = "Password updated successfully!";
                    }
                }
            }
            
            // Update profile information if no errors
            if (empty($error)) {
                $query = "UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':first_name', $first_name);
                $stmt->bindParam(':last_name', $last_name);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
                
                if ($stmt->execute()) {
                    // Update session name if first or last name changed
                    $_SESSION['name'] = $first_name . ' ' . $last_name;
                    $success = $success ? $success . " Profile information updated successfully!" : "Profile updated successfully!";
                    
                    // Refresh user data
                    $query = "SELECT * FROM users WHERE id = :id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    $error = "Error updating profile. Please try again.";
                }
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
    <title>My Profile - Customer Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>

/* Base Styles */
:root {
  --primary: #3498db;
  --primary-dark: #2980b9;
  --secondary: #2ecc71;
  --secondary-dark: #27ae60;
  --danger: #e74c3c;
  --danger-dark: #c0392b;
  --warning: #f39c12;
  --warning-dark: #e67e22;
  --light: #ecf0f1;
  --dark: #2c3e50;
  --gray: #95a5a6;
  --gray-light: #bdc3c7;
  --white: #ffffff;
  --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  --border-radius: 4px;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  line-height: 1.6;
  color: var(--dark);
  background-color: #f5f7fa;
}

a {
  text-decoration: none;
  color: var(--primary);
  transition: all 0.3s ease;
}

a:hover {
  color: var(--primary-dark);
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 15px;
}

/* Header Styles */
.dashboard-header {
  background-color: var(--white);
  box-shadow: var(--shadow);
  padding: 15px 0;
  margin-bottom: 30px;
  position: sticky;
  top: 0;
  z-index: 100;
}

.dashboard-header h1 {
  font-size: 24px;
  color: var(--dark);
  margin-bottom: 15px;
  padding-left: 15px;
}

.dashboard-header nav {
  display: flex;
  gap: 20px;
  padding: 0 15px;
}

.dashboard-header nav a {
  padding: 8px 12px;
  border-radius: var(--border-radius);
  font-weight: 500;
}

.dashboard-header nav a:hover {
  background-color: var(--light);
}

.dashboard-header nav a.active {
  background-color: var(--primary);
  color: var(--white);
}

.dashboard-header nav a.logout {
  margin-left: auto;
  color: var(--danger);
}

.dashboard-header nav a.logout:hover {
  background-color: rgba(231, 76, 60, 0.1);
}

/* Dashboard Content */
.dashboard-content {
  background-color: var(--white);
  border-radius: var(--border-radius);
  box-shadow: var(--shadow);
  padding: 25px;
  margin-bottom: 30px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
  flex-wrap: wrap;
  gap: 15px;
}

.page-header h2 {
  font-size: 20px;
  color: var(--dark);
}

/* Buttons */
.btn {
  display: inline-block;
  padding: 8px 16px;
  border-radius: var(--border-radius);
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  border: none;
  font-size: 14px;
}

.btn-primary {
  background-color: var(--primary);
  color: var(--white);
}

.btn-primary:hover {
  background-color: var(--primary-dark);
}

.btn-secondary {
  background-color: var(--gray);
  color: var(--white);
}

.btn-secondary:hover {
  background-color: var(--gray-light);
}

.btn-danger {
  background-color: var(--danger);
  color: var(--white);
}

.btn-danger:hover {
  background-color: var(--danger-dark);
}

.btn-sm {
  padding: 5px 10px;
  font-size: 12px;
}

.btn-view {
  background-color: var(--secondary);
  color: var(--white);
}

.btn-view:hover {
  background-color: var(--secondary-dark);
}

.btn-edit {
  background-color: var(--warning);
  color: var(--white);
}

.btn-edit:hover {
  background-color: var(--warning-dark);
}

.btn-delete {
  background-color: var(--danger);
  color: var(--white);
}

.btn-delete:hover {
  background-color: var(--danger-dark);
}

/* Search Form */
.search-filter {
  margin-bottom: 25px;
}

.search-form {
  display: flex;
  gap: 10px;
  align-items: center;
  flex-wrap: wrap;
}

.search-form input[type="text"] {
  flex: 1;
  min-width: 200px;
  padding: 8px 12px;
  border: 1px solid var(--gray-light);
  border-radius: var(--border-radius);
  font-size: 14px;
}

/* Tables */
.table-responsive {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 20px;
}

table th {
  background-color: var(--light);
  padding: 12px 15px;
  text-align: left;
  font-weight: 600;
}

table td {
  padding: 12px 15px;
  border-bottom: 1px solid var(--gray-light);
  vertical-align: top;
}

table tr:last-child td {
  border-bottom: none;
}

table tr:hover td {
  background-color: rgba(52, 152, 219, 0.05);
}

.actions {
  display: flex;
  gap: 5px;
}

.inline-form {
  display: inline;
}

/* Badges */
.badge {
  display: inline-block;
  padding: 3px 8px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
  background-color: var(--light);
  color: var(--dark);
}

/* Alerts */
.alert {
  padding: 12px 15px;
  border-radius: var(--border-radius);
  margin-bottom: 20px;
  font-size: 14px;
}

.alert-danger {
  background-color: rgba(231, 76, 60, 0.1);
  border-left: 4px solid var(--danger);
  color: var(--danger);
}

.alert-success {
  background-color: rgba(46, 204, 113, 0.1);
  border-left: 4px solid var(--secondary);
  color: var(--secondary-dark);
}

/* Cards */
.card {
  background-color: var(--white);
  border-radius: var(--border-radius);
  box-shadow: var(--shadow);
  margin-bottom: 25px;
}

.card-header {
  padding: 15px 20px;
  border-bottom: 1px solid var(--gray-light);
}

.card-header h3 {
  font-size: 18px;
  color: var(--dark);
}

.card-body {
  padding: 20px;
}

/* Forms */
.form-group {
  margin-bottom: 20px;
}

.form-row {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
}

.form-row .form-group {
  flex: 1;
  min-width: 200px;
}

label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
}

input[type="text"],
input[type="email"],
input[type="password"],
input[type="date"],
select,
textarea {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid var(--gray-light);
  border-radius: var(--border-radius);
  font-size: 14px;
  transition: border-color 0.3s ease;
}

input[type="text"]:focus,
input[type="email"]:focus,
input[type="password"]:focus,
input[type="date"]:focus,
select:focus,
textarea:focus {
  outline: none;
  border-color: var(--primary);
}

.form-actions {
  margin-top: 25px;
}

.text-muted {
  color: var(--gray);
}

.text-small {
  font-size: 13px;
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
  margin-top: 30px;
  flex-wrap: wrap;
}

.page-link {
  padding: 8px 12px;
  border-radius: var(--border-radius);
  background-color: var(--light);
  color: var(--dark);
}

.page-link:hover {
  background-color: var(--primary);
  color: var(--white);
}

.page-info {
  padding: 8px 12px;
}

/* No Data */
.no-data {
  text-align: center;
  padding: 40px 20px;
  color: var(--gray);
}

.no-data p {
  margin-bottom: 15px;
}

/* Footer */
.dashboard-footer {
  text-align: center;
  padding: 20px 0;
  color: var(--gray);
  font-size: 14px;
}

/* Utility Classes */
.mt-4 {
  margin-top: 25px;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
  .dashboard-header nav {
    flex-direction: column;
    gap: 10px;
  }
  
  .dashboard-header nav a.logout {
    margin-left: 0;
  }
  
  .page-header {
    flex-direction: column;
    align-items: flex-start;
  }
}
        
    </style>
</head>
<body class="dashboard">
    <div class="container">
        <header class="dashboard-header">
            <h1>My Profile</h1>
            <nav>
                <a href="dashboard.php">Dashboard</a>
                <a href="customers.php">Customers</a>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="employees.php">Employees</a>
                <?php endif; ?>
                <a href="profile.php" class="active">Profile</a>
                <a href="logout.php" class="logout">Logout</a>
            </nav>
        </header>
        
        <main class="dashboard-content">
            <div class="page-header">
                <h2>Profile Settings</h2>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h3>Account Information</h3>
                </div>
                <div class="card-body">
                    <form action="profile.php" method="post">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                            <small class="text-muted">Username cannot be changed</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Role</label>
                            <input type="text" value="<?php echo ucfirst(htmlspecialchars($user['role_id'] == 1 ? 'Admin' : 'Employee')); ?>" disabled>
                        </div>
                        
                        <div class="form-group">
                            <label>Account Created</label>
                            <input type="text" value="<?php echo date('F j, Y', strtotime($user['created_at'])); ?>" disabled>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h3>Change Password</h3>
                </div>
                <div class="card-body">
                    <form action="profile.php" method="post">
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password">
                                <small class="text-muted">Leave blank to keep current password</small>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password">
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Update Password</button>
                        </div>
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
