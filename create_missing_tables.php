<?php
require_once 'includes/config.php';

$sql_dispatch = "
CREATE TABLE IF NOT EXISTS `dispatch` (
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
  FOREIGN KEY (request_id) REFERENCES blood_requests(request_id) ON DELETE CASCADE,
  FOREIGN KEY (dispatched_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

$sql_allocations = "
CREATE TABLE IF NOT EXISTS `request_allocations` (
  `allocation_id` INT AUTO_INCREMENT PRIMARY KEY,
  `request_id` INT NOT NULL,
  `unit_id` INT NOT NULL,
  `allocated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `allocated_by` INT COMMENT 'Inventory Manager',
  INDEX idx_request (request_id),
  INDEX idx_unit (unit_id),
  FOREIGN KEY (request_id) REFERENCES blood_requests(request_id) ON DELETE CASCADE,
  FOREIGN KEY (unit_id) REFERENCES blood_units(unit_id) ON DELETE CASCADE,
  FOREIGN KEY (allocated_by) REFERENCES users(user_id) ON DELETE SET NULL,
  UNIQUE KEY unique_allocation (request_id, unit_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

echo "Creating tables...\n";

if ($conn->query($sql_dispatch)) {
    echo "Table 'dispatch' created successfully.\n";
} else {
    echo "Error creating 'dispatch': " . $conn->error . "\n";
}

if ($conn->query($sql_allocations)) {
    echo "Table 'request_allocations' created successfully.\n";
} else {
    echo "Error creating 'request_allocations': " . $conn->error . "\n";
}
?>
