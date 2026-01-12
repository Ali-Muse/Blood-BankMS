<?php
require_once 'includes/config.php';

// Check dependencies
$tables = ['blood_requests', 'users'];
foreach ($tables as $table) {
    if ($conn->query("SHOW TABLES LIKE '$table'")->num_rows == 0) {
        die("Error: Dependency table '$table' does not exist.\n");
    } else {
        echo "Dependency table '$table' exists.\n";
    }
}

// Try creating dispatch again with verbose error
$sql_dispatch = "
CREATE TABLE `dispatch` (
  `dispatch_id` INT AUTO_INCREMENT PRIMARY KEY,
  `dispatch_number` VARCHAR(50) UNIQUE NOT NULL COMMENT 'Format: DISP-YYYYMMDD-XXXX',
  `request_id` INT NOT NULL,
  `dispatch_date` DATETIME NOT NULL,
  `dispatched_by` INT NOT NULL COMMENT 'Inventory Manager',
  `courier_name` VARCHAR(100),
  `courier_phone` VARCHAR(20),
  `vehicle_number` VARCHAR(50),
  `status` ENUM('PREPARED', 'IN_TRANSIT', 'DELIVERED', 'CANCELLED') DEFAULT 'PREPARED',
  `delivery_date` DATETIME,
  `received_by_name` VARCHAR(100) COMMENT 'Hospital staff who received',
  `received_by_signature` VARCHAR(255) COMMENT 'Path to signature image',
  `delivery_notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_dispatch_number (dispatch_number),
  INDEX idx_request (request_id),
  INDEX idx_status (status),
  INDEX idx_dispatch_date (dispatch_date),
  CONSTRAINT `fk_dispatch_request` FOREIGN KEY (request_id) REFERENCES blood_requests(request_id) ON DELETE CASCADE,
  CONSTRAINT `fk_dispatch_user` FOREIGN KEY (dispatched_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

if ($conn->query($sql_dispatch)) {
    echo "Table 'dispatch' created successfully.\n";
} else {
    echo "Error creating 'dispatch': " . $conn->error . "\n";
}
?>
