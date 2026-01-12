<?php
require_once 'config.php';

// Login Function
function login_user($email, $password) {
    global $conn;
    
    $email = sanitize_input($email);
    
    $stmt = $conn->prepare("SELECT user_id, full_name, email, password, role_name, status FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if ($user['status'] !== 'ACTIVE') {
            return ['success' => false, 'message' => 'Account is inactive.'];
        }
        
        // Check password - support both hashed and plain text for backward compatibility
        $password_valid = false;
        
        // Check if password is hashed (starts with $2y$ for bcrypt)
        if (substr($user['password'], 0, 4) === '$2y$') {
            // Use password_verify for hashed passwords
            $password_valid = password_verify($password, $user['password']);
        } else {
            // Direct comparison for legacy plain-text passwords
            $password_valid = ($password === $user['password']);
        }
        
        if ($password_valid) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role_name'] = $user['role_name'];
            
            log_audit($user['user_id'], "User logged in");
            
            $dashboard_url = get_dashboard_url($user['role_name']);
            return ['success' => true, 'redirect' => $dashboard_url];
        }
    }
    
    return ['success' => false, 'message' => 'Invalid email or password.'];
}

// Logout Function
function logout_user() {
    if (isset($_SESSION['user_id'])) {
        log_audit($_SESSION['user_id'], "User logged out");
    }
    
    session_unset();
    session_destroy();
    redirect('login.php');
}

// Get Dashboard URL based on Role
function get_dashboard_url($role) {
    $role_dashboards = [
        'System Administrator' => 'dashboards/admin/index.php',
        'Registration Officer' => 'dashboards/registration/index.php',
        'Laboratory Technologist' => 'dashboards/lab/index.php',
        'Inventory Manager' => 'dashboards/inventory/index.php',
        'Hospital User' => 'dashboards/hospital/index.php',
        'Red Cross' => 'dashboards/partner/index.php',
        'Minister Of Health' => 'dashboards/authority/index.php',
    ];
    
    return $role_dashboards[$role] ?? 'index.php';
}

// Log Audit Trail
function log_audit($user_id, $action) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $action);
    $stmt->execute();
}
?>
