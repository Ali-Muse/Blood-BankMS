<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';

require_role(['Hospital User']);

$page_title = 'Emergency Requests';
$user_id = get_user_id();

// Fetch Emergency Requests
$requests = [];
$query = "SELECT * FROM blood_requests WHERE hospital_user_id = ? AND request_type = 'EMERGENCY' ORDER BY created_at DESC";
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
    <?php render_sidebar('emergency-requests.php'); ?>
    
    <div class="main-content ml-0 md:ml-72 min-h-screen p-6 pt-20 md:pt-8 transition-all duration-300">
        <div class="flex justify-between items-end mb-8">
            <div>
                <h1 class="text-3xl font-extrabold text-red-700 tracking-tight flex items-center">
                   <i class="fas fa-ambulance mr-3 animate-pulse"></i> Emergency Requests
                </h1>
                <p class="text-gray-500 mt-2">Priority tracking for critical blood requirements.</p>
            </div>
            <a href="request-blood.php" class="bg-red-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-red-700 shadow-md text-sm hidden md:inline-block">
                <i class="fas fa-plus mr-1"></i> New Emergency Request
            </a>
        </div>

        <?php if (empty($requests)): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-3xl mx-auto mb-4">
                    <i class="fas fa-check"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">No Emergency Requests</h3>
                <p class="text-gray-500">There are no active emergency requests at this time.</p>
                <a href="request-blood.php" class="inline-block mt-4 text-blood-600 font-bold hover:underline">Create Request</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 gap-6">
                <?php foreach ($requests as $req): 
                    $status_colors = [
                        'PENDING' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                        'APPROVED' => 'bg-blue-100 text-blue-800 border-blue-200',
                        'DISPATCHED' => 'bg-purple-100 text-purple-800 border-purple-200',
                        'DELIVERED' => 'bg-green-100 text-green-800 border-green-200',
                        'REJECTED' => 'bg-red-100 text-red-800 border-red-200',
                        'CANCELLED' => 'bg-gray-100 text-gray-800 border-gray-200',
                    ];
                    $status_class = $status_colors[$req['status']] ?? 'bg-gray-100 text-gray-800';
                ?>
                <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-red-600 border border-gray-200 p-6 relative overflow-hidden">
                    <div class="absolute top-0 right-0 bg-red-100 text-red-800 text-xs font-bold px-3 py-1 rounded-bl-lg">CRITICAL</div>
                    
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-lg font-bold text-gray-800"><?= htmlspecialchars($req['blood_group']) ?> <span class="text-sm font-normal text-gray-500">x <?= $req['quantity'] ?> Units</span></h3>
                                <span class="px-2 py-0.5 rounded text-xs font-bold border <?= $status_class ?>"><?= $req['status'] ?></span>
                            </div>
                            <p class="text-sm text-gray-500">
                                <strong>Request:</strong> <?= htmlspecialchars($req['request_number']) ?>
                                <span class="mx-2">•</span>
                                <i class="far fa-clock"></i> <?= date('M d, Y H:i', strtotime($req['created_at'])) ?>
                            </p>
                            <?php if ($req['patient_name']): ?>
                            <p class="text-sm text-gray-600 mt-1"><i class="fas fa-user-injured mr-1"></i> Patient: <?= htmlspecialchars($req['patient_name']) ?></p>
                            <?php endif; ?>
                            <p class="text-sm text-red-700 mt-2 italic">"<?= htmlspecialchars($req['reason']) ?>"</p>
                        </div>
                        
                        <?php if ($req['status'] === 'DISPATCHED'): ?>
                        <div class="flex-shrink-0">
                             <button class="bg-green-600 text-white px-4 py-2 rounded shadow hover:bg-green-700 text-sm">
                                 <i class="fas fa-check mr-1"></i> Confirm Receipt
                             </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
    <script src="../../assets/js/sidebar.js"></script>
</body>
</html>
