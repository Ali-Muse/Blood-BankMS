<?php
require_once 'config.php';
require_once 'notification_functions.php';

header('Content-Type: application/json');

$user_id = get_user_id();

if (!$user_id) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

// Get action parameter
$action = $_GET['action'] ?? 'count';

switch ($action) {
    case 'count':
        // Return unread notification count
        echo json_encode(['count' => get_notification_count($user_id)]);
        break;
        
    case 'list':
        // Return list of notifications
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
        $unread_only = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';
        $notifications = get_user_notifications($user_id, $limit, $unread_only);
        echo json_encode(['notifications' => $notifications]);
        break;
        
    case 'mark_read':
        // Mark notification as read
        if (isset($_POST['notification_id'])) {
            $notification_id = intval($_POST['notification_id']);
            $success = mark_notification_read($notification_id, $user_id);
            echo json_encode(['success' => $success]);
        } else {
            echo json_encode(['error' => 'Missing notification_id']);
        }
        break;
        
    case 'mark_all_read':
        // Mark all notifications as read
        $success = mark_all_notifications_read($user_id);
        echo json_encode(['success' => $success]);
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>
