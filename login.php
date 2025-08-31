<?php
session_start();
require_once 'config/database.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if username and password are set
    if (empty($_POST['username']) || empty($_POST['password'])) {
        $error = "Please enter both username and password.";
    } else {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $username = trim($_POST['username']);
            $password = $_POST['password'];
            
            // Modified query to remove is_active check since column doesn't exist
            $query = "SELECT u.id, u.username, u.password_hash, u.first_name, u.last_name, u.role_id, 
                      r.name as role_name 
                      FROM users u 
                      JOIN roles r ON u.role_id = r.id 
                      WHERE u.username = :username LIMIT 1";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verify password
                if (password_verify($password, $user['password_hash'])) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role_name'];
                    $_SESSION['full_name'] = ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '');
                    
                    // Remove last_login update since column doesn't exist
                    // You can add this column later if needed
                    
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Invalid username or password.";
                }
            } else {
                $error = "Invalid username or password.";
            }
        } catch (PDOException $e) {
            // More specific error handling
            error_log("Database Error: " . $e->getMessage());
            $error = "Database connection error. Please try again later.";
        } catch (Exception $e) {
            error_log("General Error: " . $e->getMessage());
            $error = "An unexpected error occurred. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Customer Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/enhanced.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-light: #eef2ff;
            --secondary-color: #3f37c9;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
            --light-color: #f8f9fa;
            --dark-color: #1f2937;
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
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            background-color: var(--gray-50);
            color: var(--gray-800);
            line-height: 1.5;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .login-container {
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background-color: var(--gray-50);
        }
        
        .login-card {
            width: 100%;
            max-width: 28rem;
            background: white;
            border-radius: 1rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2.5rem 2rem;
            text-align: center;
        }
        
        .login-header h1 {
            margin: 0;
            font-size: 1.875rem;
            font-weight: 700;
            letter-spacing: -0.025em;
        }
        
        .login-header p {
            margin: 0.5rem 0 0;
            opacity: 0.9;
            font-size: 0.9375rem;
        }
        
        .login-body {
            padding: 2.5rem 2rem;
            background: white;
        }
        
        .alert {
            padding: 0.75rem 1rem;
            margin-bottom: 1.5rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }
        
        .alert-danger {
            background-color: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }
        
        .alert i {
            font-size: 1.25rem;
            margin-top: 0.125rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--gray-700);
        }
        
        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .input-icon {
            position: absolute;
            left: 1rem;
            color: var(--gray-400);
            pointer-events: none;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            font-size: 0.9375rem;
            line-height: 1.5;
            color: var(--gray-900);
            background-color: white;
            background-clip: padding-box;
            border: 1px solid var(--gray-300);
            border-radius: 0.5rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            outline: 0;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }
        
        .password-toggle {
            position: absolute;
            right: 0.75rem;
            background: none;
            border: none;
            color: var(--gray-400);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.25rem;
            transition: color 0.2s ease;
        }
        
        .password-toggle:hover {
            color: var(--gray-600);
            background-color: var(--gray-100);
        }
        
        .btn-login {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 0.875rem 1.5rem;
            font-size: 1rem;
            font-weight: 500;
            color: white;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 1.5rem;
        }
        
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login i {
            margin-right: 0.5rem;
            font-size: 1.1em;
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.875rem;
            color: var(--gray-600);
        }
        
        .auth-footer a {
            color: var(--primary-color);
            font-weight: 500;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        
        .auth-footer a:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fade-in {
            opacity: 0;
            animation: fadeIn 0.5s ease-out forwards;
        }
        
        /* Responsive adjustments */
        @media (max-width: 640px) {
            .login-container {
                padding: 1rem;
            }
            
            .login-body {
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Welcome Back</h1>
                <p>Sign in to continue to your account</p>
            </div>
            <div class="login-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <div><?php echo htmlspecialchars($error); ?></div>
                    </div>
                <?php endif; ?>
                
                <form action="login.php" method="post" id="loginForm" class="animate-fade-in" style="animation-delay: 0.1s;">
                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <i class="fas fa-user input-icon"></i>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="username" 
                                name="username" 
                                placeholder="Enter your username" 
                                required
                                autofocus
                            >
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="flex justify-between items-center">
                            <label for="password" class="form-label">Password</label>
                            <a href="forgot-password.php" class="text-sm text-primary-color hover:underline">
                                Forgot password?
                            </a>
                        </div>
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="password" 
                                name="password" 
                                placeholder="••••••••" 
                                required
                            >
                            <button type="button" class="password-toggle" id="togglePassword" aria-label="Toggle password visibility">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Sign In</span>
                    </button>
                    
                    <div class="auth-footer">
                        Don't have an account? <a href="register.php" class="font-medium">Create one</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.querySelector('#togglePassword');
            const password = document.querySelector('#password');
            const icon = togglePassword?.querySelector('i');
            
            if (togglePassword && password && icon) {
                togglePassword.addEventListener('click', function() {
                    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                    password.setAttribute('type', type);
                    
                    if (type === 'password') {
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                        togglePassword.setAttribute('aria-label', 'Show password');
                    } else {
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                        togglePassword.setAttribute('aria-label', 'Hide password');
                    }
                });
            }

            // Add animation class to form elements
            const formGroups = document.querySelectorAll('.form-group');
            formGroups.forEach((group, index) => {
                group.style.animation = `fadeIn 0.3s ease-out ${index * 0.1}s forwards`;
                group.style.opacity = '0';
            });

            // Focus username field on load
            const usernameField = document.getElementById('username');
            if (usernameField) {
                usernameField.focus();
            }

            // Form validation
            const loginForm = document.getElementById('loginForm');
            if (loginForm) {
                loginForm.addEventListener('submit', function(e) {
                    if (!this.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();
                        this.classList.add('was-validated');
                    }
                }, false);
            }
        });
    </script>
</body>
</html>
