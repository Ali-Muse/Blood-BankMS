<?php
// Get Config & Auth
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';

require_role(['Minister Of Health']);

$page_title = 'Authority/Supervisory Dashboard';

// Fetch National Stats
$stats = [
    'national_stock' => 0,
    'active_banks' => 0,
    'monthly_donations' => 0,
    'compliance_rate' => 100 // Default
];

// 1. National Blood Stock
$query = "SELECT COUNT(*) as count FROM blood_units WHERE status = 'APPROVED'";
$result = $conn->query($query);
if ($result) $stats['national_stock'] = $result->fetch_assoc()['count'];

// 2. Active Blood Banks (Proxy: Users with role Inventory Manager or Laboratory Technologist)
$query = "SELECT COUNT(*) as count FROM users WHERE role_name IN ('Inventory Manager', 'Laboratory Technologist')";
$result = $conn->query($query);
if ($result) $stats['active_banks'] = $result->fetch_assoc()['count'];

// 3. Monthly Donations
$query = "SELECT COUNT(*) as count FROM blood_units WHERE MONTH(collection_date) = MONTH(CURRENT_DATE()) AND YEAR(collection_date) = YEAR(CURRENT_DATE())";
$result = $conn->query($query);
if ($result) $stats['monthly_donations'] = $result->fetch_assoc()['count'];

// 4. Compliance/Safety Rate (100% - Rejection Rate)
$total_units_query = "SELECT COUNT(*) as count FROM blood_units";
$rejected_units_query = "SELECT COUNT(*) as count FROM blood_units WHERE status = 'REJECTED'";
$total_res = $conn->query($total_units_query)->fetch_assoc()['count'];
$rejected_res = $conn->query($rejected_units_query)->fetch_assoc()['count'];

if ($total_res > 0) {
    $stats['compliance_rate'] = round((($total_res - $rejected_res) / $total_res) * 100, 1);
}

// Fetch Stock by Blood Group (Replacing Regional Stock)
$stock_by_group = [];
$query = "SELECT blood_group, COUNT(*) as count FROM blood_units WHERE status = 'APPROVED' GROUP BY blood_group ORDER BY count DESC LIMIT 3";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $stock_by_group[] = $row;
    }
}

