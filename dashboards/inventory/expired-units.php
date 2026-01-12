<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';

require_role(['Inventory Manager', 'System Administrator']);

// Get expired blood units
$expired_units = $conn->query("
    SELECT bu.*, d.full_name as donor_name
    FROM blood_units bu
    JOIN donors d ON bu.donor_id = d.donor_id
    WHERE bu.status = 'EXPIRED' OR (bu.expiry_date < CURDATE() AND bu.status IN ('APPROVED', 'COLLECTED'))
    ORDER BY bu.expiry_date DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expired Blood Units - BBMS</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php render_sidebar('expired-units.php'); ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1>⏰ Expired Blood Units</h1>
            <p>Units past expiry date (42 days from collection)</p>
        </div>

        <div class="card">
            <h2 style="margin-bottom: 24px; color: var(--blood-700);">Expired Units (<?= $expired_units->num_rows ?>)</h2>
            
            <?php if ($expired_units->num_rows > 0): ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f9fafb; border-bottom: 2px solid #e5e7eb;">
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Barcode</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Blood Group</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Donor</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Collection Date</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Expiry Date</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Days Expired</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($unit = $expired_units->fetch_assoc()): ?>
                                <?php
                                $days_expired = floor((time() - strtotime($unit['expiry_date'])) / (60 * 60 * 24));
                                ?>
                                <tr style="border-bottom: 1px solid #e5e7eb; background: #fee2e2;">
                                    <td style="padding: 12px; font-family: monospace; font-weight: 600;">
                                        <?= $unit['barcode'] ?>
                                    </td>
                                    <td style="padding: 12px;">
                                        <span style="background: var(--blood-100); color: var(--blood-700); padding: 4px 12px; border-radius: 12px; font-weight: 600;">
                                            <?= $unit['blood_group'] ?>
                                        </span>
                                    </td>
                                    <td style="padding: 12px;"><?= htmlspecialchars($unit['donor_name']) ?></td>
                                    <td style="padding: 12px;"><?= date('Y-m-d', strtotime($unit['collection_date'])) ?></td>
                                    <td style="padding: 12px; color: #dc2626; font-weight: 600;"><?= $unit['expiry_date'] ?></td>
                                    <td style="padding: 12px; color: #dc2626; font-weight: 600;"><?= $days_expired ?> days</td>
                                    <td style="padding: 12px;">
                                        <span style="background: #dc2626; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                                            EXPIRED
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 24px; padding: 16px; background: #fef3c7; border-radius: 8px;">
                    <p style="font-size: 14px; color: #92400e;">
                        ⚠️ <strong>Note:</strong> Expired blood units must be properly disposed of according to safety protocols. These units are automatically flagged by the system after 42 days from collection.
                    </p>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 64px 24px; color: #6b7280;">
                    <div style="font-size: 64px; margin-bottom: 16px;">✅</div>
                    <h2 style="font-size: 20px; margin-bottom: 8px;">No Expired Units</h2>
                    <p>All blood units are within their expiry date</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../../assets/js/sidebar.js"></script>
</body>
</html>
