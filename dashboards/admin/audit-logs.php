<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';

require_role(['System Administrator']);

// Fetch audit logs
$logs = $conn->query("SELECT a.*, u.full_name, u.role_name FROM audit_logs a JOIN users u ON a.user_id = u.user_id ORDER BY a.log_time DESC LIMIT 100");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs - BBMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 font-sans text-gray-900">
    <?php render_sidebar('audit-logs.php'); ?>
    
    <div class="main-content ml-0 md:ml-72 min-h-screen p-6 pt-20 md:pt-8 transition-all duration-300">
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-2">
                <i class="fa-solid fa-shield-halved text-3xl text-blood-700"></i>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Audit Logs</h1>
            </div>
            <p class="text-gray-500">View detailed system logs for security and compliance tracking.</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="fa-solid fa-filter text-blood-600"></i> Filter Logs
                </h2>
                <button class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-semibold py-2 px-4 rounded-lg transition-colors flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-download"></i> Export to CSV
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">User Role</label>
                    <select class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2.5 border bg-white">
                        <option value="">All Roles</option>
                        <option value="System Administrator">System Administrator</option>
                        <option value="Registration Officer">Registration Officer</option>
                        <option value="Laboratory Technologist">Laboratory Technologist</option>
                        <option value="Inventory Manager">Inventory Manager</option>
                        <option value="Hospital User">Hospital User</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input type="date" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2.5 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input type="date" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2.5 border">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="text-xl font-bold text-gray-900">Recent Activity</h2>
            </div>
            <div class="overflow-x-auto max-h-[600px]">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 sticky top-0 z-10">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Time</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">User</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Role</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if ($logs && $logs->num_rows > 0): ?>
                            <?php while ($log = $logs->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= date('M d, Y H:i:s', strtotime($log['log_time'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($log['full_name']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                            <?= htmlspecialchars($log['role_name']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <?= htmlspecialchars($log['action']) ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fa-solid fa-clipboard-list text-4xl mb-3 text-gray-300"></i>
                                        <p>No audit logs found matching your criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-blue-50 rounded-xl border border-blue-100 p-6">
            <h3 class="font-bold text-lg mb-4 text-blue-900 flex items-center gap-2">
                <i class="fa-solid fa-chart-pie"></i> Audit Statistics
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-white/50 rounded-lg">
                    <p class="text-2xl font-extrabold text-blue-700">1,247</p>
                    <p class="text-xs font-medium text-blue-600 uppercase mt-1">Total Actions</p>
                </div>
                <div class="text-center p-4 bg-white/50 rounded-lg">
                    <p class="text-2xl font-extrabold text-blue-700">156</p>
                    <p class="text-xs font-medium text-blue-600 uppercase mt-1">Today</p>
                </div>
                <div class="text-center p-4 bg-white/50 rounded-lg">
                    <p class="text-2xl font-extrabold text-blue-700">847</p>
                    <p class="text-xs font-medium text-blue-600 uppercase mt-1">This Week</p>
                </div>
                <div class="text-center p-4 bg-white/50 rounded-lg">
                    <p class="text-2xl font-extrabold text-blue-700">81</p>
                    <p class="text-xs font-medium text-blue-600 uppercase mt-1">Active Users</p>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/sidebar.js"></script>
</body>
</html>
