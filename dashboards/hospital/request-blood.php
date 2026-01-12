<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';

require_role(['Hospital User']);

$page_title = 'Request Blood';
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hospital_user_id = get_user_id();
    $hospital_name = get_user_name(); // Or fetch from profile if stored differently
    // Actually users table has hospital_name column for Hospital User role.
    // Fetch real hospital name from DB
    $stmt = $conn->prepare("SELECT hospital_name FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $hospital_user_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $real_hospital_name = $res['hospital_name'] ?? 'Unknown Hospital';
    $stmt->close();

    $blood_group = $_POST['blood_group'];
    $quantity = (int)$_POST['quantity'];
    $request_type = $_POST['request_type'] ?? 'NORMAL';
    $patient_name = trim($_POST['patient_name']);
    $patient_id = trim($_POST['patient_id']);
    $reason = trim($_POST['reason']);
    
    // Generate Request Number (REQ-YYYYMMDD-XXXX)
    $req_num = 'REQ-' . date('Ymd') . '-' . rand(1000, 9999);
    
    // Insert
    $query = "INSERT INTO blood_requests (request_number, hospital_user_id, hospital_name, blood_group, quantity, request_type, patient_name, patient_id, reason, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'PENDING')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sisssssss", $req_num, $hospital_user_id, $real_hospital_name, $blood_group, $quantity, $request_type, $patient_name, $patient_id, $reason);
    
    if ($stmt->execute()) {
        $success_msg = "Blood request submitted successfully! Request ID: " . $req_num;
    } else {
        $error_msg = "Error submitting request: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - BBMS</title>
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
    <?php render_sidebar('request-blood.php'); ?>
    
    <div class="main-content ml-0 md:ml-72 min-h-screen p-6 pt-20 md:pt-8 transition-all duration-300">
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-blood-700 tracking-tight">Request Blood Units</h1>
            <p class="text-gray-500 mt-2">Submit a formal request for blood units.</p>
        </div>

        <?php if ($success_msg): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p class="font-bold">Success</p>
            <p><?= $success_msg ?></p>
        </div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p class="font-bold">Error</p>
            <p><?= $error_msg ?></p>
        </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden max-w-3xl">
            <div class="p-6 md:p-8">
                <form action="" method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Blood Group Required</label>
                            <select name="blood_group" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blood-600 focus:ring focus:ring-blood-200 p-3 border">
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Quantity (Units)</label>
                            <input type="number" name="quantity" min="1" max="20" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blood-600 focus:ring focus:ring-blood-200 p-3 border">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Patient Name (Optional)</label>
                            <input type="text" name="patient_name" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blood-600 focus:ring focus:ring-blood-200 p-3 border">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Patient ID / File No (Optional)</label>
                            <input type="text" name="patient_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blood-600 focus:ring focus:ring-blood-200 p-3 border">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Medical Reason / Justification</label>
                        <textarea name="reason" rows="3" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blood-600 focus:ring focus:ring-blood-200 p-3 border" placeholder="e.g., Surgery, Anemia, Trauma..."></textarea>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <label class="flex items-center space-x-3">
                            <input type="checkbox" name="request_type" value="EMERGENCY" class="rounded text-red-600 focus:ring-red-500 h-5 w-5">
                            <span class="text-gray-900 font-bold">Mark as Critical / Emergency Request</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1 ml-8">Only check this if the patient is in critical condition. Emergency requests are flagged for immediate review.</p>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button type="submit" class="bg-blood-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-blood-700 transition shadow-lg transform hover:-translate-y-0.5">
                            <i class="fas fa-paper-plane mr-2"></i> Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="../../assets/js/sidebar.js"></script>
</body>
</html>
