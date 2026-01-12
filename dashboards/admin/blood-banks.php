<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';

require_role(['System Administrator']);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bank_name = sanitize_input($_POST['bank_name']);
    $location = sanitize_input($_POST['location']);
    $region_id = sanitize_input($_POST['region_id']);
    
    $stmt = $conn->prepare("INSERT INTO blood_banks (bank_name, location, region_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $bank_name, $location, $region_id);
    
    if ($stmt->execute()) {
        $success = "Blood bank added successfully!";
    } else {
        $error = "Failed to add blood bank.";
    }
}

// Fetch blood banks
$banks = $conn->query("SELECT b.*, r.region_name FROM blood_banks b LEFT JOIN regions r ON b.region_id = r.region_id ORDER BY b.bank_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Banks Management - BBMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 font-sans text-gray-900">
    <?php render_sidebar('blood-banks.php'); ?>
    
    <div class="main-content ml-0 md:ml-72 min-h-screen p-6 pt-20 md:pt-8 transition-all duration-300">
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-2">
                <i class="fa-solid fa-building-columns text-3xl text-blood-700"></i>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Blood Banks / Branches</h1>
            </div>
            <p class="text-gray-500">View & manage all blood bank branches across regions</p>
        </div>

        <?php if ($success): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-r mb-6 shadow-sm flex items-start gap-3">
                <i class="fa-solid fa-circle-check mt-1"></i>
                <div>
                    <p class="font-bold">Success</p>
                    <p><?= htmlspecialchars($success) ?></p>
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

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between h-full border-l-4 border-l-blood-600">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Total Branches</p>
                    <h2 class="text-3xl font-extrabold text-gray-900">15</h2>
                </div>
                <div class="mt-4 flex items-center text-xs text-gray-400">
                    <i class="fa-solid fa-network-wired mr-1"></i> Connected
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between h-full border-l-4 border-l-green-600">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Active Branches</p>
                    <h2 class="text-3xl font-extrabold text-gray-900">15</h2>
                </div>
                 <div class="mt-4 flex items-center text-xs text-green-600 font-medium">
                    <i class="fa-solid fa-check-circle mr-1"></i> 100% Operational
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between h-full border-l-4 border-l-blue-600">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Regions Covered</p>
                    <h2 class="text-3xl font-extrabold text-gray-900">8</h2>
                </div>
                <div class="mt-4 flex items-center text-xs text-blue-600 font-medium">
                    <i class="fa-solid fa-map mr-1"></i> Nationwide
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between h-full border-l-4 border-l-yellow-500">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Total Capacity</p>
                    <h2 class="text-3xl font-extrabold text-gray-900">50,000</h2>
                </div>
                 <div class="mt-4 flex items-center text-xs text-yellow-600 font-medium">
                    <i class="fa-solid fa-database mr-1"></i> Units Max
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-fit">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Add New Branch</h2>
                    <div class="h-8 w-8 bg-blood-50 rounded-full flex items-center justify-center text-blood-600">
                        <i class="fa-solid fa-plus"></i>
                    </div>
                </div>
                <form method="POST" action="" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Branch Name <span class="text-red-500">*</span></label>
                        <input type="text" name="bank_name" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2.5 border" placeholder="e.g., Central Blood Bank" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Location <span class="text-red-500">*</span></label>
                        <input type="text" name="location" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2.5 border" placeholder="e.g., 123 Main Street, City" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Region</label>
                        <select name="region_id" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2.5 border bg-white">
                            <option value="">Select Region</option>
                            <option value="1">Central Region</option>
                            <option value="2">Northern Region</option>
                            <option value="3">Southern Region</option>
                            <option value="4">Eastern Region</option>
                            <option value="5">Western Region</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-blood-700 text-white font-bold py-3 px-4 rounded-lg hover:bg-blood-800 transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5 mt-2">
                         <i class="fa-solid fa-save mr-2"></i> Add Blood Bank
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col h-[600px]">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50 rounded-t-xl">
                    <h2 class="text-xl font-bold text-gray-900">All Blood Banks</h2>
                    <span class="bg-gray-200 text-gray-700 text-xs font-semibold px-2 py-1 rounded-full"><?= $banks ? $banks->num_rows : 0 ?> Branches</span>
                </div>
                <div class="overflow-y-auto flex-grow p-0">
                    <?php if ($banks && $banks->num_rows > 0): ?>
                        <div class="divide-y divide-gray-100">
                            <?php while ($bank = $banks->fetch_assoc()): ?>
                                <div class="p-5 hover:bg-gray-50 transition-colors flex justify-between items-start group">
                                    <div>
                                        <h3 class="font-bold text-gray-900 text-lg mb-1 group-hover:text-blood-700 transition-colors"><?= htmlspecialchars($bank['bank_name']) ?></h3>
                                        <p class="text-gray-500 text-sm mb-1 flex items-center gap-2">
                                            <i class="fa-solid fa-location-dot text-gray-400"></i> <?= htmlspecialchars($bank['location']) ?>
                                        </p>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <?= htmlspecialchars($bank['region_name'] ?? 'Not assigned') ?>
                                        </span>
                                    </div>
                                    <button class="text-gray-300 hover:text-blood-600 transition-colors p-2">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="p-8 text-center text-gray-500 flex flex-col items-center justify-center h-full">
                            <i class="fa-solid fa-building-circle-xmark text-4xl mb-3 text-gray-300"></i>
                            <p>No blood banks found. Add your first branch above.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/sidebar.js"></script>
</body>
</html>
