-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 30, 2025 at 06:12 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `govledger`
--
CREATE DATABASE IF NOT EXISTS `govledger` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `govledger`;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `access_level` enum('super_admin','review_admin') DEFAULT 'review_admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `user_id`, `access_level`) VALUES
(1, 6, 'super_admin'),
(2, 9, 'review_admin'),
(3, 10, 'review_admin'),
(4, 34, 'review_admin');

-- --------------------------------------------------------

--
-- Table structure for table `agencies`
--

CREATE TABLE `agencies` (
  `agency_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `agency_name` varchar(255) NOT NULL,
  `office_code` varchar(50) NOT NULL,
  `position` varchar(100) DEFAULT NULL,
  `gov_id_number` varchar(100) DEFAULT NULL,
  `wallet_address` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agencies`
--

INSERT INTO `agencies` (`agency_id`, `user_id`, `agency_name`, `office_code`, `position`, `gov_id_number`, `wallet_address`) VALUES
(3, 1, 'Department of Public Works and Highways ', 'DPWH-REG-I-005', 'Administrative Officer II', 'DPWH-EMP-2025-001', '0x23E093083F66AfbC1882dAA72BA5Eb0C4DA5e1c8'),
(6, 23, 'Department of Education', 'DepEd-REG-I-003', 'Budget Officer', 'DEPED-EMP-2025-045', '0x1ca142Df1253270161Fbee46b736d6550EFA7C14'),
(7, 27, 'City Agriculture Office - Dagupan', 'DCAO-2025-AGRI-014', 'Director', 'DCAO-2025-EMP-01', '0x614a10287DbdE2d145FF5195b213C4bca54f900D'),
(9, 33, 'Commission on Higher Education', 'CHED-REG-005', 'Budget Officer', 'CHED-EMP-001', '0x7f03086C4e5bd73834D9B4f6D5e172E9caF0a346');

-- --------------------------------------------------------

--
-- Table structure for table `auditors`
--

CREATE TABLE `auditors` (
  `auditor_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `organization_name` varchar(255) NOT NULL,
  `office_code` varchar(50) NOT NULL,
  `accreditation_number` varchar(100) NOT NULL,
  `wallet_address` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `auditors`
--

INSERT INTO `auditors` (`auditor_id`, `user_id`, `organization_name`, `office_code`, `accreditation_number`, `wallet_address`) VALUES
(1, 13, 'Commission on Audit (COA)', 'COA-MAIN-001', 'ACC-987654321', '0x25c4db6D4516aFF6b4cC7573Ca3059bEe40a8eED'),
(2, 28, 'Department of Public Works and Highways (IAS)', 'DPWH-REG-I-005', 'ACC-123456789', '0xE2F6Eb157D86daCbcf7728A6d5D5770f8FC0a062'),
(3, 29, 'Department of Education (IAS)', 'DepEd-REG-I-003', 'ACC-123456710', '0x13f30210aAE72e7d28F44083a085A197Db5987b1'),
(4, 32, 'Commission on Audit', 'COA-REG-005', 'ACC-987654321', '0x0CB1a0db5468C1D317e1AcE285C5a4177922557A');

-- --------------------------------------------------------

--
-- Table structure for table `audits`
--

CREATE TABLE `audits` (
  `audit_id` int(11) NOT NULL,
  `record_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `summary` text NOT NULL,
  `result` enum('PASSED','FLAGGED','REJECTED') NOT NULL,
  `document_hash` varchar(255) NOT NULL,
  `document_cid` varchar(255) NOT NULL,
  `tx_hash` varchar(255) NOT NULL,
  `audit_by` int(11) NOT NULL,
  `audited_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_trail`
--

CREATE TABLE `audit_trail` (
  `trail_id` int(11) NOT NULL,
  `audit_id` int(11) NOT NULL,
  `action` enum('VIEWED','COMMENTED','ESCALATED','DISPUTED','DOWNLOADED') NOT NULL,
  `note` text DEFAULT NULL,
  `performed_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `project_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `document_path` varchar(255) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `records`
--

CREATE TABLE `records` (
  `record_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `record_type` enum('budget','invoice','contract') NOT NULL,
  `amount` decimal(18,2) NOT NULL,
  `document_hash` char(66) NOT NULL,
  `document_cid` varchar(255) NOT NULL,
  `blockchain_tx` varchar(66) DEFAULT NULL,
  `submitted_by` int(11) NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `account_type` enum('agency','auditor','citizen','admin') NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `contact_number` varchar(50) DEFAULT NULL,
  `office_address` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','suspended') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `account_type`, `email`, `password_hash`, `full_name`, `contact_number`, `office_address`, `status`, `created_at`) VALUES
(1, 'agency', 'dpwhsample@gov.ph', '$2y$10$pTgrm8tGIMpgcxN8Wyf7WON95CpSMin93sUH2WPGq3.NCcWfuoFTm', 'John DPWH', '09959652206', 'DPWH-EMP-2025-001', 'approved', '2025-09-28 07:32:06'),
(2, 'citizen', 'catabayjosiah19@gmail.com', '$2y$10$ifofJ.VZyEALBjD/Y3Qpa.1a5jLce4Kum2ezeXkJUEFGMRTXycvcK', 'Josiah Catabay', '09959652206', NULL, 'approved', '2025-09-29 12:57:24'),
(3, 'citizen', 'emmanHao19@gmail.com', '$2b$10$2nZInZhp8ouAX6VaOJpO5.hiqHGkVzw2Naz7pxBLVodAHZNqdEcka', 'Ha0 Emman', '09959652210', NULL, 'suspended', '2025-10-01 03:37:16'),
(4, 'citizen', 'gaspelgaspel@gmail.com', '$2b$10$WsEf0rdW8UHpFqvi.3mOq.Sl2WWT6tKvJ4sbehRi7LBtauNJqEIsi', 'Gaspel Ka Nalang', '09367546849', NULL, 'suspended', '2025-10-02 09:23:49'),
(5, 'citizen', 'ronron123@gmail.com', '$2y$10$aXjB051BOeUDQa2R/Mqnaup4LE75twNb9aiTGtsX0s598/kSnB58C', 'Ron Arjie Rabaya', '09959652102', NULL, 'approved', '2025-10-04 13:09:00'),
(6, 'admin', 'superadmin@gov.ph', '$2y$10$wijyhLIxIz6ax2Rjw2pc7Oo6uOcs8/vEThxaJ7d5GLi7eKbwHYzuS', 'Super Admin', NULL, 'City Hall of Dagupan Address: Dagupan City Hall Complex, AB Fernandez Ave., Dagupan City', 'approved', '2025-10-05 13:45:59'),
(9, 'admin', 'juanAdmin@gov.ph', '$2y$10$J.KX/GjINgpoZzawu1H..efaWPFAYcudNVB5fQzocaZEkgB6k.DYC', 'Juan Admin', '0999123456', NULL, 'approved', '2025-10-06 12:08:14'),
(10, 'admin', 'adminarjie@gov.ph', '$2y$10$GROgy030T6YqsabCZTabAeHZMA5kBfFkWIAW5DLyvKw/tTtzBmx2q', 'Arjie Admin Rabaya', '0995912345', NULL, 'approved', '2025-10-08 00:45:32'),
(13, 'auditor', 'auditor.juan@coa.gov.ph', '$2y$10$WMaUdzx56/X.B.n3/WIevO.z4W7qP0JIPvSUS2Db7C0DHh2TNE236', 'John Sabado', '09959651109', NULL, 'approved', '2025-10-13 09:35:57'),
(14, 'citizen', 'martin19@gmail.com', '$2y$10$6F0UcHv6g/so0VRVBkDapuituXjXjAyvILXsEVd9bGdefxakGfamG', 'Martin', '09959652206', NULL, 'approved', '2025-10-16 02:36:01'),
(15, 'citizen', 'martin@gmail.com', '$2y$10$XdlHFOiG1JDmWhB1HAYfbOvQOHSMznKKEP8Znqpcat4cxrPUCC/Oy', 'Martin', '09959652111', NULL, 'approved', '2025-10-16 02:36:23'),
(16, 'citizen', 'marco@gmail.com', '$2y$10$Njy3Hwu7NYHcbjZ/xdSZXOYHGUNCGBMY0gSCO8oDfm4Au9nSkudsC', 'Marco', '09959652111', NULL, 'approved', '2025-10-16 02:42:21'),
(17, 'citizen', 'kaia19@gmail.com', '$2y$10$iUjvxflA.VTsuqw38Y46q.LyM74/hf3MdXfRCTQKJnZAqKmmeHp9a', 'Kaia', '09959652111', NULL, 'approved', '2025-10-16 02:44:27'),
(18, 'citizen', 'neon@gmail.com', '$2y$10$WCCMmAlZaIIMLyod6pRjR.5/bVcLoP6JxWoRmhhsdBCqsCL.bKGv6', 'neon', '09959652111', NULL, 'approved', '2025-10-16 02:48:47'),
(19, 'citizen', 'rey9@gmail.com', '$2y$10$TTer5wMtJmFZz6xDs3iP.epzoz/IDigYoq0P57pTzLyxMuLTI9TNy', 'Rey', '09367546849', NULL, 'approved', '2025-10-16 02:49:29'),
(21, 'citizen', 'anthony19@gmail.com', '$2y$10$qLrErt1yJHc6z.tHyP8HQeZ/H/d.1UveHiqGiYxChVs6akHDCsP3S', 'Anthony', '09959652206', NULL, 'approved', '2025-10-16 02:51:17'),
(22, 'citizen', 'mc2@gmail.com', '$2y$10$Dc1OXsviziV95bVQgtKb8.aVNqH01XlKF4p2C92sL3G7aKE7/73we', 'mc', '545454545454', NULL, 'approved', '2025-10-16 02:54:01'),
(23, 'agency', 'hannahDepEd@gov.ph', '$2y$10$milIfSu7wLQ8ypYe.zcSxOfr7ZVK1sIP520MAwQ3bR1.Fzo8QJJ9e', 'Hannah Cabrera', '09959652102', NULL, 'approved', '2025-10-16 03:21:31'),
(24, 'citizen', 'vince@gmail.com', '$2y$10$oVToDkRvn5p32ZFzr87/eeTeFO3uewCLx8B0AVf3bfUXAiJmTjWCO', 'Vincent', '09959652206', NULL, 'approved', '2025-10-25 15:18:50'),
(25, 'citizen', 'tr@gmail.com', '$2y$10$My0vnoLzqHLwZHu4TuFuluEJgZnxXq24d6J/N7nvFhqJCmaxwYvI6', 'TYRON', '1233263273', NULL, 'approved', '2025-10-26 10:45:29'),
(26, 'citizen', 'maja@gmail.com', '$2y$10$Ol9Ye2apSw8VT088CQIaye5xgvkY/.hh1uMv..xH548NnYK0Sh/1S', 'Maja Catabay', '09959652206', NULL, 'approved', '2025-10-27 16:18:48'),
(27, 'agency', 'dagupanAgriculture@gov.ph', '$2y$10$iJphuWp7MHx1bL6x7sMR4.8SGQyxyI2Q2rxHiYsYVlTmFJdz.z4mG', 'Renz A. Rivera', '09959651106', NULL, 'approved', '2025-10-28 06:27:11'),
(28, 'auditor', 'jansenDpwh@gov.ph', '$2y$10$HUyOdQ1ULBZcBwykJFEZSeLerPs1RyWCbP3lpOMonNAwAM6b0iZNG', 'Jansen D. Dalisay', '09959651107', NULL, 'approved', '2025-10-28 07:12:26'),
(29, 'auditor', 'markDeped@gov.ph', '$2y$10$vtt7cG.lp82vSN0jpdUZKOuNFiv9pgjPj58T.ajuyKc2lQXpqGczi', 'Mark A. Aquino', '09959652117', NULL, 'approved', '2025-10-28 07:19:20'),
(31, 'citizen', 'juandelacruz@gmail.com', '$2y$10$bU9gG/HVaIVNIpWGdTe82OymM0T4fUFbF9JM8IGRGkQ0unb0yo2nS', 'Juan Dela Cruz', '09959652206', NULL, 'approved', '2025-10-29 03:32:09'),
(32, 'auditor', 'juanCOA@gmail.com', '$2y$10$mYcgCkiWBfZSaYYxh9aAE.XtgX1d5yKjHDBY8RtrR01s8c12Q1JMS', 'Juan Dela Cruz', '09959652206', NULL, 'approved', '2025-10-29 03:35:11'),
(33, 'agency', 'ched@gov.ph', '$2y$10$MWI8o5VsSxWt3jN7MsLLGOhjcojplc5DUdfguc1/Lpbfoxa/2TE9u', 'Juan Dela Cruz', '09959652206', NULL, 'approved', '2025-10-29 03:46:32'),
(34, 'admin', 'mcadmin@admin.com', '$2y$10$eZ/XWJ1/cKaTIqgaeFT3TuE4YrzaCsBG4mUvF4PsDKUkLH6ifgTtS', 'Mc Admin', '09959652206', NULL, 'approved', '2025-10-29 03:49:28');

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `auto_approve_citizen` BEFORE INSERT ON `users` FOR EACH ROW BEGIN
    IF NEW.account_type = 'citizen' THEN
        SET NEW.status = 'approved';
    END IF;
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `agencies`
--
ALTER TABLE `agencies`
  ADD PRIMARY KEY (`agency_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `auditors`
--
ALTER TABLE `auditors`
  ADD PRIMARY KEY (`auditor_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `audits`
--
ALTER TABLE `audits`
  ADD PRIMARY KEY (`audit_id`),
  ADD KEY `audits_ibfk_1` (`record_id`),
  ADD KEY `audit_by` (`audit_by`);

--
-- Indexes for table `audit_trail`
--
ALTER TABLE `audit_trail`
  ADD PRIMARY KEY (`trail_id`),
  ADD KEY `audit_id` (`audit_id`),
  ADD KEY `performed_by` (`performed_by`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`project_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `records`
--
ALTER TABLE `records`
  ADD PRIMARY KEY (`record_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `submitted_by` (`submitted_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `agencies`
--
ALTER TABLE `agencies`
  MODIFY `agency_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `auditors`
--
ALTER TABLE `auditors`
  MODIFY `auditor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `audits`
--
ALTER TABLE `audits`
  MODIFY `audit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `audit_trail`
--
ALTER TABLE `audit_trail`
  MODIFY `trail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `records`
--
ALTER TABLE `records`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `admins_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `agencies`
--
ALTER TABLE `agencies`
  ADD CONSTRAINT `agencies_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `auditors`
--
ALTER TABLE `auditors`
  ADD CONSTRAINT `auditors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `audits`
--
ALTER TABLE `audits`
  ADD CONSTRAINT `audits_ibfk_1` FOREIGN KEY (`record_id`) REFERENCES `records` (`record_id`),
  ADD CONSTRAINT `audits_ibfk_2` FOREIGN KEY (`audit_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `audit_trail`
--
ALTER TABLE `audit_trail`
  ADD CONSTRAINT `audit_trail_ibfk_1` FOREIGN KEY (`audit_id`) REFERENCES `audits` (`audit_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `audit_trail_ibfk_2` FOREIGN KEY (`performed_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `records`
--
ALTER TABLE `records`
  ADD CONSTRAINT `records_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`),
  ADD CONSTRAINT `records_ibfk_2` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
