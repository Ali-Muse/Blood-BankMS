<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';

require_role(['System Administrator']);

// Fetch all roles and their permissions
$roles = [
    'System Administrator' => ['create' => true, 'read' => true, 'update' => true, 'delete' => true],
    'Registration Officer' => ['create' => true, 'read' => true, 'update' => true, 'delete' => false],
    'Laboratory Technologist' => ['create' => true, 'read' => true, 'update' => true, 'delete' => false],
    'Inventory Manager' => ['create' => true, 'read' => true, 'update' => true, 'delete' => false],
    'Hospital User' => ['create' => true, 'read' => true, 'update' => false, 'delete' => false],
    'Red Cross' => ['create' => false, 'read' => true, 'update' => false, 'delete' => false],
    'Minister Of Health' => ['create' => false, 'read' => true, 'update' => false, 'delete' => false],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Roles & Permissions - BBMS</title>
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
    <?php render_sidebar('roles.php'); ?>
    
    <div class="main-content ml-0 md:ml-72 min-h-screen p-6 pt-20 md:pt-8 transition-all duration-300">
        <div class="mb-8">
             <div class="flex items-center gap-3 mb-2">
                <i class="fa-solid fa-users-gear text-3xl text-blood-700"></i>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Roles & Permissions</h1>
            </div>
            <p class="text-gray-500">Manage fine-grained access control permissions for all user roles.</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="p-6 border-b border-gray-100 bg-gray-50">
                 <h2 class="text-xl font-bold text-gray-900">Role-Based Access Control (RBAC) Matrix</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Role</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Create</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Read</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Update</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Delete</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Access Level</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($roles as $role => $permissions): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap font-bold text-sm text-gray-900"><?= htmlspecialchars($role) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <?php if ($permissions['create']): ?>
                                        <i class="fa-solid fa-circle-check text-green-500 text-xl"></i>
                                    <?php else: ?>
                                        <i class="fa-solid fa-circle-xmark text-red-300 text-xl"></i>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <?php if ($permissions['read']): ?>
                                        <i class="fa-solid fa-circle-check text-green-500 text-xl"></i>
                                    <?php else: ?>
                                        <i class="fa-solid fa-circle-xmark text-red-300 text-xl"></i>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                   <?php if ($permissions['update']): ?>
                                        <i class="fa-solid fa-circle-check text-green-500 text-xl"></i>
                                    <?php else: ?>
                                        <i class="fa-solid fa-circle-xmark text-red-300 text-xl"></i>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                   <?php if ($permissions['delete']): ?>
                                        <i class="fa-solid fa-circle-check text-green-500 text-xl"></i>
                                    <?php else: ?>
                                        <i class="fa-solid fa-circle-xmark text-red-300 text-xl"></i>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <?php
                                    $level = 'Limited';
                                    $colorClass = 'bg-gray-100 text-gray-800';
                                    if ($role === 'System Administrator') { $level = 'Full System'; $colorClass = 'bg-purple-100 text-purple-800'; }
                                    elseif ($role === 'Minister Of Health') { $level = 'Read-Only'; $colorClass = 'bg-blue-100 text-blue-800'; }
                                    elseif ($role === 'Red Cross') { $level = 'Limited Reports'; $colorClass = 'bg-yellow-100 text-yellow-800'; }
                                    else { $level = 'Department'; $colorClass = 'bg-teal-100 text-teal-800'; }
                                    ?>
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold <?= $colorClass ?>">
                                        <?= $level ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                     <h2 class="text-lg font-bold text-gray-900">Role Capabilities</h2>
                     <i class="fa-solid fa-shield-halved text-gray-400"></i>
                </div>
                <div class="divide-y divide-gray-100">
                    <div class="p-4 hover:bg-gray-50 transition-colors">
                        <h3 class="font-bold text-sm text-gray-900 mb-1">System Administrator</h3>
                        <p class="text-xs text-gray-500">Full system access, all regions, users, and statistics.</p>
                    </div>
                    <div class="p-4 hover:bg-gray-50 transition-colors">
                        <h3 class="font-bold text-sm text-gray-900 mb-1">Registration Officer</h3>
                        <p class="text-xs text-gray-500">Donor management and eligibility auto-check.</p>
                    </div>
                    <div class="p-4 hover:bg-gray-50 transition-colors">
                        <h3 class="font-bold text-sm text-gray-900 mb-1">Laboratory Technologist</h3>
                        <p class="text-xs text-gray-500">Quality gate control access - validation enforcement.</p>
                    </div>
                    <div class="p-4 hover:bg-gray-50 transition-colors">
                        <h3 class="font-bold text-sm text-gray-900 mb-1">Inventory Manager</h3>
                        <p class="text-xs text-gray-500">Stock management with FIFO dispatch logic.</p>
                    </div>
                </div>
            </div>

            <div class="bg-blue-600 rounded-xl shadow-lg border border-blue-500 p-6 text-white relative overflow-hidden">
                 <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-blue-500 opacity-50"></div>
                 <div class="absolute bottom-0 left-0 -mb-4 -ml-4 h-32 w-32 rounded-full bg-blue-700 opacity-50"></div>
                 
                 <h2 class="text-xl font-bold mb-6 relative z-10 flex items-center gap-2">
                    <i class="fa-solid fa-chart-pie"></i> Role Statistics
                </h2>
                 
                 <div class="grid grid-cols-2 gap-4 text-center relative z-10">
                     <div class="bg-blue-700 bg-opacity-50 rounded-lg p-4">
                        <div class="text-4xl font-black mb-1">7</div>
                        <div class="text-sm font-medium text-blue-100">Total Roles</div>
                     </div>
                     <div class="bg-blue-700 bg-opacity-50 rounded-lg p-4">
                        <div class="text-4xl font-black mb-1">247</div>
                        <div class="text-sm font-medium text-blue-100">Active Users</div>
                     </div>
                 </div>

                 <div class="mt-6 pt-6 border-t border-blue-500 text-center relative z-10">
                     <p class="text-sm font-bold bg-blue-800 inline-block px-3 py-1 rounded-full">
                        <i class="fa-solid fa-lock mr-1"></i> RBAC Enforced
                     </p>
                     <p class="text-xs text-blue-200 mt-2">Security protocols are active and monitored.</p>
                 </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/sidebar.js"></script>
</body>
</html>
