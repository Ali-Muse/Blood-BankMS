<?php
// Get Config & Auth
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';

require_role(['Hospital User']);

$user_id = get_user_id();

// Fetch Hospital Stats
$stats = [
    'pending' => 0,
    'approved' => 0,
    'emergency' => 0,
    'month' => 0
];

// Pending Requests
$query = "SELECT COUNT(*) as count FROM blood_requests WHERE hospital_user_id = ? AND status = 'PENDING'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats['pending'] = $stmt->get_result()->fetch_assoc()['count'];

// Approved Requests
$query = "SELECT COUNT(*) as count FROM blood_requests WHERE hospital_user_id = ? AND status = 'APPROVED'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats['approved'] = $stmt->get_result()->fetch_assoc()['count'];

// Emergency Requests (Total or Active? Doing Total active/pending emergency for now, or just total made? "Emergency Requests" usually implies history or active. Let's do Total Emergency Requests made by this hospital to match "This Month" vibe, or maybe just active. The UI had "3", likely active/recent. Let's do Total All Time for this user for now, or Pending?? The UI label "Emergency Request" is separate. The card says "Emergency Requests" -> 3. Let's do All Time Emergency Requests for stats.)
// Actually, usually dashboard stats are "Actionable" or "Recent". Let's stick to "Requests made this month" for the last card. 
// Let's do: 1. Pending (Actionable), 2. Approved (Actionable/Recent), 3. Emergency (Total All Time or Pending Emergency? Let's do Pending Emergency for urgency).
// Wait, the original had "Pending: 8", "Approved: 45", "Emergency: 3", "This Month: 127".
// "This Month" implies total requests this month.
// "Emergency" might be total emergency requests ever, or pending. Let's do Total Emergency Requests (all time) to show volume.

// Emergency Requests (All Time)
$query = "SELECT COUNT(*) as count FROM blood_requests WHERE hospital_user_id = ? AND request_type = 'EMERGENCY'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats['emergency'] = $stmt->get_result()->fetch_assoc()['count'];

// Requests This Month
$query = "SELECT COUNT(*) as count FROM blood_requests WHERE hospital_user_id = ? AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats['month'] = $stmt->get_result()->fetch_assoc()['count'];


