<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get Config & Auth
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';

require_role(['System Administrator']);

$page_title = 'Admin Dashboard';
$user_name = get_user_name();

// Fetch Admin Stats with error handling
$stats = [
    'donors' => 0,
    'units_collected' => 0,
    'hospitals' => 0,
    'stock' => 0,
    'total_users' => 0,
    'blood_banks' => 0,
    'emergency_pending' => 0,
    'regions' => 8
];

try {
    // 1. Total Donors
    $query = "SELECT COUNT(*) as count FROM donors";
    $result = $conn->query($query);
    if ($result) $stats['donors'] = $result->fetch_assoc()['count'];
    
    // 2. Blood Units Collected (This Month)
    $query = "SELECT COUNT(*) as count FROM blood_units WHERE MONTH(collection_date) = MONTH(CURRENT_DATE()) AND YEAR(collection_date) = YEAR(CURRENT_DATE())";
    $result = $conn->query($query);
    if ($result) $stats['units_collected'] = $result->fetch_assoc()['count'];
    
    // 3. Partner Hospitals
    $query = "SELECT COUNT(*) as count FROM users WHERE role_name = 'Hospital User'";
    $result = $conn->query($query);
    if ($result) $stats['hospitals'] = $result->fetch_assoc()['count'];
    
    // 4. Blood Stock Levels (Total Approved Units)
    $query = "SELECT COUNT(*) as count FROM blood_units WHERE status = 'APPROVED'";
    $result = $conn->query($query);
    if ($result) $stats['stock'] = $result->fetch_assoc()['count'];
    
    // 5. Total Users
    $query = "SELECT COUNT(*) as count FROM users";
    $result = $conn->query($query);
    if ($result) $stats['total_users'] = $result->fetch_assoc()['count'];
    
    // 6. Blood Banks (Staff count)
    $query = "SELECT COUNT(*) as count FROM users WHERE role_name IN ('Inventory Manager', 'Laboratory Technologist')";
    $result = $conn->query($query);
    if ($result) $stats['blood_banks'] = $result->fetch_assoc()['count'];
    
    // 7. Emergency Requests Pending
    $query = "SELECT COUNT(*) as count FROM blood_requests WHERE request_type = 'EMERGENCY' AND status = 'PENDING'";
    $result = $conn->query($query);
    if ($result) $stats['emergency_pending'] = $result->fetch_assoc()['count'];
    
    // Recent Activity (Audit Logs)
    $activities = [];
    $query = "SELECT a.action, u.full_name, a.created_at FROM audit_logs a LEFT JOIN users u ON a.user_id = u.user_id ORDER BY a.created_at DESC LIMIT 3";
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
    }
} catch (Exception $e) {
    // Log error but don't break the page
    error_log("Admin Dashboard Error: " . $e->getMessage());
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
            <h1 class="text-3xl font-extrabold text-blood-900 tracking-tight">System Administrator Dashboard</h1>
            <p class="text-gray-500 mt-2">Welcome back, <?= htmlspecialchars($user_name) ?>! Here's your system overview.</p>
        </div>

        <!-- Stats Grid - Quick Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-gray-400">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Total Donors</p>
                        <h2 class="text-3xl font-extrabold text-gray-700"><?= number_format($stats['donors']) ?></h2>
                    </div>
                    <span class="text-3xl text-gray-400">👥</span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-medical-600">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Units Collected (Mo.)</p>
                        <h2 class="text-3xl font-extrabold text-medical-600"><?= number_format($stats['units_collected']) ?></h2>
                    </div>
                    <span class="text-3xl text-medical-200">🩸</span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-600">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Partner Hospitals</p>
                        <h2 class="text-3xl font-extrabold text-green-600"><?= number_format($stats['hospitals']) ?></h2>
                    </div>
                    <span class="text-3xl text-green-200">🏨</span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-amber-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Blood Stock Levels</p>
                        <h2 class="text-3xl font-extrabold text-amber-500"><?= number_format($stats['stock']) ?></h2>
                        <p class="text-xs text-gray-400 mt-1">Available units</p>
                    </div>
                    <span class="text-3xl text-amber-200">📦</span>
                </div>
            </div>
        </div>

        <!-- Secondary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-6">
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blood-700">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Total Users</p>
                        <h2 class="text-3xl font-extrabold text-blood-700"><?= number_format($stats['total_users']) ?></h2>
                    </div>
                    <span class="text-3xl text-blood-200">👤</span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-medical-600">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Staff / Branches</p>
                        <h2 class="text-3xl font-extrabold text-medical-600"><?= number_format($stats['blood_banks']) ?></h2>
                    </div>
                    <span class="text-3xl text-medical-200">🏥</span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-red-600">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Emergency Pending</p>
                        <h2 class="text-3xl font-extrabold text-red-600"><?= number_format($stats['emergency_pending']) ?></h2>
                    </div>
                    <span class="text-3xl text-red-200 animate-pulse">🚨</span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-600">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Active Regions</p>
                        <h2 class="text-3xl font-extrabold text-green-600"><?= $stats['regions'] ?></h2>
                    </div>
                    <span class="text-3xl text-green-200">🗺️</span>
                </div>
            </div>
        </div>

        <!-- Advanced Features -->
        <div class="mt-8 bg-gradient-to-br from-red-50 to-amber-50 rounded-xl p-6 border border-amber-100 shadow-sm">
            <h3 class="flex items-center text-lg font-bold text-blood-900 mb-2">
                <span class="mr-2">🌟</span> Advanced Feature: Full System Access
            </h3>
            <p class="text-gray-600 text-sm">As System Administrator, you have access to all regions, users, and system-wide reports. You can manage roles, permissions, and monitor all activities across the platform.</p>
        </div>

        <!-- Recent Activity -->
        <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Recent System Activity</h2>
            <div class="divide-y divide-gray-100">
                <?php if (!empty($activities)): ?>
                    <?php foreach ($activities as $act): ?>
                    <div class="py-4 flex justify-between items-center hover:bg-gray-50 transition-colors rounded px-2">
                        <div>
                            <p class="font-medium text-gray-800"><?= htmlspecialchars($act['action']) ?></p>
                            <p class="text-sm text-gray-500">by <?= htmlspecialchars($act['full_name']) ?></p>
                        </div>
                        <span class="text-xs text-gray-400"><?= date('M d, H:i', strtotime($act['created_at'])) ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="py-4 text-center text-gray-500">No recent activity logged.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
            <a href="create-user.php" class="group block bg-white border border-gray-200 rounded-xl p-8 hover:shadow-lg hover:-translate-y-1 transition-all text-center">
                <span class="text-4xl mb-4 block group-hover:scale-110 transition-transform">➕</span>
                <h3 class="text-lg font-bold text-gray-800">Create New User</h3>
            </a>
            <a href="reports-stock.php" class="group block bg-white border border-gray-200 rounded-xl p-8 hover:shadow-lg hover:-translate-y-1 transition-all text-center">
                <span class="text-4xl mb-4 block group-hover:scale-110 transition-transform">📊</span>
                <h3 class="text-lg font-bold text-gray-800">View Reports</h3>
            </a>
            <a href="audit-logs.php" class="group block bg-white border border-gray-200 rounded-xl p-8 hover:shadow-lg hover:-translate-y-1 transition-all text-center">
                <span class="text-4xl mb-4 block group-hover:scale-110 transition-transform">📋</span>
                <h3 class="text-lg font-bold text-gray-800">Audit Logs</h3>
            </a>
        </div>
    </div>

    <script src="../../assets/js/sidebar.js"></script>
</body>
</html>
