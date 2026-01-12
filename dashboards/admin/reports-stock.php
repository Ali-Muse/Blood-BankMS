<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';

require_role(['System Administrator']);

// Get filters
$date_from = $_GET['date_from'] ?? date('Y-m-01'); // First day of current month
$date_to = $_GET['date_to'] ?? date('Y-m-d'); // Today
$blood_type_filter = $_GET['blood_type'] ?? '';

// Build WHERE clause for filters
$where_conditions = ["status = 'APPROVED'"];
$params = [];
$types = '';

if ($blood_type_filter) {
    $where_conditions[] = "blood_group = ?";
    $params[] = $blood_type_filter;
    $types .= 's';
}

$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

// Get total blood units (all time)
$total_units_query = "SELECT COUNT(*) as count FROM blood_units";
$total_units = $conn->query($total_units_query)->fetch_assoc()['count'];

// Get available units (APPROVED status)
$available_query = "SELECT COUNT(*) as count FROM blood_units WHERE status = 'APPROVED'";
$available_units = $conn->query($available_query)->fetch_assoc()['count'];

// Get reserved units
$reserved_query = "SELECT COUNT(*) as count FROM blood_units WHERE status = 'RESERVED'";
$reserved_units = $conn->query($reserved_query)->fetch_assoc()['count'];

// Get expiring soon (within 7 days)
$expiring_query = "SELECT COUNT(*) as count FROM blood_units WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND expiry_date >= CURDATE() AND status = 'APPROVED'";
$expiring_units = $conn->query($expiring_query)->fetch_assoc()['count'];

// Get blood stock by type
$blood_types = ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'];
$stock_data = [];
foreach ($blood_types as $type) {
    $query = "SELECT COUNT(*) as count FROM blood_units WHERE blood_group = ? AND status = 'APPROVED'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $type);
    $stmt->execute();
    $stock_data[$type] = $stmt->get_result()->fetch_assoc()['count'];
}

// Get regional distribution (using hospital_name as proxy for region)
$regional_query = "
    SELECT 
        CASE 
            WHEN hospital_name LIKE '%Kigali%' OR hospital_name LIKE '%Central%' THEN 'Central Region'
            WHEN hospital_name LIKE '%North%' THEN 'Northern Region'
            WHEN hospital_name LIKE '%South%' THEN 'Southern Region'
            WHEN hospital_name LIKE '%East%' THEN 'Eastern Region'
            WHEN hospital_name LIKE '%West%' THEN 'Western Region'
            ELSE 'Other Regions'
        END as region,
        COUNT(*) as count
    FROM blood_units bu
    LEFT JOIN blood_requests br ON bu.unit_id = br.unit_id
    LEFT JOIN users u ON br.user_id = u.user_id
    WHERE bu.status = 'APPROVED'
    GROUP BY region
    ORDER BY count DESC
";
$regional_result = $conn->query($regional_query);
$regional_data = [];
while ($row = $regional_result->fetch_assoc()) {
    $regional_data[] = $row;
}

