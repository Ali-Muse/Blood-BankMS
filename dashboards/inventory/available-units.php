<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';

require_role(['Inventory Manager', 'System Administrator']);

// Get available blood units (APPROVED, not expired)
$available_units = $conn->query("
    SELECT bu.*, d.full_name as donor_name
    FROM blood_units bu
    JOIN donors d ON bu.donor_id = d.donor_id
    WHERE bu.status = 'APPROVED' AND bu.expiry_date > CURDATE()
    ORDER BY bu.collection_date ASC
");

// Get stock summary
$stock_summary = [];
$blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
foreach ($blood_groups as $group) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM blood_units 
        WHERE blood_group = ? AND status = 'APPROVED' AND expiry_date > CURDATE()
    ");
    $stmt->bind_param("s", $group);
    $stmt->execute();
    $stock_summary[$group] = $stmt->get_result()->fetch_assoc()['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Blood Units - BBMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 font-sans text-gray-900">
    <?php render_sidebar('available-units.php'); ?>
    
    <div class="main-content ml-0 md:ml-72 min-h-screen p-6 pt-20 md:pt-8 transition-all duration-300">
        <div class="mb-8">
             <div class="flex items-center gap-3 mb-2">
                <i class="fa-solid fa-vial-circle-check text-3xl text-blood-700"></i>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Available Blood Units</h1>
            </div>
            <p class="text-gray-500">Live inventory of approved blood units ready for dispatch.</p>
        </div>

        <!-- Stock Summary Grid -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
            <h3 class="font-bold text-gray-700 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-chart-simple"></i> Quick Stock Summary
            </h3>
            <div class="grid grid-cols-4 md:grid-cols-8 gap-4">
                <?php foreach ($blood_groups as $group): ?>
                    <div class="text-center p-3 bg-gray-50 rounded-lg border <?= $stock_summary[$group] < 5 ? 'border-red-200 bg-red-50' : 'border-gray-200' ?> transition-transform hover:-translate-y-1">
                        <div class="font-bold text-gray-700 mb-1 text-sm"><?= $group ?></div>
                        <div class="text-xl font-black <?= $stock_summary[$group] < 5 ? 'text-red-600' : 'text-green-600' ?>">
                            <?= $stock_summary[$group] ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Available Units Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-900">Detailed Inventory</h2>
                <div class="flex gap-2">
                    <button class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-3 py-1.5 rounded-lg text-sm font-semibold transition-colors">
                        <i class="fa-solid fa-filter mr-1"></i> Filter
                    </button>
                    <button class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-3 py-1.5 rounded-lg text-sm font-semibold transition-colors">
                        <i class="fa-solid fa-download mr-1"></i> Export
                    </button>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Barcode</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Blood Group</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Donor</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Collection Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Expiry Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Location</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if ($available_units->num_rows > 0): ?>
                            <?php while ($unit = $available_units->fetch_assoc()): ?>
                                <?php
                                $days_until_expiry = (strtotime($unit['expiry_date']) - time()) / (60 * 60 * 24);
                                $is_expiring_soon = $days_until_expiry <= 7;
                                ?>
                                <tr class="hover:bg-gray-50 transition-colors <?= $is_expiring_soon ? 'bg-yellow-50' : '' ?>">
                                    <td class="px-6 py-4 whitespace-nowrap font-mono text-sm font-bold text-gray-700">
                                        <?= $unit['barcode'] ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blood-100 text-blood-700">
                                            <?= $unit['blood_group'] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?= htmlspecialchars($unit['donor_name']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= date('M d, Y', strtotime($unit['collection_date'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($is_expiring_soon): ?>
                                            <div class="flex flex-col">
                                                <span class="text-sm font-bold text-red-600"><?= floor($days_until_expiry) ?> days left</span>
                                                <span class="text-xs text-red-500">Expires <?= date('M d', strtotime($unit['expiry_date'])) ?></span>
                                            </div>
                                        <?php else: ?>
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium text-green-600"><?= floor($days_until_expiry) ?> days left</span>
                                                <span class="text-xs text-gray-400">Expires <?= date('M d', strtotime($unit['expiry_date'])) ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <i class="fa-regular fa-snowflake mr-1"></i> <?= htmlspecialchars($unit['storage_location'] ?? 'Main Storage') ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fa-solid fa-flask-vial text-4xl mb-3 text-gray-300"></i>
                                    <p>No available units found matching the criteria.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="../../assets/js/sidebar.js"></script>
</body>
</html>
