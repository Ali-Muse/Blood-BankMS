<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';

require_role(['Hospital User']);

$user_id = get_user_id();

// Get request history
$stmt = $conn->prepare("
    SELECT br.*, 
           (SELECT COUNT(*) FROM request_allocations WHERE request_id = br.request_id) as allocated_units,
           (SELECT COUNT(*) FROM blood_units bu 
            JOIN request_allocations ra ON bu.unit_id = ra.unit_id 
            WHERE ra.request_id = br.request_id AND bu.status IN ('TRANSFUSED', 'RETURNED', 'DISCARDED')) as reported_units
    FROM blood_requests br 
    WHERE br.hospital_user_id = ? 
    ORDER BY br.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$requests = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request History - BBMS</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php render_sidebar('history.php'); ?>
    
    <div class="main-content ml-0 md:ml-72 min-h-screen p-6 pt-20 md:pt-8 transition-all duration-300 bg-gray-50 text-gray-800">
        <div class="page-header mb-8">
            <h1 class="text-3xl font-extrabold text-blood-900 tracking-tight">📜 Request History</h1>
            <p class="text-gray-500 mt-2">View past blood requests and report usage</p>
        </div>

        <?php if ($requests->num_rows > 0): ?>
        <div class="card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="table-container overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-xs uppercase text-gray-500 font-semibold tracking-wider">
                            <th class="px-6 py-4 border-r border-gray-200 hover:bg-gray-100 transition-colors">Request #</th>
                            <th class="px-6 py-4 border-r border-gray-200 hover:bg-gray-100 transition-colors">Date</th>
                            <th class="px-6 py-4 border-r border-gray-200 hover:bg-gray-100 transition-colors">Group</th>
                            <th class="px-6 py-4 border-r border-gray-200 hover:bg-gray-100 transition-colors">Units</th>
                            <th class="px-6 py-4 border-r border-gray-200 hover:bg-gray-100 transition-colors">Urgency</th>
                            <th class="px-6 py-4 border-r border-gray-200 hover:bg-gray-100 transition-colors">Status</th>
                            <th class="px-6 py-4 hover:bg-gray-100 transition-colors">Usage Report</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php while ($req = $requests->fetch_assoc()): ?>
                            <tr class="hover:bg-blue-50/30 transition-colors group">
                                <td class="px-6 py-4 border-r border-gray-100 font-mono font-semibold text-gray-700">
                                    <?= $req['request_number'] ?>
                                </td>
                                <td class="px-6 py-4 border-r border-gray-100">
                                    <div class="font-medium text-gray-900"><?= date('M d, Y', strtotime($req['created_at'])) ?></div>
                                    <div class="text-xs text-gray-400"><?= date('h:i A', strtotime($req['created_at'])) ?></div>
                                </td>
                                <td class="px-6 py-4 border-r border-gray-100">
                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-red-50 text-red-700 font-bold border-2 border-white shadow-sm ring-1 ring-red-100">
                                        <?= $req['blood_group'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 border-r border-gray-100">
                                    <div class="font-medium text-gray-900">
                                        <?= $req['quantity'] ?> units
                                    </div>
                                    <div class="text-xs text-gray-500 mt-0.5">requested</div>
                                    <?php if ($req['allocated_units'] > 0): ?>
                                        <div class="text-xs text-green-600 font-medium mt-1">
                                            <?= $req['allocated_units'] ?> received
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 border-r border-gray-100">
                                    <?php
                                    $urgency_class = $req['request_type'] === 'EMERGENCY' 
                                        ? 'bg-red-50 text-red-600 border-red-200 animate-pulse font-semibold' 
                                        : 'bg-gray-50 text-gray-600 border-gray-200';
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border <?= $urgency_class ?>">
                                        <?= ucfirst(strtolower($req['request_type'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 border-r border-gray-100">
                                    <?php
                                    $status_map = [
                                        'PENDING' => 'bg-orange-50 text-orange-600 border-orange-200',
                                        'APPROVED' => 'bg-green-50 text-green-600 border-green-200',
                                        'REJECTED' => 'bg-red-50 text-red-600 border-red-200',
                                        'DISPATCHED' => 'bg-blue-50 text-blue-600 border-blue-200',
                                        'COMPLETED' => 'bg-green-50 text-green-600 border-green-200',
                                    ];
                                    $status_class = $status_map[$req['status']] ?? 'bg-gray-100 text-gray-500 border-gray-200';
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border <?= $status_class ?>">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current mr-1.5 opacity-60"></span>
                                        <?= str_replace('_', ' ', $req['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($req['status'] === 'DISPATCHED' || $req['status'] === 'COMPLETED' || $req['status'] === 'DELIVERED'): ?>
                                        <?php 
                                        $pending_report = $req['allocated_units'] - $req['reported_units'];
                                        ?>
                                        <?php if ($pending_report > 0): ?>
                                            <a href="report-usage.php?id=<?= $req['request_id'] ?>" class="inline-flex items-center px-3 py-1.5 bg-blood-600 text-white text-xs font-medium rounded-lg hover:bg-blood-700 transition-colors shadow-sm hover:shadow hover:-translate-y-px">
                                                <span class="mr-1.5 text-sm">📝</span> Report (<?= $pending_report ?>)
                                            </a>
                                        <?php else: ?>
                                            <div class="flex items-center text-green-600 text-sm font-medium">
                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                All Reported
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-gray-300 text-xl font-light">&bull;</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else: ?>
        <div class="card bg-white rounded-xl shadow-sm border border-gray-200 p-16 text-center">
            <div class="empty-state">
                <span class="empty-state-icon block text-6xl opacity-30 mb-4">📋</span>
                <p class="empty-state-text text-lg font-medium text-gray-900">No request history found</p>
                <p class="text-gray-500 mt-2 text-sm">
                    Any blood requests you make to the blood bank will appear here.
                </p>
                <a href="request-blood.php" class="inline-block mt-8 px-6 py-3 bg-blood-600 text-white font-medium rounded-lg hover:bg-blood-700 transition-all shadow hover:shadow-lg hover:-translate-y-0.5">
                    + Make New Request
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="../../assets/js/sidebar.js"></script>
</body>
</html>

