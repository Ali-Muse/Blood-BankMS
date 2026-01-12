<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';

require_role(['Hospital User']);

$page_title = 'Blood Availability';

// Fetch available blood counts
$stock = [];
$query = "SELECT blood_group, COUNT(*) as count FROM blood_units WHERE status = 'APPROVED' GROUP BY blood_group";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $stock[$row['blood_group']] = $row['count'];
    }
}
$all_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
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
                        blood: { 600: '#dc2626', 700: '#b91c1c', 900: '#7f1d1d' }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 font-sans text-gray-900">
    <?php render_sidebar('availability.php'); ?>
    
    <div class="main-content ml-0 md:ml-72 min-h-screen p-6 pt-20 md:pt-8 transition-all duration-300">
        <div class="mb-8">
            <div class="flex items-center gap-3">
                <a href="index.php" class="text-gray-500 hover:text-blood-700 transition-colors"><i class="fas fa-arrow-left"></i> Back</a>
                <h1 class="text-3xl font-extrabold text-blood-900 tracking-tight">Blood Availability</h1>
            </div>
            <p class="text-gray-500 mt-2 ml-16">Real-time status of national blood stock</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($all_groups as $group): 
                    $count = $stock[$group] ?? 0;
                    $status_class = $count > 10 ? 'bg-green-100 text-green-800' : ($count > 3 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                    $status_label = $count > 10 ? 'Good' : ($count > 3 ? 'Low' : 'Critical');
                ?>
                <div class="border rounded-xl p-6 text-center hover:shadow-md transition-shadow">
                    <div class="text-4xl font-extrabold text-gray-800 mb-2"><?= $group ?></div>
                    <div class="text-3xl font-bold text-blood-600 mb-2"><?= $count ?></div>
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Units Available</div>
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold <?= $status_class ?>"><?= $status_label ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="mt-8 p-4 bg-blue-50 border border-blue-100 rounded-lg flex items-start">
                <i class="fas fa-info-circle text-blue-500 mt-1 mr-3 text-lg"></i>
                <p class="text-sm text-blue-800">
                    <strong>Note:</strong> This view reflects real-time approved stock available for dispatch. If you need a specific blood type that is low or critical, please mark your request as "Emergency" or contact the inventory manager directly.
                </p>
            </div>
        </div>
    </div>
    <script src="../../assets/js/sidebar.js"></script>
</body>
</html>
