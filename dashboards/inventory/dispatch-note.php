<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

require_role(['Inventory Manager', 'System Administrator', 'Hospital User']);

if (!isset($_GET['id'])) {
    die("Dispatch ID not provided.");
}

$dispatch_id = intval($_GET['id']);

// Get dispatch details
$stmt = $conn->prepare("
    SELECT d.*, br.request_number, br.hospital_name, br.blood_group as requested_group, 
           u.full_name as dispatched_by_name
    FROM dispatch d
    JOIN blood_requests br ON d.request_id = br.request_id
    JOIN users u ON d.dispatched_by = u.user_id
    WHERE d.dispatch_id = ?
");
$stmt->bind_param("i", $dispatch_id);
$stmt->execute();
$dispatch = $stmt->get_result()->fetch_assoc();

if (!$dispatch) {
    die("Dispatch record not found.");
}

// Get dispatched units
$stmt = $conn->prepare("
    SELECT bu.barcode, bu.blood_group, bu.blood_type, bu.collection_date, bu.expiry_date
    FROM blood_units bu
    JOIN request_allocations ra ON bu.unit_id = ra.unit_id
    WHERE ra.request_id = ?
");
$stmt->bind_param("i", $dispatch['request_id']);
$stmt->execute();
$units = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dispatch Note #<?= $dispatch['dispatch_number'] ?></title>
    <style>
        body { font-family: 'Inter', sans-serif; padding: 40px; color: #111; max-width: 800px; margin: 0 auto; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 20px; margin-bottom: 30px; }
        .logo { font-size: 32px; font-weight: bold; color: #b91c1c; }
        .title { font-size: 24px; font-weight: bold; margin-top: 10px; text-transform: uppercase; }
        
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px; }
        .info-group h3 { font-size: 14px; text-transform: uppercase; letter-spacing: 1px; color: #555; margin-bottom: 5px; border-bottom: 1px solid #ccc; padding-bottom: 3px; }
        .info-group p { font-size: 16px; margin: 5px 0; font-weight: 500; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #f3f4f6; font-weight: bold; text-transform: uppercase; font-size: 12px; }
        
        .footer { margin-top: 60px; display: flex; justify-content: space-between; }
        .signature-box { border-top: 1px solid #000; width: 40%; padding-top: 10px; text-align: center; font-weight: bold; }
        
        @media print {
            body { padding: 0; }
            button { display: none; }
        }
    </style>
</head>
<body>
    <button onclick="window.print()" style="position: absolute; top: 20px; right: 20px; padding: 10px 20px; background: #2563eb; color: white; border: none; cursor: pointer; border-radius: 5px;">Print Note</button>

    <div class="header">
        <div class="logo">🩸 BBMS Dispatch</div>
        <div class="title">Blood Dispatch Note</div>
        <p>National Blood Bank Services</p>
    </div>

    <div class="info-grid">
        <div class="info-group">
            <h3>Dispatch Details</h3>
            <p><strong>Tracking #:</strong> <?= $dispatch['dispatch_number'] ?></p>
            <p><strong>Date:</strong> <?= date('Y-m-d H:i', strtotime($dispatch['dispatch_date'])) ?></p>
            <p><strong>Status:</strong> <?= $dispatch['status'] ?></p>
            <p><strong>Dispatched By:</strong> <?= htmlspecialchars($dispatch['dispatched_by_name']) ?></p>
        </div>
        <div class="info-group">
            <h3>Recipient Information</h3>
            <p><strong>Hospital:</strong> <?= htmlspecialchars($dispatch['hospital_name']) ?></p>
            <p><strong>Request #:</strong> <?= $dispatch['request_number'] ?></p>
            <p><strong>Requested Group:</strong> <?= $dispatch['requested_group'] ?></p>
        </div>
    </div>

    <div class="info-grid">
        <div class="info-group">
            <h3>Courier Details</h3>
            <p><strong>Name:</strong> <?= htmlspecialchars($dispatch['courier_name'] ?: 'N/A') ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($dispatch['courier_phone'] ?: 'N/A') ?></p>
            <p><strong>Vehicle:</strong> <?= htmlspecialchars($dispatch['vehicle_number'] ?: 'N/A') ?></p>
        </div>
    </div>

    <h3>Dispatched Units</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Barcode (DIN)</th>
                <th>Blood Group</th>
                <th>Product Type</th>
                <th>Collection Date</th>
                <th>Expiry Date</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $i = 1;
            while ($unit = $units->fetch_assoc()): 
            ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td style="font-family: monospace; font-weight: bold;"><?= $unit['barcode'] ?></td>
                    <td><?= $unit['blood_group'] ?></td>
                    <td><?= $unit['blood_type'] ?></td>
                    <td><?= date('Y-m-d', strtotime($unit['collection_date'])) ?></td>
                    <td><?= $unit['expiry_date'] ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="footer">
        <div class="signature-box">
            Dispatched By (Sign & Stamp)
        </div>
        <div class="signature-box">
            Received By (Sign & Stamp)
        </div>
    </div>
</body>
</html>
