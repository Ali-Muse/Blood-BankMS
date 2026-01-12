<?php
require_once 'includes/config.php';

echo "Checking blood units for A+...\n";

$result = $conn->query("SELECT unit_id, blood_group, status, expiry_date FROM blood_units WHERE blood_group = 'A+'");

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "Unit ID: " . $row['unit_id'] . " | Status: " . $row['status'] . " | Expiry: " . $row['expiry_date'] . "\n";
    }
} else {
    echo "No A+ units found in the database.\n";
}

echo "\nChecking all units count by status:\n";
$result = $conn->query("SELECT blood_group, status, COUNT(*) as count FROM blood_units GROUP BY blood_group, status");
while ($row = $result->fetch_assoc()) {
    echo "{$row['blood_group']} - {$row['status']}: {$row['count']}\n";
}
?>