// Get monthly trends (collection vs distribution)
$current_month_collected = $conn->query("
    SELECT COUNT(*) as count 
    FROM blood_units 
    WHERE MONTH(collection_date) = MONTH(CURRENT_DATE()) 
    AND YEAR(collection_date) = YEAR(CURRENT_DATE())
")->fetch_assoc()['count'];

$current_month_distributed = $conn->query("
    SELECT COUNT(*) as count 
    FROM blood_requests 
    WHERE status = 'APPROVED' 
    AND MONTH(created_at) = MONTH(CURRENT_DATE()) 
    AND YEAR(created_at) = YEAR(CURRENT_DATE())
")->fetch_assoc()['count'];

$last_month_collected = $conn->query("
    SELECT COUNT(*) as count 
    FROM blood_units 
    WHERE MONTH(collection_date) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) 
    AND YEAR(collection_date) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)
")->fetch_assoc()['count'];

// Calculate growth percentage
$growth = 0;
if ($last_month_collected > 0) {
    $growth = (($current_month_collected - $last_month_collected) / $last_month_collected) * 100;
}

// Handle export
if (isset($_GET['export'])) {
    $export_type = $_GET['export'];
    
    if ($export_type === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="blood_stock_report_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['National Blood Stock Report - Generated: ' . date('Y-m-d H:i:s')]);
        fputcsv($output, []);
        fputcsv($output, ['Summary Statistics']);
        fputcsv($output, ['Total Blood Units', $total_units]);
        fputcsv($output, ['Available Units', $available_units]);
        fputcsv($output, ['Reserved Units', $reserved_units]);
        fputcsv($output, ['Expiring Soon (7 days)', $expiring_units]);
        fputcsv($output, []);
        fputcsv($output, ['Blood Type', 'Units Available']);
        foreach ($stock_data as $type => $count) {
            fputcsv($output, [$type, $count]);
        }
        fputcsv($output, []);
        fputcsv($output, ['Regional Distribution']);
        fputcsv($output, ['Region', 'Units']);
        foreach ($regional_data as $region) {
            fputcsv($output, [$region['region'], $region['count']]);
        }
        fclose($output);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>National Blood Stock Report - BBMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: { colors: { blood: { 600: '#dc2626', 700: '#b91c1c', 800: '#991b1b' } } }
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-sans text-gray-900">
    <?php render_sidebar('reports-stock.php'); ?>
    
    <div class="main-content ml-0 md:ml-72 min-h-screen p-6 pt-20 md:pt-8 transition-all duration-300">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <i class="fa-solid fa-chart-simple text-3xl text-blood-700"></i>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">National Blood Stock</h1>
                </div>
                <p class="text-gray-500">Real-time blood inventory overview across all regions.</p>
            </div>
            <div class="flex gap-2">
                <a href="?export=csv&<?= http_build_query($_GET) ?>" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-5 rounded-lg transition-colors shadow-md flex items-center gap-2">
                    <i class="fa-solid fa-file-csv"></i> Export CSV
                </a>
                <button onclick="window.print()" class="bg-blood-700 hover:bg-blood-800 text-white font-bold py-2.5 px-5 rounded-lg transition-colors shadow-md flex items-center gap-2">
                    <i class="fa-solid fa-print"></i> Print
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 p-2.5 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 p-2.5 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Blood Type</label>
                    <select name="blood_type" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 p-2.5 border bg-white">
                        <option value="">All Types</option>
                        <?php foreach ($blood_types as $type): ?>
                            <option value="<?= $type ?>" <?= $blood_type_filter === $type ? 'selected' : '' ?>><?= $type ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-gray-700 hover:bg-gray-800 text-white font-bold py-2.5 px-4 rounded-lg transition-colors">
                        <i class="fa-solid fa-filter mr-2"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 border-l-4 border-l-blood-600">
                <p class="text-gray-500 text-sm font-medium mb-1">Total Blood Units</p>
                <h2 class="text-3xl font-extrabold text-gray-900"><?= number_format($total_units) ?></h2>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 border-l-4 border-l-green-600">
                <p class="text-gray-500 text-sm font-medium mb-1">Available Units</p>
                <h2 class="text-3xl font-extrabold text-green-600"><?= number_format($available_units) ?></h2>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 border-l-4 border-l-blue-600">
                <p class="text-gray-500 text-sm font-medium mb-1">Reserved Units</p>
                <h2 class="text-3xl font-extrabold text-blue-600"><?= number_format($reserved_units) ?></h2>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 border-l-4 border-l-yellow-500">
                <p class="text-gray-500 text-sm font-medium mb-1">Expiring Soon</p>
                <h2 class="text-3xl font-extrabold text-yellow-600"><?= number_format($expiring_units) ?></h2>
                <p class="text-xs text-gray-400 mt-1">Within 7 days</p>
            </div>
        </div>

        <!-- Blood Stock by Type -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                <i class="fa-solid fa-droplet text-blood-600"></i> Blood Stock by Type
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4">
                <?php foreach ($blood_types as $type):
                    $count = $stock_data[$type] ?? 0;
                    $status_class = $count > 100 ? 'bg-green-100 text-green-800 ring-green-200' : ($count > 50 ? 'bg-yellow-100 text-yellow-800 ring-yellow-200' : 'bg-red-100 text-red-800 ring-red-200');
                    $status_text = $count > 100 ? 'Adequate' : ($count > 50 ? 'Low Stock' : 'Critical');
                ?>
                    <div class="group relative rounded-xl p-4 text-center ring-1 ring-inset <?= $status_class ?> hover:shadow-md transition-shadow">
                        <h3 class="text-2xl font-black mb-1"><?= $count ?></h3>
                        <div class="font-bold text-lg mb-2"><?= $type ?></div>
                        <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ring-1 ring-inset ring-current opacity-75">
                            <?= $status_text ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Regional Distribution & Monthly Trends -->
        <div class="grid lg:grid-cols-2 gap-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-gray-50">
                    <h2 class="text-xl font-bold text-gray-900">Regional Distribution</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    <?php if (empty($regional_data)): ?>
                        <div class="p-6 text-center text-gray-500">No regional data available</div>
                    <?php else: ?>
                        <?php foreach ($regional_data as $region): ?>
                            <div class="p-4 md:p-6 flex justify-between items-center hover:bg-gray-50 transition-colors">
                                <span class="font-medium text-gray-700"><?= htmlspecialchars($region['region']) ?></span>
                                <div class="flex flex-col items-end">
                                    <span class="font-bold text-green-600 text-lg"><?= number_format($region['count']) ?></span>
                                    <span class="text-xs text-gray-400">units</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-gray-50">
                    <h2 class="text-xl font-bold text-gray-900">Monthly Trends</h2>
                </div>
                <div class="p-8">
                    <div class="bg-gray-50 rounded-xl p-6 text-center border border-gray-200">
                        <p class="text-sm font-medium text-gray-500 mb-6 uppercase tracking-wider">Collection vs Distribution</p>
                        <div class="grid grid-cols-2 gap-8 mb-6">
                            <div>
                                <p class="text-3xl font-extrabold text-green-600"><?= number_format($current_month_collected) ?></p>
                                <p class="text-sm text-gray-500 mt-1">Collected</p>
                            </div>
                            <div class="border-l border-gray-200">
                                <p class="text-3xl font-extrabold text-blood-600"><?= number_format($current_month_distributed) ?></p>
                                <p class="text-sm text-gray-500 mt-1">Distributed</p>
                            </div>
                        </div>
                        <div class="pt-6 border-t border-gray-200">
                            <div class="inline-flex items-center px-3 py-1 rounded-full <?= $growth >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?> font-bold text-sm">
                                <i class="fa-solid fa-arrow-trend-<?= $growth >= 0 ? 'up' : 'down' ?> mr-2"></i> <?= number_format(abs($growth), 1) ?>%
                            </div>
                            <p class="text-xs text-gray-400 mt-2">Growth vs Last Month</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/sidebar.js"></script>
</body>
</html>
