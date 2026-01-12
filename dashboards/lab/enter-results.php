<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';
require_once '../../includes/notification_functions.php';

require_role(['Laboratory Technologist', 'System Administrator']);

$success = '';
$error = '';
$unit = null;

// Get unit_id from URL
$unit_id = isset($_GET['unit_id']) ? intval($_GET['unit_id']) : 0;

if ($unit_id) {
    // Get blood unit details
    $stmt = $conn->prepare("
        SELECT bu.*, d.full_name, d.phone, lt.test_id, lt.test_hiv, lt.test_hbv, lt.test_hcv, lt.test_syphilis
        FROM blood_units bu
        JOIN donors d ON bu.donor_id = d.donor_id
        LEFT JOIN lab_tests lt ON bu.unit_id = lt.unit_id
        WHERE bu.unit_id = ?
    ");
    $stmt->bind_param("i", $unit_id);
    $stmt->execute();
    $unit = $stmt->get_result()->fetch_assoc();
}

// Handle test results submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_results'])) {
    $unit_id = intval($_POST['unit_id']);
    $test_hiv = sanitize_input($_POST['test_hiv']);
    $test_hbv = sanitize_input($_POST['test_hbv']);
    $test_hcv = sanitize_input($_POST['test_hcv']);
    $test_syphilis = sanitize_input($_POST['test_syphilis']);
    $notes = sanitize_input($_POST['notes'] ?? '');
    
    // Determine overall result (ALL tests must be NEGATIVE for approval)
    $all_negative = ($test_hiv === 'NEGATIVE' && $test_hbv === 'NEGATIVE' && 
                     $test_hcv === 'NEGATIVE' && $test_syphilis === 'NEGATIVE');
    
    $test_result = $all_negative ? 'APPROVED' : 'REJECTED';
    $blood_status = $all_negative ? 'APPROVED' : 'REJECTED';
    
    // Build rejection reason if any test is positive
    $rejection_reason = '';
    if (!$all_negative) {
        $positive_tests = [];
        if ($test_hiv === 'POSITIVE') $positive_tests[] = 'HIV';
        if ($test_hbv === 'POSITIVE') $positive_tests[] = 'Hepatitis B';
        if ($test_hcv === 'POSITIVE') $positive_tests[] = 'Hepatitis C';
        if ($test_syphilis === 'POSITIVE') $positive_tests[] = 'Syphilis';
        $rejection_reason = 'Positive test(s): ' . implode(', ', $positive_tests);
    }
    
    $user_id = get_user_id();
    $tested_at = date('Y-m-d H:i:s');
    
    // Update lab test record
    $stmt = $conn->prepare("
        UPDATE lab_tests 
        SET test_hiv = ?, test_hbv = ?, test_hcv = ?, test_syphilis = ?,
            test_result = ?, rejection_reason = ?, tested_by = ?, tested_at = ?, notes = ?
        WHERE unit_id = ?
    ");
    $stmt->bind_param(
        "sssssssssi",
        $test_hiv, $test_hbv, $test_hcv, $test_syphilis,
        $test_result, $rejection_reason, $user_id, $tested_at, $notes, $unit_id
    );
    
    if ($stmt->execute()) {
        // Update blood unit status
        $stmt = $conn->prepare("UPDATE blood_units SET status = ? WHERE unit_id = ?");
        $stmt->bind_param("si", $blood_status, $unit_id);
        $stmt->execute();
        
        // Log audit
        log_audit($user_id, "Tested blood unit #{$unit_id}: {$test_result}");
        
        // Send notification to inventory manager if approved
        if ($test_result === 'APPROVED') {
            $stmt = $conn->prepare("SELECT barcode, blood_group FROM blood_units WHERE unit_id = ?");
            $stmt->bind_param("i", $unit_id);
            $stmt->execute();
            $unit_info = $stmt->get_result()->fetch_assoc();
            
            send_role_notification(
                'Inventory Manager',
                'SUCCESS',
                'Blood Unit Approved',
                "Blood unit {$unit_info['barcode']} ({$unit_info['blood_group']}) has been approved and is ready for inventory.",
                'dashboards/inventory/blood-inventory.php'
            );
            
            $success = "Test results saved! Blood unit APPROVED and sent to inventory.";
        } else {
            $success = "Test results saved. Blood unit REJECTED: {$rejection_reason}";
        }
        
        // Redirect to samples queue after 2 seconds
        header("refresh:2;url=samples-queue.php");
    } else {
        $error = "Failed to save test results. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Test Results - BBMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 font-sans text-gray-900">
    <?php render_sidebar('enter-results.php'); ?>
    
    <div class="main-content ml-0 md:ml-72 min-h-screen p-6 pt-20 md:pt-8 transition-all duration-300">
        <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                 <div class="flex items-center gap-3 mb-2">
                    <i class="fa-solid fa-microscope text-3xl text-blood-700"></i>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Enter Test Results</h1>
                </div>
                <p class="text-gray-500">Record laboratory screening results for blood safety.</p>
            </div>
            <a href="samples-queue.php" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-bold py-2 px-4 rounded-lg transition-colors shadow-sm flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Back to Queue
            </a>
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

        <?php if ($unit): ?>
            <!-- Blood Unit Info -->
            <div class="bg-gradient-to-br from-blood-50 to-white rounded-xl shadow-sm border border-blood-100 p-6 mb-8 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-10">
                    <i class="fa-solid fa-file-medical text-9xl text-blood-700"></i>
                </div>
                <h2 class="text-xl font-bold text-blood-900 mb-4 flex items-center gap-2 relative z-10">
                    <i class="fa-solid fa-notes-medical"></i> Blood Unit Information
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative z-10">
                    <div class="bg-white/60 p-4 rounded-lg border border-blood-100">
                        <p class="text-xs text-gray-500 mb-1 font-semibold uppercase tracking-wide">Barcode</p>
                        <p class="font-mono text-xl font-bold text-gray-900"><?= $unit['barcode'] ?></p>
                    </div>
                    <div class="bg-white/60 p-4 rounded-lg border border-blood-100">
                        <p class="text-xs text-gray-500 mb-1 font-semibold uppercase tracking-wide">Blood Group</p>
                        <p class="text-xl font-bold text-blood-700"><?= $unit['blood_group'] ?></p>
                    </div>
                    <div class="bg-white/60 p-4 rounded-lg border border-blood-100">
                        <p class="text-xs text-gray-500 mb-1 font-semibold uppercase tracking-wide">Volume</p>
                        <p class="text-xl font-bold text-gray-900"><?= $unit['volume_ml'] ?> ml</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4 relative z-10">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Donor</p>
                        <p class="font-medium text-gray-900"><?= htmlspecialchars($unit['full_name']) ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Collection Date</p>
                        <p class="font-medium text-gray-900"><?= date('Y-m-d H:i', strtotime($unit['collection_date'])) ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Expiry Date</p>
                        <p class="font-medium text-gray-900"><?= $unit['expiry_date'] ?></p>
                    </div>
                </div>
            </div>

            <!-- Test Results Form -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 md:p-8">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-gray-500"></i> Laboratory Test Results
                </h2>
                
                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-8 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fa-solid fa-triangle-exclamation text-yellow-600"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-bold text-yellow-800">Quality Gate Control</p>
                            <p class="text-sm text-yellow-700 mt-1">
                                ALL tests must be <strong>NEGATIVE</strong> for the blood unit to be approved. If ANY test is <strong>POSITIVE</strong>, the unit will be automatically <strong>REJECTED</strong>.
                            </p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="" class="space-y-6">
                    <input type="hidden" name="unit_id" value="<?= $unit['unit_id'] ?>">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">HIV Test <span class="text-red-500">*</span></label>
                            <select name="test_hiv" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-3 border bg-white text-lg" required>
                                <option value="">Select Result</option>
                                <option value="NEGATIVE">NEGATIVE</option>
                                <option value="POSITIVE">POSITIVE</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Hepatitis B (HBV) Test <span class="text-red-500">*</span></label>
                            <select name="test_hbv" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-3 border bg-white text-lg" required>
                                <option value="">Select Result</option>
                                <option value="NEGATIVE">NEGATIVE</option>
                                <option value="POSITIVE">POSITIVE</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Hepatitis C (HCV) Test <span class="text-red-500">*</span></label>
                            <select name="test_hcv" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-3 border bg-white text-lg" required>
                                <option value="">Select Result</option>
                                <option value="NEGATIVE">NEGATIVE</option>
                                <option value="POSITIVE">POSITIVE</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Syphilis Test <span class="text-red-500">*</span></label>
                            <select name="test_syphilis" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-3 border bg-white text-lg" required>
                                <option value="">Select Result</option>
                                <option value="NEGATIVE">NEGATIVE</option>
                                <option value="POSITIVE">POSITIVE</option>
                            </select>
                        </div>
                    </div>

                    <div class="pt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Technician Notes</label>
                        <textarea name="notes" class="w-full rounded-lg border-gray-300 focus:border-blood-500 focus:ring focus:ring-blood-200 transition-shadow p-3 border" rows="3" placeholder="Any additional observations or remarks..."></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-100">
                        <a href="samples-queue.php" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-bold hover:bg-gray-50 transition-colors">
                            Cancel
                        </a>
                        <button type="submit" name="submit_results" class="px-8 py-3 bg-blood-700 text-white rounded-lg font-bold hover:bg-blood-800 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all flex items-center gap-2">
                            <i class="fa-solid fa-check-double"></i> Submit Test Results
                        </button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-xl shadow-md border border-gray-100 p-12 text-center">
                <div class="h-24 w-24 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6 text-red-500">
                    <i class="fa-solid fa-circle-xmark text-4xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Blood Unit Not Found</h2>
                <p class="text-gray-500 mb-8 max-w-md mx-auto">The requested blood unit ID is invalid or could not be found in the database. Please check the samples queue.</p>
                <a href="samples-queue.php" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-bold rounded-lg text-white bg-blood-600 hover:bg-blood-700 transition-colors">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Go to Samples Queue
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="../../assets/js/sidebar.js"></script>
</body>
</html>
