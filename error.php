<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Default error code
$error_code = isset($_GET['code']) ? (int)$_GET['code'] : 404;

// Set HTTP response code
http_response_code($error_code);

// Define error messages
$error_messages = [
    400 => 'Bad Request',
    401 => 'Unauthorized',
    403 => 'Forbidden',
    404 => 'Page Not Found',
    405 => 'Method Not Allowed',
    408 => 'Request Timeout',
    500 => 'Internal Server Error',
    502 => 'Bad Gateway',
    503 => 'Service Unavailable',
    504 => 'Gateway Timeout'
];

// Get error message or default to 500
$error_title = isset($error_messages[$error_code]) ? $error_messages[$error_code] : 'Error';
$error_message = "";

// Custom messages for specific error codes
switch ($error_code) {
    case 403:
        $error_message = "You don't have permission to access this page.";
        break;
    case 404:
        $error_message = "The page you're looking for doesn't exist or has been moved.";
        break;
    case 500:
        $error_message = "Something went wrong on our end. Please try again later.";
        break;
    default:
        $error_message = "An error occurred. Please try again later.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error <?php echo $error_code; ?> - Customer Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .error-container {
            max-width: 600px;
            margin: 100px auto;
            text-align: center;
            padding: 2rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .error-code {
            font-size: 6rem;
            font-weight: bold;
            color: #dc3545;
            margin: 0;
            line-height: 1;
        }
        .error-title {
            font-size: 2rem;
            margin: 1rem 0 0.5rem;
            color: #343a40;
        }
        .error-message {
            color: #6c757d;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }
        .btn {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #0056b3;
            color: white;
            text-decoration: none;
        }
        .error-details {
            margin-top: 2rem;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 4px;
            font-family: monospace;
            text-align: left;
            font-size: 0.9rem;
            color: #495057;
            display: none; /* Hide by default */
        }
        .show-details {
            margin-top: 1rem;
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            text-decoration: underline;
            font-size: 0.9rem;
        }
    </style>
</head>
<body style="background-color: #f8f9fa;">
    <div class="error-container">
        <div class="error-code"><?php echo $error_code; ?></div>
        <h1 class="error-title"><?php echo htmlspecialchars($error_title); ?></h1>
        <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
        
        <a href="index.php" class="btn">Go to Homepage</a>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="dashboard.php" class="btn" style="margin-left: 1rem;">Go to Dashboard</a>
        <?php else: ?>
            <a href="login.php" class="btn" style="margin-left: 1rem;">Login</a>
        <?php endif; ?>
        
        <?php if (isset($_SERVER['HTTP_REFERER'])): ?>
            <a href="<?php echo htmlspecialchars($_SERVER['HTTP_REFERER']); ?>" class="btn" style="margin-left: 1rem;">Go Back</a>
        <?php endif; ?>
        
        <?php if (ini_get('display_errors') === '1' || strtolower(ini_get('display_errors')) === 'on'): ?>
            <button onclick="document.getElementById('errorDetails').style.display='block';this.style.display='none';" class="show-details">Show Details</button>
            <pre id="errorDetails" class="error-details">
                <strong>Error Code:</strong> <?php echo $error_code; ?>
                <strong>Message:</strong> <?php echo htmlspecialchars($error_title); ?>
                <strong>Request URI:</strong> <?php echo isset($_SERVER['REQUEST_URI']) ? htmlspecialchars($_SERVER['REQUEST_URI']) : 'N/A'; ?>
                <strong>Request Method:</strong> <?php echo isset($_SERVER['REQUEST_METHOD']) ? htmlspecialchars($_SERVER['REQUEST_METHOD']) : 'N/A'; ?>
                
                <?php if (!empty($_GET)): ?>
                    <strong>GET Parameters:</strong>
                    <?php print_r($_GET); ?>
                <?php endif; ?>
                
                <?php if (!empty($_POST)): ?>
                    <strong>POST Data:</strong>
                    <?php print_r($_POST); ?>
                <?php endif; ?>
            </pre>
        <?php endif; ?>
    </div>
    
    <script>
        // Add any JavaScript functionality here if needed
        document.addEventListener('DOMContentLoaded', function() {
            // Example: Log error to console
            console.error('Error <?php echo $error_code; ?>: <?php echo addslashes($error_title); ?>');
        });
    </script>
</body>
</html>
