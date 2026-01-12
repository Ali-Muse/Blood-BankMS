<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';
require_once '../../includes/notification_functions.php';

require_role(['Inventory Manager', 'System Administrator']);

$success = '';
$error = '';

// Handle dispatch
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dispatch_request'])) {
    $request_id = intval($_POST['request_id']);
    $courier_name = sanitize_input($_POST['courier_name'] ?? '');
    $courier_phone = sanitize_input($_POST['courier_phone'] ?? '');
    $vehicle_number = sanitize_input($_POST['vehicle_number'] ?? '');
    
    $user_id = get_user_id();
    $dispatch_number = generate_dispatch_number();
    $dispatch_date = date('Y-m-d H:i:s');
    
    // Create dispatch record
    $stmt = $conn->prepare("
        INSERT INTO dispatch (dispatch_number, request_id, dispatch_date, dispatched_by, courier_name, courier_phone, vehicle_number, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'IN_TRANSIT')
    ");
    $stmt->bind_param("sisisss", $dispatch_number, $request_id, $dispatch_date, $user_id, $courier_name, $courier_phone, $vehicle_number);
    
    if ($stmt->execute()) {
        $dispatch_id = $stmt->insert_id;
        
        // Update request status
        $stmt = $conn->prepare("UPDATE blood_requests SET status = 'DISPATCHED' WHERE request_id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        
        // Update allocated blood units status
        $stmt = $conn->prepare("
            UPDATE blood_units bu
            JOIN request_allocations ra ON bu.unit_id = ra.unit_id
            SET bu.status = 'DISPATCHED'
            WHERE ra.request_id = ?
        ");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        
        // Get request details for notification
        $stmt = $conn->prepare("SELECT request_number, hospital_name FROM blood_requests WHERE request_id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $request = $stmt->get_result()->fetch_assoc();
        
        // Log audit
        log_audit($user_id, "Dispatched blood request #{$request['request_number']}");
        
        // Notify hospital
        notify_request_status($request_id, 'DISPATCHED');
        
        $success = "Dispatch successful! Dispatch Number: <strong>{$dispatch_number}</strong>. Hospital has been notified. <a href='dispatch-note.php?id={$dispatch_id}' target='_blank' class='underline font-bold hover:text-green-800'>Print Dispatch Note</a>";
    } else {
        $error = "Failed to create dispatch. Please try again.";
    }
}

// Get approved requests ready for dispatch
$approved_requests = $conn->query("
    SELECT br.*, u.full_name as requested_by_name,
           (SELECT COUNT(*) FROM request_allocations WHERE request_id = br.request_id) as allocated_units
    FROM blood_requests br
    JOIN users u ON br.hospital_user_id = u.user_id
    WHERE br.status = 'APPROVED'
    ORDER BY br.request_type DESC, br.created_at ASC
");

// Get dispatch history
$dispatch_history = $conn->query("
    SELECT d.*, br.request_number, br.hospital_name,
           (SELECT COUNT(*) FROM request_allocations WHERE request_id = br.request_id) as unit_count
    FROM dispatch d
    JOIN blood_requests br ON d.request_id = br.request_id
    ORDER BY d.dispatch_date DESC
    LIMIT 50
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dispatch Blood - BBMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 font-sans text-gray-900">
    <?php render_sidebar('dispatch.php'); ?>
    
    <div class="main-content ml-0 md:ml-72 min-h-screen p-6 pt-20 md:pt-8 transition-all duration-300">
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-2">
                <i class="fa-solid fa-truck-fast text-3xl text-blood-700"></i>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Blood Dispatch</h1>
            </div>
            <p class="text-gray-500">Coordinate logistical dispatch of approved blood units to hospitals.</p>
        </div>

        <?php if ($success): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-r mb-6 shadow-sm flex items-start gap-3">
                <i class="fa-solid fa-circle-check mt-1"></i>
                <div>
                    <p class="font-bold">Success</p>
                    <p><?= $success ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-r mb-6 shadow-sm flex items-start gap-3">
                <i class="fa-solid fa-circle-exclamation mt-1"></i>
                <div>
                    <p class="font-bold">Error</p>
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Tabs -->
        <div class="mb-6 border-b border-gray-200" x-data="{ activeTab: 'pending' }">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button 
                    onclick="switchTab('pending')" 
                    id="tab-pending"
                    class="tab-btn active border-blood-500 text-blood-600 whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm"
                >
                    Pending Dispatch
                </button>
                <button 
                    onclick="switchTab('history')"
                    id="tab-history"
                    class="tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                >
                    Dispatch History
                </button>
            </nav>
        </div>

        <div id="content-pending" class="tab-content block">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-900">Ready for Dispatch</h2>
                    <span class="bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1 rounded-full"><?= $approved_requests->num_rows ?> Requests</span>
                </div>
                
                <div class="divide-y divide-gray-100">
                    <?php if ($approved_requests->num_rows > 0): ?>
                        <?php while ($request = $approved_requests->fetch_assoc()): ?>
                            <div class="p-6">
                                <div class="flex flex-col md:flex-row justify-between items-start mb-6">
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900"><?= $request['hospital_name'] ?></h3>
                                        <p class="text-sm text-gray-500">
                                            Request #<?= $request['request_number'] ?> • Approved: <?= date('M d, H:i', strtotime($request['reviewed_at'])) ?>
                                        </p>
                                    </div>
                                    <div class="mt-2 md:mt-0 text-right">
                                        <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-blood-100 text-blood-800">
                                            <?= $request['blood_group'] ?> • <?= $request['allocated_units'] ?> units
                                        </div>
                                    </div>
                                </div>

                                <!-- Allocated Units List for this Request -->
                                <?php
                                $stmt = $conn->prepare("
                                    SELECT bu.barcode, bu.collection_date, bu.expiry_date
                                    FROM blood_units bu
                                    JOIN request_allocations ra ON bu.unit_id = ra.unit_id
                                    WHERE ra.request_id = ?
                                    ORDER BY bu.collection_date ASC
                                ");
                                $stmt->bind_param("i", $request['request_id']);
                                $stmt->execute();
                                $units = $stmt->get_result();
                                ?>
                                <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
                                    <h4 class="text-xs font-bold text-gray-500 uppercase mb-3">Allocated Units (Verified)</h4>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                                        <?php while ($unit = $units->fetch_assoc()): ?>
                                            <div class="bg-white p-2 rounded border border-gray-200 flex items-center gap-2 text-sm shadow-sm">
                                                <i class="fa-solid fa-barcode text-gray-400"></i>
                                                <span class="font-mono font-bold text-gray-700"><?= $unit['barcode'] ?></span>
                                                <span class="text-xs text-gray-400 ml-auto">Exp: <?= date('m/d', strtotime($unit['expiry_date'])) ?></span>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                </div>

                                <form method="POST" action="" class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <h4 class="text-xs font-bold text-gray-500 uppercase mb-3">Courier Details</h4>
                                    <input type="hidden" name="request_id" value="<?= $request['request_id'] ?>">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">Courier Name</label>
                                            <input type="text" name="courier_name" class="w-full rounded-md border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2 text-sm" placeholder="e.g. John Driver">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">Phone</label>
                                            <input type="tel" name="courier_phone" class="w-full rounded-md border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2 text-sm" placeholder="e.g. 078...">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">Vehicle No.</label>
                                            <input type="text" name="vehicle_number" class="w-full rounded-md border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2 text-sm" placeholder="e.g. RAB 123">
                                        </div>
                                    </div>
                                    <button type="submit" name="dispatch_request" class="w-full bg-blood-600 text-white font-bold py-2.5 rounded-lg hover:bg-blood-700 transition-colors shadow-md flex items-center justify-center gap-2">
                                        <i class="fa-solid fa-truck-fast"></i> Confirm Dispatch
                                    </button>
                                </form>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="p-12 text-center text-gray-500">
                            <i class="fa-solid fa-truck text-4xl mb-4 text-gray-300"></i>
                            <p>No stations waiting for dispatch.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div id="content-history" class="tab-content hidden">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Dispatch #</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Hospital</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Courier</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if ($dispatch_history->num_rows > 0): ?>
                                <?php while ($dh = $dispatch_history->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                            <?= $dh['dispatch_number'] ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= date('M d, Y H:i', strtotime($dh['dispatch_date'])) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($dh['hospital_name']) ?>
                                            <span class="text-xs text-gray-500 block"><?= $dh['unit_count'] ?> units</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= htmlspecialchars($dh['courier_name'] ?: 'N/A') ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-bold bg-blue-100 text-blue-800">
                                                IN TRANSIT
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                            <a href="dispatch-note.php?id=<?= $dh['dispatch_id'] ?>" target="_blank" class="text-blood-600 hover:text-blood-800 font-medium">Print Note</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">No dispatch history found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/sidebar.js"></script>
    <script>
        function switchTab(tabName) {
            // Hide all contents
            document.querySelectorAll('.tab-content').forEach(el => {
                el.classList.add('hidden');
            });
            // Show active content
            document.getElementById('content-' + tabName).classList.remove('hidden');
            
            // Reset all buttons
            document.querySelectorAll('.tab-btn').forEach(el => {
                el.classList.remove('active', 'border-blood-500', 'text-blood-600');
                el.classList.add('border-transparent', 'text-gray-500');
            });
            // Highlight active button
            const activeBtn = document.getElementById('tab-' + tabName);
            activeBtn.classList.add('active', 'border-blood-500', 'text-blood-600');
            activeBtn.classList.remove('border-transparent', 'text-gray-500');
        }
    </script>
</body>
</html>
