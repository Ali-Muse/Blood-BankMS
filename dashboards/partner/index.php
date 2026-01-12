<?php
// Get Config & Auth
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';

require_role(['Red Cross']);

$page_title = 'Partner Organization Dashboard';

// Fetch Stats
$stats = [
    'shortages' => 0,
    'active_campaigns' => 0,
    'donors_mobilized' => 0,
    'emergency_responses' => 0
];

// 1. Calculate Shortages (Blood Groups with < 5 units approved)
// We need to query count per blood group first
$stock_levels = [];
$query = "SELECT blood_group, COUNT(*) as count FROM blood_units WHERE status = 'APPROVED' GROUP BY blood_group";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $stock_levels[$row['blood_group']] = $row['count'];
    }
}
// Check all standard types
$all_types = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
$critical_shortages = [];
foreach ($all_types as $type) {
    $count = $stock_levels[$type] ?? 0;
    if ($count < 5) { // Threshold for "Shortage"
        $stats['shortages']++;
        if ($count < 3) {
            $critical_shortages[] = ['type' => $type, 'count' => $count, 'severity' => 'Critical'];
        } else {
            $critical_shortages[] = ['type' => $type, 'count' => $count, 'severity' => 'Low'];
        }
    }
}

// 2. Active Campaigns
$query = "SELECT COUNT(*) as count FROM campaigns WHERE start_date <= CURDATE() AND end_date >= CURDATE()";
$result = $conn->query($query);
if ($result) $stats['active_campaigns'] = $result->fetch_assoc()['count'];

// 3. Donors Mobilized (Total donors or donors linked to recent campaigns? Let's use Total Donors as proxy for "Mobilized by Partners" context or count referrals if column exists. No referral column. Using Total Donors.)
$query = "SELECT COUNT(*) as count FROM donors";
$result = $conn->query($query);
if ($result) $stats['donors_mobilized'] = $result->fetch_assoc()['count'];

// 4. Emergency Responses (Emergency requests)
$query = "SELECT COUNT(*) as count FROM blood_requests WHERE request_type = 'EMERGENCY'";
$result = $conn->query($query);
if ($result) $stats['emergency_responses'] = $result->fetch_assoc()['count'];

// Fetch Active Campaigns List
$active_campaigns_list = [];
$query = "SELECT name, target_donors, start_date, end_date, location FROM campaigns WHERE end_date >= CURDATE() ORDER BY start_date ASC LIMIT 3";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $active_campaigns_list[] = $row;
    }
}
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
            <h1 class="text-3xl font-extrabold text-blood-900 tracking-tight">Partner Organization Dashboard</h1>
            <p class="text-gray-500 mt-2">Red Cross / NGO Support & Campaigns</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-amber-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Blood Shortages</p>
                        <h2 class="text-3xl font-extrabold text-amber-500"><?= $stats['shortages'] ?></h2>
                        <p class="text-xs text-amber-600 mt-1">Groups critical</p>
                    </div>
                    <span class="text-3xl text-amber-200">⚠️</span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-medical-600">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Active Campaigns</p>
                        <h2 class="text-3xl font-extrabold text-medical-600"><?= number_format($stats['active_campaigns']) ?></h2>
                    </div>
                    <span class="text-3xl text-medical-200">📢</span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-600">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Donors Mobilized</p>
                        <h2 class="text-3xl font-extrabold text-green-600"><?= number_format($stats['donors_mobilized']) ?></h2>
                    </div>
                    <span class="text-3xl text-green-200">👥</span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blood-600">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Emergency Responses</p>
                        <h2 class="text-3xl font-extrabold text-blood-600"><?= number_format($stats['emergency_responses']) ?></h2>
                    </div>
                    <span class="text-3xl text-blood-200">🚑</span>
                </div>
            </div>
        </div>

        <!-- Advanced Feature -->
        <div class="mt-8 bg-gradient-to-br from-red-50 to-blue-50 rounded-xl p-6 border border-blue-100 shadow-sm">
            <h3 class="flex items-center text-lg font-bold text-blood-900 mb-2">
                <span class="mr-2">🌟</span> Advanced Feature: Limited Data Access
            </h3>
            <p class="text-gray-600 text-sm">Partner organizations have access only to non-sensitive data. You can view shortage alerts, campaign statistics, and mobilization reports without accessing individual donor or patient information.</p>
        </div>

        <!-- Shortage Alerts -->
        <div class="mt-8 bg-white rounded-xl shadow-sm border-l-4 border-amber-500 p-6">
            <h2 class="text-xl font-bold text-amber-600 mb-6 flex items-center"><span class="mr-2">⚠️</span> Blood Shortage Alerts</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php if (!empty($critical_shortages)): ?>
                    <?php foreach ($critical_shortages as $shortage): ?>
                    <div class="bg-amber-50 p-4 rounded-lg border border-amber-100">
                        <div class="flex justify-between items-start">
                            <h3 class="font-bold text-amber-800 text-lg"><?= $shortage['type'] ?> Blood Type</h3>
                            <span class="bg-amber-200 text-amber-800 text-xs px-2 py-1 rounded-full font-bold"><?= strtoupper($shortage['severity']) ?></span>
                        </div>
                        <p class="text-sm text-amber-700 mt-2">Only <span class="font-bold"><?= $shortage['count'] ?> units</span> available.</p>
                        <p class="text-xs text-amber-600 mt-2 font-medium">Recommended: Organize donation drive</p>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-2 text-center text-gray-500 py-4 bg-green-50 rounded-lg border border-green-100">
                        <p class="text-green-700 font-medium">✅ No critical shortages detected. Stock levels are healthy.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Active Campaigns -->
        <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center"><span class="mr-2">📢</span> Active Donation Campaigns</h2>
            <div class="divide-y divide-gray-100">
                <?php if (!empty($active_campaigns_list)): ?>
                    <?php foreach ($active_campaigns_list as $campaign): ?>
                    <div class="py-4 hover:bg-gray-50 transition-colors rounded px-2">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                            <div class="mb-2 md:mb-0">
                                <h3 class="font-bold text-gray-800"><?= htmlspecialchars($campaign['name']) ?></h3>
                                <p class="text-sm text-gray-500 mt-1">
                                    <span class="mr-3">📅 End: <?= date('M d', strtotime($campaign['end_date'])) ?></span>
                                    <span>📍 <?= htmlspecialchars($campaign['location']) ?></span>
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold border border-green-200">ACTIVE</span>
                                <p class="text-xs text-gray-400 mt-1">Target: <?= $campaign['target_donors'] ?> donors</p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-gray-500 py-8">No active campaigns at the moment.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
            <a href="campaigns.php" class="group block bg-medical-600 rounded-xl p-8 text-white shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all text-center">
                <span class="text-5xl mb-4 block group-hover:scale-110 transition-transform duration-300">📢</span>
                <h3 class="text-xl font-bold">Manage Campaigns</h3>
            </a>
            <a href="mobilization.php" class="group block bg-green-600 rounded-xl p-8 text-white shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all text-center">
                <span class="text-5xl mb-4 block group-hover:scale-110 transition-transform duration-300">👥</span>
                <h3 class="text-xl font-bold">Donor Mobilization</h3>
            </a>
        </div>
    </div>

    <script src="../../assets/js/sidebar.js"></script>
</body>
</html>
