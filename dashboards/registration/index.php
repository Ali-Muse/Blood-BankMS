<?php
// Get Config & Auth
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';

require_role(['Registration Officer']);

$page_title = 'Registration Officer Dashboard';

// Fetch Registration Stats
$stats = [
    'total_donors' => 0,
    'eligible_donors' => 0,
    'appointments_today' => 0,
    'month_registrations' => 0
];

// 1. Total Donors
$query = "SELECT COUNT(*) as count FROM donors";
$result = $conn->query($query);
if ($result) $stats['total_donors'] = $result->fetch_assoc()['count'];

// 2. Eligible Donors
$query = "SELECT COUNT(*) as count FROM donors WHERE eligibility = 'ELIGIBLE'";
$result = $conn->query($query);
if ($result) $stats['eligible_donors'] = $result->fetch_assoc()['count'];

// 3. Today's Appointments
$query = "SELECT COUNT(*) as count FROM appointments WHERE DATE(appointment_date) = CURDATE() AND status = 'SCHEDULED'";
$result = $conn->query($query);
if ($result) $stats['appointments_today'] = $result->fetch_assoc()['count'];

// 4. This Month Registrations
$query = "SELECT COUNT(*) as count FROM donors WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())";
$result = $conn->query($query);
if ($result) $stats['month_registrations'] = $result->fetch_assoc()['count'];

// Recent Registrations
$recent_donors = [];
$query = "SELECT full_name, blood_group, eligibility, created_at FROM donors ORDER BY created_at DESC LIMIT 5";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recent_donors[] = $row;
    }
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
            <h1 class="text-3xl font-extrabold text-blood-900 tracking-tight">Registration Officer Dashboard</h1>
            <p class="text-gray-500 mt-2">Manage donor registrations and appointments</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-gray-400">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Total Donors</p>
                        <h2 class="text-3xl font-extrabold text-gray-700"><?= number_format($stats['total_donors']) ?></h2>
                    </div>
                    <span class="text-3xl text-gray-400">👥</span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-600">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Eligible Donors</p>
                        <h2 class="text-3xl font-extrabold text-green-600"><?= number_format($stats['eligible_donors']) ?></h2>
                    </div>
                    <span class="text-3xl text-green-200">✅</span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-medical-600">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Total Appointments</p>
                        <h2 class="text-3xl font-extrabold text-medical-600"><?= number_format($stats['appointments_today']) ?></h2>
                        <p class="text-xs text-medical-800 opacity-70 mt-1">Scheduled for today</p>
                    </div>
                    <span class="text-3xl text-medical-200">📅</span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-amber-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">This Month</p>
                        <h2 class="text-3xl font-extrabold text-amber-500"><?= number_format($stats['month_registrations']) ?></h2>
                        <p class="text-xs text-amber-800 opacity-70 mt-1">New registrations</p>
                    </div>
                    <span class="text-3xl text-amber-200">📊</span>
                </div>
            </div>
        </div>

        <!-- Advanced Feature -->
        <div class="mt-8 bg-gradient-to-br from-red-50 to-green-50 rounded-xl p-6 border border-green-100 shadow-sm">
            <h3 class="flex items-center text-lg font-bold text-blood-900 mb-2">
                <span class="mr-2">🌟</span> Advanced Feature: Eligibility Auto-Check
            </h3>
            <p class="text-gray-600 text-sm">The system automatically validates donor eligibility based on age, last donation date, health status, and other criteria. Ineligible donors are flagged with specific reasons.</p>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
            <a href="register-donor.php" class="group block bg-blood-600 rounded-xl p-8 text-white shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all text-center">
                <span class="text-5xl mb-4 block group-hover:scale-110 transition-transform duration-300">➕</span>
                <h3 class="text-xl font-bold">Register New Donor</h3>
            </a>
            <a href="appointments.php" class="group block bg-medical-600 rounded-xl p-8 text-white shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all text-center">
                <span class="text-5xl mb-4 block group-hover:scale-110 transition-transform duration-300">📅</span>
                <h3 class="text-xl font-bold">Schedule Appointment</h3>
            </a>
        </div>

        <!-- Recent Registrations -->
        <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                <h2 class="text-xl font-bold text-gray-900">Recent Donor Registrations</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-xs uppercase text-gray-500 font-semibold">
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">Blood Group</th>
                            <th class="px-6 py-4">Eligibility</th>
                            <th class="px-6 py-4">Registration Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (!empty($recent_donors)): ?>
                            <?php foreach ($recent_donors as $donor): 
                                $eligibility_class = ($donor['eligibility'] === 'ELIGIBLE') ? 'bg-green-100 text-green-700 border-green-200' : 'bg-red-100 text-red-700 border-red-200';
                            ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 font-medium text-gray-800"><?= htmlspecialchars($donor['full_name']) ?></td>
                                <td class="px-6 py-4 font-bold text-blood-700"><?= htmlspecialchars($donor['blood_group']) ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold border <?= $eligibility_class ?>">
                                        <?= htmlspecialchars($donor['eligibility']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-500"><?= date('M d, Y', strtotime($donor['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-400 font-medium">No recent registrations found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="../../assets/js/sidebar.js"></script>
</body>
</html>
