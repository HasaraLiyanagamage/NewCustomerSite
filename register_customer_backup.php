<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
$is_employee = isset($_SESSION['user_id']) && in_array($_SESSION['role'], ['admin', 'employee']);

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $db = $database->getConnection();
    
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
    $required_fields = ['first_name', 'last_name', 'email'];
    foreach ($required_fields as $field) {
        if (empty($$field)) {
            $error = "Please fill in all required fields.";
            break;
        }
    }
    
    if (empty($error)) {
        // Check if email already exists
        $query = "SELECT id FROM customers WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $error = "A customer with this email already exists.";
        } else {
            // Get the user ID if logged in, otherwise use 0 for guest registrations
            $created_by = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
            
            // Insert new customer
            $query = "INSERT INTO customers (first_name, last_name, email, phone, address, city, state, postal_code, country, created_by) 
                     VALUES (:first_name, :last_name, :email, :phone, :address, :city, :state, :postal_code, :country, :created_by)";
            
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
                ':created_by' => $created_by
            ]);
            
            if ($result) {
                $success = "Customer registration successful!";
                // Clear form
                $_POST = array();
            } else {
                $error = "Something went wrong. Please try again.";
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
    <title><?php echo $is_employee ? 'Register New Customer' : 'Customer Registration'; ?> - Customer Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-box">
            <h2><?php echo $is_employee ? 'Register New Customer' : 'Customer Registration'; ?></h2>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                    <?php if (!$is_employee): ?>
                        <p>You can now <a href="login.php">login</a> to view your details.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if (empty($success)): ?>
            <form action="register_customer.php" method="post">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="state">State/Province</label>
                        <input type="text" id="state" name="state" value="<?php echo isset($_POST['state']) ? htmlspecialchars($_POST['state']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="postal_code">Postal Code</label>
                        <input type="text" id="postal_code" name="postal_code" value="<?php echo isset($_POST['postal_code']) ? htmlspecialchars($_POST['postal_code']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="country">Country</label>
                        <input type="text" id="country" name="country" value="<?php echo isset($_POST['country']) ? htmlspecialchars($_POST['country']) : ''; ?>">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Register Customer</button>
            </form>
            <?php endif; ?>
            
            <p class="auth-links">
                <?php if ($is_employee): ?>
                    <a href="dashboard.php">Back to Dashboard</a>
                <?php else: ?>
                    Already registered? <a href="login.php">Login here</a>
                <?php endif; ?>
            </p>
        </div>
    </div>
</body>
</html>
