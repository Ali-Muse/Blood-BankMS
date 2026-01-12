<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';

require_role(['System Administrator']);

$success = '';
$error = '';

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // CREATE USER
    if ($action === 'create') {
        $full_name = sanitize_input($_POST['full_name']);
        $email = sanitize_input($_POST['email']);
        $password = $_POST['password'];
        $role_name = sanitize_input($_POST['role_name']);
        $phone = sanitize_input($_POST['phone']);
        $status = sanitize_input($_POST['status'] ?? 'ACTIVE');
        
        $hospital_name = ($role_name === 'Hospital User') ? sanitize_input($_POST['hospital_name']) : null;
        $organization_name = (in_array($role_name, ['Red Cross', 'Minister Of Health'])) ? sanitize_input($_POST['organization_name']) : null;
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role_name, phone, status, hospital_name, organization_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $full_name, $email, $hashed_password, $role_name, $phone, $status, $hospital_name, $organization_name);
        
        if ($stmt->execute()) {
            log_audit(get_user_id(), "Created new user: $full_name ($role_name)");
            $success = "User created successfully!";
        } else {
            $error = ($conn->errno === 1062) ? "Email address already exists!" : "Failed to create user.";
        }
    }
    
    // UPDATE USER
    elseif ($action === 'update') {
        $user_id = intval($_POST['user_id']);
        $full_name = sanitize_input($_POST['full_name']);
        $email = sanitize_input($_POST['email']);
        $role_name = sanitize_input($_POST['role_name']);
        $phone = sanitize_input($_POST['phone']);
        $status = sanitize_input($_POST['status']);
        
        $hospital_name = ($role_name === 'Hospital User') ? sanitize_input($_POST['hospital_name']) : null;
        $organization_name = (in_array($role_name, ['Red Cross', 'Minister Of Health'])) ? sanitize_input($_POST['organization_name']) : null;
        
        $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, role_name=?, phone=?, status=?, hospital_name=?, organization_name=? WHERE user_id=?");
        $stmt->bind_param("sssssssi", $full_name, $email, $role_name, $phone, $status, $hospital_name, $organization_name, $user_id);
        
        if ($stmt->execute()) {
            log_audit(get_user_id(), "Updated user: $full_name (ID: $user_id)");
            $success = "User updated successfully!";
        } else {
            $error = "Failed to update user.";
        }
    }
    
    // DELETE USER
    elseif ($action === 'delete') {
        $user_id = intval($_POST['user_id']);
        $stmt = $conn->prepare("UPDATE users SET status='INACTIVE' WHERE user_id=?");
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            log_audit(get_user_id(), "Deactivated user ID: $user_id");
            $success = "User deactivated successfully!";
        } else {
            $error = "Failed to deactivate user.";
        }
    }
    
    // BULK ACTION
    elseif ($action === 'bulk') {
        $user_ids = $_POST['user_ids'] ?? [];
        $bulk_action = $_POST['bulk_action'] ?? '';
        
        if (!empty($user_ids) && in_array($bulk_action, ['activate', 'deactivate'])) {
            $status = ($bulk_action === 'activate') ? 'ACTIVE' : 'INACTIVE';
            $placeholders = implode(',', array_fill(0, count($user_ids), '?'));
            $types = str_repeat('i', count($user_ids));
            
            $stmt = $conn->prepare("UPDATE users SET status='$status' WHERE user_id IN ($placeholders)");
            $stmt->bind_param($types, ...$user_ids);
            
            if ($stmt->execute()) {
                $count = $stmt->affected_rows;
                log_audit(get_user_id(), "Bulk $bulk_action: $count users");
                $success = "$count user(s) {$bulk_action}d successfully!";
            }
        }
    }
}

