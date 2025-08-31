<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Handle employee deletion
if (isset($_POST['delete_employee']) && isset($_POST['employee_id'])) {
    $employee_id = (int)$_POST['employee_id'];
    
    // Prevent deleting self
    if ($employee_id === $_SESSION['user_id']) {
        $error = "You cannot delete your own account.";
    } else {
        $query = "DELETE FROM users WHERE id = :id AND role_id = 2";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $employee_id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $success = "Employee deleted successfully.";
        } else {
            $error = "Error deleting employee.";
        }
    }
}

// Get all employees (role_id = 2)
$query = "SELECT u.*, r.name as role_name 
          FROM users u 
          JOIN roles r ON u.role_id = r.id 
          WHERE u.role_id = 2 
          ORDER BY u.created_at DESC";
$stmt = $db->query($query);
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Employees - Customer Management System</title>
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
            <h1>Manage Employees</h1>
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
                <h2>Employee List</h2>
                <a href="register.php" class="btn btn-primary">Add New Employee</a>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="search-filter">
                <form action="employees.php" method="get" class="search-form">
                    <input type="text" name="search" placeholder="Search employees...">
                    <button type="submit" class="btn">Search</button>
                </form>
            </div>
            
            <?php if (count($employees) > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Date Added</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($employees as $employee): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['username']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['email']); ?></td>
                                    <td><span class="badge"><?php echo ucfirst(htmlspecialchars($employee['role_name'])); ?></span></td>
                                    <td><?php echo date('M d, Y', strtotime($employee['created_at'])); ?></td>
                                    <td class="actions">
                                        <a href="edit_employee.php?id=<?php echo $employee['id']; ?>" class="btn btn-sm btn-edit" title="Edit">✏️</a>
                                        <?php if ($employee['id'] != $_SESSION['user_id']): ?>
                                            <form action="employees.php" method="post" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this employee? This action cannot be undone.');">
                                                <input type="hidden" name="employee_id" value="<?php echo $employee['id']; ?>">
                                                <button type="submit" name="delete_employee" class="btn btn-sm btn-delete" title="Delete">🗑️</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <p>No employees found. <a href="register.php">Add your first employee</a>.</p>
                </div>
            <?php endif; ?>
        </main>
        
        <footer class="dashboard-footer">
            <p>&copy; <?php echo date('Y'); ?> Customer Management System. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
