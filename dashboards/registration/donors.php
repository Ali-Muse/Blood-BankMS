<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';

require_role(['Registration Officer', 'System Administrator']);

// Get search and filter parameters
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$blood_group_filter = isset($_GET['blood_group']) ? sanitize_input($_GET['blood_group']) : '';
$eligibility_filter = isset($_GET['eligibility']) ? sanitize_input($_GET['eligibility']) : '';

// Build query
$sql = "SELECT * FROM donors WHERE 1=1";
$params = [];
$types = '';

if ($search) {
    $sql .= " AND (full_name LIKE ? OR phone LIKE ? OR email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

if ($blood_group_filter) {
    $sql .= " AND blood_group = ?";
    $params[] = $blood_group_filter;
    $types .= 's';
}

if ($eligibility_filter) {
    $sql .= " AND eligibility = ?";
    $params[] = $eligibility_filter;
    $types .= 's';
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Management - BBMS</title>
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
    <?php render_sidebar('donors.php'); ?>
    
    <div class="main-content ml-0 md:ml-72 min-h-screen p-6 pt-20 md:pt-8 transition-all duration-300">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <i class="fa-solid fa-users text-3xl text-blood-700"></i>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Donor Management</h1>
                </div>
                <p class="text-gray-500">Search, filter, and manage registered blood donors.</p>
            </div>
            <a href="register-donor.php" class="bg-blood-600 hover:bg-blood-700 text-white font-bold py-2.5 px-5 rounded-lg transition-colors shadow-md flex items-center gap-2">
                <i class="fa-solid fa-user-plus"></i> New Donor
            </a>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
            <h2 class="text-sm font-bold text-gray-700 mb-4 uppercase tracking-wide">Filter Donors</h2>
            <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-500 mb-1">Search</label>
                    <div class="relative">
                        <i class="fa-solid fa-search absolute left-3 top-3 text-gray-400"></i>
                        <input type="text" name="search" class="pl-10 w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow text-sm" placeholder="Search by Name, Phone, or Email" value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Blood Group</label>
                    <select name="blood_group" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow text-sm">
                        <option value="">All Groups</option>
                        <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $group): ?>
                            <option value="<?= $group ?>" <?= $blood_group_filter === $group ? 'selected' : '' ?>><?= $group ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Eligibility Status</label>
                    <select name="eligibility" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow text-sm">
                        <option value="">All Status</option>
                        <option value="ELIGIBLE" <?= $eligibility_filter === 'ELIGIBLE' ? 'selected' : '' ?>>Eligible</option>
                        <option value="NOT_ELIGIBLE" <?= $eligibility_filter === 'NOT_ELIGIBLE' ? 'selected' : '' ?>>Not Eligible</option>
                        <option value="DEFERRED" <?= $eligibility_filter === 'DEFERRED' ? 'selected' : '' ?>>Deferred</option>
                    </select>
                </div>
            </form>
             <div class="mt-4 flex gap-2 justify-end">
                 <a href="donors.php" class="bg-white border border-gray-300 text-gray-600 px-4 py-2 rounded-lg text-sm font-bold hover:bg-gray-50 transition-colors">Clear Filters</a>
                 <button type="submit" class="bg-gray-900 text-white px-6 py-2 rounded-lg text-sm font-bold hover:bg-gray-800 transition-colors shadow-sm">Apply Filters</button>
            </div>
        </div>

        <!-- Donors Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h2 class="font-bold text-gray-900">Search Results <span class="text-gray-400 font-normal px-2">|</span> <span class="text-gray-500 text-sm font-normal"><?= $result->num_rows ?> donors found</span></h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Donor ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Group</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Gender</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Contact</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Eligibility</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Total Donations</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($donor = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 transition-colors group">
                                    <td class="px-6 py-4 whitespace-nowrap text-xs font-mono text-gray-500">
                                        #<?= str_pad($donor['donor_id'], 4, '0', STR_PAD_LEFT) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-gray-900 group-hover:text-blood-700 transition-colors">
                                            <?= htmlspecialchars($donor['full_name']) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-blood-100 text-blood-700 font-black text-xs">
                                            <?= $donor['blood_group'] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= $donor['gender'] ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="flex flex-col">
                                            <span><?= htmlspecialchars($donor['phone']) ?></span>
                                            <span class="text-xs text-gray-400"><?= htmlspecialchars($donor['email']) ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $status_classes = [
                                            'ELIGIBLE' => 'bg-green-100 text-green-800',
                                            'NOT_ELIGIBLE' => 'bg-red-100 text-red-800',
                                            'DEFERRED' => 'bg-yellow-100 text-yellow-800'
                                        ];
                                        $class = $status_classes[$donor['eligibility']] ?? 'bg-gray-100 text-gray-800';
                                        ?>
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold <?= $class ?>">
                                            <?php 
                                            echo match($donor['eligibility']) {
                                                'ELIGIBLE' => 'Available',
                                                'NOT_ELIGIBLE' => 'Disqualified',
                                                'DEFERRED' => 'Deferred',
                                                default => $donor['eligibility']
                                            };
                                            ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-gray-700">
                                        <?= $donor['total_donations'] ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="eligibility.php?donor_id=<?= $donor['donor_id'] ?>" class="text-blood-600 hover:text-blood-900 font-bold hover:underline">View Details</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="px-6 py-16 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="h-16 w-16 bg-gray-100 rounded-full flex items-center justify-center mb-4 text-gray-400">
                                            <i class="fa-solid fa-user-slash text-2xl"></i>
                                        </div>
                                        <h3 class="text-lg font-bold text-gray-900 mb-1">No Donors Found</h3>
                                        <p class="text-sm">Try adjusting your search criteria or register a new donor.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
             <!-- Pagination (Static Example) -->
            <div class="bg-gray-50 px-6 py-3 border-t border-gray-200 flex items-center justify-between">
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing <span class="font-medium">1</span> to <span class="font-medium"><?= $result->num_rows ?></span> of <span class="font-medium"><?= $result->num_rows ?></span> results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Previous</span>
                                <i class="fa-solid fa-chevron-left"></i>
                            </a>
                            <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">1</a>
                            <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Next</span>
                                <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/sidebar.js"></script>
</body>
</html>
