<?php
// Debug script to test request approval
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/notification_functions.php';

// Check if we're logged in
if (!is_logged_in()) {
    die("Please login first");
}

echo "<h2>Debug: Request Approval System</h2>";

// Check pending requests
echo "<h3>1. Checking Pending Requests</h3>";
$pending = $conn->query("SELECT * FROM blood_requests WHERE status = 'PENDING'");
echo "Found {$pending->num_rows} pending requests<br>";

if ($pending->num_rows > 0) {
    while ($req = $pending->fetch_assoc()) {
        echo "- Request #{$req['request_number']}: {$req['quantity']} units of {$req['blood_group']} for {$req['hospital_name']}<br>";
        
        // Check stock for this blood group
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM blood_units WHERE blood_group = ? AND status = 'APPROVED' AND expiry_date > CURDATE()");
        $stmt->bind_param("s", $req['blood_group']);
        $stmt->execute();
        $stock = $stmt->get_result()->fetch_assoc()['count'];
        echo "  Available stock: {$stock} units<br>";
        echo "  Can approve: " . ($stock >= $req['quantity'] ? "YES" : "NO") . "<br>";
    }
}

// Check if dispatch table exists
echo "<h3>2. Checking Dispatch Table</h3>";
$dispatch_check = $conn->query("SHOW TABLES LIKE 'dispatch'");
if ($dispatch_check->num_rows > 0) {
    echo "✓ Dispatch table exists<br>";
    $dispatch_count = $conn->query("SELECT COUNT(*) as count FROM dispatch")->fetch_assoc()['count'];
    echo "Total dispatch records: {$dispatch_count}<br>";
} else {
    echo "✗ Dispatch table MISSING!<br>";
}

// Check request_allocations table
echo "<h3>3. Checking Request Allocations Table</h3>";
$alloc_check = $conn->query("SHOW TABLES LIKE 'request_allocations'");
if ($alloc_check->num_rows > 0) {
    echo "✓ Request allocations table exists<br>";
    $alloc_count = $conn->query("SELECT COUNT(*) as count FROM request_allocations")->fetch_assoc()['count'];
    echo "Total allocation records: {$alloc_count}<br>";
} else {
    echo "✗ Request allocations table MISSING!<br>";
}

// Check approved blood units
echo "<h3>4. Checking Approved Blood Units</h3>";
$approved = $conn->query("SELECT blood_group, COUNT(*) as count FROM blood_units WHERE status = 'APPROVED' AND expiry_date > CURDATE() GROUP BY blood_group");
if ($approved->num_rows > 0) {
    while ($row = $approved->fetch_assoc()) {
        echo "{$row['blood_group']}: {$row['count']} units<br>";
    }
} else {
    echo "No approved blood units available!<br>";
}

// Test notification function
echo "<h3>5. Testing Notification Functions</h3>";
if (function_exists('notify_request_status')) {
    echo "✓ notify_request_status() function exists<br>";
} else {
    echo "✗ notify_request_status() function MISSING!<br>";
}

echo "<br><a href='dashboards/inventory/review-requests.php'>Go to Review Requests Page</a>";
?>
