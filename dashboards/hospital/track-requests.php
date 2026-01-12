<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';

require_role(['Hospital User']);

$page_title = 'Track Requests';
$user_id = get_user_id();

// Fetch All Requests
$requests = [];
$query = "SELECT * FROM blood_requests WHERE hospital_user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - BBMS</title>
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
    <?php render_sidebar('track-requests.php'); ?>
    
    <div class="main-content ml-0 md:ml-72 min-h-screen p-6 pt-20 md:pt-8 transition-all duration-300">
        <div class="mb-8">
             <h1 class="text-3xl font-extrabold text-blood-900 tracking-tight">Track Request Status</h1>
             <p class="text-gray-500 mt-2">Monitor the status of all your blood requests.</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-xs uppercase text-gray-500 font-bold tracking-wider">
                            <th class="p-4">Request Ref</th>
                            <th class="p-4">Date</th>
                            <th class="p-4">Details</th>
                            <th class="p-4">Patient</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        <?php if (empty($requests)): ?>
                            <tr>
                                <td colspan="6" class="p-8 text-center text-gray-500">No requests found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($requests as $req): 
                                $status_colors = [
                                    'PENDING' => 'bg-yellow-100 text-yellow-800',
                                    'APPROVED' => 'bg-blue-100 text-blue-800',
                                    'DISPATCHED' => 'bg-purple-100 text-purple-800',
                                    'DELIVERED' => 'bg-green-100 text-green-800',
                                    'REJECTED' => 'bg-red-100 text-red-800',
                                    'CANCELLED' => 'bg-gray-100 text-gray-800',
                                ];
                                $status_badge = $status_colors[$req['status']] ?? 'bg-gray-100';
                            ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="p-4 font-medium text-gray-900">
                                    <?= htmlspecialchars($req['request_number']) ?>
                                    <?php if ($req['request_type'] === 'EMERGENCY'): ?>
                                        <span class="block text-[10px] text-red-600 font-bold mt-1">EMERGENCY</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-gray-500">
                                    <?= date('M d, Y', strtotime($req['created_at'])) ?><br>
                                    <span class="text-xs"><?= date('H:i', strtotime($req['created_at'])) ?></span>
                                </td>
                                <td class="p-4">
                                    <span class="font-bold text-gray-800"><?= $req['blood_group'] ?></span>
                                    <span class="text-gray-500 text-xs ml-1">(<?= $req['quantity'] ?> units)</span>
                                </td>
                                <td class="p-4 text-gray-600">
                                    <?= $req['patient_name'] ? htmlspecialchars($req['patient_name']) : '<span class="italic text-gray-400">N/A</span>' ?>
                                </td>
                                <td class="p-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-bold <?= $status_badge ?>">
                                        <?= $req['status'] ?>
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <button class="text-gray-400 hover:text-blood-600 transition-colors" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="../../assets/js/sidebar.js"></script>
</body>
</html>