// Fetch Blood Availability (National/Central Stock - as Hospital View)
// Assuming Hospital sees global stock or assigned stock? Usually global/central available stock to know what they can request.
$stock = [];
$query = "SELECT blood_group, COUNT(*) as count FROM blood_units WHERE status = 'APPROVED' GROUP BY blood_group";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $stock[$row['blood_group']] = $row['count'];
    }
}
$top_groups = ['O+', 'A+', 'B+', 'AB+']; // Just showing main ones for display
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital User Dashboard - BBMS</title>
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
            <h1 class="text-3xl font-extrabold text-blood-900 tracking-tight">Hospital User Dashboard</h1>
            <p class="text-gray-500 mt-2">Blood request and tracking system</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-gray-400">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Pending Requests</p>
                        <h2 class="text-3xl font-extrabold text-gray-700"><?= number_format($stats['pending']) ?></h2>
                    </div>
                    <span class="text-3xl text-gray-400"><i class="fas fa-clock"></i></span>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-600">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Approved Requests</p>
                        <h2 class="text-3xl font-extrabold text-green-600"><?= number_format($stats['approved']) ?></h2>
                    </div>
                    <span class="text-3xl text-green-200"><i class="fas fa-check-circle"></i></span>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blood-600">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Emergency Requests</p>
                        <h2 class="text-3xl font-extrabold text-blood-600"><?= number_format($stats['emergency']) ?></h2>
                    </div>
                    <span class="text-3xl text-blood-200"><i class="fas fa-ambulance"></i></span>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-medical-600">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">This Month</p>
                        <h2 class="text-3xl font-extrabold text-medical-600"><?= number_format($stats['month']) ?></h2>
                    </div>
                    <span class="text-3xl text-medical-200"><i class="fas fa-calendar-alt"></i></span>
                </div>
            </div>
        </div>

        <!-- Advanced Feature -->
        <div class="mt-8 bg-gradient-to-br from-red-50 to-amber-50 rounded-xl p-6 border border-amber-100 shadow-sm">
            <h3 class="flex items-center text-lg font-bold text-blood-900 mb-2">
                <span class="mr-2"><i class="fas fa-star text-amber-500"></i></span> Advanced Feature: Emergency Request Priority
            </h3>
            <p class="text-gray-600 text-sm">Emergency blood requests are automatically prioritized and processed first. They bypass normal queue and trigger immediate notifications to inventory managers.</p>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
            <a href="request-blood.php" class="group block bg-blood-600 rounded-xl p-8 text-white shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all text-center">
                <span class="text-5xl mb-4 block group-hover:scale-110 transition-transform duration-300"><i class="fas fa-plus-circle"></i></span>
                <h3 class="text-xl font-bold">Request Blood</h3>
            </a>
            <a href="emergency-requests.php" class="group block bg-red-700 rounded-xl p-8 text-white shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all text-center relative overflow-hidden">
                <div class="absolute inset-0 bg-red-600 animate-pulse opacity-20"></div>
                <span class="text-5xl mb-4 block relative z-10 group-hover:scale-110 transition-transform duration-300"><i class="fas fa-ambulance"></i></span>
                <h3 class="text-xl font-bold relative z-10">Emergency Request</h3>
            </a>
        </div>

        <!-- Blood Availability -->
        <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Current Blood Availability</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <!-- Example: O+ -->
                <div class="p-4 bg-green-50 rounded-xl text-center border border-green-100">
                    <h3 class="text-3xl font-extrabold text-green-800"><?= $stock['O+'] ?? 0 ?></h3>
                    <p class="text-green-700 font-medium text-sm mt-1">O+ Units</p>
                    <span class="inline-block mt-2 px-2 py-0.5 bg-green-600 text-white text-[10px] font-bold rounded-full">Available</span>
                </div>
                <!-- Example: A+ -->
                <div class="p-4 bg-green-50 rounded-xl text-center border border-green-100">
                    <h3 class="text-3xl font-extrabold text-green-800"><?= $stock['A+'] ?? 0 ?></h3>
                    <p class="text-green-700 font-medium text-sm mt-1">A+ Units</p>
                    <span class="inline-block mt-2 px-2 py-0.5 bg-green-600 text-white text-[10px] font-bold rounded-full">Available</span>
                </div>
                <!-- Example: AB- (Simulation of low) -->
                <div class="p-4 bg-amber-50 rounded-xl text-center border border-amber-100">
                    <h3 class="text-3xl font-extrabold text-amber-800"><?= $stock['AB-'] ?? 0 ?></h3>
                    <p class="text-amber-800 font-medium text-sm mt-1">AB- Units</p>
                    <span class="inline-block mt-2 px-2 py-0.5 bg-amber-500 text-white text-[10px] font-bold rounded-full">Limited</span>
                </div>
                <!-- Example: O- -->
                <div class="p-4 bg-red-50 rounded-xl text-center border border-red-100">
                    <h3 class="text-3xl font-extrabold text-red-800"><?= $stock['O-'] ?? 0 ?></h3>
                    <p class="text-red-800 font-medium text-sm mt-1">O- Units</p>
                    <span class="inline-block mt-2 px-2 py-0.5 bg-red-600 text-white text-[10px] font-bold rounded-full">Critical</span>
                </div>
            </div>
             <div class="mt-4 text-center">
                 <p class="text-sm text-gray-500">Includes stock from all regional blood banks.</p>
                 <a href="availability.php" class="text-sm font-medium text-blood-600 hover:text-blood-800 transition-colors mt-2 inline-block">View detailed availability →</a>
            </div>
        </div>
    </div>

    <script src="../../assets/js/sidebar.js"></script>
</body>
</html>
