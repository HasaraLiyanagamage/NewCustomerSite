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

// Get user's full name
$user_name = htmlspecialchars($_SESSION['name'] ?? 'User');
$user_role = strtolower(htmlspecialchars($_SESSION['role'] ?? 'user'));
$user_id = $_SESSION['user_id'];

// Get total customers count
$query = "SELECT COUNT(*) as total_customers FROM customers";
if ($user_role === 'employee') {
    $query .= " WHERE created_by = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
} else {
    $stmt = $db->prepare($query);
}
$stmt->execute();
$total_customers = $stmt->fetch(PDO::FETCH_ASSOC)['total_customers'];

// Get recent customers
$query = "SELECT c.*, CONCAT(u.first_name, ' ', u.last_name) as created_by_name 
          FROM customers c 
          LEFT JOIN users u ON c.created_by = u.id ";
          
if ($user_role === 'employee') {
    $query .= " WHERE c.created_by = :user_id ";
}

$query .= " ORDER BY c.created_at DESC LIMIT 5";
$stmt = $db->prepare($query);

if ($user_role === 'employee') {
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
}

$stmt->execute();
$recent_customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total employees (for admin only)
$total_employees = 0;
if ($user_role === 'admin') {
    $query = "SELECT COUNT(*) as total FROM users WHERE role_id = 2"; // role_id 2 is employee
    $stmt = $db->prepare($query);
    $stmt->execute();
    $total_employees = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
}

// Get today's new customers
$query = "SELECT COUNT(*) as count FROM customers WHERE DATE(created_at) = CURDATE()";
if ($user_role === 'employee') {
    $query .= " AND created_by = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
} else {
    $stmt = $db->prepare($query);
}
$stmt->execute();
$todays_customers = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
?>

