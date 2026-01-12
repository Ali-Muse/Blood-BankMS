<?php
require_once 'includes/config.php';

echo "Seeding Blood Inventory...\n";

// Get a valid donor ID
$donor_result = $conn->query("SELECT donor_id FROM donors LIMIT 1");
if ($donor_result->num_rows > 0) {
    $donor_id = $donor_result->fetch_assoc()['donor_id'];
} else {
    // Create a dummy donor if none exist
    $conn->query("INSERT INTO donors (full_name, gender, date_of_birth, blood_group, phone) VALUES ('Seeded Donor', 'MALE', '1990-01-01', 'O+', '0000000000')");
    $donor_id = $conn->insert_id;
}

$blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
$count = 0;

foreach ($blood_groups as $group) {
    for ($i = 0; $i < 10; $i++) {
        $barcode = 'DIN-' . $group . '-' . date('Ymd') . '-' . rand(1000, 9999);
        $collection_date = date('Y-m-d H:i:s', strtotime('-' . rand(1, 10) . ' days'));
        $expiry_date = date('Y-m-d', strtotime('+35 days'));
        
        // Removed invalid columns: blood_type, lab_technician_id
        $sql = "INSERT INTO blood_units (barcode, donor_id, blood_group, collection_date, expiry_date, status, volume_ml) 
                VALUES ('$barcode', $donor_id, '$group', '$collection_date', '$expiry_date', 'APPROVED', 450)";
        
        if ($conn->query($sql)) {
            $count++;
        } else {
             echo "Failed for $group: " . $conn->error . "\n";
        }
    }
}

echo "Successfully added $count units.\n";
echo "Current Stock for A+:\n";
$res = $conn->query("SELECT COUNT(*) as count FROM blood_units WHERE blood_group = 'A+' AND status = 'APPROVED'");
$row = $res->fetch_assoc();
echo "A+ Count: " . $row['count'] . "\n";
?>
