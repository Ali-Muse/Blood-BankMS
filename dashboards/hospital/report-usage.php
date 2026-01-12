<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';
require_once '../../includes/notification_functions.php';

require_role(['Hospital User']);

if (!isset($_GET['id'])) {
    header("Location: history.php");
    exit;
}

$request_id = intval($_GET['id']);
$user_id = get_user_id();
$success = '';
$error = '';

// Verify request belongs to this hospital
$stmt = $conn->prepare("SELECT * FROM blood_requests WHERE request_id = ? AND hospital_user_id = ?");
$stmt->bind_param("ii", $request_id, $user_id);
$stmt->execute();
$request = $stmt->get_result()->fetch_assoc();

if (!$request) {
    die("Access denied or request not found.");
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_usage'])) {
    $unit_id = intval($_POST['unit_id']);
    $status = $_POST['status']; // TRANSFUSED, RETURNED, DISCARDED
    $remarks = sanitize_input($_POST['remarks'] ?? '');
    
    if (in_array($status, ['TRANSFUSED', 'RETURNED', 'DISCARDED'])) {
        // Update unit status
        $stmt = $conn->prepare("UPDATE blood_units SET status = ? WHERE unit_id = ?");
        $stmt->bind_param("si", $status, $unit_id);
        
        if ($stmt->execute()) {
            $success = "Unit status updated successfully.";
            
            // Log if Discarded
            if ($status === 'DISCARDED') {
                log_audit($user_id, "Reported blood unit #$unit_id as DISCARDED. Reason: $remarks");
            }
        } else {
            $error = "Failed to update status.";
        }
    }
}

// Get allocations for this request
$stmt = $conn->prepare("
    SELECT bu.*, ra.allocation_id 
    FROM blood_units bu
    JOIN request_allocations ra ON bu.unit_id = ra.unit_id
    WHERE ra.request_id = ?
");
$stmt->bind_param("i", $request_id);
$stmt->execute();
$units = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Usage - BBMS</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php render_sidebar('history.php'); ?>
    
    <div class="main-content">
        <div class="page-header">
            <div>
                <a href="history.php" style="color: #6b7280; text-decoration: none; font-size: 14px;">← Back to History</a>
                <h1>📝 Report Blood Usage</h1>
                <p>Request #<?= $request['request_number'] ?> • <?= $request['blood_group'] ?></p>
            </div>
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

        <div class="card">
            <h2 style="margin-bottom: 20px;">Allocated Units</h2>
            
            <?php if ($units->num_rows > 0): ?>
                <div style="display: grid; gap: 16px;">
                    <?php while ($unit = $units->fetch_assoc()): ?>
                        <div style="border: 1px solid #e5e7eb; padding: 20px; border-radius: 8px; background: #f9fafb; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h3 style="font-family: monospace; font-size: 18px; color: var(--blood-700);"><?= $unit['barcode'] ?></h3>
                                <p style="font-size: 14px; color: #6b7280; margin-top: 4px;">
                                    <?= $unit['blood_group'] ?> • Exp: <?= $unit['expiry_date'] ?>
                                </p>
                                <div style="margin-top: 8px;">
                                    Current Status: 
                                    <span class="status-badge status-<?= strtolower($unit['status']) ?>">
                                        <?= str_replace('_', ' ', $unit['status']) ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div>
                                <?php if ($unit['status'] === 'DISPATCHED'): ?>
                                    <form method="POST" style="display: flex; gap: 10px; align-items: end;">
                                        <input type="hidden" name="unit_id" value="<?= $unit['unit_id'] ?>">
                                        
                                        <div class="form-group" style="margin: 0;">
                                            <select name="status" class="form-input" required style="padding: 8px; width: 150px;">
                                                <option value="" disabled selected>Update Status...</option>
                                                <option value="TRANSFUSED">✅ Transfused</option>
                                                <option value="RETURNED">↩️ Returned</option>
                                                <option value="DISCARDED">🗑️ Discarded/Wasted</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group" style="margin: 0;">
                                             <input type="text" name="remarks" class="form-input" placeholder="Remarks (Optional)" style="padding: 8px;">
                                        </div>
                                        
                                        <button type="submit" name="report_usage" class="btn-primary" style="padding: 8px 16px;">Update</button>
                                    </form>
                                <?php else: ?>
                                    <button disabled class="btn-secondary" style="opacity: 0.5; cursor: not-allowed;">Updated</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>No units allocated to this request.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="../../assets/js/sidebar.js"></script>
</body>
</html>
