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

$user_role = strtolower(htmlspecialchars($_SESSION['role'] ?? 'user'));
$user_id = $_SESSION['user_id'];
$user_name = htmlspecialchars($_SESSION['name'] ?? 'User');

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
          LIMIT :offset, :per_page";

$stmt = $db->prepare($query);

// Bind search parameter if it exists
if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}

// Bind user_id parameter for employees
if ($user_role === 'employee') {
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
}

$stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
$stmt->bindValue(':per_page', (int)$per_page, PDO::PARAM_INT);
$stmt->execute();
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - Customer Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Reuse styles from dashboard */
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
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
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

        .page-title h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-800);
            margin: 0;
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

        .btn-success {
            background-color: var(--success-color);
            color: white;
        }

        .btn-success:hover {
            background-color: #0d9c6e;
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background-color: #dc2626;
        }

        /* Search Bar */
        .search-bar {
            position: relative;
            max-width: 400px;
            width: 100%;
        }

        .search-bar input {
            width: 100%;
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .search-bar input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .search-bar i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
        }

        /* Table */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }

        .table th {
            background-color: var(--gray-50);
            color: var(--gray-600);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }

        .table tbody tr {
            transition: background-color 0.2s;
        }

        .table tbody tr:hover {
            background-color: var(--gray-50);
        }

        .table .avatar {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background-color: var(--primary-light);
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
            margin-right: 0.75rem;
        }

        .customer-info {
            display: flex;
            align-items: center;
        }

        .customer-name {
            font-weight: 500;
            color: var(--gray-900);
        }

        .customer-email {
            font-size: 0.8125rem;
            color: var(--gray-500);
            margin-top: 0.125rem;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.5rem;
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

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: flex-end;
            margin-top: 1.5rem;
            gap: 0.5rem;
        }

        .pagination .btn {
            min-width: 2.25rem;
            height: 2.25rem;
            padding: 0 0.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--gray-300);
            background-color: white;
            color: var(--gray-700);
        }

        .pagination .btn:hover {
            background-color: var(--gray-100);
        }

        .pagination .btn.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .pagination .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 1.5rem;
            color: var(--gray-500);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--gray-300);
        }

        .empty-state h3 {
            font-size: 1.25rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--gray-700);
        }

        .empty-state p {
            margin-bottom: 1.5rem;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Footer */
        .footer {
            padding: 1.5rem 2rem;
            background-color: white;
            border-top: 1px solid var(--gray-200);
            text-align: center;
            color: var(--gray-500);
            font-size: 0.875rem;
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
                padding: 1rem;
            }
        }

        @media (max-width: 640px) {
            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .search-bar {
                max-width: 100%;
                margin-top: 0.5rem;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 0.25rem;
            }
            
            .action-buttons .btn {
                width: 100%;
                justify-content: center;
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
                    <h1>Customers</h1>
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
                    <h2>Customer Management</h2>
                    <a href="add_customer.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Customer
                    </a>
                </div>

                <!-- Search and Filter -->
                <div class="card">
                    <div class="card-body">
                        <form action="" method="GET" class="search-bar">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" placeholder="Search customers..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-primary" style="margin-left: 0.5rem;">Search</button>
                            <?php if (!empty($search)): ?>
                                <a href="customers.php" class="btn btn-outline-primary" style="margin-left: 0.5rem;">Clear</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Customers Table -->
                <div class="card">
                    <div class="card-header">
                        <h2>All Customers</h2>
                        <div class="total-customers">
                            <span class="text-muted">Total: <?php echo number_format($total_customers); ?></span>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (count($customers) > 0): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Customer</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($customers as $customer): ?>
                                            <tr>
                                                <td>
                                                    <div class="customer-info">
                                                        <div class="avatar">
                                                            <?php echo strtoupper(substr($customer['first_name'], 0, 1) . substr($customer['last_name'], 0, 1)); ?>
                                                        </div>
                                                        <div>
                                                            <div class="customer-name">
                                                                <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>
                                                            </div>
                                                            <div class="customer-email">
                                                                ID: <?php echo htmlspecialchars($customer['id']); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                                <td><?php echo !empty($customer['phone']) ? htmlspecialchars($customer['phone']) : 'N/A'; ?></td>
                                                <td>
                                                    <span class="status-badge status-active">
                                                        <i class="fas fa-circle"></i> Active
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="text-sm text-muted">
                                                        <?php echo date('M d, Y', strtotime($customer['created_at'])); ?>
                                                    </div>
                                                    <div class="text-xs text-muted">
                                                        by <?php echo htmlspecialchars($customer['created_by_name'] ?? 'System'); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <a href="view_customer.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-outline-primary" title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="edit_customer.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-outline-secondary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Delete" onclick="confirmDelete(<?php echo $customer['id']; ?>)">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <div class="pagination">
                                    <?php if ($current_page > 1): ?>
                                        <a href="?page=1<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="btn">
                                            <i class="fas fa-angle-double-left"></i>
                                        </a>
                                        <a href="?page=<?php echo ($current_page - 1) . (!empty($search) ? '&search=' . urlencode($search) : ''); ?>" class="btn">
                                            <i class="fas fa-angle-left"></i>
                                        </a>
                                    <?php else: ?>
                                        <button type="button" class="btn" disabled><i class="fas fa-angle-double-left"></i></button>
                                        <button type="button" class="btn" disabled><i class="fas fa-angle-left"></i></button>
                                    <?php endif; ?>

                                    <?php
                                    $start_page = max(1, $current_page - 2);
                                    $end_page = min($total_pages, $start_page + 4);
                                    if ($end_page - $start_page < 4) {
                                        $start_page = max(1, $end_page - 4);
                                    }
                                    
                                    for ($i = $start_page; $i <= $end_page; $i++):
                                    ?>
                                        <a href="?page=<?php echo $i . (!empty($search) ? '&search=' . urlencode($search) : ''); ?>" class="btn <?php echo $i == $current_page ? 'active' : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>

                                    <?php if ($current_page < $total_pages): ?>
                                        <a href="?page=<?php echo ($current_page + 1) . (!empty($search) ? '&search=' . urlencode($search) : ''); ?>" class="btn">
                                            <i class="fas fa-angle-right"></i>
                                        </a>
                                        <a href="?page=<?php echo $total_pages . (!empty($search) ? '&search=' . urlencode($search) : ''); ?>" class="btn">
                                            <i class="fas fa-angle-double-right"></i>
                                        </a>
                                    <?php else: ?>
                                        <button type="button" class="btn" disabled><i class="fas fa-angle-right"></i></button>
                                        <button type="button" class="btn" disabled><i class="fas fa-angle-double-right"></i></button>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-users-slash"></i>
                                <h3>No Customers Found</h3>
                                <p>There are no customers to display. Try adjusting your search or add a new customer.</p>
                                <a href="add_customer.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add New Customer
                                </a>
                            </div>
                        <?php endif; ?>
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
