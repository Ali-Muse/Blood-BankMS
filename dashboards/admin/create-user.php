<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';

require_role(['System Administrator']);

$success = '';
$error = '';

// Handle user creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize_input($_POST['full_name']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password']; // Will be hashed
    $role_name = sanitize_input($_POST['role_name']);
    $phone = sanitize_input($_POST['phone']);
    $status = sanitize_input($_POST['status'] ?? 'ACTIVE');
    
    // Role-specific fields
    $hospital_name = ($role_name === 'Hospital User') ? sanitize_input($_POST['hospital_name']) : null;
    $organization_name = (in_array($role_name, ['Red Cross', 'Minister Of Health'])) ? sanitize_input($_POST['organization_name']) : null;
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert into users table
    $stmt = $conn->prepare("
        INSERT INTO users (full_name, email, password, role_name, phone, status, hospital_name, organization_name)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param(
        "ssssssss",
        $full_name, $email, $hashed_password, $role_name, $phone, $status, $hospital_name, $organization_name
    );
    
    if ($stmt->execute()) {
        $user_id = $conn->insert_id;
        
        // Log audit
        log_audit(get_user_id(), "Created new user: $full_name ($role_name)");
        
        $success = "User created successfully! Email: <strong>{$email}</strong>, Password: <strong>{$password}</strong> (Please share these credentials securely)";
    } else {
        if ($conn->errno === 1062) { // Duplicate entry
            $error = "Email address already exists!";
        } else {
            $error = "Failed to create user. Please try again.";
        }
    }
}

// Get all users
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - BBMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 font-sans text-gray-900">
    <?php render_sidebar('create-user.php'); ?>
    
    <div class="main-content ml-0 md:ml-72 min-h-screen p-6 pt-20 md:pt-8 transition-all duration-300">
        <div class="mb-8">
             <div class="flex items-center gap-3 mb-2">
                <i class="fa-solid fa-users-cog text-3xl text-blood-700"></i>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">User Management</h1>
            </div>
            <p class="text-gray-500">Create new accounts and manage existing system users.</p>
        </div>

        <?php if ($success): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-r mb-6 shadow-sm flex items-start gap-3">
                <i class="fa-solid fa-circle-check mt-1"></i>
                <div>
                    <p class="font-bold">Success</p>
                    <p><?= $success ?></p>
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

        <div class="grid lg:grid-cols-12 gap-8">
            <!-- Create User Form -->
            <div class="lg:col-span-5 md:col-span-12">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-24">
                     <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-gray-900">Create New User</h2>
                        <span class="bg-blood-100 text-blood-700 text-xs font-semibold px-2 py-1 rounded-full">Admin Only</span>
                    </div>
                
                    <form method="POST" action="" id="create-user-form" class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" name="full_name" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2.5 border" required placeholder="John Doe">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                            <input type="email" name="email" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2.5 border" required placeholder="user@example.com">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                            <input type="password" name="password" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2.5 border" required minlength="6" placeholder="******">
                            <p class="text-xs text-gray-500 mt-1">Minimum 6 characters</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                            <select name="role_name" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2.5 border bg-white" required id="role-select">
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
                                <input type="tel" name="phone" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2.5 border" placeholder="+25078..." required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                                <select name="status" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2.5 border bg-white" required>
                                    <option value="ACTIVE">Active</option>
                                    <option value="INACTIVE">Inactive</option>
                                    <option value="SUSPENDED">Suspended</option>
                                </select>
                            </div>
                        </div>

                        <!-- Hospital Name (only for Hospital User) -->
                        <div id="hospital-field" style="display: none;">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hospital Name <span class="text-red-500">*</span></label>
                            <input type="text" name="hospital_name" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2.5 border" placeholder="e.g., Kigali University Teaching Hospital">
                        </div>

                        <!-- Organization Name (only for Partners/Authority) -->
                        <div id="organization-field" style="display: none;">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Organization Name <span class="text-red-500">*</span></label>
                            <input type="text" name="organization_name" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2.5 border" placeholder="e.g., Rwanda Red Cross Society">
                        </div>

                        <button type="submit" class="w-full bg-blood-700 text-white font-bold py-3 px-4 rounded-lg hover:bg-blood-800 transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5 mt-2">
                             <i class="fa-solid fa-user-plus mr-2"></i> Create User
                        </button>
                    </form>
                </div>
            </div>

            <!-- Existing Users -->
            <div class="lg:col-span-7 md:col-span-12">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                        <h2 class="text-xl font-bold text-gray-900">Existing Users <span class="text-sm font-normal text-gray-500 ml-2">(<?= $users->num_rows ?> total)</span></h2>
                        <button class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-filter"></i></button>
                    </div>
                    
                    <div class="overflow-y-auto max-h-[800px] p-0">
                        <!-- Desktop Table View -->
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">User Details</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Role & Status</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php while ($user = $users->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-blood-100 flex items-center justify-center text-blood-700 font-bold">
                                                        <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($user['full_name']) ?></div>
                                                    <div class="text-sm text-gray-500"><?= $user['email'] ?></div>
                                                    <div class="text-xs text-gray-400 mt-0.5"><?= $user['phone'] ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900 font-medium mb-1"><?= $user['role_name'] ?></div>
                                            
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
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="#" class="text-indigo-600 hover:text-indigo-900 mr-3"><i class="fa-solid fa-pen-to-square"></i></a>
                                            <a href="#" class="text-red-600 hover:text-red-900"><i class="fa-solid fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/sidebar.js"></script>
    <script>
        // Show/hide role-specific fields
        document.getElementById('role-select').addEventListener('change', function() {
            const role = this.value;
            const hospitalField = document.getElementById('hospital-field');
            const organizationField = document.getElementById('organization-field');
            const hospitalInput = document.querySelector('input[name="hospital_name"]');
            const organizationInput = document.querySelector('input[name="organization_name"]');
            
            // Hide all role-specific fields first
            hospitalField.style.display = 'none';
            organizationField.style.display = 'none';
            hospitalInput.removeAttribute('required');
            organizationInput.removeAttribute('required');
            
            // Show relevant field based on role
            if (role === 'Hospital User') {
                hospitalField.style.display = 'block';
                hospitalInput.setAttribute('required', 'required');
            } else if (role === 'Red Cross' || role === 'Minister Of Health') {
                organizationField.style.display = 'block';
                organizationInput.setAttribute('required', 'required');
            }
        });
    </script>
</body>
</html>