// Get filters
$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';
$status_filter = $_GET['status'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build query
$where = [];
$params = [];
$types = '';

if ($search) {
    $where[] = "(full_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

if ($role_filter) {
    $where[] = "role_name = ?";
    $params[] = $role_filter;
    $types .= 's';
}

if ($status_filter) {
    $where[] = "status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

$where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total count
$count_query = "SELECT COUNT(*) as total FROM users $where_clause";
if (!empty($params)) {
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->bind_param($types, ...$params);
    $count_stmt->execute();
    $total_users = $count_stmt->get_result()->fetch_assoc()['total'];
} else {
    $total_users = $conn->query($count_query)->fetch_assoc()['total'];
}
$total_pages = ceil($total_users / $per_page);

// Get users
$query = "SELECT * FROM users $where_clause ORDER BY created_at DESC LIMIT ? OFFSET ?";
$all_params = array_merge($params, [$per_page, $offset]);
$all_types = $types . 'ii';

$stmt = $conn->prepare($query);
$stmt->bind_param($all_types, ...$all_params);
$stmt->execute();
$users = $stmt->get_result();

// Get statistics
$stats = [
    'total' => $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'],
    'active' => $conn->query("SELECT COUNT(*) as count FROM users WHERE status='ACTIVE'")->fetch_assoc()['count'],
    'inactive' => $conn->query("SELECT COUNT(*) as count FROM users WHERE status='INACTIVE'")->fetch_assoc()['count'],
];

// Get users by role
$role_stats = [];
$role_query = $conn->query("SELECT role_name, COUNT(*) as count FROM users GROUP BY role_name");
while ($row = $role_query->fetch_assoc()) {
    $role_stats[$row['role_name']] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - BBMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        blood: { 600: '#dc2626', 700: '#b91c1c', 800: '#991b1b' }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-sans text-gray-900">
    <?php render_sidebar('users.php'); ?>
    
    <div class="main-content ml-0 md:ml-72 min-h-screen p-6 pt-20 md:pt-8 transition-all duration-300">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-2">
                <i class="fa-solid fa-users-cog text-3xl text-blood-700"></i>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">User Management</h1>
            </div>
            <p class="text-gray-500">Manage system users, roles, and permissions</p>
        </div>

        <!-- Alerts -->
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

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 border-l-4 border-l-blue-600">
                <p class="text-sm font-medium text-gray-500 mb-1">Total Users</p>
                <h2 class="text-3xl font-extrabold text-gray-900"><?= number_format($stats['total']) ?></h2>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 border-l-4 border-l-green-600">
                <p class="text-sm font-medium text-gray-500 mb-1">Active Users</p>
                <h2 class="text-3xl font-extrabold text-green-600"><?= number_format($stats['active']) ?></h2>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 border-l-4 border-l-gray-400">
                <p class="text-sm font-medium text-gray-500 mb-1">Inactive Users</p>
                <h2 class="text-3xl font-extrabold text-gray-600"><?= number_format($stats['inactive']) ?></h2>
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Toolbar -->
            <div class="p-6 border-b border-gray-100 bg-gray-50">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <h2 class="text-xl font-bold text-gray-900">All Users <span class="text-sm font-normal text-gray-500">(<?= number_format($total_users) ?> found)</span></h2>
                    <button onclick="openCreateModal()" class="bg-blood-700 hover:bg-blood-800 text-white font-bold py-2 px-4 rounded-lg transition-colors shadow-md flex items-center gap-2">
                        <i class="fa-solid fa-user-plus"></i> Create New User
                    </button>
                </div>

                <!-- Filters -->
                <form method="GET" class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name, email, phone..." class="rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 p-2.5 border">
                    
                    <select name="role" class="rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 p-2.5 border bg-white">
                        <option value="">All Roles</option>
                        <option value="System Administrator" <?= $role_filter === 'System Administrator' ? 'selected' : '' ?>>System Administrator</option>
                        <option value="Registration Officer" <?= $role_filter === 'Registration Officer' ? 'selected' : '' ?>>Registration Officer</option>
                        <option value="Laboratory Technologist" <?= $role_filter === 'Laboratory Technologist' ? 'selected' : '' ?>>Laboratory Technologist</option>
                        <option value="Inventory Manager" <?= $role_filter === 'Inventory Manager' ? 'selected' : '' ?>>Inventory Manager</option>
                        <option value="Hospital User" <?= $role_filter === 'Hospital User' ? 'selected' : '' ?>>Hospital User</option>
                        <option value="Red Cross" <?= $role_filter === 'Red Cross' ? 'selected' : '' ?>>Red Cross</option>
                        <option value="Minister Of Health" <?= $role_filter === 'Minister Of Health' ? 'selected' : '' ?>>Minister Of Health</option>
                    </select>
                    
                    <select name="status" class="rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 p-2.5 border bg-white">
                        <option value="">All Statuses</option>
                        <option value="ACTIVE" <?= $status_filter === 'ACTIVE' ? 'selected' : '' ?>>Active</option>
                        <option value="INACTIVE" <?= $status_filter === 'INACTIVE' ? 'selected' : '' ?>>Inactive</option>
                        <option value="SUSPENDED" <?= $status_filter === 'SUSPENDED' ? 'selected' : '' ?>>Suspended</option>
                    </select>
                    
                    <button type="submit" class="bg-gray-700 hover:bg-gray-800 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                        <i class="fa-solid fa-filter mr-2"></i> Apply Filters
                    </button>
                </form>

                <!-- Bulk Actions -->
                <div id="bulk-actions" class="mt-4 hidden">
                    <form method="POST" onsubmit="return confirmBulkAction()" class="flex items-center gap-4">
                        <input type="hidden" name="action" value="bulk">
                        <input type="hidden" name="user_ids" id="bulk-user-ids">
                        <span class="text-sm font-medium text-gray-700"><span id="selected-count">0</span> selected</span>
                        <select name="bulk_action" required class="rounded-lg border-gray-300 p-2 border bg-white text-sm">
                            <option value="">Choose action...</option>
                            <option value="activate">Activate</option>
                            <option value="deactivate">Deactivate</option>
                        </select>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg text-sm">Apply</button>
                        <button type="button" onclick="clearSelection()" class="text-gray-600 hover:text-gray-800 text-sm">Clear</button>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)" class="rounded border-gray-300 text-blood-600 focus:ring-blood-500">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">User Details</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Role & Status</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while ($user = $users->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <input type="checkbox" class="user-checkbox rounded border-gray-300 text-blood-600 focus:ring-blood-500" value="<?= $user['user_id'] ?>" onchange="updateBulkActions()">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-blood-100 flex items-center justify-center text-blood-700 font-bold">
                                                <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($user['full_name']) ?></div>
                                            <div class="text-sm text-gray-500"><?= htmlspecialchars($user['email']) ?></div>
                                            <div class="text-xs text-gray-400 mt-0.5"><?= htmlspecialchars($user['phone']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 font-medium mb-1"><?= htmlspecialchars($user['role_name']) ?></div>
                                    <?php if ($user['hospital_name']): ?>
                                        <div class="text-xs text-blue-600 mb-1"><i class="fa-solid fa-hospital mr-1"></i> <?= htmlspecialchars($user['hospital_name']) ?></div>
                                    <?php endif; ?>
                                    <?php if ($user['organization_name']): ?>
                                        <div class="text-xs text-purple-600 mb-1"><i class="fa-solid fa-building mr-1"></i> <?= htmlspecialchars($user['organization_name']) ?></div>
                                    <?php endif; ?>
                                    <?php
                                    $status_class = match($user['status']) {
                                        'ACTIVE' => 'bg-green-100 text-green-800',
                                        'INACTIVE' => 'bg-gray-100 text-gray-800',
                                        'SUSPENDED' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                    ?>
                                    <span class="inline-flex px-2 text-xs font-semibold leading-5 rounded-full <?= $status_class ?>">
                                        <?= $user['status'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('M d, Y', strtotime($user['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button onclick='openEditModal(<?= json_encode($user) ?>)' class="text-indigo-600 hover:text-indigo-900 mr-3" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button onclick="deleteUser(<?= $user['user_id'] ?>, '<?= htmlspecialchars($user['full_name']) ?>')" class="text-red-600 hover:text-red-900" title="Deactivate">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing <?= $offset + 1 ?> to <?= min($offset + $per_page, $total_users) ?> of <?= $total_users ?> users
                        </div>
                        <div class="flex gap-2">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($role_filter) ?>&status=<?= urlencode($status_filter) ?>" class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 text-gray-700">Previous</a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($role_filter) ?>&status=<?= urlencode($status_filter) ?>" class="px-3 py-1 rounded <?= $i === $page ? 'bg-blood-700 text-white' : 'bg-gray-200 hover:bg-gray-300 text-gray-700' ?>"><?= $i ?></a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($role_filter) ?>&status=<?= urlencode($status_filter) ?>" class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 text-gray-700">Next</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div id="user-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center sticky top-0 bg-white">
                <h3 id="modal-title" class="text-xl font-bold text-gray-900">Create New User</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
            
            <form method="POST" id="user-form" class="p-6 space-y-5">
                <input type="hidden" name="action" id="form-action" value="create">
                <input type="hidden" name="user_id" id="form-user-id">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="full_name" id="form-full-name" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 p-2.5 border" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="form-email" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 p-2.5 border" required>
                </div>

                <div id="password-field">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" id="form-password" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 p-2.5 border" minlength="6">
                    <p class="text-xs text-gray-500 mt-1">Minimum 6 characters</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                    <select name="role_name" id="form-role" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 p-2.5 border bg-white" required onchange="toggleRoleFields()">
                        <option value="">Select Role</option>
                        <option value="System Administrator">System Administrator</option>
                        <option value="Registration Officer">Registration Officer</option>
                        <option value="Laboratory Technologist">Laboratory Technologist</option>
                        <option value="Inventory Manager">Inventory Manager</option>
                        <option value="Hospital User">Hospital User</option>
                        <option value="Red Cross">Red Cross (Partner)</option>
                        <option value="Minister Of Health">Minister Of Health (Authority)</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone <span class="text-red-500">*</span></label>
                        <input type="tel" name="phone" id="form-phone" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 p-2.5 border" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                        <select name="status" id="form-status" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 p-2.5 border bg-white" required>
                            <option value="ACTIVE">Active</option>
                            <option value="INACTIVE">Inactive</option>
                            <option value="SUSPENDED">Suspended</option>
                        </select>
                    </div>
                </div>

                <div id="hospital-field" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hospital Name <span class="text-red-500">*</span></label>
                    <input type="text" name="hospital_name" id="form-hospital" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 p-2.5 border">
                </div>

                <div id="organization-field" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Organization Name <span class="text-red-500">*</span></label>
                    <input type="text" name="organization_name" id="form-organization" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 p-2.5 border">
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 bg-blood-700 text-white font-bold py-3 px-4 rounded-lg hover:bg-blood-800 transition-all shadow-md">
                        <i class="fa-solid fa-save mr-2"></i> Save User
                    </button>
                    <button type="button" onclick="closeModal()" class="px-6 bg-gray-200 text-gray-700 font-bold py-3 rounded-lg hover:bg-gray-300 transition-all">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../assets/js/sidebar.js"></script>
    <script>
        function openCreateModal() {
            document.getElementById('modal-title').textContent = 'Create New User';
            document.getElementById('form-action').value = 'create';
            document.getElementById('user-form').reset();
            document.getElementById('password-field').style.display = 'block';
            document.getElementById('form-password').required = true;
            document.getElementById('user-modal').classList.remove('hidden');
            document.getElementById('user-modal').classList.add('flex');
        }

        function openEditModal(user) {
            document.getElementById('modal-title').textContent = 'Edit User';
            document.getElementById('form-action').value = 'update';
            document.getElementById('form-user-id').value = user.user_id;
            document.getElementById('form-full-name').value = user.full_name;
            document.getElementById('form-email').value = user.email;
            document.getElementById('form-role').value = user.role_name;
            document.getElementById('form-phone').value = user.phone;
            document.getElementById('form-status').value = user.status;
            document.getElementById('form-hospital').value = user.hospital_name || '';
            document.getElementById('form-organization').value = user.organization_name || '';
            document.getElementById('password-field').style.display = 'none';
            document.getElementById('form-password').required = false;
            toggleRoleFields();
            document.getElementById('user-modal').classList.remove('hidden');
            document.getElementById('user-modal').classList.add('flex');
        }

        function closeModal() {
            document.getElementById('user-modal').classList.add('hidden');
            document.getElementById('user-modal').classList.remove('flex');
        }

        function toggleRoleFields() {
            const role = document.getElementById('form-role').value;
            const hospitalField = document.getElementById('hospital-field');
            const organizationField = document.getElementById('organization-field');
            const hospitalInput = document.getElementById('form-hospital');
            const organizationInput = document.getElementById('form-organization');
            
            hospitalField.style.display = 'none';
            organizationField.style.display = 'none';
            hospitalInput.removeAttribute('required');
            organizationInput.removeAttribute('required');
            
            if (role === 'Hospital User') {
                hospitalField.style.display = 'block';
                hospitalInput.setAttribute('required', 'required');
            } else if (role === 'Red Cross' || role === 'Minister Of Health') {
                organizationField.style.display = 'block';
                organizationInput.setAttribute('required', 'required');
            }
        }

        function deleteUser(userId, userName) {
            if (confirm(`Are you sure you want to deactivate ${userName}?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="user_id" value="${userId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function toggleSelectAll(checkbox) {
            document.querySelectorAll('.user-checkbox').forEach(cb => {
                cb.checked = checkbox.checked;
            });
            updateBulkActions();
        }

        function updateBulkActions() {
            const checkboxes = document.querySelectorAll('.user-checkbox:checked');
            const count = checkboxes.length;
            const bulkActions = document.getElementById('bulk-actions');
            
            if (count > 0) {
                bulkActions.classList.remove('hidden');
                document.getElementById('selected-count').textContent = count;
                const ids = Array.from(checkboxes).map(cb => cb.value);
                document.getElementById('bulk-user-ids').value = JSON.stringify(ids);
            } else {
                bulkActions.classList.add('hidden');
            }
        }

        function clearSelection() {
            document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = false);
            document.getElementById('select-all').checked = false;
            updateBulkActions();
        }

        function confirmBulkAction() {
            const action = document.querySelector('select[name="bulk_action"]').value;
            const count = document.querySelectorAll('.user-checkbox:checked').length;
            return confirm(`Are you sure you want to ${action} ${count} user(s)?`);
        }
    </script>
</body>
</html>
