<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';

require_role(['System Administrator']);

// Fetch partner organizations (users with Red Cross role)
$partners = $conn->query("SELECT * FROM users WHERE role_name = 'Red Cross' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partner Organizations - BBMS</title>
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
    <?php render_sidebar('partners.php'); ?>
    
    <div class="main-content ml-0 md:ml-72 min-h-screen p-6 pt-20 md:pt-8 transition-all duration-300">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                 <div class="flex items-center gap-3 mb-1">
                    <i class="fa-solid fa-handshake-angle text-3xl text-blood-700"></i>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Partner Organizations</h1>
                </div>
                <p class="text-gray-500">Collaborate with Red Cross and other NGOs for blood drives.</p>
            </div>
            <button onclick="document.getElementById('addPartnerModal').classList.remove('hidden')" class="bg-blood-600 hover:bg-blood-700 text-white font-bold py-2.5 px-5 rounded-lg transition-colors shadow-md flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> Add Partner
            </button>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 border-l-4 border-l-gray-500">
                <p class="text-xs font-bold text-gray-500 uppercase mb-1">Total Partners</p>
                <h2 class="text-3xl font-black text-gray-800">8</h2>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 border-l-4 border-l-teal-500">
                <p class="text-xs font-bold text-gray-500 uppercase mb-1">Active Campaigns</p>
                <h2 class="text-3xl font-black text-teal-600">12</h2>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 border-l-4 border-l-blue-500">
                <p class="text-xs font-bold text-gray-500 uppercase mb-1">Donors Mobilized</p>
                <h2 class="text-3xl font-black text-blue-600">2,847</h2>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 border-l-4 border-l-yellow-500">
                <p class="text-xs font-bold text-gray-500 uppercase mb-1">Units Collected</p>
                <h2 class="text-3xl font-black text-yellow-600">1,923</h2>
            </div>
        </div>

        <!-- Partner List -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="p-6 border-b border-gray-100 bg-gray-50">
                <h2 class="text-lg font-bold text-gray-900">Registered Organizations</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Organization</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Contact Person</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Email</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Phone</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if ($partners && $partners->num_rows > 0): ?>
                            <?php while ($partner = $partners->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-red-100 flex items-center justify-center text-red-600">
                                                <i class="fa-solid fa-hand-holding-heart"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-bold text-gray-900">Red Cross Society</div>
                                                <div class="text-xs text-gray-500">NGO</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium"><?= htmlspecialchars($partner['full_name']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($partner['email']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($partner['phone'] ?? 'N/A') ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                            <?= $partner['status'] ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fa-solid fa-users-slash text-4xl mb-3 text-gray-300"></i>
                                    <p>No partner organizations found.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Active Campaigns -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 bg-gray-50">
                 <h2 class="text-lg font-bold text-gray-900">Active Campaigns</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Campaign Card 1 -->
                <div class="p-6 bg-gray-50 rounded-xl border-l-4 border-teal-500 transition-shadow hover:shadow-md">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-bold text-lg text-gray-900">University Blood Drive</h3>
                        <span class="bg-teal-100 text-teal-800 text-xs font-bold px-2 py-1 rounded">Active</span>
                    </div>
                    <p class="text-sm text-gray-500 mb-4"><i class="fa-solid fa-user-group mr-1"></i> Partner: Red Cross Society</p>
                    
                    <div class="space-y-2">
                        <div class="flex justify-between text-xs font-bold text-gray-600">
                            <span>Target: 200 donors</span>
                            <span class="text-teal-600">Collected: 156 (78%)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-teal-500 h-2.5 rounded-full" style="width: 78%"></div>
                        </div>
                    </div>
                </div>

                <!-- Campaign Card 2 -->
                <div class="p-6 bg-gray-50 rounded-xl border-l-4 border-teal-500 transition-shadow hover:shadow-md">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-bold text-lg text-gray-900">Mobile Donation Van</h3>
                        <span class="bg-teal-100 text-teal-800 text-xs font-bold px-2 py-1 rounded">Active</span>
                    </div>
                    <p class="text-sm text-gray-500 mb-4"><i class="fa-solid fa-van-shuttle mr-1"></i> Partner: Red Cross Society</p>
                    
                    <div class="space-y-2">
                        <div class="flex justify-between text-xs font-bold text-gray-600">
                            <span>Target: 100 donors</span>
                            <span class="text-teal-600">Collected: 87 (87%)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-teal-500 h-2.5 rounded-full" style="width: 87%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Partner Modal -->
    <div id="addPartnerModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto transform transition-all">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h2 class="text-xl font-bold text-gray-900">Add New Partner Organization</h2>
                <button onclick="document.getElementById('addPartnerModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
            
            <form method="POST" action="" class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Organization Name *</label>
                    <div class="relative">
                        <i class="fa-solid fa-building absolute left-3 top-3 text-gray-400"></i>
                        <input type="text" name="org_name" class="pl-10 w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow" placeholder="e.g. Red Cross Society" required>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Organization Type *</label>
                    <select name="org_type" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow" required>
                        <option value="">Select Type</option>
                        <option value="Red Cross">Red Cross</option>
                        <option value="NGO">NGO</option>
                        <option value="Community Organization">Community Organization</option>
                        <option value="Religious Organization">Religious Organization</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Contact Person *</label>
                        <input type="text" name="contact_person" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow" placeholder="Full Name" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Phone Number *</label>
                        <input type="tel" name="phone" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow" placeholder="+1 234..." required>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Email Address *</label>
                    <div class="relative">
                        <i class="fa-solid fa-envelope absolute left-3 top-3 text-gray-400"></i>
                        <input type="email" name="email" class="pl-10 w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow" placeholder="partner@example.com" required>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Address</label>
                    <textarea name="address" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow" rows="2" placeholder="Organization address"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Region</label>
                    <select name="region_id" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow">
                        <option value="">Select Region</option>
                        <option value="1">Central Region</option>
                        <option value="2">Northern Region</option>
                        <option value="3">Southern Region</option>
                        <option value="4">Eastern Region</option>
                        <option value="5">Western Region</option>
                    </select>
                </div>

                <div class="flex gap-4 pt-2">
                    <button type="button" onclick="document.getElementById('addPartnerModal').classList.add('hidden')" class="flex-1 bg-white border border-gray-300 text-gray-700 font-bold py-2.5 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 bg-blood-600 text-white font-bold py-2.5 rounded-lg hover:bg-blood-700 transition-colors shadow-md">
                        Add Partner
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../assets/js/sidebar.js"></script>
    <script>
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('addPartnerModal');
            if (event.target == modal) {
                modal.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
