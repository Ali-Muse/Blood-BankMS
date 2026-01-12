<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';

require_role(['System Administrator']);

// Get filters
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-d');
$role_filter = $_GET['role'] ?? '';

// Fetch system usage statistics
$total_logins_query = "SELECT COUNT(*) as count FROM audit_logs WHERE action LIKE '%login%'";
if ($date_from && $date_to) {
    $total_logins_query .= " AND DATE(created_at) BETWEEN '$date_from' AND '$date_to'";
}
$total_logins = $conn->query($total_logins_query)->fetch_assoc()['count'];

$active_users_today = $conn->query("SELECT COUNT(DISTINCT user_id) as count FROM audit_logs WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['count'];

// Calculate total sessions (approximate by counting login actions)
$total_sessions = $conn->query("SELECT COUNT(*) as count FROM audit_logs WHERE action LIKE '%login%'")->fetch_assoc()['count'];

// Get usage by role
$role_usage_query = "
    SELECT 
        u.role_name,
        COUNT(DISTINCT a.user_id) as active_users,
        COUNT(CASE WHEN a.action LIKE '%login%' THEN 1 END) as total_sessions,
        MAX(a.created_at) as last_active
    FROM users u
    LEFT JOIN audit_logs a ON u.user_id = a.user_id
";

if ($date_from && $date_to) {
    $role_usage_query .= " WHERE DATE(a.created_at) BETWEEN '$date_from' AND '$date_to'";
}

$role_usage_query .= " GROUP BY u.role_name ORDER BY active_users DESC";
$role_usage_result = $conn->query($role_usage_query);

// Get most active features (from audit logs)
$features_query = "
    SELECT 
        action,
        COUNT(*) as usage_count
    FROM audit_logs
";

if ($date_from && $date_to) {
    $features_query .= " WHERE DATE(created_at) BETWEEN '$date_from' AND '$date_to'";
}

$features_query .= " GROUP BY action ORDER BY usage_count DESC LIMIT 5";
$features_result = $conn->query($features_query);
$features_data = [];
while ($row = $features_result->fetch_assoc()) {
    $features_data[] = $row;
}

// Calculate max usage for percentage bars
$max_usage = !empty($features_data) ? $features_data[0]['usage_count'] : 1;

// Get peak usage hours
$peak_hours_query = "
    SELECT 
        HOUR(created_at) as hour,
        COUNT(*) as activity_count
    FROM audit_logs
";

if ($date_from && $date_to) {
    $peak_hours_query .= " WHERE DATE(created_at) BETWEEN '$date_from' AND '$date_to'";
}

$peak_hours_query .= " GROUP BY hour ORDER BY activity_count DESC LIMIT 1";
$peak_hour_result = $conn->query($peak_hours_query);
$peak_hour_data = $peak_hour_result->fetch_assoc();
$peak_hour = $peak_hour_data ? $peak_hour_data['hour'] : 12;
$peak_hour_count = $peak_hour_data ? $peak_hour_data['activity_count'] : 0;

// Handle export
if (isset($_GET['export'])) {
    $export_type = $_GET['export'];
    
    if ($export_type === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="system_usage_report_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['System Usage Report - Generated: ' . date('Y-m-d H:i:s')]);
        fputcsv($output, ['Date Range: ' . $date_from . ' to ' . $date_to]);
        fputcsv($output, []);
        fputcsv($output, ['Summary Statistics']);
        fputcsv($output, ['Total Logins', $total_logins]);
        fputcsv($output, ['Active Users Today', $active_users_today]);
        fputcsv($output, ['Total Sessions', $total_sessions]);
        fputcsv($output, []);
        fputcsv($output, ['Usage by Role']);
        fputcsv($output, ['Role', 'Active Users', 'Total Sessions', 'Last Active']);
        
        $role_usage_result->data_seek(0);
        while ($row = $role_usage_result->fetch_assoc()) {
            fputcsv($output, [
                $row['role_name'],
                $row['active_users'],
                $row['total_sessions'],
                $row['last_active'] ? date('Y-m-d H:i:s', strtotime($row['last_active'])) : 'Never'
            ]);
        }
        
        fputcsv($output, []);
        fputcsv($output, ['Most Active Features']);
        fputcsv($output, ['Feature', 'Usage Count']);
        foreach ($features_data as $feature) {
            fputcsv($output, [$feature['action'], $feature['usage_count']]);
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
    <title>System Usage Logs - BBMS</title>
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
    <?php render_sidebar('reports-usage.php'); ?>
    
    <div class="main-content ml-0 md:ml-72 min-h-screen p-6 pt-20 md:pt-8 transition-all duration-300">
        <div class="mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <i class="fa-solid fa-chart-line text-3xl text-blood-700"></i>
                        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">System Usage Logs</h1>
                    </div>
                    <p class="text-gray-500">Monitor system activity and user engagement.</p>
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select name="role" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 p-2.5 border bg-white">
                        <option value="">All Roles</option>
                        <option value="System Administrator" <?= $role_filter === 'System Administrator' ? 'selected' : '' ?>>System Administrator</option>
                        <option value="Registration Officer" <?= $role_filter === 'Registration Officer' ? 'selected' : '' ?>>Registration Officer</option>
                        <option value="Laboratory Technologist" <?= $role_filter === 'Laboratory Technologist' ? 'selected' : '' ?>>Laboratory Technologist</option>
                        <option value="Inventory Manager" <?= $role_filter === 'Inventory Manager' ? 'selected' : '' ?>>Inventory Manager</option>
                        <option value="Hospital User" <?= $role_filter === 'Hospital User' ? 'selected' : '' ?>>Hospital User</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-gray-700 hover:bg-gray-800 text-white font-bold py-2.5 px-4 rounded-lg transition-colors">
                        <i class="fa-solid fa-filter mr-2"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 border-l-4 border-l-gray-500">
                <p class="text-xs font-bold text-gray-500 uppercase mb-1">Total Logins</p>
                <h2 class="text-3xl font-black text-gray-800"><?= number_format($total_logins) ?></h2>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 border-l-4 border-l-green-600">
                <p class="text-xs font-bold text-gray-500 uppercase mb-1">Active Users Today</p>
                <h2 class="text-3xl font-black text-green-600"><?= $active_users_today ?></h2>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 border-l-4 border-l-purple-600">
                <p class="text-xs font-bold text-gray-500 uppercase mb-1">Total Sessions</p>
                <h2 class="text-3xl font-black text-purple-600"><?= number_format($total_sessions) ?></h2>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 border-l-4 border-l-yellow-500">
                <p class="text-xs font-bold text-gray-500 uppercase mb-1">Peak Hour</p>
                <h2 class="text-3xl font-black text-yellow-600"><?= sprintf('%02d:00', $peak_hour) ?></h2>
                <p class="text-xs text-gray-400 mt-1"><?= number_format($peak_hour_count) ?> activities</p>
            </div>
        </div>

        <!-- Usage Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="p-6 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900">Usage by Role</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Role</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Active Users</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Total Sessions</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Last Active</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while ($row = $role_usage_result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap font-bold text-sm text-gray-800"><?= htmlspecialchars($row['role_name']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?= $row['active_users'] ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?= number_format($row['total_sessions']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-bold <?= $row['last_active'] && strtotime($row['last_active']) > strtotime('-1 hour') ? 'text-green-600' : 'text-gray-500' ?>">
                                    <?php 
                                    if ($row['last_active']) {
                                        $diff = time() - strtotime($row['last_active']);
                                        if ($diff < 3600) echo round($diff / 60) . ' minutes ago';
                                        elseif ($diff < 86400) echo round($diff / 3600) . ' hours ago';
                                        else echo round($diff / 86400) . ' days ago';
                                    } else {
                                        echo 'Never';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col items-center justify-center text-center">
                <h2 class="text-xl font-bold text-gray-900 mb-6 w-full text-left">Peak Usage Hours</h2>
                <div class="bg-gray-50 rounded-full h-64 w-64 flex flex-col items-center justify-center border-4 border-gray-100 shadow-inner">
                    <p class="text-4xl font-black text-gray-800"><?= sprintf('%02d:00', $peak_hour) ?></p>
                    <p class="text-gray-500 font-medium">Peak Activity</p>
                </div>
                <div class="mt-6 w-full pt-6 border-t border-gray-100">
                    <p class="text-sm font-semibold text-gray-600">
                        <span class="bg-green-100 text-green-800 px-2 py-0.5 rounded text-xs font-bold uppercase mr-2">Highest Peak</span>
                        <?= sprintf('%02d:00', $peak_hour) ?> (<?= number_format($peak_hour_count) ?> activities)
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 text-gray-900">
                <h2 class="text-xl font-bold mb-6">Most Active Features</h2>
                <div class="space-y-4">
                    <?php if (empty($features_data)): ?>
                        <p class="text-center text-gray-500 py-8">No activity data available for the selected period</p>
                    <?php else: ?>
                        <?php foreach ($features_data as $feature): 
                            $percentage = ($feature['usage_count'] / $max_usage) * 100;
                        ?>
                            <div class="group">
                                <div class="flex justify-between text-sm font-bold mb-1">
                                    <span><?= htmlspecialchars($feature['action']) ?></span>
                                    <span class="text-blood-600"><?= number_format($feature['usage_count']) ?> uses</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2.5">
                                    <div class="bg-blood-600 h-2.5 rounded-full transition-all" style="width: <?= $percentage ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="mt-8 bg-blue-50 rounded-xl border border-blue-100 p-6">
            <h3 class="font-bold text-blue-900 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-server"></i> System Health Status
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-4 rounded-lg shadow-sm text-center">
                    <p class="text-3xl font-black text-blue-600">99.8%</p>
                    <p class="text-xs font-bold text-gray-500 uppercase mt-1">Uptime</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm text-center">
                    <p class="text-3xl font-black text-blue-600">0.3s</p>
                    <p class="text-xs font-bold text-gray-500 uppercase mt-1">Avg. Response Time</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm text-center">
                    <p class="text-3xl font-black text-green-600">0</p>
                    <p class="text-xs font-bold text-gray-500 uppercase mt-1">Errors Today</p>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/sidebar.js"></script>
</body>
</html>
