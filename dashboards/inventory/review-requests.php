<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';
require_once '../../includes/notification_functions.php';

require_role(['Inventory Manager', 'System Administrator']);

$success = '';
$error = '';

// Handle request approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = intval($_POST['request_id']);
    $action = $_POST['action']; // 'approve' or 'reject'
    $review_notes = sanitize_input($_POST['review_notes'] ?? '');
    
    $user_id = get_user_id();
    $reviewed_at = date('Y-m-d H:i:s');
    
    if ($action === 'approve') {
        // Get request details
        $stmt = $conn->prepare("SELECT * FROM blood_requests WHERE request_id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $request = $stmt->get_result()->fetch_assoc();
        
        // Check if enough stock available
        $stmt = $conn->prepare("
            SELECT COUNT(*) as available 
            FROM blood_units 
            WHERE blood_group = ? AND status = 'APPROVED' AND expiry_date > CURDATE()
            ORDER BY collection_date ASC
        ");
        $stmt->bind_param("s", $request['blood_group']);
        $stmt->execute();
        $available = $stmt->get_result()->fetch_assoc()['available'];
        
        if ($available < $request['quantity']) {
            $error = "Insufficient stock! Only {$available} units available, but {$request['quantity']} requested.";
        } else {
            // Approve request
            $stmt = $conn->prepare("
                UPDATE blood_requests 
                SET status = 'APPROVED', reviewed_by = ?, reviewed_at = ?, review_notes = ?
                WHERE request_id = ?
            ");
            $stmt->bind_param("issi", $user_id, $reviewed_at, $review_notes, $request_id);
            $stmt->execute();
            
            // Allocate blood units using FIFO
            $stmt = $conn->prepare("
                SELECT unit_id 
                FROM blood_units 
                WHERE blood_group = ? AND status = 'APPROVED' AND expiry_date > CURDATE()
                ORDER BY collection_date ASC
                LIMIT ?
            ");
            $stmt->bind_param("si", $request['blood_group'], $request['quantity']);
            $stmt->execute();
            $units = $stmt->get_result();
            
            // Reserve units and create allocations
            while ($unit = $units->fetch_assoc()) {
                // Update unit status to RESERVED
                $stmt = $conn->prepare("UPDATE blood_units SET status = 'RESERVED' WHERE unit_id = ?");
                $stmt->bind_param("i", $unit['unit_id']);
                $stmt->execute();
                
                // Create allocation record
                $stmt = $conn->prepare("INSERT INTO request_allocations (request_id, unit_id, allocated_by) VALUES (?, ?, ?)");
                $stmt->bind_param("iii", $request_id, $unit['unit_id'], $user_id);
                $stmt->execute();
            }
            
            // Log audit
            log_audit($user_id, "Approved blood request #{$request['request_number']}");
            
            // Notify hospital
            notify_request_status($request_id, 'APPROVED', $review_notes);
            
            $success = "Request approved! {$request['quantity']} units of {$request['blood_group']} reserved for {$request['hospital_name']}.";
        }
    } elseif ($action === 'reject') {
        // Reject request
        $stmt = $conn->prepare("
            UPDATE blood_requests 
            SET status = 'REJECTED', reviewed_by = ?, reviewed_at = ?, review_notes = ?
            WHERE request_id = ?
        ");
        $stmt->bind_param("issi", $user_id, $reviewed_at, $review_notes, $request_id);
        $stmt->execute();
        
        // Get request details for logging
        $stmt = $conn->prepare("SELECT request_number FROM blood_requests WHERE request_id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $request_number = $stmt->get_result()->fetch_assoc()['request_number'];
        
        // Log audit
        log_audit($user_id, "Rejected blood request #{$request_number}");
        
        // Notify hospital
        notify_request_status($request_id, 'REJECTED', $review_notes);
        
        $success = "Request rejected. Hospital has been notified.";
    }
}

// Get pending requests (emergency first)
$pending_requests = $conn->query("
    SELECT br.*, u.full_name as requested_by_name
    FROM blood_requests br
    JOIN users u ON br.hospital_user_id = u.user_id
    WHERE br.status = 'PENDING'
    ORDER BY br.priority_score DESC, br.created_at ASC
");

// Get stock availability
$stock_availability = [];
$blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
foreach ($blood_groups as $group) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM blood_units 
        WHERE blood_group = ? AND status = 'APPROVED' AND expiry_date > CURDATE()
    ");
    $stmt->bind_param("s", $group);
    $stmt->execute();
    $stock_availability[$group] = $stmt->get_result()->fetch_assoc()['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Requests - BBMS</title>
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
    <?php render_sidebar('review-requests.php'); ?>
    
    <div class="main-content ml-0 md:ml-72 min-h-screen p-6 pt-20 md:pt-8 transition-all duration-300">
        <div class="mb-8 block">
            <div class="flex items-center gap-3 mb-2">
                <i class="fa-solid fa-clipboard-check text-3xl text-blood-700"></i>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Review Blood Requests</h1>
            </div>
            <p class="text-gray-500">Approve or reject pending blood requests from hospitals.</p>
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

        <!-- Stock Overview -->
        <div class="bg-gradient-to-br from-blood-50 to-white rounded-xl shadow-sm border border-blood-100 p-6 mb-8">
            <h3 class="font-bold text-blood-700 mb-4 flex items-center gap-2"><i class="fa-solid fa-cubes-stacked"></i> Current Stock Availability</h3>
            <div class="grid grid-cols-4 md:grid-cols-8 gap-4">
                <?php foreach ($blood_groups as $group): ?>
                    <div class="text-center p-3 bg-white rounded-lg border-2 <?= $stock_availability[$group] < 5 ? 'border-red-500 bg-red-50' : 'border-gray-200' ?> shadow-sm">
                        <div class="font-bold text-gray-700 mb-1"><?= $group ?></div>
                        <div class="text-xl font-black <?= $stock_availability[$group] < 5 ? 'text-red-600' : 'text-green-600' ?>">
                            <?= $stock_availability[$group] ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Pending Requests -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                 <h2 class="text-xl font-bold text-gray-900">Pending Requests</h2>
                 <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-3 py-1 rounded-full"><?= $pending_requests->num_rows ?> Pending</span>
            </div>
            
            <div class="p-6 space-y-6">
                <?php if ($pending_requests->num_rows > 0): ?>
                    <?php while ($request = $pending_requests->fetch_assoc()): ?>
                        <div class="bg-white rounded-xl border <?= $request['request_type'] === 'EMERGENCY' ? 'border-red-200 shadow-red-50' : 'border-gray-200' ?> shadow-sm overflow-hidden relative">
                             <?php if ($request['request_type'] === 'EMERGENCY'): ?>
                                <div class="absolute top-0 right-0 bg-red-600 text-white text-xs font-bold px-3 py-1 rounded-bl-lg">
                                    <i class="fa-solid fa-bolt mr-1"></i> EMERGENCY
                                </div>
                            <?php endif; ?>

                            <div class="p-6">
                                <div class="flex flex-col md:flex-row justify-between items-start gap-4 mb-6">
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900"><?= $request['hospital_name'] ?></h3>
                                        <p class="text-sm text-gray-500 mt-1">
                                            Request #<?= $request['request_number'] ?> • <i class="fa-regular fa-clock"></i> <?= date('Y-m-d H:i', strtotime($request['created_at'])) ?>
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-4 bg-gray-50 px-4 py-2 rounded-lg border border-gray-100">
                                        <div class="text-center">
                                            <p class="text-xs text-gray-500 font-bold uppercase">Group</p>
                                            <p class="text-2xl font-black text-blood-700"><?= $request['blood_group'] ?></p>
                                        </div>
                                        <div class="h-8 w-px bg-gray-300"></div>
                                        <div class="text-center">
                                            <p class="text-xs text-gray-500 font-bold uppercase">Units</p>
                                            <p class="text-2xl font-black text-gray-900"><?= $request['quantity'] ?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                                        <h4 class="text-sm font-bold text-gray-700 mb-2 border-b border-gray-200 pb-1">Patient Details</h4>
                                        <div class="grid grid-cols-2 gap-4 text-sm">
                                            <div>
                                                <p class="text-gray-500 text-xs">Patient Name</p>
                                                <p class="font-medium"><?= htmlspecialchars($request['patient_name'] ?? 'N/A') ?></p>
                                            </div>
                                            <div>
                                                <p class="text-gray-500 text-xs">Patient ID</p>
                                                <p class="font-medium"><?= htmlspecialchars($request['patient_id'] ?? 'N/A') ?></p>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                             <p class="text-gray-500 text-xs">Reason/Diagnosis</p>
                                             <p class="font-medium"><?= htmlspecialchars($request['reason']) ?></p>
                                        </div>
                                    </div>

                                    <div class="p-4 rounded-lg border <?= $stock_availability[$request['blood_group']] >= $request['quantity'] ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' ?>">
                                         <h4 class="text-sm font-bold <?= $stock_availability[$request['blood_group']] >= $request['quantity'] ? 'text-green-800' : 'text-red-800' ?> mb-2 border-b <?= $stock_availability[$request['blood_group']] >= $request['quantity'] ? 'border-green-200' : 'border-red-200' ?> pb-1">Stock Check</h4>
                                         <?php if ($stock_availability[$request['blood_group']] >= $request['quantity']): ?>
                                            <div class="flex items-center gap-3 text-green-700">
                                                <i class="fa-solid fa-circle-check text-2xl"></i>
                                                <div>
                                                    <p class="font-bold text-lg">Sufficient Stock</p>
                                                    <p class="text-xs"><?= $stock_availability[$request['blood_group']] ?> units available</p>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="flex items-center gap-3 text-red-700">
                                                <i class="fa-solid fa-circle-xmark text-2xl"></i>
                                                <div>
                                                    <p class="font-bold text-lg">Insufficient Stock</p>
                                                    <p class="text-xs">Only <?= $stock_availability[$request['blood_group']] ?> units available</p>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <form method="POST" action="" class="flex flex-col md:flex-row gap-4 items-end bg-gray-50 p-4 rounded-lg">
                                    <input type="hidden" name="request_id" value="<?= $request['request_id'] ?>">
                                    
                                    <div class="w-full">
                                        <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Review Notes</label>
                                        <input type="text" name="review_notes" class="w-full rounded-md border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2 text-sm" placeholder="Add optional notes for the hospital...">
                                    </div>
                                    
                                    <div class="flex gap-3 w-full md:w-auto shrink-0">
                                        <button type="submit" name="action" value="reject" class="flex-1 md:flex-none bg-white border border-red-300 text-red-700 hover:bg-red-50 font-bold py-2 px-4 rounded-lg transition-colors shadow-sm text-sm">
                                            <i class="fa-solid fa-times mr-1"></i> Reject
                                        </button>
                                        <button type="submit" name="action" value="approve" class="flex-1 md:flex-none bg-green-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-green-700 transition-colors shadow-md text-sm">
                                            <i class="fa-solid fa-check mr-1"></i> Approve
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-16">
                        <div class="h-20 w-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4 text-green-600">
                             <i class="fa-solid fa-check-double text-4xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">All Caught Up!</h3>
                        <p class="text-gray-500">There are no pending blood requests at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="../../assets/js/sidebar.js"></script>
</body>
</html>
