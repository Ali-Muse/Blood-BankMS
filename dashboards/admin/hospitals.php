<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';

require_role(['System Administrator']);

// Fetch hospitals
$hospitals = $conn->query("SELECT h.*, u.full_name, u.email FROM hospitals h LEFT JOIN users u ON h.user_id = u.user_id ORDER BY h.hospital_id DESC");

// Fetch blood requests
$requests = $conn->query("SELECT br.*, h.hospital_name, br.request_date FROM blood_requests br JOIN hospitals h ON br.hospital_id = h.hospital_id ORDER BY br.request_date DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospitals Management - BBMS</title>
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
    <?php render_sidebar('hospitals.php'); ?>
    
    <div class="main-content ml-0 md:ml-72 min-h-screen p-6 pt-20 md:pt-8 transition-all duration-300">
        <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                 <div class="flex items-center gap-3 mb-2">
                    <i class="fa-solid fa-hospital text-3xl text-blood-700"></i>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Hospitals Management</h1>
                </div>
                <p class="text-gray-500">Coordinate with partner hospitals and manage their blood unit requests.</p>
            </div>
            <button onclick="document.getElementById('addHospitalModal').classList.remove('hidden')" class="bg-blood-700 hover:bg-blood-800 text-white font-bold py-2.5 px-5 rounded-lg transition-colors shadow-md flex items-center gap-2">
                <i class="fa-solid fa-plus-circle"></i> Add Hospital
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between border-l-4 border-l-blood-600">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Total Hospitals</p>
                    <h2 class="text-3xl font-extrabold text-gray-900">52</h2>
                </div>
                <div class="mt-4 flex items-center text-xs text-green-600 font-bold">
                    <i class="fa-solid fa-arrow-up mr-1"></i> +4 new this month
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between border-l-4 border-l-green-600">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Active Requests</p>
                    <h2 class="text-3xl font-extrabold text-gray-900 text-green-600">18</h2>
                </div>
                 <div class="mt-4 flex items-center text-xs text-gray-400">
                    <i class="fa-solid fa-clock mr-1"></i> Processing now
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between border-l-4 border-l-red-500">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Emergency Requests</p>
                    <h2 class="text-3xl font-extrabold text-gray-900 text-red-600">5</h2>
                </div>
                 <div class="mt-4 flex items-center text-xs text-red-600 font-bold animate-pulse">
                    <i class="fa-solid fa-circle-exclamation mr-1"></i> Needs Attention
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between border-l-4 border-l-blue-500">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Monthly Distribution</p>
                    <h2 class="text-3xl font-extrabold text-gray-900 text-blue-600">247</h2>
                </div>
                 <div class="mt-4 flex items-center text-xs text-gray-400">
                    Units dispatched
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-8 mb-8">
            <!-- Recent Requests List -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-900">Recent Requests</h2>
                    <a href="#" class="text-sm text-blood-600 font-semibold hover:text-blood-800">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Hospital</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Details</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if ($requests && $requests->num_rows > 0): ?>
                                <?php while ($req = $requests->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($req['hospital_name']) ?></div>
                                            <?php if ($req['request_type'] === 'EMERGENCY'): ?>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 mt-1">
                                                    <i class="fa-solid fa-bolt mr-1"></i> EMERGENCY
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900"><span class="font-bold text-blood-700"><?= htmlspecialchars($req['blood_group']) ?></span> <span class="text-gray-400">|</span> <?= htmlspecialchars($req['quantity']) ?> units</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php
                                            $status_class = match($req['status']) {
                                                'PENDING' => 'bg-yellow-100 text-yellow-800',
                                                'APPROVED' => 'bg-green-100 text-green-800',
                                                'DISPATCHED' => 'bg-blue-100 text-blue-800',
                                                'REJECTED' => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-800',
                                            };
                                            ?>
                                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium <?= $status_class ?>">
                                                <?= $req['status'] ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                                            <?= date('M d', strtotime($req['request_date'])) ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                        <i class="fa-solid fa-inbox text-4xl mb-3 text-gray-300"></i>
                                        <p>No recent requests found.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Hospitals List -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col h-[600px]">
                <div class="p-6 border-b border-gray-100 bg-gray-50">
                    <h2 class="text-xl font-bold text-gray-900">Registered Hospitals</h2>
                </div>
                <div class="overflow-y-auto flex-grow p-0 divide-y divide-gray-100">
                    <?php if ($hospitals && $hospitals->num_rows > 0): ?>
                        <?php while ($hospital = $hospitals->fetch_assoc()): ?>
                            <div class="p-5 hover:bg-gray-50 transition-colors group">
                                <div class="flex justify-between items-start mb-1">
                                    <h3 class="font-bold text-gray-900 group-hover:text-blood-700 transition-colors"><?= htmlspecialchars($hospital['hospital_name']) ?></h3>
                                    <a href="#" class="text-gray-300 hover:text-blood-600"><i class="fa-solid fa-pen-to-square"></i></a>
                                </div>
                                <p class="text-sm text-gray-500 mb-2"><i class="fa-solid fa-location-dot mr-1.5 text-gray-400"></i> <?= htmlspecialchars($hospital['location']) ?></p>
                                <div class="flex items-center gap-3 text-xs text-gray-400">
                                    <?php if($hospital['full_name']): ?>
                                        <span class="bg-gray-100 px-2 py-1 rounded"><?= htmlspecialchars($hospital['full_name']) ?></span>
                                    <?php endif; ?>
                                    <?php if($hospital['email']): ?>
                                        <span><?= htmlspecialchars($hospital['email']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="p-8 text-center text-gray-500 flex flex-col items-center justify-center h-full">
                            <p>No hospitals registered yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Hospital Modal -->
    <div id="addHospitalModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto border border-gray-100">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h2 class="text-xl font-bold text-gray-900">Add New Hospital</h2>
                <button onclick="document.getElementById('addHospitalModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fa-solid fa-xmark text-2xl"></i>
                </button>
            </div>
            
            <form method="POST" action="" class="p-6 space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hospital Name <span class="text-red-500">*</span></label>
                    <input type="text" name="hospital_name" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2.5 border" placeholder="e.g., City General Hospital" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Location <span class="text-red-500">*</span></label>
                    <input type="text" name="location" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2.5 border" placeholder="e.g., 123 Main Street, City" required>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label>
                        <input type="text" name="contact_person" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2.5 border" placeholder="Full Name">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <input type="tel" name="phone" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2.5 border" placeholder="+1 234...">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="email" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2.5 border" placeholder="hospital@example.com">
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

                <div class="flex items-center gap-4 pt-4">
                    <button type="button" onclick="document.getElementById('addHospitalModal').classList.add('hidden')" class="flex-1 py-3 border border-gray-300 rounded-lg text-gray-700 font-bold hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 py-3 bg-blood-700 text-white rounded-lg font-bold hover:bg-blood-800 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all">
                        Add Hospital
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../assets/js/sidebar.js"></script>
    <script>
        // Use Tailwind's hidden class for modal toggling
        window.onclick = function(event) {
            const modal = document.getElementById('addHospitalModal');
            if (event.target == modal) {
                modal.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
