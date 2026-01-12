<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';

require_role(['Inventory Manager']);

$page_title = 'Inventory Manager Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - BBMS</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<?php
// Fetch Inventory Stats
$stats = [
    'available' => 0,
    'reserved' => 0,
    'expiring' => 0,
    'dispatched' => 0
];

// 1. Available Units
$query = "SELECT COUNT(*) as count FROM blood_units WHERE status = 'APPROVED'";
$result = $conn->query($query);
if ($result) $stats['available'] = $result->fetch_assoc()['count'];

// 2. Reserved Units
$query = "SELECT COUNT(*) as count FROM blood_units WHERE status = 'RESERVED'";
$result = $conn->query($query);
if ($result) $stats['reserved'] = $result->fetch_assoc()['count'];

// 3. Expiring Soon (7 days)
$query = "SELECT COUNT(*) as count FROM blood_units WHERE status = 'APPROVED' AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
$result = $conn->query($query);
if ($result) $stats['expiring'] = $result->fetch_assoc()['count'];

// 4. Dispatched Today
$query = "SELECT COUNT(ra.unit_id) as count 
          FROM dispatch d 
          JOIN request_allocations ra ON d.request_id = ra.request_id 
          WHERE DATE(d.dispatch_date) = CURDATE() AND d.status != 'CANCELLED'";
$result = $conn->query($query);
if ($result) $stats['dispatched'] = $result->fetch_assoc()['count'];

// 5. Inventory by Type
$inventory_by_type = [];
$query = "SELECT blood_group, COUNT(*) as count FROM blood_units WHERE status = 'APPROVED' GROUP BY blood_group";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $inventory_by_type[$row['blood_group']] = $row['count'];
    }
}
// Ensure all groups are present
$blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
foreach ($blood_groups as $bg) {
    if (!isset($inventory_by_type[$bg])) $inventory_by_type[$bg] = 0;
}

