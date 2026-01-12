<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';
require_once '../../includes/notification_functions.php';

require_role(['Registration Officer', 'System Administrator']);

$success = '';
$error = '';

// Handle donor registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize_input($_POST['full_name']);
    $gender = sanitize_input($_POST['gender']);
    $dob = sanitize_input($_POST['dob']);
    $blood_group = sanitize_input($_POST['blood_group']);
    $phone = sanitize_input($_POST['phone']);
    $email = sanitize_input($_POST['email'] ?? '');
    $address = sanitize_input($_POST['address'] ?? '');
    $weight_kg = floatval($_POST['weight_kg']);
    $blood_pressure = sanitize_input($_POST['blood_pressure']);
    $hemoglobin_level = floatval($_POST['hemoglobin_level']);
    $medical_history = sanitize_input($_POST['medical_history'] ?? '');
    
    // Prepare donor data for eligibility check
    $donor_data = [
        'date_of_birth' => $dob,
        'gender' => $gender,
        'weight_kg' => $weight_kg,
        'hemoglobin_level' => $hemoglobin_level,
        'last_donation_date' => null // New donor
    ];
    
    // Check eligibility
    $eligibility_result = check_donor_eligibility($donor_data);
    $eligibility = $eligibility_result['status'];
    $deferral_reason = $eligibility_result['eligible'] ? null : implode('; ', $eligibility_result['reasons']);
    
    // Insert donor
    $stmt = $conn->prepare("
        INSERT INTO donors (
            full_name, gender, date_of_birth, blood_group, phone, email, address,
            weight_kg, blood_pressure, hemoglobin_level, medical_history,
            eligibility, deferral_reason, registered_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $user_id = get_user_id();
    $stmt->bind_param(
        "sssssssddssssi",
        $full_name, $gender, $dob, $blood_group, $phone, $email, $address,
        $weight_kg, $blood_pressure, $hemoglobin_level, $medical_history,
        $eligibility, $deferral_reason, $user_id
    );
    
    if ($stmt->execute()) {
        $donor_id = $conn->insert_id;
        log_audit($user_id, "Registered new donor: $full_name (ID: $donor_id)");
        
        if ($eligibility === 'ELIGIBLE') {
            $success = "Donor registered successfully! Donor is ELIGIBLE for donation.";
        } else {
            $success = "Donor registered. Status: NOT ELIGIBLE. Reason: $deferral_reason";
        }
    } else {
        $error = "Failed to register donor. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Donor - BBMS</title>
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
    <?php render_sidebar('register-donor.php'); ?>
    
    <div class="main-content ml-0 md:ml-72 min-h-screen p-6 pt-20 md:pt-8 transition-all duration-300">
        <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                 <div class="flex items-center gap-3 mb-2">
                    <i class="fa-solid fa-user-plus text-3xl text-blood-700"></i>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Register New Donor</h1>
                </div>
                <p class="text-gray-500">Complete donor registration and initial health screening.</p>
            </div>
            <a href="donors.php" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-bold py-2 px-4 rounded-lg transition-colors shadow-sm flex items-center gap-2">
                <i class="fa-solid fa-users"></i> View All Donors
            </a>
        </div>

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

        <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="p-6 md:p-8">
                <form method="POST" action="" class="space-y-8">
                    
                    <!-- Section: Personal Information -->
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2 border-b border-gray-100 pb-2">
                            <i class="fa-solid fa-address-card text-blood-600"></i> Personal Information
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                                <input type="text" name="full_name" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2.5 border" required placeholder="John Doe">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Gender <span class="text-red-500">*</span></label>
                                <select name="gender" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2.5 border bg-white" required>
                                    <option value="">Select Gender</option>
                                    <option value="MALE">Male</option>
                                    <option value="FEMALE">Female</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth <span class="text-red-500">*</span></label>
                                <input type="date" name="dob" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2.5 border" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Blood Group <span class="text-red-500">*</span></label>
                                <select name="blood_group" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2.5 border bg-white" required>
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

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
                                <input type="tel" name="phone" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2.5 border" placeholder="+25078..." required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                <input type="email" name="email" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2.5 border" placeholder="donor@example.com">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                <textarea name="address" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2.5 border" rows="2" placeholder="Full residential address"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Health Screening -->
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2 border-b border-gray-100 pb-2">
                             <i class="fa-solid fa-heart-pulse text-blood-600"></i> Health Screening
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Weight (kg) <span class="text-red-500">*</span></label>
                                <input type="number" step="0.1" name="weight_kg" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2.5 border" placeholder="50.0" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Blood Pressure <span class="text-red-500">*</span></label>
                                <input type="text" name="blood_pressure" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2.5 border" placeholder="120/80" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Hemoglobin (g/dL) <span class="text-red-500">*</span></label>
                                <input type="number" step="0.1" name="hemoglobin_level" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2.5 border" placeholder="13.5" required>
                            </div>
                            
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Medical History</label>
                                <textarea name="medical_history" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-2.5 border" rows="3" placeholder="Any medical conditions, medications, or recent illnesses..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-5">
                        <p class="text-sm font-bold text-yellow-800 mb-2 flex items-center gap-2"><i class="fa-solid fa-clipboard-check"></i> Eligibility Criteria:</p>
                        <ul class="text-sm text-yellow-800 list-disc ml-5 space-y-1">
                            <li><span class="font-semibold">Age:</span> 18-65 years</li>
                            <li><span class="font-semibold">Weight:</span> Minimum 50 kg</li>
                            <li><span class="font-semibold">Hemoglobin:</span> Male ≥13.0 g/dL, Female ≥12.5 g/dL</li>
                            <li><span class="font-semibold">Blood Pressure:</span> Normal range (approx. 120/80)</li>
                            <li>No recent illnesses or active infections.</li>
                        </ul>
                    </div>

                    <div class="flex items-center justify-end pt-4">
                        <button type="submit" class="bg-blood-700 text-white font-bold py-3 px-8 rounded-lg hover:bg-blood-800 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center gap-2">
                             <i class="fa-solid fa-paper-plane"></i> Register Donor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../../assets/js/sidebar.js"></script>
</body>
</html>
