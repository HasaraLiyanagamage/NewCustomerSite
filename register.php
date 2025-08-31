<?php
session_start();
require_once 'config/database.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $db = $database->getConnection();
    
    // Get form data
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    
    // Validate input
    if (empty($username) || empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } else {
        // Check if username or email already exists
        $query = "SELECT id FROM users WHERE username = :username OR email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $error = "Username or email already exists.";
        } else {
            // Default role is employee (ID 2)
            $role_id = 2;
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $query = "INSERT INTO users (username, email, password_hash, first_name, last_name, role_id) 
                     VALUES (:username, :email, :password_hash, :first_name, :last_name, :role_id)";
            
            $stmt = $db->prepare($query);
            $result = $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password_hash' => $password_hash,
                ':first_name' => $first_name,
                ':last_name' => $last_name,
                ':role_id' => $role_id
            ]);
            
            if ($result) {
                $success = "Registration successful! You can now login.";
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
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Registration - Customer Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/enhanced.css">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }
        
        .auth-card {
            width: 100%;
            max-width: 520px;
            background: white;
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: var(--shadow-2xl);
            animation: fadeIn 0.5s ease-out;
        }
        
        .auth-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 2.5rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .auth-header::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M54.627 0H5.373A5.38 5.38 0 0 0 0 5.373v49.254A5.38 5.38 0 0 0 5.373 60h49.254A5.38 5.38 0 0 0 60 54.627V5.373A5.38 5.38 0 0 0 54.627 0zM21 33.03v-6.06l10.5-6.06 10.5 6.06v12.12l-10.5 6.06L21 45.15V33.03z' fill='%23ffffff' fill-opacity='0.1' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.5;
        }
        
        .auth-header-content {
            position: relative;
            z-index: 1;
        }
        
        .brand-logo {
            font-size: 2.5rem;
            font-weight: 800;
            margin: 0 0 0.5rem;
            display: inline-block;
            background: linear-gradient(135deg, #fff 0%, rgba(255, 255, 255, 0.8) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .auth-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0 0 0.5rem;
            color: white;
        }
        
        .auth-subtitle {
            margin: 0;
            opacity: 0.9;
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .auth-body {
            padding: 2.5rem 2rem;
            background: white;
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--gray-700);
            font-size: 0.9375rem;
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
            font-size: 1.1em;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 3rem;
            font-size: 1rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
            transition: var(--transition);
            background-color: var(--gray-50);
            color: var(--gray-800);
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
            background-color: white;
            outline: none;
        }
        
        .password-toggle {
            position: absolute;
            right: 1rem;
            color: var(--gray-400);
            cursor: pointer;
            transition: color 0.2s ease;
            background: none;
            border: none;
            padding: 0.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .password-toggle:hover {
            color: var(--gray-600);
        }
        
        .btn-register {
            width: 100%;
            padding: 1rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            color: white;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1.5rem;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--gray-200);
            font-size: 0.9375rem;
            color: var(--gray-600);
        }
        
        .auth-footer a {
            color: var(--primary-color);
            font-weight: 500;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        
        .auth-footer a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        .alert {
            padding: 1rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            font-size: 0.9375rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            animation: fadeIn 0.3s ease-out;
        }
        
        .alert-danger {
            background-color: var(--red-50);
            color: var(--red-700);
            border-left: 4px solid var(--red-500);
        }
        
        .alert-success {
            background-color: var(--green-50);
            color: var(--green-700);
            border-left: 4px solid var(--green-500);
        }
        
        .alert i {
            font-size: 1.25rem;
            margin-top: 0.125rem;
        }
        
        .name-fields {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @media (max-width: 480px) {
            .auth-container {
                padding: 1rem;
            }
            
            .auth-body {
                padding: 2rem 1.5rem;
            }
            
            .auth-header {
                padding: 2rem 1.5rem;
            }
            
            .brand-logo {
                font-size: 2rem;
            }
            
            .auth-title {
                font-size: 1.5rem;
            }
            
            .name-fields {
                grid-template-columns: 1fr;
                gap: 1.25rem;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-header-content">
                    <div class="brand-logo">CustomerPro</div>
                    <h1 class="auth-title">Create an Account</h1>
                    <p class="auth-subtitle">Join our team as an employee</p>
                </div>
            </div>
            
            <div class="auth-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <div><?php echo htmlspecialchars($error); ?></div>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <div><?php echo htmlspecialchars($success); ?></div>
                    </div>
                <?php endif; ?>
                
                <form action="register.php" method="post" id="registerForm" class="animate-fade-in" style="animation-delay: 0.1s;">
                    <div class="name-fields">
                        <div class="form-group">
                            <label for="first_name" class="form-label">First Name</label>
                            <div class="input-group">
                                <i class="fas fa-user input-icon"></i>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="first_name" 
                                    name="first_name" 
                                    value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>" 
                                    required
                                    autofocus
                                >
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name" class="form-label">Last Name</label>
                            <div class="input-group">
                                <i class="fas fa-user input-icon"></i>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="last_name" 
                                    name="last_name" 
                                    value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>" 
                                    required
                                >
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <i class="fas fa-at input-icon"></i>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="username" 
                                name="username" 
                                value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                                placeholder="johndoe"
                                required
                            >
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <i class="fas fa-envelope input-icon"></i>
                            <input 
                                type="email" 
                                class="form-control" 
                                id="email" 
                                name="email" 
                                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                placeholder="you@example.com"
                                required
                            >
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="password" 
                                name="password" 
                                placeholder="••••••••" 
                                minlength="8"
                                required
                            >
                            <button type="button" class="password-toggle" id="togglePassword" aria-label="Toggle password visibility">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="text-xs text-gray-500 mt-1">Must be at least 8 characters long</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="confirm_password" 
                                name="confirm_password" 
                                placeholder="••••••••" 
                                required
                            >
                            <button type="button" class="password-toggle" id="toggleConfirmPassword" aria-label="Toggle password visibility">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-register">
                        <i class="fas fa-user-plus"></i>
                        <span>Create Account</span>
                    </button>
                    
                    <div class="auth-footer">
                        Already have an account? <a href="login.php" class="font-medium">Sign in</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.addEventListener('DOMContentLoaded', function() {
            // Password field
            const togglePassword = document.querySelector('#togglePassword');
            const password = document.querySelector('#password');
            const passwordIcon = togglePassword?.querySelector('i');
            
            // Confirm password field
            const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');
            const confirmPassword = document.querySelector('#confirm_password');
            const confirmPasswordIcon = toggleConfirmPassword?.querySelector('i');
            
            // Toggle main password visibility
            if (togglePassword && password && passwordIcon) {
                togglePassword.addEventListener('click', function() {
                    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                    password.setAttribute('type', type);
                    
                    if (type === 'password') {
                        passwordIcon.classList.remove('fa-eye-slash');
                        passwordIcon.classList.add('fa-eye');
                        togglePassword.setAttribute('aria-label', 'Show password');
                    } else {
                        passwordIcon.classList.remove('fa-eye');
                        passwordIcon.classList.add('fa-eye-slash');
                        togglePassword.setAttribute('aria-label', 'Hide password');
                    }
                });
            }
            
            // Toggle confirm password visibility
            if (toggleConfirmPassword && confirmPassword && confirmPasswordIcon) {
                toggleConfirmPassword.addEventListener('click', function() {
                    const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
                    confirmPassword.setAttribute('type', type);
                    
                    if (type === 'password') {
                        confirmPasswordIcon.classList.remove('fa-eye-slash');
                        confirmPasswordIcon.classList.add('fa-eye');
                        toggleConfirmPassword.setAttribute('aria-label', 'Show password');
                    } else {
                        confirmPasswordIcon.classList.remove('fa-eye');
                        confirmPasswordIcon.classList.add('fa-eye-slash');
                        toggleConfirmPassword.setAttribute('aria-label', 'Hide password');
                    }
                });
            }

            // Add animation class to form elements
            const formGroups = document.querySelectorAll('.form-group');
            formGroups.forEach((group, index) => {
                group.style.animation = `fadeIn 0.3s ease-out ${index * 0.05}s forwards`;
                group.style.opacity = '0';
            });

            // Form validation
            const form = document.getElementById('registerForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const password = document.getElementById('password').value;
                    const confirmPassword = document.getElementById('confirm_password').value;
                    
                    if (password !== confirmPassword) {
                        e.preventDefault();
                        alert('Passwords do not match!');
                        return false;
                    }
                    
                    if (password.length < 8) {
                        e.preventDefault();
                        alert('Password must be at least 8 characters long!');
                        return false;
                    }
                    
                    return true;
                });
            }
        });
    </script>
</body>
</html>