// 6. Expiry Alerts List
$expiry_alerts = [];
$query = "SELECT unit_id, barcode, blood_group, expiry_date, DATEDIFF(expiry_date, CURDATE()) as days_left 
          FROM blood_units 
          WHERE status = 'APPROVED' 
          AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) 
          ORDER BY expiry_date ASC LIMIT 5";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $expiry_alerts[] = $row;
    }
}
?>
    <?php render_sidebar('index.php'); ?>
    
    <div class="main-content ml-0 md:ml-72 min-h-screen p-6 pt-20 md:pt-8 transition-all duration-300 bg-gray-50 text-gray-800">
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-blood-900 tracking-tight">Inventory Manager Dashboard</h1>
            <p class="text-gray-500 mt-2">Blood stock management and distribution</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-600">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Available Units</p>
                        <h2 class="text-3xl font-extrabold text-green-600"><?= number_format($stats['available']) ?></h2>
                    </div>
                    <span class="text-3xl grayscale opacity-80">✅</span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-medical-600">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Reserved Units</p>
                        <h2 class="text-3xl font-extrabold text-medical-600"><?= number_format($stats['reserved']) ?></h2>
                    </div>
                    <span class="text-3xl">🔒</span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-amber-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Expiring Soon (7 days)</p>
                        <h2 class="text-3xl font-extrabold text-amber-500"><?= number_format($stats['expiring']) ?></h2>
                    </div>
                    <span class="text-3xl">⏰</span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blood-600">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Dispatched Today</p>
                        <h2 class="text-3xl font-extrabold text-blood-600"><?= number_format($stats['dispatched']) ?></h2>
                    </div>
                    <span class="text-3xl">🚚</span>
                </div>
            </div>
        </div>

        <!-- Advanced Feature -->
        <div class="mt-8 bg-gradient-to-br from-red-50 to-amber-50 rounded-xl p-6 border border-amber-100 shadow-sm relative overflow-hidden">
            <div class="relative z-10">
                <h3 class="flex items-center text-lg font-bold text-blood-900 mb-2">
                    <span class="mr-2">🌟</span> Advanced Features: Auto-Expiry Alerts & FIFO Dispatch
                </h3>
                <p class="text-gray-600 text-sm max-w-3xl">The system automatically alerts when blood units are approaching expiry (7 days). FIFO (First In, First Out) logic ensures oldest units are dispatched first to minimize waste.</p>
            </div>
        </div>

        <!-- Blood Inventory by Type -->
        <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                <span class="bg-blood-100 text-blood-700 p-2 rounded-lg mr-3 text-lg">🩸</span>
                Blood Inventory by Type
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <!-- Top 4 Common Types -->
                <?php 
                $top_types = ['O+', 'A+', 'B+', 'AB+'];
                foreach ($top_types as $type): 
                ?>
                <div class="text-center p-6 bg-red-50 rounded-xl border border-red-100 hover:shadow-md transition-shadow">
                    <h3 class="text-4xl font-extrabold text-blood-700"><?= $inventory_by_type[$type] ?></h3>
                    <p class="text-sm font-medium text-blood-900/60 mt-2"><?= $type ?> Units</p>
                </div>
                <?php endforeach; ?>
                
                <!-- Display Others if needed (optional expansion) or leave as just top 4 for design -->
                <!-- For now, adhering to original design which showed 4 -->
            </div>
             <div class="mt-4 text-center">
                 <a href="available-units.php" class="text-sm font-medium text-blood-600 hover:text-blood-800 transition-colors">View all blood groups →</a>
            </div>
        </div>

        <!-- Expiry Alerts -->
        <div class="mt-8 bg-white rounded-xl shadow-sm border-l-4 border-amber-500 overflow-hidden">
            <div class="p-6 border-b border-gray-100 bg-amber-50/30">
                <h2 class="text-xl font-bold text-amber-600 flex items-center">
                    <span class="mr-2">⏰</span> Expiry Alerts
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-xs uppercase text-gray-500 font-semibold">
                            <th class="px-6 py-4">Unit ID</th>
                            <th class="px-6 py-4">Blood Group</th>
                            <th class="px-6 py-4">Expiry Date</th>
                            <th class="px-6 py-4">Days Left</th>
                            <th class="px-6 py-4">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (count($expiry_alerts) > 0): ?>
                            <?php foreach ($expiry_alerts as $alert): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 font-mono font-medium text-gray-700"><?= $alert['barcode'] ?></td>
                                <td class="px-6 py-4"><span class="font-bold text-blood-700"><?= $alert['blood_group'] ?></span></td>
                                <td class="px-6 py-4 text-gray-600"><?= date('M d, Y', strtotime($alert['expiry_date'])) ?></td>
                                <td class="px-6 py-4">
                                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold border border-red-200">
                                        <?= $alert['days_left'] ?> days
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="dispatch.php?id=<?= $alert['unit_id'] ?>" class="inline-flex items-center px-4 py-2 bg-blood-600 text-white text-xs font-bold uppercase tracking-wide rounded hover:bg-blood-700 transition-colors">
                                        Dispatch
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-400 font-medium">
                                    No units expiring within 7 days.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
            <a href="dispatch.php" class="group block bg-gradient-to-br from-blood-600 to-blood-700 rounded-xl p-8 text-white shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all">
                <div class="flex flex-col items-center text-center">
                    <span class="text-5xl mb-4 group-hover:scale-110 transition-transform duration-300">🚚</span>
                    <h3 class="text-xl font-bold">Dispatch Blood Units</h3>
                    <p class="text-blood-100 mt-2 text-sm">Create new dispatch requests or manage active deliveries</p>
                </div>
            </a>
            <a href="storage.php" class="group block bg-gradient-to-br from-medical-600 to-medical-700 rounded-xl p-8 text-white shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all">
                <div class="flex flex-col items-center text-center">
                    <span class="text-5xl mb-4 group-hover:scale-110 transition-transform duration-300">🌡️</span>
                    <h3 class="text-xl font-bold">Storage Monitoring</h3>
                    <p class="text-medical-100 mt-2 text-sm">Monitor temperature logs and storage capacity</p>
                </div>
            </a>
        </div>
    </div>

    <script src="../../assets/js/sidebar.js"></script>
</body>
</html>
