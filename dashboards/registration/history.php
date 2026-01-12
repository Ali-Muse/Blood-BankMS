<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';
require_once '../../includes/notification_functions.php';

require_role(['Registration Officer', 'System Administrator']);

$success = '';
$error = '';

// Handle blood collection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['collect_blood'])) {
    $donor_id = intval($_POST['donor_id']);
    $volume_ml = intval($_POST['volume_ml'] ?? 450);
    $storage_location = sanitize_input($_POST['storage_location'] ?? '');
    $notes = sanitize_input($_POST['notes'] ?? '');
    
    // Verify donor is eligible
    $stmt = $conn->prepare("SELECT * FROM donors WHERE donor_id = ? AND eligibility = 'ELIGIBLE'");
    $stmt->bind_param("i", $donor_id);
    $stmt->execute();
    $donor = $stmt->get_result()->fetch_assoc();
    
    if (!$donor) {
        $error = "Donor not found or not eligible for donation.";
    } else {
        // Generate barcode
        $barcode = generate_blood_barcode();
        
        // Calculate expiry date (42 days from collection)
        $collection_date = date('Y-m-d H:i:s');
        $expiry_date = date('Y-m-d', strtotime('+42 days'));
        
        // Insert blood unit
        $stmt = $conn->prepare("
            INSERT INTO blood_units (
                barcode, donor_id, blood_group, volume_ml, collection_date, 
                expiry_date, status, collected_by, storage_location, notes
            ) VALUES (?, ?, ?, ?, ?, ?, 'COLLECTED', ?, ?, ?)
        ");
        
        $user_id = get_user_id();
        $stmt->bind_param(
            "sissssis",
            $barcode, $donor_id, $donor['blood_group'], $volume_ml, 
            $collection_date, $expiry_date, $user_id, $storage_location, $notes
        );
        
        if ($stmt->execute()) {
            $unit_id = $conn->insert_id;
            
            // Update donor's last donation date and total donations
            $stmt = $conn->prepare("
                UPDATE donors 
                SET last_donation_date = CURDATE(), total_donations = total_donations + 1 
                WHERE donor_id = ?
            ");
            $stmt->bind_param("i", $donor_id);
            $stmt->execute();
            
            // Create lab test record
            $stmt = $conn->prepare("INSERT INTO lab_tests (unit_id) VALUES (?)");
            $stmt->bind_param("i", $unit_id);
            $stmt->execute();
            
            // Log audit
            log_audit($user_id, "Collected blood from donor #{$donor_id}, barcode: {$barcode}");
            
            // Notify lab technologists
            send_role_notification(
                'Laboratory Technologist',
                'INFO',
                'New Blood Sample',
                "New blood unit {$barcode} ({$donor['blood_group']}) ready for testing.",
                'dashboards/lab/samples-queue.php'
            );
            
            $success = "Blood collected successfully! Barcode: <strong>{$barcode}</strong>. Unit sent to laboratory for testing.";
        } else {
            $error = "Failed to record blood collection. Please try again.";
        }
    }
}

// Get eligible donors
$eligible_donors = $conn->query("
    SELECT donor_id, full_name, blood_group, phone, last_donation_date, total_donations
    FROM donors 
    WHERE eligibility = 'ELIGIBLE'
    ORDER BY full_name ASC
");

// Get recent collections
$recent_collections = $conn->query("
    SELECT bu.*, d.full_name, d.phone
    FROM blood_units bu
    JOIN donors d ON bu.donor_id = d.donor_id
    WHERE DATE(bu.collection_date) = CURDATE()
    ORDER BY bu.collection_date DESC
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Collection - BBMS</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php render_sidebar('history.php'); ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1>💉 Blood Collection</h1>
            <p>Collect blood from eligible donors</p>
        </div>

        <?php if ($success): ?>
            <div style="background: #d1fae5; color: #065f46; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
                ✓ <?= $success ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
                ✗ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-2" style="gap: 24px;">
            <!-- Collection Form -->
            <div class="card">
                <h2 style="margin-bottom: 24px; color: var(--blood-700);">Collect Blood</h2>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label">Select Eligible Donor *</label>
                        <select name="donor_id" class="form-input" required id="donor-select">
                            <option value="">Choose a donor...</option>
                            <?php while ($donor = $eligible_donors->fetch_assoc()): ?>
                                <option value="<?= $donor['donor_id'] ?>" 
                                        data-blood-group="<?= $donor['blood_group'] ?>"
                                        data-last-donation="<?= $donor['last_donation_date'] ?? 'Never' ?>"
                                        data-total="<?= $donor['total_donations'] ?>">
                                    <?= htmlspecialchars($donor['full_name']) ?> - <?= $donor['blood_group'] ?> (<?= $donor['phone'] ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div id="donor-info" style="display: none; padding: 16px; background: #f3f4f6; border-radius: 8px; margin-bottom: 16px;">
                        <p style="font-size: 14px; color: #374151; margin-bottom: 8px;"><strong>Blood Group:</strong> <span id="info-blood-group"></span></p>
                        <p style="font-size: 14px; color: #374151; margin-bottom: 8px;"><strong>Last Donation:</strong> <span id="info-last-donation"></span></p>
                        <p style="font-size: 14px; color: #374151;"><strong>Total Donations:</strong> <span id="info-total"></span></p>
                    </div>

                    <div class="grid grid-2">
                        <div class="form-group">
                            <label class="form-label">Volume (ml) *</label>
                            <input type="number" name="volume_ml" class="form-input" value="450" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Storage Location</label>
                            <input type="text" name="storage_location" class="form-input" placeholder="e.g., Fridge A, Shelf 2">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-input" rows="3" placeholder="Any additional notes..."></textarea>
                    </div>

                    <button type="submit" name="collect_blood" class="btn-primary" style="width: 100%;">
                        Collect Blood & Generate Barcode
                    </button>
                </form>
            </div>

            <!-- Today's Collections -->
            <div class="card">
                <h2 style="margin-bottom: 24px; color: var(--blood-700);">Today's Collections</h2>
                
                <?php if ($recent_collections->num_rows > 0): ?>
                    <div style="max-height: 500px; overflow-y: auto;">
                        <?php while ($collection = $recent_collections->fetch_assoc()): ?>
                            <div style="padding: 16px; background: #f9fafb; border-radius: 8px; margin-bottom: 12px;">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                                    <div>
                                        <p style="font-weight: 600; color: var(--blood-700); margin-bottom: 4px;">
                                            <?= htmlspecialchars($collection['full_name']) ?>
                                        </p>
                                        <p style="font-size: 12px; color: #6b7280;">
                                            <?= $collection['phone'] ?>
                                        </p>
                                    </div>
                                    <span style="background: var(--blood-100); color: var(--blood-700); padding: 4px 12px; border-radius: 12px; font-weight: 600; font-size: 12px;">
                                        <?= $collection['blood_group'] ?>
                                    </span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 12px; padding-top: 12px; border-top: 1px solid #e5e7eb;">
                                    <p style="font-size: 13px; color: #374151;">
                                        <strong>Barcode:</strong> <?= $collection['barcode'] ?>
                                    </p>
                                    <p style="font-size: 12px; color: #6b7280;">
                                        <?= date('H:i', strtotime($collection['collection_date'])) ?>
                                    </p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 48px 24px; color: #6b7280;">
                        <div style="font-size: 48px; margin-bottom: 16px;">💉</div>
                        <p>No collections today yet</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="../../assets/js/sidebar.js"></script>
    <script>
        // Show donor info when selected
        document.getElementById('donor-select').addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const infoDiv = document.getElementById('donor-info');
            
            if (this.value) {
                document.getElementById('info-blood-group').textContent = selected.dataset.bloodGroup;
                document.getElementById('info-last-donation').textContent = selected.dataset.lastDonation;
                document.getElementById('info-total').textContent = selected.dataset.total;
                infoDiv.style.display = 'block';
            } else {
                infoDiv.style.display = 'none';
            }
        });
    </script>
</body>
</html>
