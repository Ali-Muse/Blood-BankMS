<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';

require_role(['System Administrator']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Alerts - BBMS</title>
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
    <?php render_sidebar('alerts.php'); ?>
    
    <div class="main-content ml-0 md:ml-72 min-h-screen p-6 pt-20 md:pt-8 transition-all duration-300">
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-2">
                <i class="fa-solid fa-bell text-3xl text-blood-700"></i>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Emergency Alerts</h1>
            </div>
            <p class="text-gray-500">Send notifications for blood shortages or urgent campaigns.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Send Alert Form -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6 border-b border-gray-100 pb-2">Send Emergency Alert</h2>
                <form>
                    <div class="mb-5">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Alert Type <span class="text-red-500">*</span></label>
                        <select class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2.5" required>
                            <option value="">Select Type</option>
                            <option value="BLOOD_SHORTAGE">🚨 Blood Shortage</option>
                            <option value="URGENT_CAMPAIGN">📢 Urgent Campaign</option>
                            <option value="EMERGENCY_REQUEST">⚠️ Emergency Request</option>
                            <option value="SYSTEM_ALERT">🔔 System Alert</option>
                        </select>
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Blood Group (if applicable)</label>
                        <select class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2.5">
                            <option value="">All Blood Groups</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                        </select>
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Recipients <span class="text-red-500">*</span></label>
                        <select class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2 text-sm h-32" multiple>
                            <option value="ALL">All Users</option>
                            <option value="DONORS">All Donors</option>
                            <option value="HOSPITALS">All Hospitals</option>
                            <option value="PARTNERS">Partner Organizations</option>
                            <option value="REGISTRATION">Registration Officers</option>
                            <option value="INVENTORY">Inventory Managers</option>
                        </select>
                        <p class="text-xs text-gray-400 mt-1 italic">Hold Ctrl to select multiple</p>
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Message <span class="text-red-500">*</span></label>
                        <textarea class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-3" rows="5" placeholder="Enter your emergency message..." required></textarea>
                    </div>

                    <div class="space-y-3 mb-6">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" class="w-5 h-5 text-blood-600 border-gray-300 rounded focus:ring-blood-500">
                            <span class="text-gray-700 font-medium group-hover:text-blood-700 transition-colors">Send SMS notification</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" class="w-5 h-5 text-blood-600 border-gray-300 rounded focus:ring-blood-500">
                            <span class="text-gray-700 font-medium group-hover:text-blood-700 transition-colors">Send Email notification</span>
                        </label>
                    </div>

                    <button type="submit" class="w-full bg-blood-600 hover:bg-blood-700 text-white font-bold py-3 px-6 rounded-lg transition-all shadow-md transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-tower-broadcast animate-pulse"></i> Send Emergency Alert
                    </button>
                </form>
            </div>

            <!-- Side Cards -->
            <div class="space-y-6">
                <!-- Active Alerts List -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-rss text-orange-500"></i> Active Alerts
                    </h2>
                    
                    <div class="space-y-4">
                        <!-- Alert Item 1 -->
                        <div class="p-4 bg-red-50 rounded-lg border-l-4 border-red-600">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-bold text-red-800 mb-1 flex items-center gap-2">
                                        <i class="fa-solid fa-triangle-exclamation"></i> Critical O- Blood Shortage
                                    </h3>
                                    <p class="text-sm text-red-700 font-medium mb-2">Only 3 units remaining. Urgent donations needed.</p>
                                    <p class="text-xs text-red-500 font-semibold">
                                        <i class="fa-regular fa-paper-plane mr-1"></i> Sent to: All Donors, Partners • 2 hours ago
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Alert Item 2 -->
                        <div class="p-4 bg-yellow-50 rounded-lg border-l-4 border-yellow-500">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-bold text-yellow-800 mb-1 flex items-center gap-2">
                                        <i class="fa-solid fa-bullhorn"></i> Blood Donation Campaign
                                    </h3>
                                    <p class="text-sm text-yellow-700 font-medium mb-2">University campus drive this weekend. Target: 200 donors.</p>
                                    <p class="text-xs text-yellow-600 font-semibold">
                                        <i class="fa-regular fa-paper-plane mr-1"></i> Sent to: All Users • 1 day ago
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Card -->
                <div class="bg-blue-600 rounded-xl shadow-lg border border-blue-500 p-6 text-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-blue-500 opacity-50"></div>
                    <div class="absolute bottom-0 left-0 -mb-4 -ml-4 h-32 w-32 rounded-full bg-blue-700 opacity-50"></div>
                    
                    <h3 class="font-bold text-lg mb-4 relative z-10 flex items-center gap-2">
                        <i class="fa-solid fa-chart-simple"></i> Alert Statistics
                    </h3>
                    
                    <div class="grid grid-cols-2 gap-4 text-center relative z-10">
                        <div class="bg-blue-700 bg-opacity-50 p-4 rounded-lg backdrop-blur-sm">
                            <p class="text-3xl font-black mb-1">23</p>
                            <p class="text-xs font-bold text-blue-100 uppercase tracking-wide">Alerts This Month</p>
                        </div>
                        <div class="bg-blue-700 bg-opacity-50 p-4 rounded-lg backdrop-blur-sm">
                            <p class="text-3xl font-black mb-1">5,847</p>
                            <p class="text-xs font-bold text-blue-100 uppercase tracking-wide">Recipients Reached</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/sidebar.js"></script>
</body>
</html>
