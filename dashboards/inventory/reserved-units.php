<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';

require_role(['Inventory Manager', 'System Administrator']);

// Get reserved blood units
$reserved_units = $conn->query("
    SELECT bu.*, d.full_name as donor_name, br.request_number, br.hospital_name, br.created_at as reserved_at
    FROM blood_units bu
    JOIN donors d ON bu.donor_id = d.donor_id
    LEFT JOIN request_allocations ra ON bu.unit_id = ra.unit_id
    LEFT JOIN blood_requests br ON ra.request_id = br.request_id
    WHERE bu.status = 'RESERVED'
    ORDER BY bu.collection_date ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserved Blood Units - BBMS</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php render_sidebar('reserved-units.php'); ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1>🔒 Reserved Blood Units</h1>
            <p>Units allocated to approved requests</p>
        </div>

        <div class="card">
            <h2 style="margin-bottom: 24px; color: var(--blood-700);">Reserved Units (<?= $reserved_units->num_rows ?>)</h2>
            
            <?php if ($reserved_units->num_rows > 0): ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f9fafb; border-bottom: 2px solid #e5e7eb;">
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Barcode</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Blood Group</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Donor</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Reserved For</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Request #</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Expiry Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($unit = $reserved_units->fetch_assoc()): ?>
                                <tr style="border-bottom: 1px solid #e5e7eb;">
                                    <td style="padding: 12px; font-family: monospace; font-weight: 600;">
                                        <?= $unit['barcode'] ?>
                                    </td>
                                    <td style="padding: 12px;">
                                        <span style="background: var(--blood-100); color: var(--blood-700); padding: 4px 12px; border-radius: 12px; font-weight: 600;">
                                            <?= $unit['blood_group'] ?>
                                        </span>
                                    </td>
                                    <td style="padding: 12px;"><?= htmlspecialchars($unit['donor_name']) ?></td>
                                    <td style="padding: 12px; font-weight: 600;"><?= htmlspecialchars($unit['hospital_name'] ?? 'N/A') ?></td>
                                    <td style="padding: 12px; font-family: monospace;"><?= $unit['request_number'] ?? 'N/A' ?></td>
                                    <td style="padding: 12px;"><?= $unit['expiry_date'] ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 64px 24px; color: #6b7280;">
                    <div style="font-size: 64px; margin-bottom: 16px;">🔒</div>
                    <h2 style="font-size: 20px; margin-bottom: 8px;">No Reserved Units</h2>
                    <p>No blood units are currently reserved</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../../assets/js/sidebar.js"></script>
</body>
</html>
