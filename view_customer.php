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
    header("Location: customers.php");
    exit();
}

$customer_id = (int)$_GET['id'];
$database = new Database();
$db = $database->getConnection();

// Get customer details
$query = "SELECT c.*, CONCAT(u.first_name, ' ', u.last_name) as created_by_name 
          FROM customers c 
          LEFT JOIN users u ON c.created_by = u.id 
          WHERE c.id = :id";

// Add access control for employees (can only view their own customers)
if ($_SESSION['role'] === 'employee') {
    $query .= " AND c.created_by = :user_id";
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

$page_title = "View Customer: " . htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Customer Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="dashboard">
    <div class="container">
        <header class="dashboard-header">
            <h1>View Customer</h1>
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
                    <h2><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></h2>
                    <nav class="breadcrumb">
                        <a href="dashboard.php">Dashboard</a> &raquo;
                        <a href="customers.php">Customers</a> &raquo;
                        <span>View Customer</span>
                    </nav>
                </div>
                <div class="actions">
                    <a href="customers.php" class="btn btn-secondary">Back to List</a>
                    <a href="edit_customer.php?id=<?php echo $customer['id']; ?>" class="btn btn-primary">Edit Customer</a>
                </div>
            </div>
            
            <div class="customer-details">
                <div class="card">
                    <div class="card-header">
                        <h3>Customer Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="detail-row">
                            <div class="detail-label">Full Name</div>
                            <div class="detail-value"><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="detail-label">Email</div>
                            <div class="detail-value">
                                <?php if (!empty($customer['email'])): ?>
                                    <a href="mailto:<?php echo htmlspecialchars($customer['email']); ?>">
                                        <?php echo htmlspecialchars($customer['email']); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">Not provided</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="detail-label">Phone</div>
                            <div class="detail-value">
                                <?php if (!empty($customer['phone'])): ?>
                                    <a href="tel:<?php echo htmlspecialchars(preg_replace('/[^0-9+]/', '', $customer['phone'])); ?>">
                                        <?php echo htmlspecialchars($customer['phone']); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">Not provided</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($customer['address']) || !empty($customer['city']) || !empty($customer['state']) || !empty($customer['postal_code']) || !empty($customer['country'])): ?>
                        <div class="detail-row">
                            <div class="detail-label">Address</div>
                            <div class="detail-value">
                                <?php 
                                $address_parts = [];
                                if (!empty($customer['address'])) $address_parts[] = $customer['address'];
                                if (!empty($customer['city'])) $address_parts[] = $customer['city'];
                                if (!empty($customer['state'])) $address_parts[] = $customer['state'];
                                if (!empty($customer['postal_code'])) $address_parts[] = $customer['postal_code'];
                                if (!empty($customer['country'])) $address_parts[] = $customer['country'];
                                
                                echo !empty($address_parts) ? nl2br(htmlspecialchars(implode("\n", $address_parts))) : '<span class="text-muted">Not provided</span>';
                                ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="detail-row">
                            <div class="detail-label">Account Created</div>
                            <div class="detail-value">
                                <?php echo date('F j, Y \a\t g:i A', strtotime($customer['created_at'])); ?>
                                <?php if (!empty($customer['created_by_name'])): ?>
                                    <div class="text-muted">by <?php echo htmlspecialchars($customer['created_by_name']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="detail-label">Last Updated</div>
                            <div class="detail-value">
                                <?php 
                                $updated_at = !empty($customer['updated_at']) && $customer['updated_at'] !== $customer['created_at'] 
                                    ? date('F j, Y \a\t g:i A', strtotime($customer['updated_at'])) 
                                    : 'Never';
                                echo $updated_at;
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <a href="edit_customer.php?id=<?php echo $customer['id']; ?>" class="btn btn-primary">
                        <span class="icon">✏️</span> Edit Customer
                    </a>
                    <?php if ($_SESSION['role'] === 'admin' || $customer['created_by'] == $_SESSION['user_id']): ?>
                        <a href="delete_customer.php?id=<?php echo $customer['id']; ?>" 
                           class="btn btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this customer? This action cannot be undone.');">
                            <span class="icon">🗑️</span> Delete Customer
                        </a>
                    <?php endif; ?>
                    <a href="customers.php" class="btn btn-secondary">
                        <span class="icon">←</span> Back to Customers
                    </a>
                </div>
            </div>
        </main>
        
        <footer class="dashboard-footer">
            <p>&copy; <?php echo date('Y'); ?> Customer Management System. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
