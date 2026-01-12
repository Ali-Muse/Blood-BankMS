-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 12, 2026 at 04:08 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `blood banking management system`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `donor_id` int(11) NOT NULL,
  `appointment_date` datetime NOT NULL,
  `appointment_type` enum('DONATION','SCREENING','FOLLOW_UP') DEFAULT 'DONATION',
  `status` enum('SCHEDULED','CONFIRMED','COMPLETED','CANCELLED','NO_SHOW') DEFAULT 'SCHEDULED',
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL COMMENT 'Table affected',
  `record_id` int(11) DEFAULT NULL COMMENT 'ID of affected record',
  `old_values` text DEFAULT NULL COMMENT 'JSON of old values',
  `new_values` text DEFAULT NULL COMMENT 'JSON of new values',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`log_id`, `user_id`, `action`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'User logged in', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-05 12:29:48'),
(2, 1, 'User logged out', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-05 12:30:27'),
(3, 5, 'User logged in', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-05 12:31:05'),
(4, 5, 'Created blood request #REQ-20260105-0002: 4 units of A+', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-05 12:33:03'),
(5, 5, 'User logged out', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-05 12:33:26'),
(6, 4, 'User logged in', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-05 12:33:42'),
(7, 4, 'Rejected blood request #REQ-20260105-0001', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-05 12:48:19'),
(8, 4, 'User logged out', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-05 12:52:19'),
(9, 5, 'User logged in', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-05 12:52:32'),
(10, 5, 'Created blood request #REQ-20260105-0003: 6 units of B+', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-05 12:53:06'),
(11, 5, 'User logged out', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-05 12:53:09'),
(12, 4, 'User logged in', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-05 12:53:20'),
(13, 1, 'User logged in', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-05 19:20:11'),
(14, 1, 'User logged out', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-05 19:23:02'),
(15, 5, 'User logged in', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-05 19:23:19'),
(16, 5, 'Created blood request #REQ-20260105-0004: 8 units of O+', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-05 19:24:44'),
(17, 5, 'User logged out', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-05 19:25:09'),
(18, 4, 'User logged in', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-05 19:25:23'),
(19, 4, 'Rejected blood request #REQ-20260105-0003', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-05 19:53:05'),
(20, 4, 'Approved blood request #REQ-20260105-0002', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-05 20:02:26'),
(21, 1, 'User logged in', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-10 18:49:35'),
(22, 1, 'User logged out', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-10 18:51:18'),
(23, 5, 'User logged in', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-10 18:51:30'),
(24, 5, 'Created blood request #REQ-20260110-0001: 7 units of A+', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-10 19:09:48'),
(25, 5, 'User logged out', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-10 19:09:54'),
(26, 4, 'User logged in', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-10 19:11:24'),
(27, 4, 'Approved blood request #REQ-20260105-0004', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-10 19:12:09'),
(28, 4, 'User logged out', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-10 19:20:11'),
(29, 5, 'User logged in', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-10 19:20:22'),
(30, 5, 'User logged out', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-10 19:36:07'),
(31, 4, 'User logged in', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-10 19:36:22'),
(32, 4, 'Dispatched blood request #REQ-20260105-0004', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-10 19:37:12'),
(33, 5, 'User logged in', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-11 12:03:45'),
(34, 5, 'User logged out', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-11 12:09:16'),
(35, 4, 'User logged in', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-11 12:09:47'),
(36, 4, 'User logged out', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-11 12:21:31'),
(37, 1, 'User logged in', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-11 12:22:08');

-- --------------------------------------------------------

--
-- Table structure for table `blood_requests`
--

CREATE TABLE `blood_requests` (
  `request_id` int(11) NOT NULL,
  `request_number` varchar(50) NOT NULL COMMENT 'Format: REQ-YYYYMMDD-XXXX',
  `hospital_user_id` int(11) NOT NULL COMMENT 'Hospital user who made the request',
  `hospital_name` varchar(100) NOT NULL,
  `blood_group` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
  `quantity` int(11) NOT NULL COMMENT 'Number of units requested',
  `request_type` enum('NORMAL','EMERGENCY') DEFAULT 'NORMAL',
  `patient_name` varchar(100) DEFAULT NULL COMMENT 'Optional patient details',
  `patient_id` varchar(50) DEFAULT NULL,
  `reason` text NOT NULL COMMENT 'Justification for request',
  `status` enum('PENDING','APPROVED','REJECTED','DISPATCHED','DELIVERED','CANCELLED') DEFAULT 'PENDING',
  `reviewed_by` int(11) DEFAULT NULL COMMENT 'Inventory Manager who reviewed',
  `reviewed_at` datetime DEFAULT NULL,
  `review_notes` text DEFAULT NULL COMMENT 'Approval/rejection notes',
  `priority_score` int(11) DEFAULT 0 COMMENT 'Higher for emergency requests',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blood_requests`
--

INSERT INTO `blood_requests` (`request_id`, `request_number`, `hospital_user_id`, `hospital_name`, `blood_group`, `quantity`, `request_type`, `patient_name`, `patient_id`, `reason`, `status`, `reviewed_by`, `reviewed_at`, `review_notes`, `priority_score`, `created_at`, `updated_at`) VALUES
(1, 'REQ-20260105-0001', 5, 'Kigali University Teaching Hospital', 'A+', 4, 'NORMAL', '', '', 'i have patient that needs This blood type group', 'REJECTED', 4, '2026-01-05 13:48:19', '', 0, '2026-01-05 12:31:57', '2026-01-05 12:48:19'),
(2, 'REQ-20260105-0002', 5, 'Kigali University Teaching Hospital', 'A+', 4, 'NORMAL', '', '', 'i have patient that needs This blood type group', 'APPROVED', 4, '2026-01-05 21:02:26', '', 0, '2026-01-05 12:33:03', '2026-01-05 20:02:26'),
(3, 'REQ-20260105-0003', 5, 'Kigali University Teaching Hospital', 'B+', 6, 'NORMAL', '', '', 'this is normal request', 'REJECTED', 4, '2026-01-05 20:53:05', '', 0, '2026-01-05 12:53:06', '2026-01-05 19:53:05'),
(4, 'REQ-20260105-0004', 5, 'Kigali University Teaching Hospital', 'O+', 8, 'NORMAL', '', '', 'provide this blood ype', 'DISPATCHED', 4, '2026-01-10 20:12:09', '', 0, '2026-01-05 19:24:44', '2026-01-10 19:37:12'),
(5, 'REQ-20260110-0001', 5, 'Kigali University Teaching Hospital', 'A+', 7, 'NORMAL', '', '', 'provide this A+ blood type', 'PENDING', NULL, NULL, NULL, 0, '2026-01-10 19:09:48', '2026-01-10 19:09:48');

-- --------------------------------------------------------

--
-- Table structure for table `blood_units`
--

CREATE TABLE `blood_units` (
  `unit_id` int(11) NOT NULL,
  `barcode` varchar(50) NOT NULL COMMENT 'Format: BB-YYYYMMDD-XXXX',
  `donor_id` int(11) NOT NULL,
  `blood_group` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
  `volume_ml` int(11) DEFAULT 450 COMMENT 'Volume in milliliters',
  `collection_date` datetime NOT NULL,
  `expiry_date` date NOT NULL COMMENT 'Calculated as collection_date + 42 days',
  `status` enum('COLLECTED','TESTING','APPROVED','REJECTED','RESERVED','DISPATCHED','EXPIRED','DISPOSED') DEFAULT 'COLLECTED',
  `collected_by` int(11) DEFAULT NULL COMMENT 'User ID who collected the blood',
  `storage_location` varchar(50) DEFAULT NULL COMMENT 'Refrigerator/location identifier',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blood_units`
--

INSERT INTO `blood_units` (`unit_id`, `barcode`, `donor_id`, `blood_group`, `volume_ml`, `collection_date`, `expiry_date`, `status`, `collected_by`, `storage_location`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'DIN-A+-20260105-3028', 2, 'A+', 450, '2025-12-27 21:01:06', '2026-02-09', 'RESERVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:02:26'),
(2, 'DIN-A+-20260105-8624', 2, 'A+', 450, '2025-12-26 21:01:06', '2026-02-09', 'RESERVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:02:26'),
(3, 'DIN-A+-20260105-6864', 2, 'A+', 450, '2025-12-27 21:01:06', '2026-02-09', 'RESERVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:02:26'),
(4, 'DIN-A+-20260105-8892', 2, 'A+', 450, '2026-01-01 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(5, 'DIN-A+-20260105-8181', 2, 'A+', 450, '2025-12-29 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(6, 'DIN-A+-20260105-2138', 2, 'A+', 450, '2026-01-02 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(7, 'DIN-A+-20260105-7807', 2, 'A+', 450, '2026-01-04 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(8, 'DIN-A+-20260105-1826', 2, 'A+', 450, '2025-12-30 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(9, 'DIN-A+-20260105-8666', 2, 'A+', 450, '2026-01-03 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(10, 'DIN-A+-20260105-8114', 2, 'A+', 450, '2025-12-28 21:01:06', '2026-02-09', 'RESERVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:02:26'),
(11, 'DIN-A--20260105-6790', 2, 'A-', 450, '2026-01-01 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(12, 'DIN-A--20260105-3909', 2, 'A-', 450, '2026-01-03 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(13, 'DIN-A--20260105-3236', 2, 'A-', 450, '2026-01-01 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(14, 'DIN-A--20260105-5957', 2, 'A-', 450, '2026-01-01 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(15, 'DIN-A--20260105-2045', 2, 'A-', 450, '2025-12-28 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(16, 'DIN-A--20260105-5436', 2, 'A-', 450, '2026-01-04 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(17, 'DIN-A--20260105-8820', 2, 'A-', 450, '2025-12-31 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(18, 'DIN-A--20260105-8739', 2, 'A-', 450, '2025-12-30 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(19, 'DIN-A--20260105-6364', 2, 'A-', 450, '2026-01-02 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(20, 'DIN-A--20260105-3550', 2, 'A-', 450, '2026-01-01 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(21, 'DIN-B+-20260105-3380', 2, 'B+', 450, '2025-12-28 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(22, 'DIN-B+-20260105-4944', 2, 'B+', 450, '2025-12-30 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(23, 'DIN-B+-20260105-5881', 2, 'B+', 450, '2025-12-29 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(24, 'DIN-B+-20260105-9069', 2, 'B+', 450, '2026-01-03 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(25, 'DIN-B+-20260105-8007', 2, 'B+', 450, '2026-01-03 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(26, 'DIN-B+-20260105-8882', 2, 'B+', 450, '2025-12-27 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(27, 'DIN-B+-20260105-5116', 2, 'B+', 450, '2026-01-03 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(28, 'DIN-B+-20260105-6711', 2, 'B+', 450, '2025-12-30 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(29, 'DIN-B+-20260105-1472', 2, 'B+', 450, '2025-12-31 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(30, 'DIN-B+-20260105-3868', 2, 'B+', 450, '2025-12-29 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(31, 'DIN-B--20260105-9836', 2, 'B-', 450, '2025-12-26 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(32, 'DIN-B--20260105-3374', 2, 'B-', 450, '2026-01-03 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(33, 'DIN-B--20260105-1685', 2, 'B-', 450, '2025-12-27 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(34, 'DIN-B--20260105-7455', 2, 'B-', 450, '2026-01-03 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(35, 'DIN-B--20260105-8330', 2, 'B-', 450, '2026-01-01 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(36, 'DIN-B--20260105-9794', 2, 'B-', 450, '2026-01-04 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(37, 'DIN-B--20260105-4172', 2, 'B-', 450, '2025-12-30 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(38, 'DIN-B--20260105-5481', 2, 'B-', 450, '2025-12-27 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(39, 'DIN-B--20260105-9477', 2, 'B-', 450, '2026-01-01 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(40, 'DIN-B--20260105-8421', 2, 'B-', 450, '2025-12-26 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(41, 'DIN-AB+-20260105-1180', 2, 'AB+', 450, '2025-12-30 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(42, 'DIN-AB+-20260105-4588', 2, 'AB+', 450, '2025-12-30 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(43, 'DIN-AB+-20260105-8844', 2, 'AB+', 450, '2025-12-26 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(44, 'DIN-AB+-20260105-1390', 2, 'AB+', 450, '2025-12-31 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(45, 'DIN-AB+-20260105-7427', 2, 'AB+', 450, '2026-01-01 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(46, 'DIN-AB+-20260105-1108', 2, 'AB+', 450, '2025-12-31 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(47, 'DIN-AB+-20260105-6621', 2, 'AB+', 450, '2026-01-01 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(48, 'DIN-AB+-20260105-5866', 2, 'AB+', 450, '2026-01-04 21:01:06', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:06', '2026-01-05 20:01:06'),
(49, 'DIN-AB+-20260105-4529', 2, 'AB+', 450, '2025-12-26 21:01:07', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:07', '2026-01-05 20:01:07'),
(50, 'DIN-AB+-20260105-4415', 2, 'AB+', 450, '2026-01-01 21:01:07', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:07', '2026-01-05 20:01:07'),
(51, 'DIN-AB--20260105-7997', 2, 'AB-', 450, '2025-12-29 21:01:07', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:07', '2026-01-05 20:01:07'),
(52, 'DIN-AB--20260105-5723', 2, 'AB-', 450, '2025-12-30 21:01:07', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:07', '2026-01-05 20:01:07'),
(53, 'DIN-AB--20260105-7620', 2, 'AB-', 450, '2025-12-31 21:01:07', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:07', '2026-01-05 20:01:07'),
(54, 'DIN-AB--20260105-6452', 2, 'AB-', 450, '2025-12-31 21:01:07', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:07', '2026-01-05 20:01:07'),
(55, 'DIN-AB--20260105-5038', 2, 'AB-', 450, '2026-01-02 21:01:07', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:07', '2026-01-05 20:01:07'),
(56, 'DIN-AB--20260105-2014', 2, 'AB-', 450, '2025-12-29 21:01:07', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:07', '2026-01-05 20:01:07'),
(57, 'DIN-AB--20260105-9956', 2, 'AB-', 450, '2026-01-03 21:01:07', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:07', '2026-01-05 20:01:07'),
(58, 'DIN-AB--20260105-5196', 2, 'AB-', 450, '2026-01-01 21:01:07', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:07', '2026-01-05 20:01:07'),
(59, 'DIN-AB--20260105-4530', 2, 'AB-', 450, '2026-01-04 21:01:07', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:07', '2026-01-05 20:01:07'),
(60, 'DIN-AB--20260105-4678', 2, 'AB-', 450, '2026-01-01 21:01:07', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:07', '2026-01-05 20:01:07'),
(61, 'DIN-O+-20260105-7941', 2, 'O+', 450, '2025-12-31 21:01:07', '2026-02-09', 'DISPATCHED', NULL, NULL, NULL, '2026-01-05 20:01:07', '2026-01-10 19:37:12'),
(62, 'DIN-O+-20260105-4209', 2, 'O+', 450, '2026-01-03 21:01:07', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:07', '2026-01-05 20:01:07'),
(63, 'DIN-O+-20260105-8241', 2, 'O+', 450, '2025-12-29 21:01:07', '2026-02-09', 'DISPATCHED', NULL, NULL, NULL, '2026-01-05 20:01:07', '2026-01-10 19:37:12'),
(64, 'DIN-O+-20260105-8684', 2, 'O+', 450, '2026-01-03 21:01:07', '2026-02-09', 'DISPATCHED', NULL, NULL, NULL, '2026-01-05 20:01:07', '2026-01-10 19:37:12'),
(65, 'DIN-O+-20260105-5830', 2, 'O+', 450, '2026-01-02 21:01:07', '2026-02-09', 'DISPATCHED', NULL, NULL, NULL, '2026-01-05 20:01:07', '2026-01-10 19:37:12'),
(66, 'DIN-O+-20260105-6998', 2, 'O+', 450, '2025-12-27 21:01:07', '2026-02-09', 'DISPATCHED', NULL, NULL, NULL, '2026-01-05 20:01:07', '2026-01-10 19:37:12'),
(67, 'DIN-O+-20260105-7867', 2, 'O+', 450, '2025-12-27 21:01:07', '2026-02-09', 'DISPATCHED', NULL, NULL, NULL, '2026-01-05 20:01:07', '2026-01-10 19:37:12'),
(68, 'DIN-O+-20260105-6896', 2, 'O+', 450, '2025-12-31 21:01:07', '2026-02-09', 'DISPATCHED', NULL, NULL, NULL, '2026-01-05 20:01:07', '2026-01-10 19:37:12'),
(69, 'DIN-O+-20260105-3200', 2, 'O+', 450, '2026-01-03 21:01:07', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:07', '2026-01-05 20:01:07'),
(70, 'DIN-O+-20260105-4187', 2, 'O+', 450, '2026-01-03 21:01:07', '2026-02-09', 'DISPATCHED', NULL, NULL, NULL, '2026-01-05 20:01:07', '2026-01-10 19:37:12'),
(71, 'DIN-O--20260105-6996', 2, 'O-', 450, '2025-12-26 21:01:07', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:07', '2026-01-05 20:01:07'),
(72, 'DIN-O--20260105-6387', 2, 'O-', 450, '2025-12-30 21:01:07', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:07', '2026-01-05 20:01:07'),
(73, 'DIN-O--20260105-5699', 2, 'O-', 450, '2026-01-01 21:01:07', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:07', '2026-01-05 20:01:07'),
(74, 'DIN-O--20260105-3724', 2, 'O-', 450, '2026-01-03 21:01:07', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:07', '2026-01-05 20:01:07'),
(75, 'DIN-O--20260105-6966', 2, 'O-', 450, '2025-12-29 21:01:07', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:07', '2026-01-05 20:01:07'),
(76, 'DIN-O--20260105-6702', 2, 'O-', 450, '2026-01-01 21:01:07', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:07', '2026-01-05 20:01:07'),
(77, 'DIN-O--20260105-8086', 2, 'O-', 450, '2026-01-03 21:01:07', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:07', '2026-01-05 20:01:07'),
(78, 'DIN-O--20260105-6297', 2, 'O-', 450, '2025-12-29 21:01:07', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:07', '2026-01-05 20:01:07'),
(79, 'DIN-O--20260105-8592', 2, 'O-', 450, '2025-12-31 21:01:07', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:07', '2026-01-05 20:01:07'),
(80, 'DIN-O--20260105-4640', 2, 'O-', 450, '2025-12-28 21:01:07', '2026-02-09', 'APPROVED', NULL, NULL, NULL, '2026-01-05 20:01:07', '2026-01-05 20:01:07');

-- --------------------------------------------------------

--
-- Table structure for table `campaigns`
--

CREATE TABLE `campaigns` (
  `campaign_id` int(11) NOT NULL,
  `campaign_name` varchar(200) NOT NULL,
  `organization` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `target_donors` int(11) DEFAULT NULL COMMENT 'Target number of donors',
  `actual_donors` int(11) DEFAULT 0,
  `target_units` int(11) DEFAULT NULL COMMENT 'Target blood units',
  `actual_units` int(11) DEFAULT 0,
  `status` enum('PLANNED','ACTIVE','COMPLETED','CANCELLED') DEFAULT 'PLANNED',
  `description` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dispatch`
--

CREATE TABLE `dispatch` (
  `dispatch_id` int(11) NOT NULL,
  `dispatch_number` varchar(50) NOT NULL COMMENT 'Format: DISP-YYYYMMDD-XXXX',
  `request_id` int(11) NOT NULL,
  `dispatch_date` datetime NOT NULL,
  `dispatched_by` int(11) DEFAULT NULL COMMENT 'Inventory Manager',
  `courier_name` varchar(100) DEFAULT NULL,
  `courier_phone` varchar(20) DEFAULT NULL,
  `vehicle_number` varchar(50) DEFAULT NULL,
  `status` enum('PREPARED','IN_TRANSIT','DELIVERED','CANCELLED') DEFAULT 'PREPARED',
  `delivery_date` datetime DEFAULT NULL,
  `received_by_name` varchar(100) DEFAULT NULL COMMENT 'Hospital staff who received',
  `received_by_signature` varchar(255) DEFAULT NULL COMMENT 'Path to signature image',
  `delivery_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dispatch`
--

INSERT INTO `dispatch` (`dispatch_id`, `dispatch_number`, `request_id`, `dispatch_date`, `dispatched_by`, `courier_name`, `courier_phone`, `vehicle_number`, `status`, `delivery_date`, `received_by_name`, `received_by_signature`, `delivery_notes`, `created_at`, `updated_at`) VALUES
(1, 'DISP-20260110-0001', 4, '2026-01-10 20:37:12', 4, '', '', '', 'IN_TRANSIT', NULL, NULL, NULL, NULL, '2026-01-10 19:37:12', '2026-01-10 19:37:12');

-- --------------------------------------------------------

--
-- Table structure for table `donors`
--

CREATE TABLE `donors` (
  `donor_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `gender` enum('MALE','FEMALE') NOT NULL,
  `date_of_birth` date NOT NULL,
  `blood_group` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `weight_kg` decimal(5,2) DEFAULT NULL COMMENT 'Weight in kilograms',
  `blood_pressure` varchar(20) DEFAULT NULL COMMENT 'Format: 120/80',
  `hemoglobin_level` decimal(4,2) DEFAULT NULL COMMENT 'g/dL',
  `medical_history` text DEFAULT NULL COMMENT 'Medical conditions, medications',
  `eligibility` enum('ELIGIBLE','NOT_ELIGIBLE','DEFERRED','PERMANENTLY_DEFERRED') DEFAULT 'NOT_ELIGIBLE',
  `deferral_reason` text DEFAULT NULL COMMENT 'Reason for deferral if not eligible',
  `deferral_until` date DEFAULT NULL COMMENT 'Date until which donor is deferred',
  `last_donation_date` date DEFAULT NULL,
  `total_donations` int(11) DEFAULT 0,
  `registered_by` int(11) DEFAULT NULL COMMENT 'User ID who registered the donor',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donors`
--

INSERT INTO `donors` (`donor_id`, `full_name`, `gender`, `date_of_birth`, `blood_group`, `phone`, `email`, `address`, `weight_kg`, `blood_pressure`, `hemoglobin_level`, `medical_history`, `eligibility`, `deferral_reason`, `deferral_until`, `last_donation_date`, `total_donations`, `registered_by`, `created_at`, `updated_at`) VALUES
(1, 'John Doe', 'MALE', '1990-05-15', 'O+', '+250788111111', 'john@example.com', NULL, 75.50, '120/80', 14.50, NULL, 'ELIGIBLE', NULL, NULL, NULL, 0, 2, '2026-01-05 12:16:25', '2026-01-05 12:16:25'),
(2, 'Jane Smith', 'FEMALE', '1995-08-22', 'A+', '+250788222222', 'jane@example.com', NULL, 62.00, '118/78', 13.20, NULL, 'ELIGIBLE', NULL, NULL, NULL, 0, 2, '2026-01-05 12:16:25', '2026-01-05 12:16:25'),
(3, 'Bob Johnson', 'MALE', '1988-03-10', 'B+', '+250788333333', 'bob@example.com', NULL, 80.00, '125/82', 15.00, NULL, 'ELIGIBLE', NULL, NULL, NULL, 0, 2, '2026-01-05 12:16:25', '2026-01-05 12:16:25'),
(4, 'Alice Williams', 'FEMALE', '1992-11-30', 'AB+', '+250788444444', 'alice@example.com', NULL, 58.50, '115/75', 12.80, NULL, 'ELIGIBLE', NULL, NULL, NULL, 0, 2, '2026-01-05 12:16:25', '2026-01-05 12:16:25');

-- --------------------------------------------------------

--
-- Table structure for table `lab_tests`
--

CREATE TABLE `lab_tests` (
  `test_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `test_hiv` enum('POSITIVE','NEGATIVE','PENDING') DEFAULT 'PENDING',
  `test_hbv` enum('POSITIVE','NEGATIVE','PENDING') DEFAULT 'PENDING' COMMENT 'Hepatitis B',
  `test_hcv` enum('POSITIVE','NEGATIVE','PENDING') DEFAULT 'PENDING' COMMENT 'Hepatitis C',
  `test_syphilis` enum('POSITIVE','NEGATIVE','PENDING') DEFAULT 'PENDING',
  `test_result` enum('APPROVED','REJECTED','PENDING') DEFAULT 'PENDING',
  `rejection_reason` text DEFAULT NULL COMMENT 'Reason if any test is positive',
  `tested_by` int(11) DEFAULT NULL COMMENT 'Lab technologist user ID',
  `tested_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('INFO','WARNING','EMERGENCY','SUCCESS','ERROR') DEFAULT 'INFO',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL COMMENT 'URL to related page',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `type`, `title`, `message`, `link`, `is_read`, `created_at`) VALUES
(1, 4, 'INFO', 'New Blood Request', 'Kigali University Teaching Hospital requested 4 units of A+ blood. Request #REQ-20260105-0002', 'dashboards/inventory/review-requests.php', 0, '2026-01-05 12:33:03'),
(2, 5, 'ERROR', 'Request Rejected', 'Your request #REQ-20260105-0001 has been rejected. Reason: ', 'dashboards/hospital/track-requests.php', 0, '2026-01-05 12:48:19'),
(3, 4, 'INFO', 'New Blood Request', 'Kigali University Teaching Hospital requested 6 units of B+ blood. Request #REQ-20260105-0003', 'dashboards/inventory/review-requests.php', 0, '2026-01-05 12:53:06'),
(4, 4, 'INFO', 'New Blood Request', 'Kigali University Teaching Hospital requested 8 units of O+ blood. Request #REQ-20260105-0004', 'dashboards/inventory/review-requests.php', 0, '2026-01-05 19:24:44'),
(5, 5, 'ERROR', 'Request Rejected', 'Your request #REQ-20260105-0003 has been rejected. Reason: ', 'dashboards/hospital/track-requests.php', 0, '2026-01-05 19:53:05'),
(6, 5, 'SUCCESS', 'Request Approved', 'Your request #REQ-20260105-0002 for 4 units of A+ has been approved.', 'dashboards/hospital/track-requests.php', 0, '2026-01-05 20:02:26'),
(7, 4, 'INFO', 'New Blood Request', 'Kigali University Teaching Hospital requested 7 units of A+ blood. Request #REQ-20260110-0001', 'dashboards/inventory/review-requests.php', 0, '2026-01-10 19:09:48'),
(8, 5, 'SUCCESS', 'Request Approved', 'Your request #REQ-20260105-0004 for 8 units of O+ has been approved.', 'dashboards/hospital/track-requests.php', 0, '2026-01-10 19:12:09'),
(9, 5, 'SUCCESS', 'Blood Dispatched', 'Your request #REQ-20260105-0004 has been dispatched and is on the way.', 'dashboards/hospital/track-requests.php', 0, '2026-01-10 19:37:12');

-- --------------------------------------------------------

--
-- Table structure for table `request_allocations`
--

CREATE TABLE `request_allocations` (
  `allocation_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `allocated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `allocated_by` int(11) DEFAULT NULL COMMENT 'Inventory Manager'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `request_allocations`
--

INSERT INTO `request_allocations` (`allocation_id`, `request_id`, `unit_id`, `allocated_at`, `allocated_by`) VALUES
(1, 2, 2, '2026-01-05 20:02:26', 4),
(2, 2, 1, '2026-01-05 20:02:26', 4),
(3, 2, 3, '2026-01-05 20:02:26', 4),
(4, 2, 10, '2026-01-05 20:02:26', 4),
(5, 4, 66, '2026-01-10 19:12:09', 4),
(6, 4, 67, '2026-01-10 19:12:09', 4),
(7, 4, 63, '2026-01-10 19:12:09', 4),
(8, 4, 61, '2026-01-10 19:12:09', 4),
(9, 4, 68, '2026-01-10 19:12:09', 4),
(10, 4, 65, '2026-01-10 19:12:09', 4),
(11, 4, 70, '2026-01-10 19:12:09', 4),
(12, 4, 64, '2026-01-10 19:12:09', 4);

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_id`, `setting_key`, `setting_value`, `description`, `updated_by`, `updated_at`) VALUES
(1, 'blood_expiry_days', '42', 'Number of days until blood expires after collection', NULL, '2026-01-05 12:16:25'),
(2, 'expiry_warning_days', '7', 'Days before expiry to show warning', NULL, '2026-01-05 12:16:25'),
(3, 'min_donor_age', '18', 'Minimum age for blood donation', NULL, '2026-01-05 12:16:25'),
(4, 'max_donor_age', '65', 'Maximum age for blood donation', NULL, '2026-01-05 12:16:25'),
(5, 'min_donor_weight', '50', 'Minimum weight in kg for blood donation', NULL, '2026-01-05 12:16:25'),
(6, 'min_hemoglobin_male', '13.0', 'Minimum hemoglobin level for male donors (g/dL)', NULL, '2026-01-05 12:16:25'),
(7, 'min_hemoglobin_female', '12.5', 'Minimum hemoglobin level for female donors (g/dL)', NULL, '2026-01-05 12:16:25'),
(8, 'donation_interval_days', '56', 'Minimum days between donations', NULL, '2026-01-05 12:16:25'),
(9, 'low_stock_threshold', '5', 'Alert when stock falls below this number', NULL, '2026-01-05 12:16:25'),
(10, 'reservation_expiry_hours', '24', 'Hours before reserved units are released back to stock', NULL, '2026-01-05 12:16:25');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_name` enum('System Administrator','Registration Officer','Laboratory Technologist','Inventory Manager','Hospital User','Red Cross','Minister Of Health') NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` enum('ACTIVE','INACTIVE','SUSPENDED') DEFAULT 'ACTIVE',
  `hospital_name` varchar(100) DEFAULT NULL COMMENT 'For Hospital Users',
  `organization_name` varchar(100) DEFAULT NULL COMMENT 'For Partners',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `role_name`, `phone`, `status`, `hospital_name`, `organization_name`, `created_at`, `updated_at`) VALUES
(1, 'System Admin', 'admin@example.com', 'password123', 'System Administrator', '+250788000001', 'ACTIVE', NULL, NULL, '2026-01-05 12:16:25', '2026-01-05 12:16:25'),
(2, 'Registration Officer', 'registration@example.com', 'password123', 'Registration Officer', '+250788000002', 'ACTIVE', NULL, NULL, '2026-01-05 12:16:25', '2026-01-05 12:16:25'),
(3, 'Lab Technologist', 'lab@example.com', 'password123', 'Laboratory Technologist', '+250788000003', 'ACTIVE', NULL, NULL, '2026-01-05 12:16:25', '2026-01-05 12:16:25'),
(4, 'Inventory Manager', 'inventory@example.com', 'password123', 'Inventory Manager', '+250788000004', 'ACTIVE', NULL, NULL, '2026-01-05 12:16:25', '2026-01-05 12:16:25'),
(5, 'Hospital User', 'hospital@example.com', 'password123', 'Hospital User', '+250788000005', 'ACTIVE', 'Kigali University Teaching Hospital', NULL, '2026-01-05 12:16:25', '2026-01-05 12:16:25'),
(6, 'Red Cross', 'redcross@example.com', 'password123', 'Red Cross', '+250788000006', 'ACTIVE', NULL, 'Rwanda Red Cross Society', '2026-01-05 12:16:25', '2026-01-05 12:16:25'),
(7, 'Minister Of Health', 'minister@example.com', 'password123', 'Minister Of Health', '+250788000007', 'ACTIVE', NULL, 'Ministry of Health', '2026-01-05 12:16:25', '2026-01-05 12:16:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `idx_donor` (`donor_id`),
  ADD KEY `idx_date` (`appointment_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_table` (`table_name`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `blood_requests`
--
ALTER TABLE `blood_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD UNIQUE KEY `request_number` (`request_number`),
  ADD KEY `idx_request_number` (`request_number`),
  ADD KEY `idx_hospital` (`hospital_user_id`),
  ADD KEY `idx_blood_group` (`blood_group`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_type` (`request_type`),
  ADD KEY `idx_priority` (`priority_score`),
  ADD KEY `reviewed_by` (`reviewed_by`);

--
-- Indexes for table `blood_units`
--
ALTER TABLE `blood_units`
  ADD PRIMARY KEY (`unit_id`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD KEY `idx_barcode` (`barcode`),
  ADD KEY `idx_donor` (`donor_id`),
  ADD KEY `idx_blood_group` (`blood_group`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_expiry` (`expiry_date`),
  ADD KEY `idx_collection_date` (`collection_date`),
  ADD KEY `collected_by` (`collected_by`);

--
-- Indexes for table `campaigns`
--
ALTER TABLE `campaigns`
  ADD PRIMARY KEY (`campaign_id`),
  ADD KEY `idx_organization` (`organization`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_dates` (`start_date`,`end_date`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `dispatch`
--
ALTER TABLE `dispatch`
  ADD PRIMARY KEY (`dispatch_id`),
  ADD UNIQUE KEY `dispatch_number` (`dispatch_number`),
  ADD KEY `idx_dispatch_number` (`dispatch_number`),
  ADD KEY `idx_request` (`request_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_dispatch_date` (`dispatch_date`),
  ADD KEY `fk_dispatch_user` (`dispatched_by`);

--
-- Indexes for table `donors`
--
ALTER TABLE `donors`
  ADD PRIMARY KEY (`donor_id`),
  ADD KEY `idx_blood_group` (`blood_group`),
  ADD KEY `idx_eligibility` (`eligibility`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `registered_by` (`registered_by`);

--
-- Indexes for table `lab_tests`
--
ALTER TABLE `lab_tests`
  ADD PRIMARY KEY (`test_id`),
  ADD KEY `idx_unit` (`unit_id`),
  ADD KEY `idx_result` (`test_result`),
  ADD KEY `idx_tested_by` (`tested_by`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `request_allocations`
--
ALTER TABLE `request_allocations`
  ADD PRIMARY KEY (`allocation_id`),
  ADD UNIQUE KEY `unique_allocation` (`request_id`,`unit_id`),
  ADD KEY `idx_request` (`request_id`),
  ADD KEY `idx_unit` (`unit_id`),
  ADD KEY `allocated_by` (`allocated_by`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role_name`),
  ADD KEY `idx_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `blood_requests`
--
ALTER TABLE `blood_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `blood_units`
--
ALTER TABLE `blood_units`
  MODIFY `unit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `campaigns`
--
ALTER TABLE `campaigns`
  MODIFY `campaign_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dispatch`
--
ALTER TABLE `dispatch`
  MODIFY `dispatch_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `donors`
--
ALTER TABLE `donors`
  MODIFY `donor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `lab_tests`
--
ALTER TABLE `lab_tests`
  MODIFY `test_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `request_allocations`
--
ALTER TABLE `request_allocations`
  MODIFY `allocation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`donor_id`) REFERENCES `donors` (`donor_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `blood_requests`
--
ALTER TABLE `blood_requests`
  ADD CONSTRAINT `blood_requests_ibfk_1` FOREIGN KEY (`hospital_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `blood_requests_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `blood_units`
--
ALTER TABLE `blood_units`
  ADD CONSTRAINT `blood_units_ibfk_1` FOREIGN KEY (`donor_id`) REFERENCES `donors` (`donor_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `blood_units_ibfk_2` FOREIGN KEY (`collected_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `campaigns`
--
ALTER TABLE `campaigns`
  ADD CONSTRAINT `campaigns_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `dispatch`
--
ALTER TABLE `dispatch`
  ADD CONSTRAINT `fk_dispatch_request` FOREIGN KEY (`request_id`) REFERENCES `blood_requests` (`request_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_dispatch_user` FOREIGN KEY (`dispatched_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `donors`
--
ALTER TABLE `donors`
  ADD CONSTRAINT `donors_ibfk_1` FOREIGN KEY (`registered_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `lab_tests`
--
ALTER TABLE `lab_tests`
  ADD CONSTRAINT `lab_tests_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `blood_units` (`unit_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lab_tests_ibfk_2` FOREIGN KEY (`tested_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `request_allocations`
--
ALTER TABLE `request_allocations`
  ADD CONSTRAINT `request_allocations_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `blood_requests` (`request_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `request_allocations_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `blood_units` (`unit_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `request_allocations_ibfk_3` FOREIGN KEY (`allocated_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD CONSTRAINT `system_settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
