-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 05, 2025 at 03:33 PM
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
-- Database: `vitalcare_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `patient_name` varchar(100) DEFAULT NULL,
  `appointment_date` date DEFAULT NULL,
  `status` enum('Pending','Completed','Cancelled') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `patient_name`, `appointment_date`, `status`) VALUES
(1, 'John Doe', '2025-12-04', 'Pending'),
(2, 'Jane Smith', '2025-12-04', 'Completed'),
(3, 'Alice Jones', '2025-12-05', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` char(36) NOT NULL,
  `actor_role` varchar(50) DEFAULT NULL,
  `actor_id` char(36) DEFAULT NULL,
  `action` text NOT NULL,
  `object_type` varchar(255) DEFAULT NULL,
  `object_id` char(36) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` char(36) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `doctor_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `specialty` varchar(100) NOT NULL,
  `education` text DEFAULT NULL,
  `address_line1` varchar(255) DEFAULT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `experience` varchar(50) DEFAULT NULL,
  `fees` decimal(10,2) DEFAULT NULL,
  `about` text DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `license_number` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `human_admins`
--

CREATE TABLE `human_admins` (
  `admin_id` int(15) NOT NULL,
  `username` varchar(30) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password_hash` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `human_admins`
--

INSERT INTO `human_admins` (`admin_id`, `username`, `email`, `password_hash`, `created_at`) VALUES
(1, 'admin', 'admin@clinic.com', '$2y$10$2lq7N6M4EwzXgK3jXo9w9.JtQ.1Q5F1kP/I7x/5L5E.2D/C4E6F7G', '2025-12-04 22:20:54'),
(2, 'ami24', 'amirah@gmail.com', '$2y$10$e4i01apV/xRgv11mZM.5EutFxRl0qRxB0MtWbHlaR09omy823syEy', '2025-12-05 01:59:57');

-- --------------------------------------------------------

--
-- Table structure for table `human_appointments`
--

CREATE TABLE `human_appointments` (
  `appointment_id` char(36) NOT NULL,
  `patient_id` char(36) NOT NULL,
  `doctor_id` char(36) DEFAULT NULL,
  `speciality` varchar(255) DEFAULT NULL,
  `appointment_date` date NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','approved','done','cancelled') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `human_doctors`
--

CREATE TABLE `human_doctors` (
  `doctor_id` char(36) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `speciality` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `education` text DEFAULT NULL,
  `password_hash` text NOT NULL,
  `address` text DEFAULT NULL,
  `experience` int(11) DEFAULT NULL CHECK (`experience` >= 0),
  `fees` decimal(10,2) DEFAULT 0.00,
  `about` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `human_doctors`
--

INSERT INTO `human_doctors` (`doctor_id`, `full_name`, `speciality`, `email`, `education`, `password_hash`, `address`, `experience`, `fees`, `about`, `created_at`) VALUES
('d1', 'Dr Alice', 'General', 'alice@example.com', NULL, '$2y$10$examplehash3', NULL, NULL, 0.00, NULL, '2025-12-04 22:03:36'),
('d2', 'Dr Bob', 'Cardiology', 'bob@example.com', NULL, '$2y$10$examplehash4', NULL, NULL, 0.00, NULL, '2025-12-04 22:03:36');

-- --------------------------------------------------------

--
-- Table structure for table `human_medical_records`
--

CREATE TABLE `human_medical_records` (
  `record_id` char(36) NOT NULL,
  `doctor_id` char(36) DEFAULT NULL,
  `patient_id` char(36) DEFAULT NULL,
  `record_date` datetime DEFAULT current_timestamp(),
  `diagnosis` text DEFAULT NULL,
  `treatment` text DEFAULT NULL,
  `prescription` text DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `human_nurses`
--

CREATE TABLE `human_nurses` (
  `nurse_id` char(36) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `speciality` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `education` text DEFAULT NULL,
  `password_hash` text NOT NULL,
  `address` text DEFAULT NULL,
  `experience` int(11) DEFAULT NULL CHECK (`experience` >= 0),
  `about` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `human_patients`
--

CREATE TABLE `human_patients` (
  `patient_id` char(36) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `dob` date DEFAULT NULL,
  `nic_passport` varchar(255) DEFAULT NULL,
  `gender` enum('male','female','other','unknown') DEFAULT 'unknown',
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `password_hash` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `human_patients`
--

INSERT INTO `human_patients` (`patient_id`, `first_name`, `last_name`, `dob`, `nic_passport`, `gender`, `phone`, `email`, `address`, `password_hash`, `created_at`) VALUES
('p1', 'John', 'Doe', NULL, NULL, 'unknown', NULL, 'john@example.com', NULL, '$2y$10$examplehash1', '2025-12-04 22:03:36'),
('p2', 'Jane', 'Smith', NULL, NULL, 'unknown', NULL, 'jane@example.com', NULL, '$2y$10$examplehash2', '2025-12-04 22:03:36');

-- --------------------------------------------------------

--
-- Table structure for table `human_reviews`
--

CREATE TABLE `human_reviews` (
  `review_id` char(36) NOT NULL,
  `patient_id` char(36) DEFAULT NULL,
  `doctor_id` char(36) DEFAULT NULL,
  `rating` tinyint(4) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nurse_checklist`
--

CREATE TABLE `nurse_checklist` (
  `checklist_id` char(36) NOT NULL,
  `nurse_id` char(36) NOT NULL,
  `role` enum('human_patient','pet_owner','human_doctor','pet_doctor','human_nurse','pet_nurse','human_admin','pet_admin','receptionist') NOT NULL,
  `patient_id` char(36) DEFAULT NULL,
  `pet_id` char(36) DEFAULT NULL,
  `task_description` text NOT NULL,
  `completed` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pets`
--

CREATE TABLE `pets` (
  `pet_id` char(36) NOT NULL,
  `owner_id` char(36) NOT NULL,
  `pet_name` varchar(255) NOT NULL,
  `species` varchar(255) DEFAULT NULL,
  `breed` varchar(255) DEFAULT NULL,
  `age` int(11) DEFAULT NULL CHECK (`age` >= 0),
  `gender` enum('male','female','other','unknown') DEFAULT 'unknown',
  `vaccination_status` enum('unknown','not_vaccinated','partially_vaccinated','fully_vaccinated') DEFAULT 'unknown',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pets`
--

INSERT INTO `pets` (`pet_id`, `owner_id`, `pet_name`, `species`, `breed`, `age`, `gender`, `vaccination_status`, `created_at`) VALUES
('pet1', 'o1', 'Rex', 'Dog', 'Labrador', 5, 'unknown', 'unknown', '2025-12-04 22:03:36'),
('pet2', 'o2', 'Milo', 'Cat', 'Siamese', 3, 'unknown', 'unknown', '2025-12-04 22:03:36');

-- --------------------------------------------------------

--
-- Table structure for table `pet_admins`
--

CREATE TABLE `pet_admins` (
  `admin_id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pet_appointments`
--

CREATE TABLE `pet_appointments` (
  `appointment_id` char(36) NOT NULL,
  `pet_id` char(36) NOT NULL,
  `doctor_id` char(36) DEFAULT NULL,
  `speciality` varchar(255) DEFAULT NULL,
  `appointment_date` date NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','approved','done','cancelled') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pet_doctors`
--

CREATE TABLE `pet_doctors` (
  `doctor_id` char(36) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `speciality` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `education` text DEFAULT NULL,
  `password_hash` text NOT NULL,
  `address` text DEFAULT NULL,
  `experience` int(11) DEFAULT NULL CHECK (`experience` >= 0),
  `fees` decimal(10,2) DEFAULT NULL,
  `about` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pet_doctors`
--

INSERT INTO `pet_doctors` (`doctor_id`, `full_name`, `speciality`, `email`, `education`, `password_hash`, `address`, `experience`, `fees`, `about`, `created_at`) VALUES
('pd1', 'Dr Vet', 'Veterinary', 'vet@example.com', NULL, '$2y$10$examplehash7', NULL, NULL, NULL, NULL, '2025-12-04 22:03:36');

-- --------------------------------------------------------

--
-- Table structure for table `pet_medical_records`
--

CREATE TABLE `pet_medical_records` (
  `record_id` char(36) NOT NULL,
  `doctor_id` char(36) DEFAULT NULL,
  `pet_id` char(36) DEFAULT NULL,
  `record_date` datetime DEFAULT current_timestamp(),
  `diagnosis` text DEFAULT NULL,
  `treatment` text DEFAULT NULL,
  `prescription` text DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pet_nurses`
--

CREATE TABLE `pet_nurses` (
  `nurse_id` char(36) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `speciality` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `education` text DEFAULT NULL,
  `password_hash` text NOT NULL,
  `address` text DEFAULT NULL,
  `experience` int(11) DEFAULT NULL CHECK (`experience` >= 0),
  `about` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pet_owners`
--

CREATE TABLE `pet_owners` (
  `owner_id` char(36) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `gender` enum('male','female','other','unknown') DEFAULT 'unknown',
  `dob` date DEFAULT NULL,
  `contact` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password_hash` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pet_owners`
--

INSERT INTO `pet_owners` (`owner_id`, `full_name`, `gender`, `dob`, `contact`, `email`, `address`, `username`, `password_hash`, `created_at`) VALUES
('o1', 'Bob Owner', 'unknown', NULL, NULL, 'bob@example.com', NULL, 'bobowner', '$2y$10$examplehash5', '2025-12-04 22:03:36'),
('o2', 'Alice Owner', 'unknown', NULL, NULL, 'alice@example.com', NULL, 'aliceowner', '$2y$10$examplehash6', '2025-12-04 22:03:36');

-- --------------------------------------------------------

--
-- Table structure for table `receptionists`
--

CREATE TABLE `receptionists` (
  `receptionist_id` char(36) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password_hash` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` enum('Doctor','Nurse') NOT NULL,
  `specialty` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `name`, `role`, `specialty`, `phone`, `email`) VALUES
(1, 'Dr. Gregory House', 'Doctor', 'Diagnostics', '555-0199', 'house@clinic.com'),
(2, 'Nurse Joy', 'Nurse', 'General Care', '555-0200', 'joy@clinic.com'),
(3, 'amirah', 'Doctor', 'pediatrics', '12345678', 'doctor@gmail.com'),
(4, 'bob', 'Doctor', 'dermatology', '123456789', 'bob@gmail.com');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`doctor_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `human_admins`
--
ALTER TABLE `human_admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `human_appointments`
--
ALTER TABLE `human_appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `human_doctors`
--
ALTER TABLE `human_doctors`
  ADD PRIMARY KEY (`doctor_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `human_medical_records`
--
ALTER TABLE `human_medical_records`
  ADD PRIMARY KEY (`record_id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `human_nurses`
--
ALTER TABLE `human_nurses`
  ADD PRIMARY KEY (`nurse_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `human_patients`
--
ALTER TABLE `human_patients`
  ADD PRIMARY KEY (`patient_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `human_reviews`
--
ALTER TABLE `human_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `nurse_checklist`
--
ALTER TABLE `nurse_checklist`
  ADD PRIMARY KEY (`checklist_id`);

--
-- Indexes for table `pets`
--
ALTER TABLE `pets`
  ADD PRIMARY KEY (`pet_id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `pet_admins`
--
ALTER TABLE `pet_admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `pet_appointments`
--
ALTER TABLE `pet_appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `pet_id` (`pet_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `pet_doctors`
--
ALTER TABLE `pet_doctors`
  ADD PRIMARY KEY (`doctor_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `pet_medical_records`
--
ALTER TABLE `pet_medical_records`
  ADD PRIMARY KEY (`record_id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `pet_id` (`pet_id`);

--
-- Indexes for table `pet_nurses`
--
ALTER TABLE `pet_nurses`
  ADD PRIMARY KEY (`nurse_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `pet_owners`
--
ALTER TABLE `pet_owners`
  ADD PRIMARY KEY (`owner_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `receptionists`
--
ALTER TABLE `receptionists`
  ADD PRIMARY KEY (`receptionist_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `doctor_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `human_admins`
--
ALTER TABLE `human_admins`
  MODIFY `admin_id` int(15) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `human_appointments`
--
ALTER TABLE `human_appointments`
  ADD CONSTRAINT `human_appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `human_patients` (`patient_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `human_appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `human_doctors` (`doctor_id`) ON DELETE SET NULL;

--
-- Constraints for table `human_medical_records`
--
ALTER TABLE `human_medical_records`
  ADD CONSTRAINT `human_medical_records_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `human_doctors` (`doctor_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `human_medical_records_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `human_patients` (`patient_id`) ON DELETE CASCADE;

--
-- Constraints for table `human_reviews`
--
ALTER TABLE `human_reviews`
  ADD CONSTRAINT `human_reviews_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `human_patients` (`patient_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `human_reviews_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `human_doctors` (`doctor_id`) ON DELETE SET NULL;

--
-- Constraints for table `pets`
--
ALTER TABLE `pets`
  ADD CONSTRAINT `pets_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `pet_owners` (`owner_id`) ON DELETE CASCADE;

--
-- Constraints for table `pet_appointments`
--
ALTER TABLE `pet_appointments`
  ADD CONSTRAINT `pet_appointments_ibfk_1` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`pet_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pet_appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `pet_doctors` (`doctor_id`) ON DELETE SET NULL;

--
-- Constraints for table `pet_medical_records`
--
ALTER TABLE `pet_medical_records`
  ADD CONSTRAINT `pet_medical_records_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `pet_doctors` (`doctor_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pet_medical_records_ibfk_2` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`pet_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
