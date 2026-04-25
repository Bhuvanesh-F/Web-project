-- ============================================================
-- VitalCare Clinic - Database Schema
-- ICT2213 Web Technologies and Security - Week 10
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- Create and select database
CREATE DATABASE IF NOT EXISTS `vitalcare_db`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `vitalcare_db`;

-- --------------------------------------------------------
-- Table: human_patients
-- --------------------------------------------------------
CREATE TABLE `human_patients` (
  `patient_id`    CHAR(36)      NOT NULL,
  `first_name`    VARCHAR(100)  NOT NULL,
  `last_name`     VARCHAR(100)  NOT NULL,
  `dob`           DATE          DEFAULT NULL,
  `nic_passport`  VARCHAR(50)   DEFAULT NULL,
  `gender`        ENUM('male','female','other','unknown') DEFAULT 'unknown',
  `phone`         VARCHAR(20)   DEFAULT NULL,
  `email`         VARCHAR(255)  NOT NULL,
  `address`       TEXT          DEFAULT NULL,
  `password_hash` VARCHAR(255)  NOT NULL,
  `created_at`    DATETIME      DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`patient_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: human_doctors
-- --------------------------------------------------------
CREATE TABLE `human_doctors` (
  `doctor_id`     CHAR(36)      NOT NULL,
  `full_name`     VARCHAR(255)  NOT NULL,
  `speciality`    VARCHAR(100)  DEFAULT NULL,
  `email`         VARCHAR(255)  NOT NULL,
  `education`     TEXT          DEFAULT NULL,
  `password_hash` VARCHAR(255)  NOT NULL,
  `address`       TEXT          DEFAULT NULL,
  `experience`    INT(11)       DEFAULT NULL CHECK (`experience` >= 0),
  `fees`          DECIMAL(10,2) DEFAULT 0.00,
  `about`         TEXT          DEFAULT NULL,
  `created_at`    DATETIME      DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`doctor_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: human_nurses
-- --------------------------------------------------------
CREATE TABLE `human_nurses` (
  `nurse_id`      CHAR(36)      NOT NULL,
  `full_name`     VARCHAR(255)  NOT NULL,
  `speciality`    VARCHAR(100)  DEFAULT NULL,
  `email`         VARCHAR(255)  NOT NULL,
  `education`     TEXT          DEFAULT NULL,
  `password_hash` VARCHAR(255)  NOT NULL,
  `address`       TEXT          DEFAULT NULL,
  `experience`    INT(11)       DEFAULT NULL CHECK (`experience` >= 0),
  `about`         TEXT          DEFAULT NULL,
  `created_at`    DATETIME      DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`nurse_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: human_admins
-- --------------------------------------------------------
CREATE TABLE `human_admins` (
  `admin_id`      INT(11)       NOT NULL AUTO_INCREMENT,
  `username`      VARCHAR(50)   NOT NULL,
  `email`         VARCHAR(255)  NOT NULL,
  `password_hash` VARCHAR(255)  NOT NULL,
  `created_at`    DATETIME      DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: human_appointments
-- --------------------------------------------------------
CREATE TABLE `human_appointments` (
  `appointment_id`  CHAR(36)     NOT NULL,
  `patient_id`      CHAR(36)     NOT NULL,
  `doctor_id`       CHAR(36)     DEFAULT NULL,
  `speciality`      VARCHAR(100) DEFAULT NULL,
  `appointment_date` DATE        NOT NULL,
  `preferred_time`  VARCHAR(10)  DEFAULT NULL,
  `notes`           TEXT         DEFAULT NULL,
  `status`          ENUM('pending','approved','done','cancelled') DEFAULT 'pending',
  `created_at`      DATETIME     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`appointment_id`),
  KEY `patient_id` (`patient_id`),
  KEY `doctor_id` (`doctor_id`),
  CONSTRAINT `appt_patient_fk` FOREIGN KEY (`patient_id`)
    REFERENCES `human_patients` (`patient_id`) ON DELETE CASCADE,
  CONSTRAINT `appt_doctor_fk`  FOREIGN KEY (`doctor_id`)
    REFERENCES `human_doctors`  (`doctor_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: human_medical_records
-- --------------------------------------------------------
CREATE TABLE `human_medical_records` (
  `record_id`    CHAR(36) NOT NULL,
  `doctor_id`    CHAR(36) DEFAULT NULL,
  `patient_id`   CHAR(36) DEFAULT NULL,
  `record_date`  DATETIME DEFAULT CURRENT_TIMESTAMP,
  `diagnosis`    TEXT     DEFAULT NULL,
  `treatment`    TEXT     DEFAULT NULL,
  `prescription` TEXT     DEFAULT NULL,
  `notes`        TEXT     DEFAULT NULL,
  PRIMARY KEY (`record_id`),
  KEY `doctor_id`  (`doctor_id`),
  KEY `patient_id` (`patient_id`),
  CONSTRAINT `rec_doctor_fk`  FOREIGN KEY (`doctor_id`)
    REFERENCES `human_doctors`  (`doctor_id`) ON DELETE SET NULL,
  CONSTRAINT `rec_patient_fk` FOREIGN KEY (`patient_id`)
    REFERENCES `human_patients` (`patient_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: contact_messages
-- --------------------------------------------------------
CREATE TABLE `contact_messages` (
  `id`         INT(11)      NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(255) NOT NULL,
  `email`      VARCHAR(255) NOT NULL,
  `subject`    VARCHAR(255) DEFAULT NULL,
  `message`    TEXT         NOT NULL,
  `created_at` DATETIME     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Seed Data
-- --------------------------------------------------------

-- Admin account  (password: Admin@1234)
INSERT INTO `human_admins` (`username`, `email`, `password_hash`) VALUES
('admin', 'admin@vitalcare.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Sample doctors  (password for all: Doctor@1234)
INSERT INTO `human_doctors`
  (`doctor_id`,`full_name`,`speciality`,`email`,`education`,`password_hash`,`experience`,`fees`,`about`)
VALUES
  (UUID(),'Dr. Alice Martin','General Medicine','alice.martin@vitalcare.com',
   'MBBS, MD General Practice','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
   8, 1500.00,'Experienced general practitioner with 8 years in primary care.'),
  (UUID(),'Dr. Robert Chen','Cardiology','robert.chen@vitalcare.com',
   'MBBS, MD Cardiology, FRCP','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
   12, 2500.00,'Specialist cardiologist with expertise in interventional procedures.'),
  (UUID(),'Dr. Priya Sharma','Dermatology','priya.sharma@vitalcare.com',
   'MBBS, MD Dermatology','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
   6, 2000.00,'Dermatology specialist focusing on skin health and cosmetic procedures.'),
  (UUID(),'Dr. James Okonkwo','Pediatrics','james.okonkwo@vitalcare.com',
   'MBBS, MD Pediatrics','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
   10, 1800.00,'Dedicated pediatrician providing care for children from birth to 18.');

-- Sample nurses  (password: Nurse@1234)
INSERT INTO `human_nurses`
  (`nurse_id`,`full_name`,`speciality`,`email`,`education`,`password_hash`,`experience`,`about`)
VALUES
  (UUID(),'Nurse Sarah Johnson','General Care','sarah.j@vitalcare.com',
   'BSc Nursing','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
   5,'Compassionate nurse specialising in patient care and recovery.'),
  (UUID(),'Nurse Michael Brown','Emergency Care','michael.b@vitalcare.com',
   'BSc Nursing, Emergency Care Certificate','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
   7,'Emergency care nurse with critical response training.');

COMMIT;
