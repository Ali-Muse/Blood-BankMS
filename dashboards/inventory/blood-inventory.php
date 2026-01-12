<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';

require_role(['Inventory Manager', 'System Administrator']);

// Get consolidated stock data
$stock_data = [];
$blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
$total_units = 0;

foreach ($blood_groups as $group) {
    // Approved & Not Expired
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM blood_units WHERE blood_group = ? AND status = 'APPROVED' AND expiry_date > CURDATE()");
    $stmt->bind_param("s", $group);
    $stmt->execute();
    $approved = $stmt->get_result()->fetch_assoc()['count'];
    
    // Low Stock Threshold
    $status = $approved < 10 ? 'CRITICAL' : ($approved < 20 ? 'LOW' : 'GOOD');
    
    $stock_data[$group] = [
        'count' => $approved,
        'status' => $status
    ];
    $total_units += $approved;
}

// Get expiring soon count (next 7 days)
$expiring_soon = $conn->query("SELECT COUNT(*) as count FROM blood_units WHERE status = 'APPROVED' AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)")->fetch_assoc()['count'];

// Get recent movements (audits related to inventory)
$recent_movements = $conn->query("
    SELECT al.*, u.full_name 
    FROM audit_logs al 
    JOIN users u ON al.user_id = u.user_id 
    WHERE al.action LIKE '%blood unit%' OR al.action LIKE '%request%'
    ORDER BY al.log_time DESC 
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Inventory - BBMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: { colors: { blood: { 600: '#dc2626', 700: '#b91c1c' } } }
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-sans text-gray-900">
    <?php render_sidebar('blood-inventory.php'); ?>
    
    <div class="main-content ml-0 md:ml-72 min-h-screen p-6 pt-20 md:pt-8 transition-all duration-300">
        <div class="mb-8 block">
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Blood Inventory Overview</h1>
            <p class="text-gray-500 mt-2">Real-time status of blood stock, movements, and alerts.</p>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between border-l-4 border-l-blood-600">
                 <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Total Available Units</p>
                    <h2 class="text-3xl font-black text-gray-900"><?= number_format($total_units) ?></h2>
                </div>
                <div class="h-12 w-12 bg-blood-50 text-blood-600 rounded-full flex items-center justify-center text-xl">
                    <i class="fa-solid fa-droplet"></i>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between border-l-4 border-l-yellow-500">
                 <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Expiring Soon (7 days)</p>
                    <h2 class="text-3xl font-black text-yellow-600"><?= number_format($expiring_soon) ?></h2>
                </div>
                <div class="h-12 w-12 bg-yellow-50 text-yellow-600 rounded-full flex items-center justify-center text-xl">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
            </div>

             <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between border-l-4 border-l-blue-500">
                 <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Pending Requests</p>
                    <?php 
                        $pending = $conn->query("SELECT COUNT(*) as count FROM blood_requests WHERE status = 'PENDING'")->fetch_assoc()['count']; 
                    ?>
                    <h2 class="text-3xl font-black text-blue-600"><?= number_format($pending) ?></h2>
                </div>
                <div class="h-12 w-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center text-xl">
                    <i class="fa-solid fa-clipboard-question"></i>
                </div>
            </div>
        </div>

        <!-- Inventory Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Stock Levels -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-900">Current Stock Levels</h2>
                    <a href="available-units.php" class="text-sm font-semibold text-blood-600 hover:text-blood-800">View Detailed List &rarr;</a>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <?php foreach ($stock_data as $group => $data): ?>
                            <?php 
                                $bg_class = $data['status'] === 'CRITICAL' ? 'bg-red-50 border-red-200' : ($data['status'] === 'LOW' ? 'bg-yellow-50 border-yellow-200' : 'bg-green-50 border-green-200');
                                $text_class = $data['status'] === 'CRITICAL' ? 'text-red-700' : ($data['status'] === 'LOW' ? 'text-yellow-700' : 'text-green-700');
                            ?>
                            <div class="p-4 rounded-xl border <?= $bg_class ?> flex flex-col items-center justify-center text-center transition-all hover:shadow-md">
                                <h3 class="text-lg font-bold text-gray-900 mb-1"><?= $group ?></h3>
                                <div class="text-3xl font-black <?= $text_class ?> mb-1"><?= $data['count'] ?></div>
                                <span class="text-xs font-bold px-2 py-0.5 rounded-full <?= $data['status'] === 'CRITICAL' ? 'bg-red-200 text-red-800' : ($data['status'] === 'LOW' ? 'bg-yellow-200 text-yellow-800' : 'bg-green-200 text-green-800') ?>">
                                    <?= $data['status'] ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Actions & Recent Activity -->
            <div class="space-y-6">
                <!-- Action Buttons -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-900 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <a href="review-requests.php" class="block w-full text-center py-2.5 bg-blood-600 hover:bg-blood-700 text-white font-bold rounded-lg transition-colors shadow-sm">
                            Review Requests
                        </a>
                        <a href="dispatch.php" class="block w-full text-center py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition-colors shadow-sm">
                            Dispatch Units
                        </a>
                        <a href="expired-units.php" class="block w-full text-center py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-bold rounded-lg transition-colors">
                            Manage Expired Units
                        </a>
                    </div>
                </div>

                <!-- Recent Movements -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-4 border-b border-gray-100 bg-gray-50">
                        <h3 class="font-bold text-gray-900 text-sm uppercase">Recent Movements</h3>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <?php if ($recent_movements->num_rows > 0): ?>
                            <?php while ($log = $recent_movements->fetch_assoc()): ?>
                                <div class="p-4 hover:bg-gray-50 transition-colors">
                                    <p class="text-sm font-medium text-gray-800"><?= $log['action'] ?></p>
                                    <div class="flex justify-between mt-1 text-xs text-gray-500">
                                        <span><?= explode(' ', $log['full_name'])[0] ?></span>
                                        <span><?= date('H:i', strtotime($log['log_time'])) ?></span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="p-6 text-center text-gray-500 text-sm">No recent activity</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/sidebar.js"></script>
</body>
</html>
