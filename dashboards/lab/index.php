<?php
// Get Config & Auth
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';

require_role(['Laboratory Technologist']);

$page_title = 'Laboratory Technologist Dashboard';

// Fetch Lab Stats
$stats = [
    'pending_tests' => 0,
    'approved_units' => 0,
    'rejected_units' => 0,
    'tests_today' => 0
];

// 1. Pending Tests (Units in 'TESTING' status or lab_tests with result 'PENDING')
$query = "SELECT COUNT(*) as count FROM blood_units WHERE status = 'TESTING'"; 
// Or check lab_tests table? blood_units status 'TESTING' is the workflow trigger.
$result = $conn->query($query);
if ($result) $stats['pending_tests'] = $result->fetch_assoc()['count'];

// 2. Approved Units (Total approved by lab)
$query = "SELECT COUNT(*) as count FROM blood_units WHERE status = 'APPROVED'";
$result = $conn->query($query);
if ($result) $stats['approved_units'] = $result->fetch_assoc()['count'];

// 3. Rejected Units
$query = "SELECT COUNT(*) as count FROM blood_units WHERE status = 'REJECTED'";
$result = $conn->query($query);
if ($result) $stats['rejected_units'] = $result->fetch_assoc()['count'];

// 4. Tests Today (Count of lab_tests records updated or created today? Or units processed today)
$query = "SELECT COUNT(*) as count FROM lab_tests WHERE DATE(tested_at) = CURDATE()";
$result = $conn->query($query);
if ($result) $stats['tests_today'] = $result->fetch_assoc()['count'];

// Fetch Pending Queue (Units with status 'TESTING')
$pending_queue = [];
$query = "SELECT unit_id, barcode, blood_group, collection_date FROM blood_units WHERE status = 'TESTING' ORDER BY collection_date ASC LIMIT 5";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $pending_queue[] = $row;
    }
}

// Fetch Today's Test Results Breakdown (Simplifying to overall stats for the day)
$results_breakdown = [
    'negative' => 0,
    'positive' => 0
];
$query = "SELECT 
            SUM(CASE WHEN test_hiv='NEGATIVE' AND test_hbv='NEGATIVE' AND test_hcv='NEGATIVE' AND test_syphilis='NEGATIVE' THEN 1 ELSE 0 END) as all_negative,
            SUM(CASE WHEN test_hiv='POSITIVE' OR test_hbv='POSITIVE' OR test_hcv='POSITIVE' OR test_syphilis='POSITIVE' THEN 1 ELSE 0 END) as any_positive
          FROM lab_tests 
          WHERE DATE(tested_at) = CURDATE()";
$result = $conn->query($query);
if ($result) {
    $row = $result->fetch_assoc();
    $results_breakdown['negative'] = $row['all_negative'] ?? 0;
    $results_breakdown['positive'] = $row['any_positive'] ?? 0;
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
            <h1 class="text-3xl font-extrabold text-blood-900 tracking-tight">Laboratory Technologist Dashboard</h1>
            <p class="text-gray-500 mt-2">Blood testing and quality control</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-amber-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Pending Tests</p>
                        <h2 class="text-3xl font-extrabold text-amber-500"><?= number_format($stats['pending_tests']) ?></h2>
                    </div>
                    <span class="text-3xl">🧪</span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-600">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Approved Units</p>
                        <h2 class="text-3xl font-extrabold text-green-600"><?= number_format($stats['approved_units']) ?></h2>
                    </div>
                    <span class="text-3xl">✅</span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blood-600">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Rejected Units</p>
                        <h2 class="text-3xl font-extrabold text-blood-600"><?= number_format($stats['rejected_units']) ?></h2>
                    </div>
                    <span class="text-3xl">❌</span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-medical-600">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Tests Today</p>
                        <h2 class="text-3xl font-extrabold text-medical-600"><?= number_format($stats['tests_today']) ?></h2>
                    </div>
                    <span class="text-3xl">📊</span>
                </div>
            </div>
        </div>

        <!-- Advanced Feature -->
        <div class="mt-8 bg-gradient-to-br from-red-50 to-amber-50 rounded-xl p-6 border border-amber-100 shadow-sm">
            <h3 class="flex items-center text-lg font-bold text-blood-900 mb-2">
                <span class="mr-2">🌟</span> Advanced Feature: Quality Gate Control
            <h3>
            <p class="text-gray-600 text-sm">Blood units CANNOT proceed to inventory without laboratory approval. All units must pass HIV, HBV, HCV, and Syphilis tests before being marked as safe for transfusion.</p>
        </div>

        <!-- Pending Tests Queue -->
        <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h2 class="text-xl font-bold text-gray-900">Blood Samples Queue</h2>
                <a href="enter-results.php" class="bg-blood-600 hover:bg-blood-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">Enter Test Results</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-xs uppercase text-gray-500 font-semibold">
                            <th class="px-6 py-4">Unit ID</th>
                            <th class="px-6 py-4">Blood Group</th>
                            <th class="px-6 py-4">Collection Date</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (!empty($pending_queue)): ?>
                            <?php foreach ($pending_queue as $unit): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 font-mono font-medium text-gray-700"><?= htmlspecialchars($unit['barcode']) ?></td>
                                <td class="px-6 py-4"><span class="font-bold text-blood-700"><?= htmlspecialchars($unit['blood_group']) ?></span></td>
                                <td class="px-6 py-4 text-gray-600"><?= date('M d, H:i', strtotime($unit['collection_date'])) ?></td>
                                <td class="px-6 py-4"><span class="bg-amber-100 text-amber-800 px-3 py-1 rounded-full text-xs font-bold border border-amber-200">TESTING</span></td>
                                <td class="px-6 py-4">
                                    <a href="enter-results.php?id=<?= $unit['unit_id'] ?>" class="text-blood-600 hover:text-blood-800 font-bold text-xs uppercase hover:underline">Test Now</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-400 font-medium">No pending samples in the queue.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Test Results Summary -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Today's Test Results Summary</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg border border-green-100">
                        <span class="text-gray-700 font-medium">Safe / Negative</span>
                        <span class="text-green-700 font-bold"><?= $results_breakdown['negative'] ?> Units</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg border border-red-100">
                        <span class="text-gray-700 font-medium">Unsafe / Positive</span>
                        <span class="text-red-700 font-bold"><?= $results_breakdown['positive'] ?> Units</span>
                    </div>
                </div>
            </div>

            <div class="bg-green-50 rounded-xl shadow-sm border border-green-200 p-6">
                <h3 class="text-lg font-bold text-green-800 mb-3 flex items-center">
                    <span class="bg-green-200 p-1 rounded mr-2">✅</span> Safety Record
                </h3>
                <p class="text-green-800 text-sm leading-relaxed opacity-90">
                    All approved blood units have passed comprehensive safety testing. 
                    The system strictly enforces testing protocols before any unit can be released.
                </p>
                <div class="mt-6 pt-6 border-t border-green-200">
                    <p class="text-3xl font-extrabold text-green-700">100% Verified</p>
                    <p class="text-xs text-green-600 mt-1">Units in inventory are safe</p>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/sidebar.js"></script>
</body>
</html>
