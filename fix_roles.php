<?php
// Script to fix role permissions in placeholder pages

$fixes = [
    // Registration Officer pages
    'dashboards/registration/notifications.php' => "['Registration Officer', 'System Administrator']",
    'dashboards/registration/eligibility.php' => "['Registration Officer', 'System Administrator']",
    'dashboards/registration/appointments.php' => "['Registration Officer', 'System Administrator']",
    
    // Lab Technologist pages
    'dashboards/lab/test-history.php' => "['Laboratory Technologist', 'System Administrator']",
    'dashboards/lab/rejected-units.php' => "['Laboratory Technologist', 'System Administrator']",
    'dashboards/lab/notifications.php' => "['Laboratory Technologist', 'System Administrator']",
    'dashboards/lab/lab-reports.php' => "['Laboratory Technologist', 'System Administrator']",
    'dashboards/lab/approved-units.php' => "['Laboratory Technologist', 'System Administrator']",
    
    // Hospital User pages
    'dashboards/hospital/track-requests.php' => "['Hospital User', 'System Administrator']",
    'dashboards/hospital/notifications.php' => "['Hospital User', 'System Administrator']",
    'dashboards/hospital/history.php' => "['Hospital User', 'System Administrator']",
    'dashboards/hospital/emergency-requests.php' => "['Hospital User', 'System Administrator']",
    'dashboards/hospital/availability.php' => "['Hospital User', 'System Administrator']",
    
    // Partner (Red Cross) pages
    'dashboards/partner/shortage-alerts.php' => "['Red Cross', 'System Administrator']",
    'dashboards/partner/reports.php' => "['Red Cross', 'System Administrator']",
    'dashboards/partner/notifications.php' => "['Red Cross', 'System Administrator']",
    'dashboards/partner/mobilization.php' => "['Red Cross', 'System Administrator']",
    'dashboards/partner/emergency-support.php' => "['Red Cross', 'System Administrator']",
    'dashboards/partner/campaigns.php' => "['Red Cross', 'System Administrator']",
    
    // Authority (Minister) pages
    'dashboards/authority/trends.php' => "['Minister Of Health', 'System Administrator']",
    'dashboards/authority/statistics.php' => "['Minister Of Health', 'System Administrator']",
    'dashboards/authority/safety-reports.php' => "['Minister Of Health', 'System Administrator']",
    'dashboards/authority/regional-stock.php' => "['Minister Of Health', 'System Administrator']",
    'dashboards/authority/emergency-reports.php' => "['Minister Of Health', 'System Administrator']",
    'dashboards/authority/compliance.php' => "['Minister Of Health', 'System Administrator']",
];

$base_path = 'C:/xampp/htdocs/Blood BankMS/';
$fixed_count = 0;
$error_count = 0;

foreach ($fixes as $file => $new_role) {
    $full_path = $base_path . $file;
    
    if (file_exists($full_path)) {
        $content = file_get_contents($full_path);
        $old_pattern = "require_role(['System Administrator']);";
        $new_pattern = "require_role($new_role);";
        
        $new_content = str_replace($old_pattern, $new_pattern, $content);
        
        if ($new_content !== $content) {
            if (file_put_contents($full_path, $new_content)) {
                echo "✓ Fixed: $file\n";
                $fixed_count++;
            } else {
                echo "✗ Error writing: $file\n";
                $error_count++;
            }
        } else {
            echo "- Skipped (no change needed): $file\n";
        }
    } else {
        echo "✗ File not found: $file\n";
        $error_count++;
    }
}

echo "\n=== Summary ===\n";
echo "Fixed: $fixed_count files\n";
echo "Errors: $error_count files\n";
?>
