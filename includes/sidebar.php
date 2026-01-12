<?php
require_once __DIR__ . '/config.php';

// Sidebar Menu Configuration
function get_sidebar_menu($role) {
    $menus = [
        'System Administrator' => [
            ['icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard', 'url' => 'index.php'],
            ['icon' => 'fas fa-users-cog', 'label' => 'User Management', 'url' => '#', 'submenu' => [
                ['label' => 'All Users', 'url' => 'users.php'],
                ['label' => 'Create User', 'url' => 'create-user.php'],
                ['label' => 'Manage Roles & Permissions', 'url' => 'roles.php'],
            ]],
            ['icon' => 'fas fa-clinic-medical', 'label' => 'Blood Banks / Branches', 'url' => 'blood-banks.php'],
            ['icon' => 'fas fa-hospital', 'label' => 'Hospitals Management', 'url' => 'hospitals.php'],
            ['icon' => 'fas fa-hand-holding-heart', 'label' => 'Partner Organizations', 'url' => 'partners.php'],
            ['icon' => 'fas fa-chart-line', 'label' => 'System Reports', 'url' => '#', 'submenu' => [
                ['label' => 'National Blood Stock', 'url' => 'reports-stock.php'],
                ['label' => 'System Usage Logs', 'url' => 'reports-usage.php'],
            ]],
            ['icon' => 'fas fa-clipboard-list', 'label' => 'Audit Logs', 'url' => 'audit-logs.php'],
            ['icon' => 'fas fa-exclamation-triangle', 'label' => 'Emergency Alerts', 'url' => 'alerts.php', 'badge' => 'emergency'],
            ['icon' => 'fas fa-cogs', 'label' => 'System Settings', 'url' => 'settings.php'],
        ],
        
        'Registration Officer' => [
            ['icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard', 'url' => 'index.php'],
            ['icon' => 'fas fa-user-plus', 'label' => 'Register Donor', 'url' => 'register-donor.php'],
            ['icon' => 'fas fa-users', 'label' => 'Donor List', 'url' => 'donors.php'],
            ['icon' => 'fas fa-notes-medical', 'label' => 'Eligibility Screening', 'url' => 'eligibility.php'],
            ['icon' => 'fas fa-history', 'label' => 'Donation History', 'url' => 'history.php'],
            ['icon' => 'fas fa-calendar-alt', 'label' => 'Appointment Scheduling', 'url' => 'appointments.php'],
            ['icon' => 'fas fa-bell', 'label' => 'Notifications', 'url' => 'notifications.php', 'badge' => 'notifications'],
        ],
        
        'Laboratory Technologist' => [
            ['icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard', 'url' => 'index.php'],
            ['icon' => 'fas fa-vial', 'label' => 'Blood Samples Queue', 'url' => 'samples-queue.php', 'badge' => 'pending'],
            ['icon' => 'fas fa-microscope', 'label' => 'Enter Test Results', 'url' => 'enter-results.php'],
            ['icon' => 'fas fa-file-medical-alt', 'label' => 'Test History', 'url' => 'test-history.php'],
            ['icon' => 'fas fa-check-circle', 'label' => 'Approved Blood Units', 'url' => 'approved-units.php'],
            ['icon' => 'fas fa-times-circle', 'label' => 'Rejected / Discarded', 'url' => 'rejected-units.php'],
            ['icon' => 'fas fa-file-alt', 'label' => 'Lab Reports', 'url' => 'lab-reports.php'],
            ['icon' => 'fas fa-bell', 'label' => 'Notifications', 'url' => 'notifications.php', 'badge' => 'notifications'],
        ],
        
        'Inventory Manager' => [
            ['icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard', 'url' => 'index.php'],
            ['icon' => 'fas fa-boxes', 'label' => 'Blood Inventory', 'url' => 'blood-inventory.php'],
            ['icon' => 'fas fa-check-double', 'label' => 'Available Blood Units', 'url' => 'available-units.php'],
            ['icon' => 'fas fa-lock', 'label' => 'Reserved Blood Units', 'url' => 'reserved-units.php'],
            ['icon' => 'fas fa-hourglass-end', 'label' => 'Expired Blood Units', 'url' => 'expired-units.php', 'badge' => 'expiry'],
            ['icon' => 'fas fa-clipboard-check', 'label' => 'Review Requests', 'url' => 'review-requests.php', 'badge' => 'pending_requests'],
            ['icon' => 'fas fa-truck', 'label' => 'Dispatch Blood', 'url' => 'dispatch.php'],
            ['icon' => 'fas fa-thermometer-half', 'label' => 'Storage Monitoring', 'url' => 'storage.php'],
            ['icon' => 'fas fa-chart-bar', 'label' => 'Inventory Reports', 'url' => 'reports.php'],
            ['icon' => 'fas fa-bell', 'label' => 'Notifications', 'url' => 'notifications.php', 'badge' => 'notifications'],
        ],
        
        'Hospital User' => [
            ['icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard', 'url' => 'index.php'],
            ['icon' => 'fas fa-search', 'label' => 'View Blood Availability', 'url' => 'availability.php'],
            ['icon' => 'fas fa-plus-circle', 'label' => 'Request Blood', 'url' => 'request-blood.php'],
            ['icon' => 'fas fa-ambulance', 'label' => 'Emergency Requests', 'url' => 'emergency-requests.php', 'badge' => 'emergency'],
            ['icon' => 'fas fa-tasks', 'label' => 'Request Status Tracking', 'url' => 'track-requests.php'],
            ['icon' => 'fas fa-history', 'label' => 'Request History', 'url' => 'history.php'],
            ['icon' => 'fas fa-bell', 'label' => 'Notifications', 'url' => 'notifications.php', 'badge' => 'notifications'],
        ],
        
        'Red Cross' => [
            ['icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard', 'url' => 'index.php'],
            ['icon' => 'fas fa-exclamation-circle', 'label' => 'Blood Shortage Alerts', 'url' => 'shortage-alerts.php', 'badge' => 'shortage'],
            ['icon' => 'fas fa-bullhorn', 'label' => 'Donation Campaigns', 'url' => 'campaigns.php'],
            ['icon' => 'fas fa-first-aid', 'label' => 'Emergency Support', 'url' => 'emergency-support.php'],
            ['icon' => 'fas fa-users', 'label' => 'Donor Mobilization', 'url' => 'mobilization.php'],
            ['icon' => 'fas fa-chart-pie', 'label' => 'Reports (Limited)', 'url' => 'reports.php'],
            ['icon' => 'fas fa-bell', 'label' => 'Notifications', 'url' => 'notifications.php', 'badge' => 'notifications'],
        ],
        
        'Minister Of Health' => [
            ['icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard', 'url' => 'index.php'],
            ['icon' => 'fas fa-chart-area', 'label' => 'National Statistics', 'url' => 'statistics.php'],
            ['icon' => 'fas fa-map-marked-alt', 'label' => 'Regional Stock Levels', 'url' => 'regional-stock.php'],
            ['icon' => 'fas fa-chart-line', 'label' => 'Donation Trends', 'url' => 'trends.php'],
            ['icon' => 'fas fa-shield-alt', 'label' => 'Expiry & Safety Reports', 'url' => 'safety-reports.php'],
            ['icon' => 'fas fa-ambulance', 'label' => 'Emergency Response', 'url' => 'emergency-reports.php'],
            ['icon' => 'fas fa-clipboard-check', 'label' => 'Compliance Reports', 'url' => 'compliance.php'],
        ],
    ];
    
    return $menus[$role] ?? [];
}

