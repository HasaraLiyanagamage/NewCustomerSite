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

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $postal_code = trim($_POST['postal_code']);
    $country = trim($_POST['country']);
    
    // Validate required fields
    $error = '';
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $error = "First name, last name, and email are required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Check if email already exists for another customer
        $query = "SELECT id FROM customers WHERE email = :email AND id != :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id', $customer_id, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $error = "A customer with this email already exists.";
        }
    }
    
    // If no errors, update the customer
    if (empty($error)) {
        $query = "UPDATE customers SET 
                  first_name = :first_name, 
                  last_name = :last_name, 
                  email = :email, 
                  phone = :phone, 
                  address = :address, 
                  city = :city, 
                  state = :state, 
                  postal_code = :postal_code, 
                  country = :country,
                  updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";
        
        $stmt = $db->prepare($query);
        $result = $stmt->execute([
            ':first_name' => $first_name,
            ':last_name' => $last_name,
            ':email' => $email,
            ':phone' => $phone,
            ':address' => $address,
            ':city' => $city,
            ':state' => $state,
            ':postal_code' => $postal_code,
            ':country' => $country,
            ':id' => $customer_id
        ]);
        
        if ($result) {
            $_SESSION['success_message'] = "Customer updated successfully.";
            header("Location: view_customer.php?id=" . $customer_id);
            exit();
        } else {
            $error = "Error updating customer. Please try again.";
        }
    }
}

// Get customer details
$query = "SELECT * FROM customers WHERE id = :id";

// Add access control for employees (can only edit their own customers)
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

$page_title = "Edit Customer: " . htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']);
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
            <h1>Edit Customer</h1>
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
                    <h2>Edit Customer: <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></h2>
                    <nav class="breadcrumb">
                        <a href="dashboard.php">Dashboard</a> &raquo;
                        <a href="customers.php">Customers</a> &raquo;
                        <a href="view_customer.php?id=<?php echo $customer['id']; ?>">View Customer</a> &raquo;
                        <span>Edit</span>
                    </nav>
                </div>
                <div class="actions">
                    <a href="view_customer.php?id=<?php echo $customer['id']; ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <form action="edit_customer.php?id=<?php echo $customer['id']; ?>" method="post">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <input type="text" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($customer['first_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($customer['last_name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($customer['email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($customer['phone']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address" 
                                   value="<?php echo htmlspecialchars($customer['address']); ?>">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" id="city" name="city" 
                                       value="<?php echo htmlspecialchars($customer['city']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="state">State/Province</label>
                                <input type="text" id="state" name="state" 
                                       value="<?php echo htmlspecialchars($customer['state']); ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="postal_code">Postal Code</label>
                                <input type="text" id="postal_code" name="postal_code" 
                                       value="<?php echo htmlspecialchars($customer['postal_code']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="country">Country</label>
                                <input type="text" id="country" name="country" 
                                       value="<?php echo htmlspecialchars($customer['country']); ?>">
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Update Customer</button>
                            <a href="view_customer.php?id=<?php echo $customer['id']; ?>" class="btn btn-secondary">Cancel</a>
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
