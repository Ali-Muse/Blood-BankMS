<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';

require_role(['Inventory Manager', 'System Administrator']);

// Get all notifications for the user
$user_id = get_user_id();
$notifications = $conn->query("
    SELECT * FROM notifications 
    WHERE user_id = $user_id 
    ORDER BY created_at DESC 
    LIMIT 50
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - BBMS</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php render_sidebar('notifications.php'); ?>
    
    <div class="main-content">
        <div class="page-header">
            <div>
                <h1>🔔 Notifications</h1>
                <p>System alerts and updates</p>
            </div>
            <?php if ($notifications->num_rows > 0): ?>
                <form method="POST" action="">
                    <button type="submit" name="mark_all_read" class="btn-outline">Mark All as Read</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="card">
            <?php if ($notifications->num_rows > 0): ?>
                <?php while ($notification = $notifications->fetch_assoc()): ?>
                    <?php
                    $type_colors = [
                        'INFO' => 'background: #dbeafe; color: #1e40af;',
                        'SUCCESS' => 'background: #d1fae5; color: #065f46;',
                        'WARNING' => 'background: #fef3c7; color: #92400e;',
                        'ERROR' => 'background: #fee2e2; color: #991b1b;',
                        'EMERGENCY' => 'background: #dc2626; color: white;'
                    ];
                    $color = $type_colors[$notification['type']] ?? 'background: #f3f4f6; color: #374151;';
                    ?>
                    <div style="padding: 16px; margin-bottom: 12px; border-radius: 8px; <?= $color ?> <?= $notification['is_read'] ? 'opacity: 0.6;' : '' ?>">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                            <h3 style="font-weight: 600; margin-bottom: 4px;"><?= htmlspecialchars($notification['title']) ?></h3>
                            <span style="font-size: 12px; opacity: 0.8;"><?= date('Y-m-d H:i', strtotime($notification['created_at'])) ?></span>
                        </div>
                        <p style="margin-bottom: 8px;"><?= htmlspecialchars($notification['message']) ?></p>
                        <?php if ($notification['link']): ?>
                            <a href="<?= htmlspecialchars($notification['link']) ?>" style="font-size: 13px; font-weight: 600; text-decoration: underline;">
                                View Details →
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 64px 24px; color: #6b7280;">
                    <div style="font-size: 64px; margin-bottom: 16px;">🔔</div>
                    <h2 style="font-size: 20px; margin-bottom: 8px;">No Notifications</h2>
                    <p>You're all caught up!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../../assets/js/sidebar.js"></script>
</body>
</html>
