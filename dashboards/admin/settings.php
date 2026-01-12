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
    <title>System Settings - BBMS</title>
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
    <?php render_sidebar('settings.php'); ?>
    
    <div class="main-content ml-0 md:ml-72 min-h-screen p-6 pt-20 md:pt-8 transition-all duration-300">
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-2">
                <i class="fa-solid fa-gears text-3xl text-blood-700"></i>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">System Settings</h1>
            </div>
            <p class="text-gray-500">Configure global parameters, alerts, and system preferences.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Blood Configuration -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-droplet text-blood-600"></i> Blood Type Configuration
                </h2>
                
                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-700 mb-3">Active Blood Types</label>
                    <div class="grid grid-cols-4 gap-3">
                        <?php
                        $blood_types = ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'];
                        foreach ($blood_types as $type):
                        ?>
                            <label class="flex items-center justify-center gap-2 p-3 bg-gray-50 rounded-lg cursor-pointer border border-gray-200 hover:border-blood-300 hover:bg-blood-50 transition-all group">
                                <input type="checkbox" checked class="w-4 h-4 text-blood-600 border-gray-300 rounded focus:ring-blood-500">
                                <span class="font-bold text-gray-700 group-hover:text-blood-700"><?= $type ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Minimum Stock Alert Level</label>
                        <div class="relative">
                            <input type="number" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow pr-16" value="50">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500 font-bold bg-gray-50 rounded-r-lg border-l border-gray-200 px-3 text-sm">
                                Units
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Triggers low stock warnings when inventory drops below this.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Expiry Warning Lead Time</label>
                        <div class="relative">
                            <input type="number" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow pr-16" value="7">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500 font-bold bg-gray-50 rounded-r-lg border-l border-gray-200 px-3 text-sm">
                                Days
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Days before expiration to flag units as 'Expiring Soon'.</p>
                    </div>
                </div>
            </div>

            <!-- Notification Settings -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-bell text-blood-600"></i> Notification Settings
                </h2>
                
                <div class="space-y-3">
                    <label class="flex items-start gap-3 p-4 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100 transition-colors">
                         <div class="flex items-center h-5 mt-0.5">
                            <input type="checkbox" checked class="w-5 h-5 text-blood-600 border-gray-300 rounded focus:ring-blood-500">
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-gray-900">Email Notifications</span>
                            <span class="block text-xs text-gray-500 leading-relaxed">Send system alerts and reports via email.</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-4 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100 transition-colors">
                        <div class="flex items-center h-5 mt-0.5">
                            <input type="checkbox" checked class="w-5 h-5 text-blood-600 border-gray-300 rounded focus:ring-blood-500">
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-gray-900">SMS Notifications</span>
                            <span class="block text-xs text-gray-500 leading-relaxed">Send critical emergency alerts to mobile numbers.</span>
                        </div>
                    </label>

                     <label class="flex items-start gap-3 p-4 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100 transition-colors">
                        <div class="flex items-center h-5 mt-0.5">
                            <input type="checkbox" checked class="w-5 h-5 text-blood-600 border-gray-300 rounded focus:ring-blood-500">
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-gray-900">Low Stock Alerts</span>
                            <span class="block text-xs text-gray-500 leading-relaxed">Notify Inventory Managers when stock levels are critical.</span>
                        </div>
                    </label>

                     <label class="flex items-start gap-3 p-4 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100 transition-colors">
                        <div class="flex items-center h-5 mt-0.5">
                            <input type="checkbox" checked class="w-5 h-5 text-blood-600 border-gray-300 rounded focus:ring-blood-500">
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-gray-900">Expiry Warnings</span>
                            <span class="block text-xs text-gray-500 leading-relaxed">Daily digest of units approaching expiration.</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-4 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100 transition-colors">
                        <div class="flex items-center h-5 mt-0.5">
                            <input type="checkbox" checked class="w-5 h-5 text-blood-600 border-gray-300 rounded focus:ring-blood-500">
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-gray-900">Emergency Request Broadcast</span>
                            <span class="block text-xs text-gray-500 leading-relaxed">Immediately notify all relevant staff of emergency requests.</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- System Preferences -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 lg:col-span-2">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-blood-600"></i> System Preferences
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">System Language</label>
                        <select class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow">
                            <option value="en">English</option>
                            <option value="fr">French</option>
                            <option value="es">Spanish</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Date Format</label>
                        <select class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow">
                            <option value="MM/DD/YYYY">MM/DD/YYYY</option>
                            <option value="DD/MM/YYYY">DD/MM/YYYY</option>
                            <option value="YYYY-MM-DD">YYYY-MM-DD</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Time Zone</label>
                        <select class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow">
                            <option value="UTC">UTC</option>
                            <option value="EST">Eastern Time</option>
                            <option value="PST">Pacific Time</option>
                            <option value="GMT">GMT</option>
                        </select>
                    </div>
                    <div>
                         <label class="block text-sm font-bold text-gray-700 mb-1">Session Timeout</label>
                        <div class="relative">
                            <input type="number" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow pr-20" value="30">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500 font-bold bg-gray-50 rounded-r-lg border-l border-gray-200 px-3 text-sm">
                                Minutes
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end gap-3 pb-8">
            <button class="bg-white border border-gray-300 text-gray-700 font-bold py-2.5 px-6 rounded-lg hover:bg-gray-50 transition-colors shadow-sm">
                Reset to Defaults
            </button>
            <button class="bg-blood-600 text-white font-bold py-2.5 px-8 rounded-lg hover:bg-blood-700 transition-colors shadow-md flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i> Save Settings
            </button>
        </div>
    </div>

    <script src="../../assets/js/sidebar.js"></script>
</body>
</html>
