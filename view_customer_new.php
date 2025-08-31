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
$user_role = strtolower(htmlspecialchars($_SESSION['role'] ?? 'user'));
$user_id = $_SESSION['user_id'];
$user_name = htmlspecialchars($_SESSION['name'] ?? 'User');

// Get customer details
$query = "SELECT c.*, CONCAT(u.first_name, ' ', u.last_name) as created_by_name 
          FROM customers c 
          LEFT JOIN users u ON c.created_by = u.id 
          WHERE c.id = :id";

// Add access control for employees (can only view their own customers)
if ($user_role === 'employee') {
    $query .= " AND c.created_by = :user_id";
}

$stmt = $db->prepare($query);
$stmt->bindParam(':id', $customer_id, PDO::PARAM_INT);

if ($user_role === 'employee') {
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Reuse styles from customers.php */
        :root {
            --sidebar-width: 250px;
            --header-height: 70px;
            --primary-color: #4361ee;
            --primary-light: #eef2ff;
            --primary-dark: #3730a3;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
            --light-color: #f9fafb;
            --dark-color: #111827;
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
            --radius-sm: 0.25rem;
            --radius: 0.375rem;
            --radius-lg: 0.5rem;
            --radius-xl: 0.75rem;
            --radius-2xl: 1rem;
            --radius-full: 9999px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            background-color: var(--gray-100);
            color: var(--gray-800);
            line-height: 1.5;
        }

        /* Layout */
        .app-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: white;
            box-shadow: var(--shadow-md);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 50;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sidebar-menu {
            padding: 1rem 0;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: var(--gray-600);
            text-decoration: none;
            transition: all 0.2s;
            font-weight: 500;
        }

        .menu-item:hover, .menu-item.active {
            background-color: var(--primary-light);
            color: var(--primary-color);
        }

        .menu-item i {
            width: 24px;
            margin-right: 0.75rem;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Bar */
        .top-bar {
            background: white;
            height: var(--header-height);
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 40;
        }

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .page-title {
            display: flex;
            flex-direction: column;
        }

        .page-title h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-800);
            margin: 0;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            font-size: 0.875rem;
            color: var(--gray-500);
            margin-top: 0.25rem;
        }

        .breadcrumb a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .breadcrumb-separator {
            margin: 0 0.5rem;
            color: var(--gray-400);
        }

        /* Content Wrapper */
        .content-wrapper {
            flex: 1;
            padding: 2rem;
            max-width: 100%;
            overflow-x: auto;
        }

        /* Cards */
        .card {
            background: white;
            border-radius: var(--radius-lg);
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
            flex-wrap: wrap;
            gap: 1rem;
        }

        .card-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-800);
            margin: 0;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Buttons */
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
        }

        .btn i {
            margin-right: 0.5rem;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.8125rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-outline-primary {
            background-color: white;
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-light);
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background-color: #dc2626;
        }

        /* Customer Profile */
        .customer-profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
        }

        .customer-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: var(--primary-light);
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 600;
            margin-right: 1.5rem;
        }

        .customer-info {
            flex: 1;
        }

        .customer-name {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--gray-900);
            margin: 0 0 0.25rem;
        }

        .customer-email {
            color: var(--gray-600);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }

        .customer-email i {
            margin-right: 0.5rem;
            color: var(--gray-500);
        }

        .customer-meta {
            display: flex;
            gap: 1.5rem;
            margin-top: 1rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            font-size: 0.875rem;
            color: var(--gray-600);
        }

        .meta-item i {
            margin-right: 0.5rem;
            color: var(--gray-500);
        }

        /* Details Grid */
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .detail-group {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow);
        }

        .detail-group h3 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-700);
            margin: 0 0 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .detail-row {
            display: flex;
            margin-bottom: 1rem;
        }

        .detail-label {
            width: 120px;
            color: var(--gray-600);
            font-size: 0.875rem;
        }

        .detail-value {
            flex: 1;
            font-weight: 500;
            color: var(--gray-800);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-active {
            background-color: #ecfdf5;
            color: #059669;
        }

        .status-inactive {
            background-color: #fef2f2;
            color: #dc2626;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.75rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--gray-200);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .top-bar {
                padding-left: 1rem;
                padding-right: 1rem;
            }
            
            .content-wrapper {
                padding: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .customer-profile-header {
                flex-direction: column;
                text-align: center;
            }
            
            .customer-avatar {
                margin: 0 0 1rem 0;
            }
            
            .customer-meta {
                justify-content: center;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .action-buttons .btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .customer-meta {
                flex-direction: column;
                gap: 0.5rem;
                align-items: center;
            }
            
            .detail-row {
                flex-direction: column;
                gap: 0.25rem;
            }
            
            .detail-label {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="brand">
                    <i class="fas fa-users-cog"></i>
                    <span>CMS Pro</span>
                </a>
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <nav class="sidebar-menu">
                <a href="dashboard.php" class="menu-item">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="customers.php" class="menu-item active">
                    <i class="fas fa-users"></i>
                    <span>Customers</span>
                </a>
                <?php if ($user_role === 'admin'): ?>
                <a href="employees.php" class="menu-item">
                    <i class="fas fa-user-tie"></i>
                    <span>Employees</span>
                </a>
                <?php endif; ?>
                <a href="profile.php" class="menu-item">
                    <i class="fas fa-user"></i>
                    <span>My Profile</span>
                </a>
                <a href="logout.php" class="menu-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Bar -->
            <header class="top-bar">
                <div class="page-title">
                    <h1>Customer Details</h1>
                </div>
                <div class="user-menu">
                    <button class="btn btn-icon">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </button>
                    <div class="user-avatar">
                        <?php 
                        $initials = '';
                        $name_parts = explode(' ', $user_name);
                        foreach ($name_parts as $part) {
                            $initials .= strtoupper(substr($part, 0, 1));
                        }
                        echo $initials;
                        ?>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="content-wrapper">
                <div class="page-header">
                    <div>
                        <h1>View Customer</h1>
                        <nav class="breadcrumb">
                            <a href="dashboard.php">Dashboard</a>
                            <span class="breadcrumb-separator">/</span>
                            <a href="customers.php">Customers</a>
                            <span class="breadcrumb-separator">/</span>
                            <span>View</span>
                        </nav>
                    </div>
                    <div>
                        <a href="customers.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left"></i> Back to Customers
                        </a>
                    </div>
                </div>

                <!-- Customer Profile -->
                <div class="card">
                    <div class="card-body">
                        <div class="customer-profile-header">
                            <div class="customer-avatar">
                                <?php echo strtoupper(substr($customer['first_name'], 0, 1) . substr($customer['last_name'], 0, 1)); ?>
                            </div>
                            <div class="customer-info">
                                <h1 class="customer-name">
                                    <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>
                                    <span class="status-badge status-active" style="margin-left: 0.75rem;">
                                        <i class="fas fa-circle"></i> Active
                                    </span>
                                </h1>
                                <div class="customer-email">
                                    <i class="fas fa-envelope"></i>
                                    <?php echo htmlspecialchars($customer['email']); ?>
                                </div>
                                <div class="customer-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-phone"></i>
                                        <?php echo !empty($customer['phone']) ? htmlspecialchars($customer['phone']) : 'N/A'; ?>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-id-card"></i>
                                        ID: <?php echo htmlspecialchars($customer['id']); ?>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-calendar-alt"></i>
                                        Member since <?php echo date('M d, Y', strtotime($customer['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Details Grid -->
                        <div class="details-grid">
                            <!-- Personal Information -->
                            <div class="detail-group">
                                <h3><i class="fas fa-user-circle"></i> Personal Information</h3>
                                <div class="detail-row">
                                    <div class="detail-label">First Name</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($customer['first_name']); ?></div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">Last Name</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($customer['last_name']); ?></div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">Date of Birth</div>
                                    <div class="detail-value">
                                        <?php echo !empty($customer['date_of_birth']) ? date('M d, Y', strtotime($customer['date_of_birth'])) : 'N/A'; ?>
                                    </div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">Gender</div>
                                    <div class="detail-value">
                                        <?php 
                                        $gender = !empty($customer['gender']) ? ucfirst($customer['gender']) : 'N/A';
                                        echo htmlspecialchars($gender);
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="detail-group">
                                <h3><i class="fas fa-address-book"></i> Contact Information</h3>
                                <div class="detail-row">
                                    <div class="detail-label">Email</div>
                                    <div class="detail-value">
                                        <a href="mailto:<?php echo htmlspecialchars($customer['email']); ?>">
                                            <?php echo htmlspecialchars($customer['email']); ?>
                                        </a>
                                    </div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">Phone</div>
                                    <div class="detail-value">
                                        <?php echo !empty($customer['phone']) ? 
                                            '<a href="tel:' . htmlspecialchars($customer['phone']) . '">' . 
                                            htmlspecialchars($customer['phone']) . '</a>' : 'N/A'; ?>
                                    </div>
                                </div>
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
                                        
                                        echo !empty($address_parts) ? nl2br(htmlspecialchars(implode("\n", $address_parts))) : 'N/A';
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Account Information -->
                            <div class="detail-group">
                                <h3><i class="fas fa-info-circle"></i> Account Information</h3>
                                <div class="detail-row">
                                    <div class="detail-label">Customer ID</div>
                                    <div class="detail-value">
                                        #<?php echo htmlspecialchars(str_pad($customer['id'], 6, '0', STR_PAD_LEFT)); ?>
                                    </div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">Status</div>
                                    <div class="detail-value">
                                        <span class="status-badge status-active">
                                            <i class="fas fa-circle"></i> Active
                                        </span>
                                    </div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">Created On</div>
                                    <div class="detail-value">
                                        <?php echo date('M d, Y', strtotime($customer['created_at'])); ?>
                                    </div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">Last Updated</div>
                                    <div class="detail-value">
                                        <?php 
                                        $updated_at = !empty($customer['updated_at']) ? $customer['updated_at'] : $customer['created_at'];
                                        echo date('M d, Y', strtotime($updated_at));
                                        ?>
                                    </div>
                                </div>
                                <div class="detail-row">
                                    <div class="detail-label">Added By</div>
                                    <div class="detail-value">
                                        <?php echo !empty($customer['created_by_name']) ? 
                                            htmlspecialchars($customer['created_by_name']) : 'System'; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div class="detail-group">
                                <h3><i class="fas fa-ellipsis-h"></i> Additional Information</h3>
                                <?php if (!empty($customer['notes'])): ?>
                                    <div class="detail-row">
                                        <div class="detail-label">Notes</div>
                                        <div class="detail-value">
                                            <?php echo nl2br(htmlspecialchars($customer['notes'])); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Add any additional custom fields here -->
                                <?php 
                                // Example of how to add custom fields
                                $custom_fields = [
                                    'company' => ['icon' => 'building', 'label' => 'Company'],
                                    'website' => ['icon' => 'globe', 'label' => 'Website'],
                                    'source' => ['icon' => 'search', 'label' => 'Lead Source'],
                                ];
                                
                                $has_custom_fields = false;
                                
                                foreach ($custom_fields as $field => $data) {
                                    if (!empty($customer[$field])) {
                                        $has_custom_fields = true;
                                        break;
                                    }
                                }
                                
                                if ($has_custom_fields):
                                ?>
                                    <?php foreach ($custom_fields as $field => $data): ?>
                                        <?php if (!empty($customer[$field])): ?>
                                            <div class="detail-row">
                                                <div class="detail-label"><?php echo htmlspecialchars($data['label']); ?></div>
                                                <div class="detail-value">
                                                    <?php if ($field === 'website'): ?>
                                                        <a href="<?php echo htmlspecialchars($customer[$field]); ?>" target="_blank" rel="noopener noreferrer">
                                                            <?php echo htmlspecialchars($customer[$field]); ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <?php echo htmlspecialchars($customer[$field]); ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="action-buttons">
                            <a href="edit_customer.php?id=<?php echo $customer['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Edit Customer
                            </a>
                            <a href="#" class="btn btn-outline-primary">
                                <i class="fas fa-file-invoice"></i> Create Invoice
                            </a>
                            <a href="#" class="btn btn-outline-primary">
                                <i class="fas fa-envelope"></i> Send Email
                            </a>
                            <?php if ($user_role === 'admin' || $customer['created_by'] == $user_id): ?>
                                <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?php echo $customer['id']; ?>)">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="footer">
                <div class="container">
                    <p>&copy; <?php echo date('Y'); ?> Customer Management System. All rights reserved.</p>
                </div>
            </footer>
        </div>
    </div>

    <script>
        // Toggle sidebar on mobile
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                });
            }
            
            // Close sidebar when clicking outside
            document.addEventListener('click', function(event) {
                if (!sidebar.contains(event.target) && event.target !== sidebarToggle) {
                    sidebar.classList.remove('active');
                }
            });
        });

        // Confirm before deleting
        function confirmDelete(customerId) {
            if (confirm('Are you sure you want to delete this customer? This action cannot be undone.')) {
                window.location.href = 'delete_customer.php?id=' + customerId;
            }
        }
    </script>
</body>
</html>
