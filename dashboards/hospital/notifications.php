<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';

require_login(); // Accessible by any logged in user role

$page_title = 'Notifications';
$user_id = get_user_id();

// Mark all as read logic could go here
if (isset($_GET['mark_read'])) {
    $conn->query("UPDATE notifications SET is_read = TRUE WHERE user_id = $user_id");
}

// Fetch Notifications
$notifications = [];
$query = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
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
    <?php render_sidebar('notifications.php'); ?>
    
    <div class="main-content ml-0 md:ml-72 min-h-screen p-6 pt-20 md:pt-8 transition-all duration-300">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-extrabold text-blood-900 tracking-tight">Notifications</h1>
            <?php if (!empty($notifications)): ?>
            <a href="?mark_read=1" class="text-sm text-blood-600 hover:text-blood-800 font-medium">Mark all as read</a>
            <?php endif; ?>
        </div>

        <div class="space-y-4 max-w-4xl">
            <?php if (empty($notifications)): ?>
                <div class="text-center py-12 bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="text-gray-300 text-5xl mb-4"><i class="far fa-bell-slash"></i></div>
                    <p class="text-gray-500">You have no notifications at the moment.</p>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $notif): 
                    $icon = 'info-circle';
                    $color = 'blue';
                    if ($notif['type'] == 'WARNING') { $icon = 'exclamation-triangle'; $color = 'yellow'; }
                    if ($notif['type'] == 'EMERGENCY') { $icon = 'ambulance'; $color = 'red'; }
                    if ($notif['type'] == 'SUCCESS') { $icon = 'check-circle'; $color = 'green'; }
                    if ($notif['type'] == 'ERROR') { $icon = 'times-circle'; $color = 'red'; }
                    
                    $bg_class = $notif['is_read'] ? 'bg-white opacity-75' : 'bg-white border-l-4 border-blood-600 shadow-md';
                ?>
                <div class="<?= $bg_class ?> p-4 rounded-lg flex items-start gap-4 transition-all hover:bg-gray-50 border border-gray-100">
                    <div class="text-<?= $color ?>-500 text-xl mt-1 shrink-0">
                        <i class="fas fa-<?= $icon ?>"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-bold text-gray-800 text-sm"><?= htmlspecialchars($notif['title']) ?></h4>
                        <p class="text-gray-600 text-sm mt-1"><?= htmlspecialchars($notif['message']) ?></p>
                        <p class="text-xs text-gray-400 mt-2"><?= date('M d, H:i', strtotime($notif['created_at'])) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <script src="../../assets/js/sidebar.js"></script>
</body>
</html>
