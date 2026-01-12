<?php
// Notification Helper Functions for Blood Banking Management System

require_once 'config.php';

/**
 * Send notification to a specific user
 */
function send_notification($user_id, $type, $title, $message, $link = null) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $type, $title, $message, $link);
    
    if ($stmt->execute()) {
        return true;
    }
    return false;
}

/**
 * Send notification to all users with a specific role
 */
function send_role_notification($role_name, $type, $title, $message, $link = null) {
    global $conn;
    
    // Get all users with this role
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE role_name = ? AND status = 'ACTIVE'");
    $stmt->bind_param("s", $role_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $count = 0;
    while ($row = $result->fetch_assoc()) {
        if (send_notification($row['user_id'], $type, $title, $message, $link)) {
            $count++;
        }
    }
    
    return $count;
}

/**
 * Get notifications for a user
 */
function get_user_notifications($user_id, $limit = 10, $unread_only = false) {
    global $conn;
    
    $sql = "SELECT * FROM notifications WHERE user_id = ?";
    if ($unread_only) {
        $sql .= " AND is_read = 0";
    }
    $sql .= " ORDER BY created_at DESC LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    
    return $notifications;
}

/**
 * Mark notification as read
 */
function mark_notification_read($notification_id, $user_id) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notification_id, $user_id);
    return $stmt->execute();
}

/**
 * Mark all notifications as read for a user
 */
function mark_all_notifications_read($user_id) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
    $stmt->bind_param("i", $user_id);
    return $stmt->execute();
}

/**
 * Check for low stock and send alerts
 */
function check_low_stock_alerts() {
    global $conn;
    
    // Get low stock threshold from settings
    $result = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'low_stock_threshold'");
    $row = $result->fetch_assoc();
    $threshold = $row['setting_value'] ?? 5;
    
    // Check stock for each blood group
    $blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
    
    foreach ($blood_groups as $group) {
        $stmt = $conn->prepare("
            SELECT COUNT(*) as count 
            FROM blood_units 
            WHERE blood_group = ? 
            AND status = 'APPROVED' 
            AND expiry_date > CURDATE()
        ");
        $stmt->bind_param("s", $group);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] < $threshold) {
            $message = "Low stock alert: Only {$row['count']} units of {$group} blood available.";
            send_role_notification('Inventory Manager', 'WARNING', 'Low Stock Alert', $message, 'dashboards/inventory/blood-inventory.php');
            send_role_notification('System Administrator', 'WARNING', 'Low Stock Alert', $message, 'dashboards/admin/reports-stock.php');
        }
    }
}

/**
 * Check for expiring blood units and send alerts
 */
