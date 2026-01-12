<?php
require_once 'includes/config.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize_input($_POST['full_name']);
    $gender = sanitize_input($_POST['gender']);
    $dob = sanitize_input($_POST['dob']);
    $blood_group = sanitize_input($_POST['blood_group']);
    $phone = sanitize_input($_POST['phone']);
    $email = sanitize_input($_POST['email']);
    
    // Check eligibility (must be 18+)
    $age = date_diff(date_create($dob), date_create('now'))->y;
    $eligibility = ($age >= 18) ? 'ELIGIBLE' : 'NOT_ELIGIBLE';
    
    $stmt = $conn->prepare("INSERT INTO donors (full_name, gender, date_of_birth, blood_group, phone, eligibility) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $full_name, $gender, $dob, $blood_group, $phone, $eligibility);
    
    if ($stmt->execute()) {
        $success = "Registration successful! " . ($eligibility === 'ELIGIBLE' ? "You are eligible to donate." : "You must be 18+ to donate.");
    } else {
        $error = "Registration failed. Please try again.";
    }
}
?>
<?php require_once 'includes/public-header.php'; ?>

<div class="bg-gray-50 min-h-screen py-12">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-10">
                <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4">Register as Blood Donor</h1>
                <p class="text-xl text-gray-600">Join thousands of heroes saving lives every day. It only takes a minute.</p>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
                <div class="bg-blood-50 px-8 py-6 border-b border-blood-100">
                    <h2 class="text-blood-800 font-bold flex items-center gap-2">
                        <i class="fa-solid fa-user-plus"></i> Donor Registration Form
                    </h2>
                </div>

                <div class="p-8">
                    <?php if ($success): ?>
                        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-md mb-8 flex items-start gap-3">
                            <i class="fa-solid fa-circle-check mt-1"></i>
                            <p><?= htmlspecialchars($success) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-md mb-8 flex items-start gap-3">
                            <i class="fa-solid fa-circle-exclamation mt-1"></i>
                            <p><?= htmlspecialchars($error) ?></p>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="space-y-6">
                        <!-- Full Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fa-solid fa-user text-gray-400"></i>
                                </div>
                                <input type="text" name="full_name" required 
                                    class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blood-200 focus:border-blood-500 transition-colors"
                                    placeholder="John Doe">
                            </div>
                        </div>

                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Gender -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Gender <span class="text-red-500">*</span></label>
                                <select name="gender" required class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blood-200 focus:border-blood-500 transition-colors bg-white">
                                    <option value="">Select Gender</option>
                                    <option value="MALE">Male</option>
                                    <option value="FEMALE">Female</option>
                                </select>
                            </div>

                            <!-- DOB -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth <span class="text-red-500">*</span></label>
                                <input type="date" name="dob" required 
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blood-200 focus:border-blood-500 transition-colors">
                            </div>
                        </div>

                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Blood Group -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Blood Group <span class="text-red-500">*</span></label>
                                <select name="blood_group" required class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blood-200 focus:border-blood-500 transition-colors bg-white">
                                    <option value="">Select Blood Group</option>
                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                </select>
                            </div>

                            <!-- Phone -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="fa-solid fa-phone text-gray-400"></i>
                                    </div>
                                    <input type="tel" name="phone" required 
                                        class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blood-200 focus:border-blood-500 transition-colors"
                                        placeholder="+1 234 567 8900">
                                </div>
                            </div>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fa-solid fa-envelope text-gray-400"></i>
                                </div>
                                <input type="email" name="email" 
                                    class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blood-200 focus:border-blood-500 transition-colors"
                                    placeholder="john@example.com">
                            </div>
                        </div>

                        <!-- Submit Button -->
                         <button type="submit" class="w-full bg-blood-600 text-white font-bold py-4 rounded-xl hover:bg-blood-700 transition-colors shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 text-lg">
                            Complete Registration
                        </button>
                    </form>
                </div>
            </div>

             <!-- Eligibility Reminder -->
             <div class="mt-8 p-6 bg-yellow-50 rounded-xl border border-yellow-100 flex items-start gap-4">
                <div class="text-yellow-600 text-2xl"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <div>
                     <h3 class="font-bold text-yellow-800 mb-2">Eligibility Checklist</h3>
                    <ul class="text-yellow-800/80 text-sm space-y-1 list-disc list-inside">
                        <li>Must be at least 18 years old</li>
                        <li>Must weigh at least 50kg</li>
                        <li>Must be in good general health</li>
                        <li>No recent illnesses or medications</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/public-footer.php'; ?>
