<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';

require_role(['Laboratory Technologist', 'System Administrator']);

// Get blood units awaiting testing
$samples = $conn->query("
    SELECT bu.*, d.full_name, d.phone, lt.test_id
    FROM blood_units bu
    JOIN donors d ON bu.donor_id = d.donor_id
    LEFT JOIN lab_tests lt ON bu.unit_id = lt.unit_id
    WHERE bu.status = 'COLLECTED'
    ORDER BY bu.collection_date ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Samples Queue - BBMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 font-sans text-gray-900">
    <?php render_sidebar('samples-queue.php'); ?>
    
    <div class="main-content ml-0 md:ml-72 min-h-screen p-6 pt-20 md:pt-8 transition-all duration-300">
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-2">
                <i class="fa-solid fa-flask-vial text-3xl text-blood-700"></i>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Laboratory Samples Queue</h1>
            </div>
            <p class="text-gray-500">View and manage blood units awaiting laboratory testing.</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-900">Pending Samples</h2>
                <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2.5 py-0.5 rounded-full"><?= $samples->num_rows ?> Pending</span>
            </div>
            
            <?php if ($samples->num_rows > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Barcode</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Blood Group</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Donor Details</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Collection Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Volume</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($sample = $samples->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 transition-colors group">
                                    <td class="px-6 py-4 whitespace-nowrap font-mono text-sm font-medium text-gray-900">
                                        <?= $sample['barcode'] ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blood-100 text-blood-700">
                                            <?= $sample['blood_group'] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium text-gray-900"><?= htmlspecialchars($sample['full_name']) ?></span>
                                            <span class="text-xs text-gray-500"><?= $sample['phone'] ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= date('Y-m-d H:i', strtotime($sample['collection_date'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= $sample['volume_ml'] ?> ml
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="enter-results.php?unit_id=<?= $sample['unit_id'] ?>" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-blood-600 hover:bg-blood-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blood-500 transition-colors">
                                            Enter Results <i class="fa-solid fa-arrow-right ml-1.5"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="h-16 w-16 bg-gray-100 rounded-full flex items-center justify-center mb-4 text-gray-400">
                        <i class="fa-solid fa-flask text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">No Samples in Queue</h3>
                    <p class="text-gray-500 max-w-sm mt-1">Great job! All collected blood units have been tested and processed.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../../assets/js/sidebar.js"></script>
</body>
</html>