// Donation Trend (Last Month vs This Month)
$last_month_query = "SELECT COUNT(*) as count FROM blood_units WHERE MONTH(collection_date) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH)";
$last_month_count = $conn->query($last_month_query)->fetch_assoc()['count'];
$trend_percent = 0;
if ($last_month_count > 0) {
    $trend_percent = round((($stats['monthly_donations'] - $last_month_count) / $last_month_count) * 100, 1);
}
$trend_sign = ($trend_percent >= 0) ? '+' : '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - BBMS</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        blood: {
                            50: '#fef2f2',
                            100: '#fee2e2',
                            600: '#dc2626',
                            700: '#b91c1c',
                            800: '#991b1b',
                            900: '#7f1d1d',
                        },
                        medical: {
                            500: '#0ea5e9',
                            600: '#0284c7',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-sans text-gray-900">
    <?php render_sidebar('index.php'); ?>
    
    <div class="main-content ml-0 md:ml-72 min-h-screen p-6 pt-20 md:pt-8 transition-all duration-300">
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-blood-900 tracking-tight">Authority / Supervisory Dashboard</h1>
            <p class="text-gray-500 mt-2">Ministry of Health - National Oversight & Compliance</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-gray-400">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">National Blood Stock</p>
                        <h2 class="text-3xl font-extrabold text-gray-700"><?= number_format($stats['national_stock']) ?></h2>
                    </div>
                    <span class="text-3xl text-gray-400">📦</span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-600">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Staff / Banks</p>
                        <h2 class="text-3xl font-extrabold text-green-600"><?= number_format($stats['active_banks']) ?></h2>
                    </div>
                    <span class="text-3xl text-green-200">🏥</span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-medical-600">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Monthly Donations</p>
                        <h2 class="text-3xl font-extrabold text-medical-600"><?= number_format($stats['monthly_donations']) ?></h2>
                    </div>
                    <span class="text-3xl text-medical-200">🩸</span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-amber-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Purity Rate</p>
                        <h2 class="text-3xl font-extrabold text-amber-500"><?= $stats['compliance_rate'] ?>%</h2>
                    </div>
                    <span class="text-3xl text-amber-200">🛡️</span>
                </div>
            </div>
        </div>

        <!-- Advanced Feature -->
        <div class="mt-8 bg-gradient-to-br from-indigo-50 to-blue-50 rounded-xl p-6 border border-indigo-100 shadow-sm">
            <h3 class="flex items-center text-lg font-bold text-blood-900 mb-2">
                <span class="mr-2">🌟</span> Advanced Feature: Read-Only Access
            </h3>
            <p class="text-gray-600 text-sm">Authority/Supervisory users have READ-ONLY access to all national statistics, reports, and compliance data. No create, update, or delete permissions to ensure data integrity and audit trail.</p>
        </div>

        <!-- National Stock Breakdown -->
        <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center"><span class="mr-2">📊</span> National Stock by Blood Group (Top 3)</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php if (!empty($stock_by_group)): ?>
                    <?php foreach ($stock_by_group as $group): ?>
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-100 text-center">
                        <h3 class="font-bold text-gray-600 text-lg"><?= $group['blood_group'] ?></h3>
                        <p class="text-3xl font-extrabold text-blood-700 mt-2"><?= number_format($group['count']) ?></p>
                        <p class="text-xs text-gray-400 mt-1">units available</p>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-3 text-center text-gray-500">No blood units in stock.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Donation Trends -->
        <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">📈 National Donation Trends</h2>
            <div class="bg-gray-50 rounded-xl p-8 text-center border-2 border-dashed border-gray-200">
                <p class="text-sm text-gray-500 mb-4 font-medium uppercase tracking-wide">Monthly Donation Growth</p>
                <div class="flex items-center justify-center">
                    <span class="text-5xl font-extrabold <?= ($trend_percent >= 0) ? 'text-green-600' : 'text-red-500' ?>">
                        <?= $trend_sign . $trend_percent ?>%
                    </span>
                    <span class="ml-2 text-2xl">
                        <?= ($trend_percent >= 0) ? '↗️' : '↘️' ?>
                    </span>
                </div>
                <p class="text-sm text-gray-400 mt-4">Compared to last month</p>
            </div>
        </div>

        <!-- Compliance & Safety -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
            <div class="bg-green-50 rounded-xl shadow-sm border border-green-200 p-6">
                <h3 class="text-lg font-bold text-green-800 mb-3 flex items-center">
                    <span class="bg-green-200 p-1 rounded mr-2">✅</span> Safety Compliance
                </h3>
                <p class="text-green-800 text-sm leading-relaxed opacity-90">
                    All blood banks maintain a <?= $stats['compliance_rate'] ?>% safety purity rate. All units are tested for HIV, HBV, HCV, and Syphilis before approval.
                </p>
            </div>

            <div class="bg-blue-50 rounded-xl shadow-sm border border-blue-200 p-6">
                <h3 class="text-lg font-bold text-blue-800 mb-3 flex items-center">
                    <span class="bg-blue-200 p-1 rounded mr-2">📊</span> System Performance
                </h3>
                <p class="text-blue-800 text-sm leading-relaxed opacity-90">
                    Real-time monitoring of nationwide blood stocks ensures rapid response to emergency requests and minimizes wastage.
                </p>
            </div>
        </div>

        <!-- Quick Access Reports -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
            <a href="statistics.php" class="group block bg-white border border-gray-200 rounded-xl p-8 hover:shadow-lg hover:-translate-y-1 transition-all text-center">
                <span class="text-4xl mb-4 block group-hover:scale-110 transition-transform">📊</span>
                <h3 class="text-lg font-bold text-gray-800">National Statistics</h3>
            </a>
            <a href="safety-reports.php" class="group block bg-white border border-gray-200 rounded-xl p-8 hover:shadow-lg hover:-translate-y-1 transition-all text-center">
                <span class="text-4xl mb-4 block group-hover:scale-110 transition-transform">⚠️</span>
                <h3 class="text-lg font-bold text-gray-800">Safety Reports</h3>
            </a>
            <a href="compliance.php" class="group block bg-white border border-gray-200 rounded-xl p-8 hover:shadow-lg hover:-translate-y-1 transition-all text-center">
                <span class="text-4xl mb-4 block group-hover:scale-110 transition-transform">✅</span>
                <h3 class="text-lg font-bold text-gray-800">Compliance Reports</h3>
            </a>
        </div>
    </div>

    <script src="../../assets/js/sidebar.js"></script>
</body>
</html>
