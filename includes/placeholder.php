<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/sidebar.php';

require_role(['System Administrator']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Placeholder Page - BBMS</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php 
    $page_name = basename($_SERVER['PHP_SELF'], '.php');
    $page_title = ucwords(str_replace('-', ' ', $page_name));
    render_sidebar($page_name . '.php'); 
    ?>
    
    <div class="main-content">
        <div class="card" style="text-align: center; padding: 60px 24px;">
            <div style="font-size: 64px; margin-bottom: 24px;">🚧</div>
            <h1 style="font-size: 32px; font-weight: 800; color: var(--blood-700); margin-bottom: 16px;"><?= $page_title ?></h1>
            <p style="color: #6b7280; font-size: 18px; margin-bottom: 32px;">This page is under development</p>
            <a href="index.php" class="btn-primary">← Back to Dashboard</a>
        </div>
    </div>

    <script src="../../assets/js/sidebar.js"></script>
</body>
</html>
