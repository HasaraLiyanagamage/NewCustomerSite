<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['employee_id'])) {
    $employee_id = (int)$_POST['employee_id'];
    
    // Prevent resetting the default admin account
    if ($employee_id === 1) {
        $_SESSION['error_message'] = "Cannot reset password for the default admin account.";
        header("Location: employees.php");
        exit();
    }
    
    $database = new Database();
    $db = $database->getConnection();
    
    // Generate a random password
    $temp_password = bin2hex(random_bytes(8));
    $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);
    
    // Update the user's password
    $query = "UPDATE users SET password_hash = :password_hash WHERE id = :id AND role_id = 2";
    $stmt = $db->prepare($query);
    $result = $stmt->execute([
        ':password_hash' => $hashed_password,
        ':id' => $employee_id
    ]);
    
    if ($result && $stmt->rowCount() > 0) {
        // Get employee email to send the temporary password (in a real app, you would send an email)
        $query = "SELECT email, first_name, last_name FROM users WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $employee_id, PDO::PARAM_INT);
        $stmt->execute();
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($employee) {
            // In a real application, you would send an email here
            // For this example, we'll just store the temp password in the session
            $_SESSION['temp_password'] = [
                'name' => $employee['first_name'] . ' ' . $employee['last_name'],
                'email' => $employee['email'],
                'temp_password' => $temp_password
            ];
            
            $_SESSION['success_message'] = "Password has been reset. The temporary password is: " . $temp_password . 
                                         " (In a real application, this would be sent to the employee's email)";
        } else {
            $_SESSION['error_message'] = "Employee not found.";
        }
    } else {
        $_SESSION['error_message'] = "Error resetting password. Please try again.";
    }
    
    header("Location: employees.php");
    exit();
} else {
    // If not a POST request or no employee_id provided
    header("Location: employees.php");
    exit();
}
?>
