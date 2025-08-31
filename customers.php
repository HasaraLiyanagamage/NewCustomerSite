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

$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where_clause = "";
$params = [];

if (!empty($search)) {
    $where_clause = "WHERE (c.first_name LIKE :search OR c.last_name LIKE :search OR c.email LIKE :search OR c.phone LIKE :search)";
    $params[':search'] = "%$search%";
}

// Add role-based filtering
if ($user_role === 'employee') {
    $where_clause .= empty($where_clause) ? "WHERE" : " AND";
    $where_clause .= " c.created_by = :user_id";
    $params[':user_id'] = $user_id;
}

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM customers c $where_clause";
$stmt = $db->prepare($count_query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$total_customers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Pagination
$per_page = 10;
$total_pages = ceil($total_customers / $per_page);
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $per_page;

// Get customers with pagination
$query = "SELECT c.*, CONCAT(u.first_name, ' ', u.last_name) as created_by_name 
          FROM customers c 
          LEFT JOIN users u ON c.created_by = u.id 
          $where_clause 
          ORDER BY c.created_at DESC 
          LIMIT :limit OFFSET :offset";

$stmt = $db->prepare($query);

// Bind all parameters including search and user_id if needed
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - Customer Management System</title>
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
            <h1>Customers</h1>
            <nav>
                <a href="dashboard.php">Dashboard</a>
                <a href="customers.php" class="active">Customers</a>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="employees.php">Employees</a>
                <?php endif; ?>
                <a href="profile.php">Profile</a>
                <a href="logout.php" class="logout">Logout</a>
            </nav>
        </header>
        
        <main class="dashboard-content">
            <div class="page-header">
                <h2>Customer List</h2>
                <a href="register_customer.php" class="btn btn-primary">Add New Customer</a>
            </div>
            
            <div class="search-filter">
                <form action="customers.php" method="get" class="search-form">
                    <input type="text" name="search" placeholder="Search customers..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn">Search</button>
                    <?php if (!empty($search)): ?>
                        <a href="customers.php" class="btn btn-secondary">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <?php if (count($customers) > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Contact</th>
                                <th>Location</th>
                                <th>Created By</th>
                                <th>Date Added</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $customer): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></strong>
                                    </td>
                                    <td>
                                        <div><?php echo !empty($customer['email']) ? htmlspecialchars($customer['email']) : '<span class="text-muted">No email</span>'; ?></div>
                                        <?php if (!empty($customer['phone'])): ?>
                                            <div class="text-small"><?php echo htmlspecialchars($customer['phone']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $location = [];
                                        if (!empty($customer['city'])) $location[] = $customer['city'];
                                        if (!empty($customer['state'])) $location[] = $customer['state'];
                                        if (!empty($customer['country'])) $location[] = $customer['country'];
                                        echo !empty($location) ? htmlspecialchars(implode(', ', $location)) : '<span class="text-muted">-</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo !empty($customer['created_by_name']) ? htmlspecialchars($customer['created_by_name']) : 'Guest'; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                                    <td class="actions">
                                        <a href="view_customer.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-view" title="View">👁️</a>
                                        <a href="edit_customer.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-edit" title="Edit">✏️</a>
                                        <?php if ($user_role === 'admin' || $customer['created_by'] == $user_id): ?>
                                            <a href="delete_customer.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-delete" title="Delete" onclick="return confirm('Are you sure you want to delete this customer?');">🗑️</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($current_page > 1): ?>
                            <a href="?page=1<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="page-link">&laquo; First</a>
                            <a href="?page=<?php echo ($current_page - 1) . (!empty($search) ? '&search=' . urlencode($search) : ''); ?>" class="page-link">&lsaquo; Previous</a>
                        <?php endif; ?>
                        
                        <span class="page-info">Page <?php echo $current_page; ?> of <?php echo $total_pages; ?></span>
                        
                        <?php if ($current_page < $total_pages): ?>
                            <a href="?page=<?php echo ($current_page + 1) . (!empty($search) ? '&search=' . urlencode($search) : ''); ?>" class="page-link">Next &rsaquo;</a>
                            <a href="?page=<?php echo $total_pages . (!empty($search) ? '&search=' . urlencode($search) : ''); ?>" class="page-link">Last &raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="no-data">
                    <?php if (!empty($search)): ?>
                        <p>No customers found matching your search. <a href="customers.php">Clear search</a> or <a href="register_customer.php">add a new customer</a>.</p>
                    <?php else: ?>
                        <p>No customers found. <a href="register_customer.php">Add your first customer</a>.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
        
        <footer class="dashboard-footer">
            <p>&copy; <?php echo date('Y'); ?> Customer Management System. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