<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Customer Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-50: #f0f9ff;
            --primary-100: #e0f2fe;
            --primary-200: #bae6fd;
            --primary-300: #7dd3fc;
            --primary-400: #38bdf8;
            --primary-500: #0ea5e9;
            --primary-600: #0284c7;
            --primary-700: #0369a1;
            --primary-800: #075985;
            --primary-900: #0c4a6e;
            
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            
            --green-50: #f0fdf4;
            --green-100: #dcfce7;
            --green-200: #bbf7d0;
            --green-300: #86efac;
            --green-400: #4ade80;
            --green-500: #22c55e;
            --green-600: #16a34a;
            --green-700: #15803d;
            --green-800: #166534;
            --green-900: #14532d;
            
            --red-50: #fef2f2;
            --red-100: #fee2e2;
            --red-200: #fecaca;
            --red-300: #fca5a5;
            --red-400: #f87171;
            --red-500: #ef4444;
            --red-600: #dc2626;
            --red-700: #b91c1c;
            --red-800: #991b1b;
            --red-900: #7f1d1d;
            
            --amber-50: #fffbeb;
            --amber-100: #fef3c7;
            --amber-200: #fde68a;
            --amber-300: #fcd34d;
            --amber-400: #fbbf24;
            --amber-500: #f59e0b;
            --amber-600: #d97706;
            --amber-700: #b45309;
            --amber-800: #92400e;
            --amber-900: #78350f;
            
            --purple-50: #faf5ff;
            --purple-100: #f3e8ff;
            --purple-200: #e9d5ff;
            --purple-300: #d8b4fe;
            --purple-400: #c084fc;
            --purple-500: #a855f7;
            --purple-600: #9333ea;
            --purple-700: #7e22ce;
            --purple-800: #6b21a8;
            --purple-900: #581c87;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            color: var(--gray-800);
        }
        
        .app-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: 260px;
            background: white;
            border-right: 1px solid var(--gray-200);
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
            z-index: 20;
        }
        
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .brand {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: var(--gray-800);
            font-weight: 600;
            font-size: 1.125rem;
        }
        
        .brand-logo {
            width: 36px;
            height: 36px;
            background: var(--primary-600);
            color: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.75rem;
            font-weight: 700;
        }
        
        .sidebar-menu {
            flex: 1;
            padding: 1.5rem 0;
            overflow-y: auto;
        }
        
        .menu-title {
            padding: 0.5rem 1.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--gray-500);
            margin-bottom: 0.5rem;
        }
        
        .menu-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            margin: 0.25rem 0;
            color: var(--gray-600);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }
        
        .menu-item:hover {
            background: var(--primary-50);
            color: var(--primary-600);
        }
        
        .menu-item.active {
            background: var(--primary-50);
            color: var(--primary-600);
            border-left-color: var(--primary-600);
        }
        
        .menu-item i {
            width: 24px;
            margin-right: 0.75rem;
            font-size: 0.875rem;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        
        /* Top Bar */
        .top-bar {
            height: 72px;
            background: white;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
        }
        
        .page-title h1 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-800);
        }
        
        .user-menu {
            display: flex;
            align-items: center;
        }
        
        .notification-bell {
            position: relative;
            margin-right: 1.5rem;
            color: var(--gray-500);
            cursor: pointer;
        }
        
        .notification-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: var(--red-500);
            color: white;
            border-radius: 9999px;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.625rem;
            font-weight: 600;
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        
        .user-avatar {
            width: 36px;
            height: 36px;
            background: var(--primary-600);
            color: white;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        .user-info {
            margin-left: 0.75rem;
        }
        
        .user-name {
            font-weight: 500;
            font-size: 0.875rem;
            color: var(--gray-800);
            margin-bottom: 0.125rem;
        }
        
        .user-role {
            font-size: 0.75rem;
            color: var(--gray-500);
            text-transform: capitalize;
        }
        
        /* Content */
        .content-wrapper {
            flex: 1;
            padding: 2rem;
            background: var(--gray-50);
        }
        
        /* Welcome Banner */
        .welcome-banner {
            background: white;
            border-radius: 0.75rem;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid var(--gray-200);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }
        
        .welcome-banner h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
        }
        
        .welcome-banner p {
            color: var(--gray-600);
        }
        
        /* Stats Grid */
        .grid {
            display: grid;
            gap: 1.5rem;
        }
        
        .grid-cols-1 {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }
        
        .grid-cols-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        
        .grid-cols-4 {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
        
        .stat-card {
            background: white;
            border-radius: 0.75rem;
            padding: 1.5rem;
            border: 1px solid var(--gray-200);
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }
        
        .stat-card:hover {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        
        .stat-content {
            text-align: right;
        }
        
        .stat-label {
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-800);
            margin-top: 0.25rem;
        }
        
        .stat-trend {
            display: flex;
            align-items: center;
            font-size: 0.75rem;
            margin-top: 0.75rem;
            color: var(--gray-600);
        }
        
        .trend-up {
            color: var(--green-600);
        }
        
        .trend-down {
            color: var(--red-600);
        }
        
        /* Recent Customers */
        .recent-customers {
            background: white;
            border-radius: 0.75rem;
            border: 1px solid var(--gray-200);
            margin-top: 2rem;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }
        
        .recent-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .recent-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-800);
        }
        
        .btn-primary {
            background: var(--primary-600);
            color: white;
            border-radius: 0.375rem;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            transition: all 0.2s ease;
        }
        
        .btn-primary:hover {
            background: var(--primary-700);
        }
        
        .btn-primary i {
            margin-right: 0.5rem;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            padding: 0.75rem 1.5rem;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: var(--gray-50);
            border-bottom: 1px solid var(--gray-200);
        }
        
        td {
            padding: 1rem 1.5rem;
            font-size: 0.875rem;
            border-bottom: 1px solid var(--gray-200);
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        tr:hover td {
            background: var(--gray-50);
        }
        
        .customer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
            flex-shrink: 0;
        }
        
        .customer-info {
            margin-left: 0.75rem;
        }
        
        .customer-name {
            font-weight: 500;
            color: var(--gray-800);
        }
        
        .customer-phone {
            font-size: 0.75rem;
            color: var(--gray-500);
            margin-top: 0.125rem;
        }
        
        .customer-email {
            color: var(--gray-700);
        }
        
        .date-day {
            font-weight: 500;
            color: var(--gray-800);
        }
        
        .date-time {
            font-size: 0.75rem;
            color: var(--gray-500);
            margin-top: 0.125rem;
        }
        
        .action-btn {
            padding: 0.375rem;
            border-radius: 9999px;
            color: var(--gray-400);
            transition: all 0.2s ease;
            display: inline-flex;
        }
        
        .action-btn:hover {
            color: var(--primary-600);
            background: var(--primary-50);
        }
        
        .action-btn.edit:hover {
            color: var(--blue-600);
            background: var(--blue-50);
        }
        
        .action-btn.delete:hover {
            color: var(--red-600);
            background: var(--red-50);
        }
        
        .table-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.875rem;
            color: var(--gray-600);
        }
        
        .pagination {
            display: flex;
            gap: 0.5rem;
        }
        
        .page-btn {
            padding: 0.375rem 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: 0.375rem;
            background: white;
            color: var(--gray-700);
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        
        .page-btn:hover {
            background: var(--gray-100);
        }
        
        .page-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .empty-state {
            padding: 3rem 1.5rem;
            text-align: center;
        }
        
        .empty-icon {
            width: 64px;
            height: 64px;
            background: var(--gray-100);
            color: var(--gray-400);
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
        }
        
        .empty-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
        }
        
        .empty-description {
            color: var(--gray-600);
            margin-bottom: 1.5rem;
        }
        
        /* Footer */
        .footer {
            padding: 1.5rem;
            text-align: center;
            font-size: 0.875rem;
            color: var(--gray-500);
            border-top: 1px solid var(--gray-200);
            background: white;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                position: fixed;
                left: 0;
                top: 0;
                bottom: 0;
                transform: translateX(-100%);
            }
            
            .sidebar-open .sidebar {
                transform: translateX(0);
                box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
            }
            
            .sidebar-open .main-content {
                position: relative;
                overflow: hidden;
            }
            
            .sidebar-open .main-content::after {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 10;
            }
        }
        
        @media (max-width: 768px) {
            .grid-cols-2, .grid-cols-4 {
                grid-template-columns: repeat(1, minmax(0, 1fr));
            }
            
            .content-wrapper {
                padding: 1.5rem 1rem;
            }
            
            .top-bar {
                padding: 0 1rem;
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
                    <div class="brand-logo">CMS</div>
                    <span>CustomerMS</span>
                </a>
            </div>
            
            <nav class="sidebar-menu">
                <div class="menu-title">Main Menu</div>
                <a href="dashboard.php" class="menu-item active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="customers.php" class="menu-item">
                    <i class="fas fa-users"></i>
                    <span>Customers</span>
                </a>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="employees.php" class="menu-item">
                    <i class="fas fa-user-tie"></i>
                    <span>Employees</span>
                </a>
                <?php endif; ?>
                
                <div class="menu-title mt-4">Account</div>
                <a href="profile.php" class="menu-item">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
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
                    <h1>Dashboard</h1>
                </div>
                
                <div class="user-menu">
                    <div class="notification-bell">
                        <i class="far fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </div>
                    
                    <div class="user-profile">
                        <div class="user-avatar">
                            <?php 
                            $name_parts = explode(' ', $_SESSION['name'] ?? 'U');
                            $initials = '';
                            foreach ($name_parts as $part) {
                                $initials .= strtoupper(substr($part, 0, 1));
                                if (strlen($initials) >= 2) break;
                            }
                            echo $initials;
                            ?>
                        </div>
                        <div class="user-info">
                            <p class="user-name"><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></p>
                            <p class="user-role"><?php echo htmlspecialchars($_SESSION['role'] ?? 'user'); ?></p>
                        </div>
                        <i class="fas fa-chevron-down" style="margin-left: 0.5rem; font-size: 0.75rem; color: var(--gray-500);"></i>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="content-wrapper">
                <!-- Welcome Banner -->
                <div class="welcome-banner">
                    <h1>Welcome back, <?php echo htmlspecialchars(explode(' ', $_SESSION['name'] ?? 'User')[0]); ?>! 👋</h1>
                    <p>Here's what's happening with your business today.</p>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Customers -->
                    <div class="stat-card">
                        <div class="flex items-center justify-between">
                            <div class="stat-icon" style="background-color: var(--primary-100); color: var(--primary-600);">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-label">Total Customers</div>
                                <div class="stat-value"><?php echo number_format($total_customers); ?></div>
                                <div class="stat-trend">
                                    <span class="trend-up">
                                        <i class="fas fa-arrow-up mr-1"></i> 12.5%
                                    </span>
                                    <span>from last month</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Active Employees -->
                    <div class="stat-card">
                        <div class="flex items-center justify-between">
                            <div class="stat-icon" style="background-color: var(--green-100); color: var(--green-600);">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-label">Active Employees</div>
                                <div class="stat-value"><?php
                                $stmt = $db->query("SELECT COUNT(*) as total_employees FROM users WHERE role_id = 2");
                                $total_employees = $stmt->fetch(PDO::FETCH_ASSOC)['total_employees'];
                                echo $total_employees; ?></div>
                                <div class="stat-trend">
                                    <span class="trend-up">
                                        <i class="fas fa-arrow-up mr-1"></i> 5.2%
                                    </span>
                                    <span>this year</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Tasks -->
                    <div class="stat-card">
                        <div class="flex items-center justify-between">
                            <div class="stat-icon" style="background-color: var(--amber-100); color: var(--amber-600);">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-label">Pending Tasks</div>
                                <div class="stat-value">18</div>
                                <div class="stat-trend">
                                    <span class="trend-down">
                                        <i class="fas fa-arrow-down mr-1"></i> 3.1%
                                    </span>
                                    <span>from yesterday</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Revenue -->
                    <div class="stat-card">
                        <div class="flex items-center justify-between">
                            <div class="stat-icon" style="background-color: var(--purple-100); color: var(--purple-600);">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-label">Monthly Revenue</div>
                                <div class="stat-value">$24,580</div>
                                <div class="stat-trend">
                                    <span class="trend-up">
                                        <i class="fas fa-arrow-up mr-1"></i> 18.7%
                                    </span>
                                    <span>vs last month</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Customers -->
                <div class="recent-customers">
                    <div class="recent-header">
                        <h2 class="recent-title">Recent Customers</h2>
                        <a href="register_customer.php" class="btn-primary">
                            <i class="fas fa-plus"></i> Add New
                        </a>
                    </div>
                    
                    <?php if (count($recent_customers) > 0): ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Email</th>
                                        <th>Date Added</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_customers as $customer): 
                                        $initials = strtoupper(substr($customer['first_name'], 0, 1) . substr($customer['last_name'], 0, 1));
                                        $colors = ['#4361ee', '#3f37c9', '#3a0ca3', '#4cc9f0', '#4895ef', '#f72585', '#7209b7'];
                                        $color = $colors[array_rand($colors)];
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="flex items-center">
                                                    <div class="customer-avatar" style="background-color: <?php echo $color; ?>">
                                                        <?php echo $initials; ?>
                                                    </div>
                                                    <div class="customer-info">
                                                        <div class="customer-name">
                                                            <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>
                                                        </div>
                                                        <div class="customer-phone">
                                                            <?php echo !empty($customer['phone']) ? htmlspecialchars($customer['phone']) : 'N/A'; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="customer-email">
                                                <?php echo htmlspecialchars($customer['email']); ?>
                                            </td>
                                            <td>
                                                <div class="date-day">
                                                    <?php echo date('M j, Y', strtotime($customer['created_at'])); ?>
                                                </div>
                                                <div class="date-time">
                                                    <?php echo date('g:i A', strtotime($customer['created_at'])); ?>
                                                </div>
                                            </td>
                                            <td class="text-right">
                                                <div class="flex justify-end space-x-2">
                                                    <a href="view_customer.php?id=<?php echo $customer['id']; ?>" class="action-btn" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="edit_customer.php?id=<?php echo $customer['id']; ?>" class="action-btn edit" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="#" class="action-btn delete" title="Delete" data-id="<?php echo $customer['id']; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="table-footer">
                            <div>
                                Showing <span class="font-medium">1</span> to <span class="font-medium"><?php echo count($recent_customers); ?></span> of <span class="font-medium"><?php echo count($recent_customers); ?></span> results
                            </div>
                            <div class="pagination">
                                <button class="page-btn" disabled>
                                    Previous
                                </button>
                                <button class="page-btn">
                                    Next
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <h3 class="empty-title">No customers yet</h3>
                            <p class="empty-description">Get started by adding your first customer</p>
                            <a href="register_customer.php" class="btn-primary">
                                <i class="fas fa-plus"></i> Add Customer
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
            
            <footer class="footer">
                <span>&copy; <?php echo date('Y'); ?> Customer Management System. All rights reserved.</span>
            </footer>
        </div>
    </div>

    <!-- Include Alpine.js for interactive components -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.0/dist/cdn.min.js" defer></script>
    
    <script>
        // Initialize tooltips and interactive elements
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar toggle functionality
            const sidebar = document.querySelector('aside');
            const sidebarToggle = document.getElementById('sidebar-toggle');
            
            if (sidebar && sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('-translate-x-full');
                    document.body.classList.toggle('sidebar-open');
                });
            }
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                const isClickInsideSidebar = sidebar.contains(event.target);
                const isClickOnToggle = (sidebarToggle && sidebarToggle.contains(event.target));
                
                if (window.innerWidth < 1024 && !isClickInsideSidebar && !isClickOnToggle) {
                    sidebar.classList.add('-translate-x-full');
                    document.body.classList.remove('sidebar-open');
                }
            });
            
            // Handle window resize
            function handleResize() {
                if (window.innerWidth >= 1024) {
                    sidebar.classList.remove('-translate-x-full');
                    document.body.classList.remove('sidebar-open');
                } else {
                    sidebar.classList.add('-translate-x-full');
                }
            }
            
            // Initial check
            handleResize();
            
            // Add resize event listener
            window.addEventListener('resize', handleResize);
            
            // Handle delete confirmation
            const deleteButtons = document.querySelectorAll('.action-btn.delete');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const customerId = this.getAttribute('data-id');
                    if (confirm('Are you sure you want to delete this customer? This action cannot be undone.')) {
                        // Add loading state
                        const icon = this.querySelector('i');
                        if (icon) {
                            icon.className = 'fas fa-spinner fa-spin';
                        }
                        this.disabled = true;
                        
                        // In a real app, you would make an API call here
                        // For now, we'll just simulate a delay
                        setTimeout(() => {
                            // Show success message
                            alert('Customer deleted successfully!');
                            // Reload the page to see changes
                            window.location.reload();
                        }, 1000);
                    }
                });
            });
        });
    </script>
</body>
</html>