// Render Sidebar
function render_sidebar($current_page = '') {
    if (!is_logged_in()) {
        return;
    }
    
    $role = get_user_role();
    $user_name = get_user_name();
    $user_id = get_user_id();
    $menu_items = get_sidebar_menu($role);
    $notification_count = get_notification_count($user_id);
    
    $base_path = '';
    if ($role === 'System Administrator') $base_path = BASE_URL . 'dashboards/admin/';
    elseif ($role === 'Registration Officer') $base_path = BASE_URL . 'dashboards/registration/';
    elseif ($role === 'Laboratory Technologist') $base_path = BASE_URL . 'dashboards/lab/';
    elseif ($role === 'Inventory Manager') $base_path = BASE_URL . 'dashboards/inventory/';
    elseif ($role === 'Hospital User') $base_path = BASE_URL . 'dashboards/hospital/';
    elseif ($role === 'Red Cross') $base_path = BASE_URL . 'dashboards/partner/';
    elseif ($role === 'Minister Of Health') $base_path = BASE_URL . 'dashboards/authority/';
    
    ?>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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
    <style>
        /* Sidebar transition */
        .sidebar-transition { transition: all 0.3s ease; }
        /* Hide scrollbar */
        .no-scrollbar::-webkit-scrollbar { display: none; }
    </style>

    <!-- Mobile Toggle -->
    <button class="fixed top-4 left-4 z-50 p-2 bg-blood-700 text-white rounded-lg shadow-lg md:hidden hover:bg-blood-800 transition-colors mobile-toggle" onclick="toggleMobileSidebar()">
        <i class="fas fa-bars text-xl"></i>
    </button>
    
    <!-- Overlay -->
    <div class="sidebar-overlay fixed inset-0 bg-black/50 z-40 hidden backdrop-blur-sm transition-opacity" onclick="toggleMobileSidebar()"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar fixed top-0 left-0 h-screen w-72 bg-gradient-to-b from-blood-700 to-blood-900 text-white shadow-2xl z-50 flex flex-col sidebar-transition -translate-x-full md:translate-x-0 [&.collapsed]:w-20">
        <!-- Header -->
        <div class="sidebar-header p-6 border-b border-white/10 flex justify-between items-center [&.collapsed_&]:flex-col [&.collapsed_&]:p-4 [&.collapsed_&]:gap-4">
            <div class="sidebar-logo flex items-center gap-3 [&.collapsed_&]:justify-center">
                <div class="logo-icon w-12 h-12 bg-white rounded-xl flex items-center justify-center text-2xl shrink-0 text-blood-700 shadow-sm">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <div class="[&.collapsed_&]:hidden">
                    <h2 class="text-lg font-bold tracking-tight">BBMS</h2>
                    <p class="text-xs opacity-80">Blood Bank System</p>
                </div>
            </div>
            <button class="sidebar-toggle text-white/60 hover:text-white transition-colors hidden md:flex items-center justify-center" onclick="toggleSidebar()">
                <i class="fas fa-chevron-left text-sm"></i>
            </button>
        </div>
        
        <!-- Menu -->
        <nav class="sidebar-menu flex-1 overflow-y-auto overflow-x-hidden p-4 space-y-1 no-scrollbar">
            <?php foreach ($menu_items as $item): ?>
                <?php
                $is_active = ($current_page === $item['url']);
                $has_submenu = isset($item['submenu']);
                $item_url = $has_submenu ? 'javascript:void(0)' : $base_path . $item['url'];
                ?>
                
                <a href="<?= $item_url ?>" 
                   class="menu-item flex items-center py-3 px-4 text-white/80 hover:text-white hover:bg-white/10 rounded-lg transition-all cursor-pointer relative group whitespace-nowrap <?= $is_active ? 'bg-white/15 text-white border-l-4 border-white' : '' ?> [&.collapsed_&]:justify-center [&.collapsed_&]:p-3"
                   <?= $has_submenu ? 'onclick="toggleSubmenu(this)"' : '' ?>>
                    
                    <span class="text-xl shrink-0 <?= $has_submenu ? 'mr-3' : 'mr-3' ?> [&.collapsed_&]:mr-0 w-6 text-center">
                        <i class="<?= $item['icon'] ?>"></i>
                    </span>
                    
                    <span class="flex-1 font-medium [&.collapsed_&]:hidden"><?= htmlspecialchars($item['label']) ?></span>
                    
                    <?php if (isset($item['badge'])): ?>
                        <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full ml-auto [&.collapsed_&]:hidden" id="badge-<?= $item['badge'] ?>">
                            <?php
                            if ($item['badge'] === 'notifications') {
                                echo $notification_count > 0 ? $notification_count : '';
                            } else {
                                echo get_badge_count($item['badge']);
                            }
                            ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($has_submenu): ?>
                        <span class="text-xs opacity-70 [&.collapsed_&]:hidden">
                            <i class="fas fa-chevron-down"></i>
                        </span>
                    <?php endif; ?>

                    <!-- Tooltip for collapsed state -->
                    <div class="absolute left-full top-1/2 -translate-y-1/2 ml-2 bg-gray-900 text-white text-xs px-2 py-1 rounded hidden group-hover:block md:group-hover:[.collapsed_&]:block z-50 whitespace-nowrap pointer-events-none shadow-lg">
                        <?= htmlspecialchars($item['label']) ?>
                    </div>
                </a>
                
                <?php if ($has_submenu): ?>
                    <div class="submenu hidden bg-black/20 rounded-lg mt-1 mb-1 overflow-hidden transition-all [&.collapsed_&]:hidden">
                        <?php foreach ($item['submenu'] as $subitem): ?>
                            <a href="<?= $base_path . $subitem['url'] ?>" class="flex items-center py-2 pl-12 pr-4 text-sm text-white/70 hover:text-white hover:bg-white/5 transition-colors">
                                <span><?= htmlspecialchars($subitem['label']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        
            <div class="mt-auto pt-4 border-t border-white/10">
                <a href="<?= BASE_URL ?>includes/logout.php" class="flex items-center justify-center md:justify-start gap-3 w-full p-3 text-white/90 hover:bg-white/10 rounded-lg transition-colors font-medium group">
                    <span class="text-lg w-6 text-center"><i class="fas fa-sign-out-alt"></i></span>
                    <span class="[&.collapsed_&]:hidden">Logout</span>
                    <!-- Tooltip -->
                    <div class="absolute left-full ml-2 bg-gray-900 text-white text-xs px-2 py-1 rounded hidden group-hover:[.collapsed_&]:block z-50 whitespace-nowrap shadow-lg">
                        Logout
                    </div>
                </a>
            </div>
        </nav>
    </aside>
    <?php
}

// Get Badge Count
function get_badge_count($badge_type) {
    global $conn;
    
    switch ($badge_type) {
        case 'pending':
            $result = $conn->query("SELECT COUNT(*) as count FROM blood_units WHERE status = 'TESTING'");
            $row = $result->fetch_assoc();
            return $row['count'] ?? 0;
        case 'expiry':
            $result = $conn->query("SELECT COUNT(*) as count FROM blood_units WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND status = 'APPROVED'");
            $row = $result->fetch_assoc();
            return $row['count'] ?? 0;
        case 'pending_requests':
            $result = $conn->query("SELECT COUNT(*) as count FROM blood_requests WHERE status = 'PENDING'");
            $row = $result->fetch_assoc();
            return $row['count'] ?? 0;
        default:
            return 0;
    }
}
?>
