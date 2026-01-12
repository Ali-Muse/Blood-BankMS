<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'blood banking management system');

// Application Configuration
define('SITE_NAME', 'Blood Banking Management System');
define('BASE_URL', 'http://localhost/Blood%20BankMS/');

// Session Configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database connection error. Please contact administrator.");
}

// Helper Functions
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role_name']);
}

function get_user_role() {
    return $_SESSION['role_name'] ?? null;
}

function get_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function get_user_name() {
    return $_SESSION['full_name'] ?? 'User';
}

function require_login() {
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

function require_role($allowed_roles) {
    require_login();
    $user_role = get_user_role();
    
    if (!in_array($user_role, $allowed_roles)) {
        redirect('unauthorized.php');
    }
}

// Get unread notification count
function get_notification_count($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'] ?? 0;
}
?>
