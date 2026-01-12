-- Blood Banking Management System - Complete Database Schema
-- Drop existing database and create fresh
DROP DATABASE IF EXISTS `blood banking management system`;
CREATE DATABASE `blood banking management system` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `blood banking management system`;

-- ============================================
-- 1. USERS & AUTHENTICATION
-- ============================================

CREATE TABLE `users` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role_name` ENUM('System Administrator', 'Registration Officer', 'Laboratory Technologist', 'Inventory Manager', 'Hospital User', 'Red Cross', 'Minister Of Health') NOT NULL,
  `phone` VARCHAR(20),
  `status` ENUM('ACTIVE', 'INACTIVE', 'SUSPENDED') DEFAULT 'ACTIVE',
  `hospital_name` VARCHAR(100) NULL COMMENT 'For Hospital Users',
  `organization_name` VARCHAR(100) NULL COMMENT 'For Partners',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_email (email),
  INDEX idx_role (role_name),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 2. DONORS & ELIGIBILITY
-- ============================================

CREATE TABLE `donors` (
  `donor_id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `gender` ENUM('MALE', 'FEMALE') NOT NULL,
  `date_of_birth` DATE NOT NULL,
  `blood_group` ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `email` VARCHAR(100),
  `address` TEXT,
  `weight_kg` DECIMAL(5,2) COMMENT 'Weight in kilograms',
  `blood_pressure` VARCHAR(20) COMMENT 'Format: 120/80',
  `hemoglobin_level` DECIMAL(4,2) COMMENT 'g/dL',
  `medical_history` TEXT COMMENT 'Medical conditions, medications',
  `eligibility` ENUM('ELIGIBLE', 'NOT_ELIGIBLE', 'DEFERRED', 'PERMANENTLY_DEFERRED') DEFAULT 'NOT_ELIGIBLE',
  `deferral_reason` TEXT COMMENT 'Reason for deferral if not eligible',
  `deferral_until` DATE COMMENT 'Date until which donor is deferred',
  `last_donation_date` DATE,
  `total_donations` INT DEFAULT 0,
  `registered_by` INT COMMENT 'User ID who registered the donor',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_blood_group (blood_group),
  INDEX idx_eligibility (eligibility),
  INDEX idx_phone (phone),
  FOREIGN KEY (registered_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 3. BLOOD COLLECTION
-- ============================================

CREATE TABLE `blood_units` (
  `unit_id` INT AUTO_INCREMENT PRIMARY KEY,
  `barcode` VARCHAR(50) UNIQUE NOT NULL COMMENT 'Format: BB-YYYYMMDD-XXXX',
  `donor_id` INT NOT NULL,
  `blood_group` ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
  `volume_ml` INT DEFAULT 450 COMMENT 'Volume in milliliters',
  `collection_date` DATETIME NOT NULL,
  `expiry_date` DATE NOT NULL COMMENT 'Calculated as collection_date + 42 days',
  `status` ENUM('COLLECTED', 'TESTING', 'APPROVED', 'REJECTED', 'RESERVED', 'DISPATCHED', 'EXPIRED', 'DISPOSED') DEFAULT 'COLLECTED',
  `collected_by` INT COMMENT 'User ID who collected the blood',
  `storage_location` VARCHAR(50) COMMENT 'Refrigerator/location identifier',
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_barcode (barcode),
  INDEX idx_donor (donor_id),
  INDEX idx_blood_group (blood_group),
  INDEX idx_status (status),
  INDEX idx_expiry (expiry_date),
  INDEX idx_collection_date (collection_date),
  FOREIGN KEY (donor_id) REFERENCES donors(donor_id) ON DELETE CASCADE,
  FOREIGN KEY (collected_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 4. LABORATORY TESTING
-- ============================================

CREATE TABLE `lab_tests` (
  `test_id` INT AUTO_INCREMENT PRIMARY KEY,
  `unit_id` INT NOT NULL,
  `test_hiv` ENUM('POSITIVE', 'NEGATIVE', 'PENDING') DEFAULT 'PENDING',
  `test_hbv` ENUM('POSITIVE', 'NEGATIVE', 'PENDING') DEFAULT 'PENDING' COMMENT 'Hepatitis B',
  `test_hcv` ENUM('POSITIVE', 'NEGATIVE', 'PENDING') DEFAULT 'PENDING' COMMENT 'Hepatitis C',
  `test_syphilis` ENUM('POSITIVE', 'NEGATIVE', 'PENDING') DEFAULT 'PENDING',
  `test_result` ENUM('APPROVED', 'REJECTED', 'PENDING') DEFAULT 'PENDING',
  `rejection_reason` TEXT COMMENT 'Reason if any test is positive',
  `tested_by` INT COMMENT 'Lab technologist user ID',
  `tested_at` DATETIME,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_unit (unit_id),
  INDEX idx_result (test_result),
  INDEX idx_tested_by (tested_by),
  FOREIGN KEY (unit_id) REFERENCES blood_units(unit_id) ON DELETE CASCADE,
  FOREIGN KEY (tested_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 5. BLOOD REQUESTS (Hospital)
-- ============================================

CREATE TABLE `blood_requests` (
  `request_id` INT AUTO_INCREMENT PRIMARY KEY,
  `request_number` VARCHAR(50) UNIQUE NOT NULL COMMENT 'Format: REQ-YYYYMMDD-XXXX',
  `hospital_user_id` INT NOT NULL COMMENT 'Hospital user who made the request',
  `hospital_name` VARCHAR(100) NOT NULL,
  `blood_group` ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
  `quantity` INT NOT NULL COMMENT 'Number of units requested',
  `request_type` ENUM('NORMAL', 'EMERGENCY') DEFAULT 'NORMAL',
  `patient_name` VARCHAR(100) COMMENT 'Optional patient details',
  `patient_id` VARCHAR(50),
  `reason` TEXT NOT NULL COMMENT 'Justification for request',
  `status` ENUM('PENDING', 'APPROVED', 'REJECTED', 'DISPATCHED', 'DELIVERED', 'CANCELLED') DEFAULT 'PENDING',
  `reviewed_by` INT COMMENT 'Inventory Manager who reviewed',
  `reviewed_at` DATETIME,
  `review_notes` TEXT COMMENT 'Approval/rejection notes',
  `priority_score` INT DEFAULT 0 COMMENT 'Higher for emergency requests',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_request_number (request_number),
  INDEX idx_hospital (hospital_user_id),
  INDEX idx_blood_group (blood_group),
  INDEX idx_status (status),
  INDEX idx_type (request_type),
  INDEX idx_priority (priority_score),
  FOREIGN KEY (hospital_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (reviewed_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 6. REQUEST ALLOCATION (Which units for which request)
-- ============================================

CREATE TABLE `request_allocations` (
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

-- ============================================
-- 7. DISPATCH & DELIVERY
-- ============================================

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
  FOREIGN KEY (request_id) REFERENCES blood_requests(request_id) ON DELETE CASCADE,
  FOREIGN KEY (dispatched_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 8. NOTIFICATIONS
-- ============================================

CREATE TABLE `notifications` (
  `notification_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `type` ENUM('INFO', 'WARNING', 'EMERGENCY', 'SUCCESS', 'ERROR') DEFAULT 'INFO',
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `link` VARCHAR(255) COMMENT 'URL to related page',
  `is_read` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user (user_id),
  INDEX idx_is_read (is_read),
  INDEX idx_type (type),
  INDEX idx_created (created_at),
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 9. AUDIT LOGS
-- ============================================

CREATE TABLE `audit_logs` (
  `log_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT,
  `action` VARCHAR(255) NOT NULL,
  `table_name` VARCHAR(50) COMMENT 'Table affected',
  `record_id` INT COMMENT 'ID of affected record',
  `old_values` TEXT COMMENT 'JSON of old values',
  `new_values` TEXT COMMENT 'JSON of new values',
  `ip_address` VARCHAR(45),
  `user_agent` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user (user_id),
  INDEX idx_action (action),
  INDEX idx_table (table_name),
  INDEX idx_created (created_at),
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 10. APPOINTMENTS (Donor scheduling)
-- ============================================

CREATE TABLE `appointments` (
  `appointment_id` INT AUTO_INCREMENT PRIMARY KEY,
  `donor_id` INT NOT NULL,
  `appointment_date` DATETIME NOT NULL,
  `appointment_type` ENUM('DONATION', 'SCREENING', 'FOLLOW_UP') DEFAULT 'DONATION',
  `status` ENUM('SCHEDULED', 'CONFIRMED', 'COMPLETED', 'CANCELLED', 'NO_SHOW') DEFAULT 'SCHEDULED',
  `notes` TEXT,
  `created_by` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_donor (donor_id),
  INDEX idx_date (appointment_date),
  INDEX idx_status (status),
  FOREIGN KEY (donor_id) REFERENCES donors(donor_id) ON DELETE CASCADE,
  FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 11. CAMPAIGNS (Partner Organizations)
-- ============================================

CREATE TABLE `campaigns` (
  `campaign_id` INT AUTO_INCREMENT PRIMARY KEY,
  `campaign_name` VARCHAR(200) NOT NULL,
  `organization` VARCHAR(100) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `location` VARCHAR(255),
  `target_donors` INT COMMENT 'Target number of donors',
  `actual_donors` INT DEFAULT 0,
  `target_units` INT COMMENT 'Target blood units',
  `actual_units` INT DEFAULT 0,
  `status` ENUM('PLANNED', 'ACTIVE', 'COMPLETED', 'CANCELLED') DEFAULT 'PLANNED',
  `description` TEXT,
  `created_by` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_organization (organization),
  INDEX idx_status (status),
  INDEX idx_dates (start_date, end_date),
  FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 12. SYSTEM SETTINGS
-- ============================================

CREATE TABLE `system_settings` (
  `setting_id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) UNIQUE NOT NULL,
  `setting_value` TEXT,
  `description` VARCHAR(255),
  `updated_by` INT,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (updated_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- INSERT DEFAULT DATA
-- ============================================

-- Default Users (passwords: password123)
INSERT INTO `users` (`full_name`, `email`, `password`, `role_name`, `phone`, `hospital_name`, `organization_name`) VALUES
('System Admin', 'admin@example.com', 'password123', 'System Administrator', '+250788000001', NULL, NULL),
('Registration Officer', 'registration@example.com', 'password123', 'Registration Officer', '+250788000002', NULL, NULL),
('Lab Technologist', 'lab@example.com', 'password123', 'Laboratory Technologist', '+250788000003', NULL, NULL),
('Inventory Manager', 'inventory@example.com', 'password123', 'Inventory Manager', '+250788000004', NULL, NULL),
('Hospital User', 'hospital@example.com', 'password123', 'Hospital User', '+250788000005', 'Kigali University Teaching Hospital', NULL),
('Red Cross', 'redcross@example.com', 'password123', 'Red Cross', '+250788000006', NULL, 'Rwanda Red Cross Society'),
('Minister Of Health', 'minister@example.com', 'password123', 'Minister Of Health', '+250788000007', NULL, 'Ministry of Health');

-- Default System Settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `description`) VALUES
('blood_expiry_days', '42', 'Number of days until blood expires after collection'),
('expiry_warning_days', '7', 'Days before expiry to show warning'),
('min_donor_age', '18', 'Minimum age for blood donation'),
('max_donor_age', '65', 'Maximum age for blood donation'),
('min_donor_weight', '50', 'Minimum weight in kg for blood donation'),
('min_hemoglobin_male', '13.0', 'Minimum hemoglobin level for male donors (g/dL)'),
('min_hemoglobin_female', '12.5', 'Minimum hemoglobin level for female donors (g/dL)'),
('donation_interval_days', '56', 'Minimum days between donations'),
('low_stock_threshold', '5', 'Alert when stock falls below this number'),
('reservation_expiry_hours', '24', 'Hours before reserved units are released back to stock');

-- Sample Donors
INSERT INTO `donors` (`full_name`, `gender`, `date_of_birth`, `blood_group`, `phone`, `email`, `weight_kg`, `blood_pressure`, `hemoglobin_level`, `eligibility`, `registered_by`) VALUES
('John Doe', 'MALE', '1990-05-15', 'O+', '+250788111111', 'john@example.com', 75.5, '120/80', 14.5, 'ELIGIBLE', 2),
('Jane Smith', 'FEMALE', '1995-08-22', 'A+', '+250788222222', 'jane@example.com', 62.0, '118/78', 13.2, 'ELIGIBLE', 2),
('Bob Johnson', 'MALE', '1988-03-10', 'B+', '+250788333333', 'bob@example.com', 80.0, '125/82', 15.0, 'ELIGIBLE', 2),
('Alice Williams', 'FEMALE', '1992-11-30', 'AB+', '+250788444444', 'alice@example.com', 58.5, '115/75', 12.8, 'ELIGIBLE', 2);

-- Success message
SELECT 'Database schema created successfully!' AS Status;
SELECT COUNT(*) AS 'Total Users Created' FROM users;
SELECT COUNT(*) AS 'Total Donors Created' FROM donors;
