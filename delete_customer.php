<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';

// Check if customer ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: customers.php?error=invalid_id");
    exit();
}

$customer_id = (int)$_GET['id'];
$database = new Database();
$db = $database->getConnection();

// Get customer details to check permissions
$query = "SELECT * FROM customers WHERE id = :id";

// Add access control for employees (can only delete their own customers)
if ($_SESSION['role'] === 'employee') {
    $query .= " AND created_by = :user_id";
}

$stmt = $db->prepare($query);
$stmt->bindParam(':id', $customer_id, PDO::PARAM_INT);

if ($_SESSION['role'] === 'employee') {
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
}

$stmt->execute();
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

// If customer not found or not authorized
if (!$customer) {
    header("Location: customers.php?error=not_found");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Delete the customer
    $query = "DELETE FROM customers WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $customer_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Customer deleted successfully.";
        header("Location: customers.php");
        exit();
    } else {
        $error = "Error deleting customer. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Customer - Customer Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="dashboard">
    <div class="container">
        <header class="dashboard-header">
            <h1>Delete Customer</h1>
            <nav>
                <a href="dashboard.php">Dashboard</a>
                <a href="customers.php">Customers</a>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="employees.php">Employees</a>
                <?php endif; ?>
                <a href="profile.php">Profile</a>
                <a href="logout.php" class="logout">Logout</a>
            </nav>
        </header>
        
        <main class="dashboard-content">
            <div class="page-header">
                <div>
                    <h2>Delete Customer</h2>
                    <nav class="breadcrumb">
                        <a href="dashboard.php">Dashboard</a> &raquo;
                        <a href="customers.php">Customers</a> &raquo;
                        <a href="view_customer.php?id=<?php echo $customer_id; ?>">View Customer</a> &raquo;
                        <span>Delete</span>
                    </nav>
                </div>
                <div class="actions">
                    <a href="view_customer.php?id=<?php echo $customer_id; ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-warning">
                        <h4>Warning!</h4>
                        <p>Are you sure you want to delete the following customer? This action cannot be undone.</p>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($customer['email']); ?></p>
                    </div>
                    
                    <form action="delete_customer.php?id=<?php echo $customer_id; ?>" method="post" onsubmit="return confirm('Are you absolutely sure you want to delete this customer? This action cannot be undone.');">
                        <div class="form-actions">
                            <button type="submit" class="btn btn-danger">
                                <span class="icon">🗑️</span> Confirm Delete
                            </button>
                            <a href="view_customer.php?id=<?php echo $customer_id; ?>" class="btn btn-secondary">Cancel</a>
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