function check_expiry_alerts() {
    global $conn;
    
    // Get expiry warning days from settings
    $result = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'expiry_warning_days'");
    $row = $result->fetch_assoc();
    $warning_days = $row['setting_value'] ?? 7;
    
    // Find units expiring soon
    $stmt = $conn->prepare("
        SELECT barcode, blood_group, DATEDIFF(expiry_date, CURDATE()) as days_until_expiry
        FROM blood_units 
        WHERE status = 'APPROVED' 
        AND expiry_date > CURDATE() 
        AND DATEDIFF(expiry_date, CURDATE()) <= ?
        ORDER BY expiry_date ASC
    ");
    $stmt->bind_param("i", $warning_days);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $expiring_units = [];
    while ($row = $result->fetch_assoc()) {
        $expiring_units[] = $row;
    }
    
    if (count($expiring_units) > 0) {
        $message = count($expiring_units) . " blood unit(s) expiring within {$warning_days} days.";
        send_role_notification('Inventory Manager', 'WARNING', 'Blood Expiry Alert', $message, 'dashboards/inventory/expired-units.php');
    }
    
    return $expiring_units;
}

/**
 * Send emergency request notification
 */
function notify_emergency_request($request_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT request_number, blood_group, quantity, hospital_name 
        FROM blood_requests 
        WHERE request_id = ?
    ");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $request = $result->fetch_assoc();
    
    if ($request) {
        $message = "EMERGENCY: {$request['hospital_name']} needs {$request['quantity']} units of {$request['blood_group']} blood. Request #{$request['request_number']}";
        send_role_notification('Inventory Manager', 'EMERGENCY', 'Emergency Blood Request', $message, 'dashboards/inventory/review-requests.php');
        send_role_notification('System Administrator', 'EMERGENCY', 'Emergency Blood Request', $message, 'dashboards/admin/index.php');
    }
}

/**
 * Notify hospital about request status change
 */
function notify_request_status($request_id, $status, $notes = '') {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT hospital_user_id, request_number, blood_group, quantity 
        FROM blood_requests 
        WHERE request_id = ?
    ");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $request = $result->fetch_assoc();
    
    if ($request) {
        $title = '';
        $message = '';
        $type = 'INFO';
        
        switch ($status) {
            case 'APPROVED':
                $title = 'Request Approved';
                $message = "Your request #{$request['request_number']} for {$request['quantity']} units of {$request['blood_group']} has been approved.";
                $type = 'SUCCESS';
                break;
            case 'REJECTED':
                $title = 'Request Rejected';
                $message = "Your request #{$request['request_number']} has been rejected. Reason: {$notes}";
                $type = 'ERROR';
                break;
            case 'DISPATCHED':
                $title = 'Blood Dispatched';
                $message = "Your request #{$request['request_number']} has been dispatched and is on the way.";
                $type = 'SUCCESS';
                break;
            case 'DELIVERED':
                $title = 'Blood Delivered';
                $message = "Your request #{$request['request_number']} has been successfully delivered.";
                $type = 'SUCCESS';
                break;
        }
        
        send_notification($request['hospital_user_id'], $type, $title, $message, 'dashboards/hospital/track-requests.php');
    }
}

/**
 * Auto-expire old blood units
 */
function auto_expire_blood_units() {
    global $conn;
    
    $stmt = $conn->query("
        UPDATE blood_units 
        SET status = 'EXPIRED' 
        WHERE status = 'APPROVED' 
        AND expiry_date < CURDATE()
    ");
    
    return $conn->affected_rows;
}

/**
 * Generate unique barcode for blood unit
 */
function generate_blood_barcode() {
    global $conn;
    
    $date = date('Ymd');
    
    // Get the count of blood units collected today
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM blood_units WHERE DATE(collection_date) = CURDATE()");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $sequence = $row['count'] + 1;
    
    // Format: BB-YYYYMMDD-XXXX
    $barcode = sprintf("BB-%s-%04d", $date, $sequence);
    
    // Ensure uniqueness
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM blood_units WHERE barcode = ?");
    $stmt->bind_param("s", $barcode);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        // If exists, add random suffix
        $barcode .= '-' . rand(10, 99);
    }
    
    return $barcode;
}

/**
 * Generate unique request number
 */
function generate_request_number() {
    global $conn;
    
    $date = date('Ymd');
    
    // Get the count of requests created today
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM blood_requests WHERE DATE(created_at) = CURDATE()");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $sequence = $row['count'] + 1;
    
    // Format: REQ-YYYYMMDD-XXXX
    $request_number = sprintf("REQ-%s-%04d", $date, $sequence);
    
    return $request_number;
}

/**
 * Generate unique dispatch number
 */
function generate_dispatch_number() {
    global $conn;
    
    $date = date('Ymd');
    
    // Get the count of dispatches created today
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM dispatch WHERE DATE(dispatch_date) = CURDATE()");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $sequence = $row['count'] + 1;
    
    // Format: DISP-YYYYMMDD-XXXX
    $dispatch_number = sprintf("DISP-%s-%04d", $date, $sequence);
    
    return $dispatch_number;
}

/**
 * Check donor eligibility based on system settings
 */
function check_donor_eligibility($donor_data) {
    global $conn;
    
    $reasons = [];
    $eligible = true;
    
    // Get system settings
    $settings = [];
    $result = $conn->query("SELECT setting_key, setting_value FROM system_settings");
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    // Check age
    $age = date_diff(date_create($donor_data['date_of_birth']), date_create('now'))->y;
    $min_age = $settings['min_donor_age'] ?? 18;
    $max_age = $settings['max_donor_age'] ?? 65;
    
    if ($age < $min_age) {
        $reasons[] = "Age must be at least {$min_age} years";
        $eligible = false;
    } elseif ($age > $max_age) {
        $reasons[] = "Age must not exceed {$max_age} years";
        $eligible = false;
    }
    
    // Check weight
    if (isset($donor_data['weight_kg'])) {
        $min_weight = $settings['min_donor_weight'] ?? 50;
        if ($donor_data['weight_kg'] < $min_weight) {
            $reasons[] = "Weight must be at least {$min_weight} kg";
            $eligible = false;
        }
    }
    
    // Check hemoglobin
    if (isset($donor_data['hemoglobin_level']) && isset($donor_data['gender'])) {
        if ($donor_data['gender'] === 'MALE') {
            $min_hb = $settings['min_hemoglobin_male'] ?? 13.0;
            if ($donor_data['hemoglobin_level'] < $min_hb) {
                $reasons[] = "Hemoglobin level must be at least {$min_hb} g/dL for males";
                $eligible = false;
            }
        } else {
            $min_hb = $settings['min_hemoglobin_female'] ?? 12.5;
            if ($donor_data['hemoglobin_level'] < $min_hb) {
                $reasons[] = "Hemoglobin level must be at least {$min_hb} g/dL for females";
                $eligible = false;
            }
        }
    }
    
    // Check last donation date
    if (isset($donor_data['last_donation_date']) && $donor_data['last_donation_date']) {
        $interval_days = $settings['donation_interval_days'] ?? 56;
        $last_donation = new DateTime($donor_data['last_donation_date']);
        $today = new DateTime();
        $days_since = $today->diff($last_donation)->days;
        
        if ($days_since < $interval_days) {
            $days_remaining = $interval_days - $days_since;
            $reasons[] = "Must wait {$days_remaining} more days since last donation";
            $eligible = false;
        }
    }
    
    return [
        'eligible' => $eligible,
        'reasons' => $reasons,
        'status' => $eligible ? 'ELIGIBLE' : 'NOT_ELIGIBLE'
    ];
}
?>
