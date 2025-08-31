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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --secondary: #6b7280;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --light: #f9fafb;
            --dark: #111827;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --radius-sm: 0.25rem;
            --radius: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
            --radius-2xl: 1.5rem;
            --radius-full: 9999px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            background-color: #f9fafb;
            color: var(--gray-800);
            line-height: 1.5;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background: white;
            box-shadow: var(--shadow-sm);
            padding: 1.5rem 0;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            z-index: 50;
        }
        
        .sidebar-header {
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            margin-bottom: 1rem;
        }
        
        .logo {
            display: flex;
            align-items: center;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
        }
        
        .logo i {
            margin-right: 0.75rem;
            font-size: 1.75rem;
        }
        
        .nav-menu {
            flex: 1;
            overflow-y: auto;
            padding: 0 0.75rem;
        }
        
        .nav-title {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.5rem 1rem;
            margin: 0.5rem 0;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: var(--gray-600);
            text-decoration: none;
            border-radius: var(--radius);
            margin-bottom: 0.25rem;
            transition: all 0.2s ease;
        }
        
        .nav-link i {
            width: 24px;
            margin-right: 0.75rem;
            font-size: 1.1rem;
            text-align: center;
        }
        
        .nav-link:hover {
            background-color: var(--gray-100);
            color: var(--primary);
        }
        
        .nav-link.active {
            background-color: var(--primary);
            color: white;
            font-weight: 500;
        }
        
        .nav-link.active:hover {
            background-color: var(--primary-hover);
        }
        
        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Top Bar */
        .top-bar {
            background: white;
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 40;
        }
        
        .search-bar {
            position: relative;
            width: 100%;
            max-width: 500px;
        }
        
        .search-bar input {
            width: 100%;
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
            font-size: 0.9375rem;
            transition: all 0.2s;
        }
        
        .search-bar input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
        }
        
        .search-bar i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-500);
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            position: relative;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 0.5rem;
            text-transform: uppercase;
            font-size: 0.875rem;
        }
        
        .user-name {
            font-weight: 500;
            margin-right: 0.5rem;
        }
        
        .dropdown-arrow {
            color: var(--gray-500);
            cursor: pointer;
        }
        
        /* Page Header */
        .page-header {
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            background-color: white;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-900);
            display: flex;
            align-items: center;
        }
        
        .page-title i {
            margin-right: 0.75rem;
            color: var(--primary);
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-top: 0.5rem;
        }
        
        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        .breadcrumb-separator {
            margin: 0 0.5rem;
            color: var(--gray-400);
        }
        
        .page-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        
        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            border-radius: var(--radius);
            font-weight: 500;
            font-size: 0.875rem;
            line-height: 1.25rem;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid transparent;
            text-decoration: none;
            white-space: nowrap;
        }
        
        .btn i {
            margin-right: 0.5rem;
            font-size: 1rem;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-hover);
        }
        
        .btn-outline {
            background-color: transparent;
            border-color: var(--gray-300);
            color: var(--gray-700);
        }
        
        .btn-outline:hover {
            background-color: var(--gray-100);
            border-color: var(--gray-400);
        }
        
        .btn-danger {
            background-color: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #dc2626;
        }
        
        /* Card Styles */
        .card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        
        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
            margin: 0;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .card-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--gray-200);
            background-color: var(--gray-50);
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }
        
        /* Form Styles */
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.25rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--gray-700);
        }
        
        .form-control {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
            font-size: 0.9375rem;
            transition: all 0.2s;
            background-color: white;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
        }
        
        .form-text {
            display: block;
            margin-top: 0.25rem;
            font-size: 0.8125rem;
            color: var(--gray-500);
        }
        
        /* Alert Styles */
        .alert {
            padding: 1rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
        }
        
        .alert i {
            margin-right: 0.75rem;
            font-size: 1.25rem;
            margin-top: 0.125rem;
        }
        
        .alert-danger {
            background-color: #fef2f2;
            color: #b91c1c;
            border-left: 4px solid #ef4444;
        }
        
        .alert-success {
            background-color: #f0fdf4;
            color: #047857;
            border-left: 4px solid #10b981;
        }
        
        /* Main Content */
        .content-wrapper {
            padding: 2rem;
            flex: 1;
            background-color: var(--gray-50);
        }
        
        /* Responsive Adjustments */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                z-index: 50;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .mobile-menu-btn {
                display: block;
                margin-right: 1rem;
                background: none;
                border: none;
                font-size: 1.5rem;
                color: var(--gray-600);
                cursor: pointer;
            }
        }
        
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .page-actions {
                width: 100%;
                margin-top: 1rem;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
        
        @media (max-width: 480px) {
            .content-wrapper {
                padding: 1rem;
            }
            
            .card-body {
                padding: 1.25rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
        }
    </style>
</head>
<body class="dashboard">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="dashboard.php" class="logo">
                <i class="fas fa-paw"></i>
                <span>NetworkPanda</span>
            </a>
        </div>
        
        <nav class="nav-menu">
            <div class="nav-title">Main</div>
            <a href="dashboard.php" class="nav-link">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="customers.php" class="nav-link active">
                <i class="fas fa-users"></i>
                <span>Customers</span>
            </a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="employees.php" class="nav-link">
                <i class="fas fa-user-tie"></i>
                <span>Employees</span>
            </a>
            <?php endif; ?>
            
            <div class="nav-title">Account</div>
            <a href="profile.php" class="nav-link">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
            <a href="logout.php" class="nav-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </nav>
    </aside>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <header class="top-bar">
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search...">
            </div>
            
            <div class="user-menu">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['first_name'], 0, 1) . substr($_SESSION['last_name'], 0, 1)); ?>
                </div>
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></span>
                <i class="fas fa-chevron-down dropdown-arrow"></i>
            </div>
        </header>
        
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    <i class="fas fa-user-edit"></i>
                    <span>Edit Customer</span>
                </h1>
                <div class="breadcrumb">
                    <a href="dashboard.php">Dashboard</a>
                    <span class="breadcrumb-separator">/</span>
                    <a href="customers.php">Customers</a>
                    <span class="breadcrumb-separator">/</span>
                    <a href="view_customer.php?id=<?php echo $customer['id']; ?>"><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></a>
                    <span class="breadcrumb-separator">/</span>
                    <span>Edit</span>
                </div>
            </div>
            <div class="page-actions">
                <a href="view_customer.php?id=<?php echo $customer['id']; ?>" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Customer
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="content-wrapper">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <div><?php echo $error; ?></div>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Customer Information</h2>
                </div>
                <form action="edit_customer.php?id=<?php echo $customer['id']; ?>" method="post">
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" id="first_name" name="first_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($customer['first_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" id="last_name" name="last_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($customer['last_name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" id="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($customer['email']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="tel" id="phone" name="phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($customer['phone']); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" id="address" name="address" class="form-control" 
                                   value="<?php echo htmlspecialchars($customer['address']); ?>">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="city" class="form-label">City</label>
                                <input type="text" id="city" name="city" class="form-control" 
                                       value="<?php echo htmlspecialchars($customer['city']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="state" class="form-label">State/Province</label>
                                <input type="text" id="state" name="state" class="form-control" 
                                       value="<?php echo htmlspecialchars($customer['state']); ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="postal_code" class="form-label">Postal Code</label>
                                <input type="text" id="postal_code" name="postal_code" class="form-control" 
                                       value="<?php echo htmlspecialchars($customer['postal_code']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" id="country" name="country" class="form-control" 
                                       value="<?php echo htmlspecialchars($customer['country']); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <a href="view_customer.php?id=<?php echo $customer['id']; ?>" class="btn btn-outline">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Toggle mobile menu
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const menuBtn = document.getElementById('mobileMenuBtn');
            
            if (window.innerWidth <= 1024 && !sidebar.contains(event.target) && !menuBtn.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 1024) {
                document.querySelector('.sidebar').classList.remove('active');
            }
        });
    </script>
</body>
</html>
