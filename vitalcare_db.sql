-- ============================================================
--  VitalCare Clinic — Complete MySQL Database
--  Generated from frontend UI analysis (HTML/CSS/JS)
--  Compatible with Laravel migrations (id, created_at, updated_at)
--  Charset: utf8mb4 | Engine: InnoDB | Normalization: 3NF
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- ============================================================
-- 1. CREATE DATABASE
-- ============================================================

CREATE DATABASE IF NOT EXISTS `vitalcare_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `vitalcare_db`;


-- ============================================================
-- 2. USERS (unified auth table — role-based)
--    Sources: patient-portal registration (Step 3/3 account setup)
--             login forms: PatientID or Email + Password
--             Doctor/Nurse/Admin/Receptionist portals
-- ============================================================

CREATE TABLE `users` (
  `id`              CHAR(36)        NOT NULL DEFAULT (UUID()),
  `username`        VARCHAR(50)     NOT NULL,
  `email`           VARCHAR(255)    NOT NULL,
  `password_hash`   VARCHAR(255)    NOT NULL,
  `role`            ENUM(
                      'patient',
                      'doctor',
                      'nurse',
                      'receptionist',
                      'admin'
                    )               NOT NULL DEFAULT 'patient',
  `clinic_type`     ENUM(
                      'human',
                      'pet',
                      'both'
                    )               NOT NULL DEFAULT 'human'
                    COMMENT 'Which clinic this user belongs to',
  `terms_agreed`    TINYINT(1)      NOT NULL DEFAULT 0
                    COMMENT 'Checkbox: I agree to the clinic terms & conditions',
  `is_active`       TINYINT(1)      NOT NULL DEFAULT 1,
  `last_login_at`   DATETIME        DEFAULT NULL,
  `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email`    (`email`),
  UNIQUE KEY `uq_users_username` (`username`),
  INDEX `idx_users_role`         (`role`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Unified login table for all portal roles';


-- ============================================================
-- 3. HUMAN PATIENTS (patient_portal.html — 3-step registration)
--    Step 1 – Owner Information:
--      Full Name, Gender, Date of Birth, Contact Number,
--      Email Address, Home Address
--    Step 3 – Account Setup: Username, Password (→ users table)
-- ============================================================

CREATE TABLE `human_patients` (
  `id`              CHAR(36)        NOT NULL DEFAULT (UUID()),
  `user_id`         CHAR(36)        NOT NULL
                    COMMENT 'FK → users.id (login credentials)',
  `first_name`      VARCHAR(100)    NOT NULL,
  `last_name`       VARCHAR(100)    NOT NULL,
  `gender`          ENUM(
                      'male',
                      'female',
                      'other',
                      'prefer_not_to_say'
                    )               NOT NULL DEFAULT 'prefer_not_to_say',
  `date_of_birth`   DATE            NOT NULL,
  `phone`           VARCHAR(20)     NOT NULL
                    COMMENT 'Mauritian format: +230 5XXX XXXX',
  `email`           VARCHAR(255)    NOT NULL,
  `address`         TEXT            NOT NULL
                    COMMENT 'Home Address (textarea in form)',
  `nic_passport`    VARCHAR(50)     DEFAULT NULL
                    COMMENT 'National ID or Passport — optional',
  `profile_photo`   VARCHAR(255)    DEFAULT NULL,
  `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_human_patients_email`   (`email`),
  UNIQUE KEY `uq_human_patients_user_id` (`user_id`),
  INDEX `idx_human_patients_name`        (`last_name`, `first_name`),
  CONSTRAINT `fk_human_patients_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 4. PET OWNERS (patient-portal.html — Step 2 Pet registration)
--    Same form as human patients but for the pet clinic side.
--    A pet owner can ALSO be a human patient (same user account).
-- ============================================================

CREATE TABLE `pet_owners` (
  `id`              CHAR(36)        NOT NULL DEFAULT (UUID()),
  `user_id`         CHAR(36)        NOT NULL
                    COMMENT 'FK → users.id',
  `full_name`       VARCHAR(200)    NOT NULL,
  `gender`          ENUM(
                      'male',
                      'female',
                      'other',
                      'prefer_not_to_say'
                    )               NOT NULL DEFAULT 'prefer_not_to_say',
  `date_of_birth`   DATE            DEFAULT NULL,
  `phone`           VARCHAR(20)     NOT NULL,
  `email`           VARCHAR(255)    NOT NULL,
  `address`         TEXT            DEFAULT NULL,
  `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pet_owners_email`   (`email`),
  UNIQUE KEY `uq_pet_owners_user_id` (`user_id`),
  CONSTRAINT `fk_pet_owners_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 5. PETS (patient-portal.html — Step 2 Pet Information)
--    Fields: Pet Name, Species, Breed, Age / Date Of Birth,
--            Gender, Vaccination Status
-- ============================================================

CREATE TABLE `pets` (
  `id`                  CHAR(36)        NOT NULL DEFAULT (UUID()),
  `owner_id`            CHAR(36)        NOT NULL
                        COMMENT 'FK → pet_owners.id',
  `pet_name`            VARCHAR(100)    NOT NULL,
  `species`             VARCHAR(100)    NOT NULL
                        COMMENT 'e.g. Dog, Cat, Bird, Rabbit',
  `breed`               VARCHAR(100)    DEFAULT NULL,
  `date_of_birth`       DATE            DEFAULT NULL
                        COMMENT 'Age / Date of Birth field from form',
  `age_years`           TINYINT UNSIGNED DEFAULT NULL
                        COMMENT 'Age in years if exact DOB unknown',
  `gender`              ENUM(
                          'male',
                          'female',
                          'unknown'
                        )               NOT NULL DEFAULT 'unknown',
  `vaccination_status`  ENUM(
                          'not_vaccinated',
                          'partially_vaccinated',
                          'fully_vaccinated',
                          'unknown'
                        )               NOT NULL DEFAULT 'unknown',
  `profile_photo`       VARCHAR(255)    DEFAULT NULL,
  `notes`               TEXT            DEFAULT NULL,
  `created_at`          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                        ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_pets_owner` (`owner_id`),
  CONSTRAINT `fk_pets_owner`
    FOREIGN KEY (`owner_id`) REFERENCES `pet_owners` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 6. DOCTORS (admin-add-doctor.html)
--    Fields: Doctor Name, Speciality, Doctor Email, Education,
--            Doctor Password, Address 1, Address 2, Experience,
--            Consultation Fees, About Me, Profile Photo
--    Also: Doctor ID used for login (format: DR-XXXXX)
-- ============================================================

CREATE TABLE `doctors` (
  `id`              CHAR(36)        NOT NULL DEFAULT (UUID()),
  `user_id`         CHAR(36)        NOT NULL
                    COMMENT 'FK → users.id',
  `doctor_code`     VARCHAR(10)     NOT NULL
                    COMMENT 'Login ID format: DR-XXXXX',
  `full_name`       VARCHAR(200)    NOT NULL,
  `speciality`      VARCHAR(100)    NOT NULL
                    COMMENT 'General Medicine, Cardiology, Dermatology, Pediatrics, etc.',
  `email`           VARCHAR(255)    NOT NULL,
  `education`       TEXT            DEFAULT NULL,
  `address_line1`   VARCHAR(255)    NOT NULL,
  `address_line2`   VARCHAR(255)    DEFAULT NULL,
  `experience_years` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `consultation_fee` DECIMAL(10,2)  NOT NULL DEFAULT 0.00
                    COMMENT 'Consultation fees in MUR (Rs)',
  `about`           TEXT            DEFAULT NULL
                    COMMENT 'About me / bio textarea',
  `profile_photo`   VARCHAR(255)    DEFAULT NULL,
  `clinic_type`     ENUM('human', 'pet', 'both') NOT NULL DEFAULT 'human',
  `status`          ENUM('active', 'inactive', 'on_leave')
                    NOT NULL DEFAULT 'active',
  `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_doctors_doctor_code` (`doctor_code`),
  UNIQUE KEY `uq_doctors_email`       (`email`),
  UNIQUE KEY `uq_doctors_user_id`     (`user_id`),
  INDEX `idx_doctors_speciality`      (`speciality`),
  INDEX `idx_doctors_clinic_type`     (`clinic_type`),
  CONSTRAINT `fk_doctors_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 7. NURSES (admin-add-nurse.html — Manage Staffs)
--    Fields: Nurse Name, Nurse Email, Nurse Password,
--            Experience, Speciality, Education,
--            Address 1, Address 2, About Me, Profile Photo
--    Login: Nurse ID + Password
-- ============================================================

CREATE TABLE `nurses` (
  `id`              CHAR(36)        NOT NULL DEFAULT (UUID()),
  `user_id`         CHAR(36)        NOT NULL
                    COMMENT 'FK → users.id',
  `nurse_code`      VARCHAR(10)     NOT NULL
                    COMMENT 'Login ID format: NR-XXXXX',
  `full_name`       VARCHAR(200)    NOT NULL,
  `speciality`      VARCHAR(100)    NOT NULL,
  `email`           VARCHAR(255)    NOT NULL,
  `education`       TEXT            DEFAULT NULL,
  `address_line1`   VARCHAR(255)    NOT NULL,
  `address_line2`   VARCHAR(255)    DEFAULT NULL,
  `experience_years` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `about`           TEXT            DEFAULT NULL,
  `profile_photo`   VARCHAR(255)    DEFAULT NULL,
  `clinic_type`     ENUM('human', 'pet', 'both') NOT NULL DEFAULT 'human',
  `status`          ENUM('active', 'inactive', 'on_leave')
                    NOT NULL DEFAULT 'active',
  `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_nurses_nurse_code` (`nurse_code`),
  UNIQUE KEY `uq_nurses_email`      (`email`),
  UNIQUE KEY `uq_nurses_user_id`    (`user_id`),
  CONSTRAINT `fk_nurses_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 8. RECEPTIONISTS
--    Source: index.html role selector — Receptionist Portal
--    "Manage appointments, patient registration, and billing"
-- ============================================================

CREATE TABLE `receptionists` (
  `id`              CHAR(36)        NOT NULL DEFAULT (UUID()),
  `user_id`         CHAR(36)        NOT NULL,
  `full_name`       VARCHAR(200)    NOT NULL,
  `email`           VARCHAR(255)    NOT NULL,
  `phone`           VARCHAR(20)     DEFAULT NULL,
  `status`          ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_receptionists_email`   (`email`),
  UNIQUE KEY `uq_receptionists_user_id` (`user_id`),
  CONSTRAINT `fk_receptionists_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 9. ADMINS
--    Source: admin-login.html, admin-overview.html
--    human_admins and pet_admins unified into one table
-- ============================================================

CREATE TABLE `admins` (
  `id`              CHAR(36)        NOT NULL DEFAULT (UUID()),
  `user_id`         CHAR(36)        NOT NULL,
  `full_name`       VARCHAR(200)    NOT NULL,
  `email`           VARCHAR(255)    NOT NULL,
  `clinic_type`     ENUM('human', 'pet', 'both') NOT NULL DEFAULT 'both',
  `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admins_email`   (`email`),
  UNIQUE KEY `uq_admins_user_id` (`user_id`),
  CONSTRAINT `fk_admins_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 10. DOCTOR SPECIALITIES (lookup table — 3NF)
--     Source: book-appointment.html → "Doctor Speciality" dropdown
--     Options: General Medicine, Cardiology, Dermatology, Pediatrics
-- ============================================================

CREATE TABLE `specialities` (
  `id`          SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(100)    NOT NULL,
  `clinic_type` ENUM('human', 'pet', 'both') NOT NULL DEFAULT 'human',
  `description` TEXT            DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_specialities_name_clinic` (`name`, `clinic_type`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 11. HUMAN APPOINTMENTS (book-appointment.html — "For Human")
--     Fields: Appointment For (human), Full Name, Contact Number,
--             Doctor Speciality (select), Preferred Date,
--             Preferred Time (select), Symptoms / Reason for Visit
--     Status badges: Pending, Completed, Cancelled (+ Confirmed)
--     Patient dashboard: Reschedule, Cancel, Paid badge
-- ============================================================

CREATE TABLE `human_appointments` (
  `id`                  CHAR(36)        NOT NULL DEFAULT (UUID()),
  `patient_id`          CHAR(36)        NOT NULL
                        COMMENT 'FK → human_patients.id',
  `doctor_id`           CHAR(36)        DEFAULT NULL
                        COMMENT 'FK → doctors.id (assigned after booking)',
  `speciality_id`       SMALLINT UNSIGNED DEFAULT NULL
                        COMMENT 'FK → specialities.id',
  `patient_full_name`   VARCHAR(200)    NOT NULL
                        COMMENT 'Full Name field in booking form',
  `contact_number`      VARCHAR(20)     NOT NULL
                        COMMENT 'Contact Number field in booking form',
  `appointment_date`    DATE            NOT NULL
                        COMMENT 'Preferred Date field',
  `appointment_time`    TIME            NOT NULL
                        COMMENT 'Preferred Time select field',
  `symptoms`            TEXT            DEFAULT NULL
                        COMMENT 'Symptoms / Reason for Visit textarea',
  `status`              ENUM(
                          'pending',
                          'confirmed',
                          'completed',
                          'cancelled',
                          'rescheduled'
                        )               NOT NULL DEFAULT 'pending',
  `payment_status`      ENUM(
                          'unpaid',
                          'paid',
                          'waived'
                        )               NOT NULL DEFAULT 'unpaid'
                        COMMENT 'Paid badge shown on appointment card',
  `fee_charged`         DECIMAL(10,2)   DEFAULT NULL
                        COMMENT 'Consultation fee at time of booking (MUR Rs)',
  `notes`               TEXT            DEFAULT NULL
                        COMMENT 'Internal notes by doctor/receptionist',
  `created_at`          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                        ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_human_appt_patient`  (`patient_id`),
  INDEX `idx_human_appt_doctor`   (`doctor_id`),
  INDEX `idx_human_appt_date`     (`appointment_date`),
  INDEX `idx_human_appt_status`   (`status`),
  CONSTRAINT `fk_human_appt_patient`
    FOREIGN KEY (`patient_id`) REFERENCES `human_patients` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_human_appt_doctor`
    FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`)
    ON DELETE SET NULL,
  CONSTRAINT `fk_human_appt_speciality`
    FOREIGN KEY (`speciality_id`) REFERENCES `specialities` (`id`)
    ON DELETE SET NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 12. PET APPOINTMENTS (book-appointment.html — "For Pet")
--     Same booking form fields but patient_type = 'pet'
--     Pet name shown instead of patient name
-- ============================================================

CREATE TABLE `pet_appointments` (
  `id`                CHAR(36)        NOT NULL DEFAULT (UUID()),
  `pet_id`            CHAR(36)        NOT NULL
                      COMMENT 'FK → pets.id',
  `owner_id`          CHAR(36)        NOT NULL
                      COMMENT 'FK → pet_owners.id (for contact)',
  `doctor_id`         CHAR(36)        DEFAULT NULL
                      COMMENT 'FK → doctors.id (vet)',
  `speciality_id`     SMALLINT UNSIGNED DEFAULT NULL,
  `owner_contact`     VARCHAR(20)     NOT NULL
                      COMMENT 'Contact Number field',
  `appointment_date`  DATE            NOT NULL,
  `appointment_time`  TIME            NOT NULL,
  `symptoms`          TEXT            DEFAULT NULL
                      COMMENT 'Symptoms / Reason for Visit',
  `status`            ENUM(
                        'pending',
                        'confirmed',
                        'completed',
                        'cancelled',
                        'rescheduled'
                      )               NOT NULL DEFAULT 'pending',
  `payment_status`    ENUM(
                        'unpaid',
                        'paid',
                        'waived'
                      )               NOT NULL DEFAULT 'unpaid',
  `fee_charged`       DECIMAL(10,2)   DEFAULT NULL,
  `notes`             TEXT            DEFAULT NULL,
  `created_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                      ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_pet_appt_pet`     (`pet_id`),
  INDEX `idx_pet_appt_owner`   (`owner_id`),
  INDEX `idx_pet_appt_doctor`  (`doctor_id`),
  INDEX `idx_pet_appt_date`    (`appointment_date`),
  INDEX `idx_pet_appt_status`  (`status`),
  CONSTRAINT `fk_pet_appt_pet`
    FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_pet_appt_owner`
    FOREIGN KEY (`owner_id`) REFERENCES `pet_owners` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_pet_appt_doctor`
    FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`)
    ON DELETE SET NULL,
  CONSTRAINT `fk_pet_appt_speciality`
    FOREIGN KEY (`speciality_id`) REFERENCES `specialities` (`id`)
    ON DELETE SET NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 13. HUMAN MEDICAL RECORDS (new-record.html, medical-records.html)
--     Fields: Patient (select), Date, Diagnosis, Treatment,
--             Prescription, Notes
--     Shown in doctor dashboard: date + patient name + 4 details
-- ============================================================

CREATE TABLE `human_medical_records` (
  `id`            CHAR(36)    NOT NULL DEFAULT (UUID()),
  `patient_id`    CHAR(36)    NOT NULL,
  `doctor_id`     CHAR(36)    DEFAULT NULL,
  `appointment_id` CHAR(36)   DEFAULT NULL
                  COMMENT 'Links record to appointment if applicable',
  `record_date`   DATE        NOT NULL,
  `diagnosis`     TEXT        NOT NULL,
  `treatment`     TEXT        NOT NULL,
  `prescription`  TEXT        DEFAULT NULL,
  `notes`         TEXT        DEFAULT NULL,
  `created_at`    DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP
                  ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_hmr_patient`     (`patient_id`),
  INDEX `idx_hmr_doctor`      (`doctor_id`),
  INDEX `idx_hmr_date`        (`record_date`),
  CONSTRAINT `fk_hmr_patient`
    FOREIGN KEY (`patient_id`) REFERENCES `human_patients` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_hmr_doctor`
    FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`)
    ON DELETE SET NULL,
  CONSTRAINT `fk_hmr_appointment`
    FOREIGN KEY (`appointment_id`) REFERENCES `human_appointments` (`id`)
    ON DELETE SET NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 14. PET MEDICAL RECORDS (same structure as human — vet side)
-- ============================================================

CREATE TABLE `pet_medical_records` (
  `id`              CHAR(36)    NOT NULL DEFAULT (UUID()),
  `pet_id`          CHAR(36)    NOT NULL,
  `doctor_id`       CHAR(36)    DEFAULT NULL,
  `appointment_id`  CHAR(36)    DEFAULT NULL,
  `record_date`     DATE        NOT NULL,
  `diagnosis`       TEXT        NOT NULL,
  `treatment`       TEXT        NOT NULL,
  `prescription`    TEXT        DEFAULT NULL,
  `notes`           TEXT        DEFAULT NULL,
  `weight_kg`       DECIMAL(5,2) DEFAULT NULL
                    COMMENT 'Pet weight at time of visit',
  `created_at`      DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_pmr_pet`     (`pet_id`),
  INDEX `idx_pmr_doctor`  (`doctor_id`),
  CONSTRAINT `fk_pmr_pet`
    FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_pmr_doctor`
    FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`)
    ON DELETE SET NULL,
  CONSTRAINT `fk_pmr_appointment`
    FOREIGN KEY (`appointment_id`) REFERENCES `pet_appointments` (`id`)
    ON DELETE SET NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 15. REVIEWS (patient-reviews.html)
--     Star ratings for 5 categories:
--       - Overall experience
--       - Appointment quality
--       - Doctor's professionalism
--       - Staff friendliness
--       - Cleanliness of clinic
--     Plus optional text message
--     Patient can Edit / Delete their review
-- ============================================================

CREATE TABLE `reviews` (
  `id`                        CHAR(36)        NOT NULL DEFAULT (UUID()),
  `patient_id`                CHAR(36)        NOT NULL,
  `doctor_id`                 CHAR(36)        DEFAULT NULL,
  `appointment_id`            CHAR(36)        DEFAULT NULL,
  `clinic_type`               ENUM('human','pet') NOT NULL DEFAULT 'human',
  `rating_overall`            TINYINT UNSIGNED NOT NULL
                              COMMENT '1–5 stars: Overall experience',
  `rating_appointment`        TINYINT UNSIGNED NOT NULL
                              COMMENT '1–5 stars: How was your appointment?',
  `rating_professionalism`    TINYINT UNSIGNED NOT NULL
                              COMMENT '1–5 stars: Doctor professionalism',
  `rating_staff_friendliness` TINYINT UNSIGNED NOT NULL
                              COMMENT '1–5 stars: Staff friendliness',
  `rating_cleanliness`        TINYINT UNSIGNED NOT NULL
                              COMMENT '1–5 stars: Cleanliness of clinic',
  `comment`                   TEXT            DEFAULT NULL
                              COMMENT 'Optional message textarea',
  `created_at`                DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`                DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                              ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_reviews_patient`     (`patient_id`),
  INDEX `idx_reviews_doctor`      (`doctor_id`),
  CONSTRAINT `chk_rating_overall`
    CHECK (`rating_overall` BETWEEN 1 AND 5),
  CONSTRAINT `chk_rating_appointment`
    CHECK (`rating_appointment` BETWEEN 1 AND 5),
  CONSTRAINT `chk_rating_professionalism`
    CHECK (`rating_professionalism` BETWEEN 1 AND 5),
  CONSTRAINT `chk_rating_staff`
    CHECK (`rating_staff_friendliness` BETWEEN 1 AND 5),
  CONSTRAINT `chk_rating_cleanliness`
    CHECK (`rating_cleanliness` BETWEEN 1 AND 5),
  CONSTRAINT `fk_reviews_patient`
    FOREIGN KEY (`patient_id`) REFERENCES `human_patients` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_reviews_doctor`
    FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`)
    ON DELETE SET NULL,
  CONSTRAINT `fk_reviews_appointment`
    FOREIGN KEY (`appointment_id`) REFERENCES `human_appointments` (`id`)
    ON DELETE SET NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 16. CONTACT MESSAGES (contact.html / contact-alt.html)
--     Fields: Name, Email, Subject (select), Message textarea
--     Subjects: Appointment Booking, General Inquiry,
--               Emergency, Billing Question, Feedback
-- ============================================================

CREATE TABLE `contact_messages` (
  `id`            CHAR(36)    NOT NULL DEFAULT (UUID()),
  `name`          VARCHAR(200) NOT NULL,
  `email`         VARCHAR(255) NOT NULL,
  `subject`       ENUM(
                    'appointment',
                    'general',
                    'emergency',
                    'billing',
                    'feedback'
                  )            NOT NULL,
  `message`       TEXT         NOT NULL,
  `is_read`       TINYINT(1)   NOT NULL DEFAULT 0,
  `replied_at`    DATETIME     DEFAULT NULL,
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
                  ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_contact_messages_email`   (`email`),
  INDEX `idx_contact_messages_subject` (`subject`),
  INDEX `idx_contact_messages_read`    (`is_read`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 17. NURSE CHECKLIST (nurse-dashboard.html)
--     "Today's Tasks" list: patient name, room, task description,
--     time — e.g. Blood pressure check, Medication administration,
--     Wound dressing
-- ============================================================

CREATE TABLE `nurse_checklists` (
  `id`                CHAR(36)    NOT NULL DEFAULT (UUID()),
  `nurse_id`          CHAR(36)    NOT NULL,
  `patient_id`        CHAR(36)    DEFAULT NULL
                      COMMENT 'FK → human_patients.id',
  `pet_id`            CHAR(36)    DEFAULT NULL
                      COMMENT 'FK → pets.id (if vet nurse task)',
  `task_description`  TEXT        NOT NULL
                      COMMENT 'e.g. Blood pressure check, Medication administration',
  `room_number`       VARCHAR(20) DEFAULT NULL
                      COMMENT 'e.g. Room 101, Room 102',
  `scheduled_at`      DATETIME    NOT NULL
                      COMMENT 'Time shown in task list (e.g. 9:00 AM)',
  `is_completed`      TINYINT(1)  NOT NULL DEFAULT 0,
  `completed_at`      DATETIME    DEFAULT NULL,
  `created_at`        DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP
                      ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_ncl_nurse`   (`nurse_id`),
  INDEX `idx_ncl_patient` (`patient_id`),
  CONSTRAINT `fk_ncl_nurse`
    FOREIGN KEY (`nurse_id`) REFERENCES `nurses` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_ncl_patient`
    FOREIGN KEY (`patient_id`) REFERENCES `human_patients` (`id`)
    ON DELETE SET NULL,
  CONSTRAINT `fk_ncl_pet`
    FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`)
    ON DELETE SET NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 18. PAYMENTS / BILLING (patient-dashboard.html)
--     "Unpaid Bills" card: Consultation Fee - Dr.Name — Rs 1,500
--     "Recent Payments": Consultation Fee, Blood Test, ECG
--     Quick actions: Pay Now, Payment History
-- ============================================================

CREATE TABLE `payments` (
  `id`                CHAR(36)        NOT NULL DEFAULT (UUID()),
  `patient_id`        CHAR(36)        DEFAULT NULL
                      COMMENT 'FK → human_patients.id',
  `pet_owner_id`      CHAR(36)        DEFAULT NULL
                      COMMENT 'FK → pet_owners.id',
  `appointment_id`    CHAR(36)        DEFAULT NULL,
  `description`       VARCHAR(255)    NOT NULL
                      COMMENT 'e.g. Consultation Fee - Dr. Smith, Blood Test, ECG',
  `amount`            DECIMAL(10,2)   NOT NULL
                      COMMENT 'Amount in MUR (Rs)',
  `payment_status`    ENUM(
                        'pending',
                        'paid',
                        'overdue',
                        'waived'
                      )               NOT NULL DEFAULT 'pending',
  `payment_method`    ENUM(
                        'cash',
                        'card',
                        'online',
                        'insurance'
                      )               DEFAULT NULL,
  `paid_at`           DATETIME        DEFAULT NULL,
  `created_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                      ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_payments_patient`  (`patient_id`),
  INDEX `idx_payments_owner`    (`pet_owner_id`),
  INDEX `idx_payments_status`   (`payment_status`),
  CONSTRAINT `fk_payments_patient`
    FOREIGN KEY (`patient_id`) REFERENCES `human_patients` (`id`)
    ON DELETE SET NULL,
  CONSTRAINT `fk_payments_pet_owner`
    FOREIGN KEY (`pet_owner_id`) REFERENCES `pet_owners` (`id`)
    ON DELETE SET NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 19. AUDIT LOGS (security — doctor-login.html)
--     "Activity Logging & Monitoring" security feature
--     After 3 failed attempts, account locked + admin notified
-- ============================================================

CREATE TABLE `audit_logs` (
  `id`          CHAR(36)        NOT NULL DEFAULT (UUID()),
  `user_id`     CHAR(36)        DEFAULT NULL,
  `actor_role`  VARCHAR(50)     DEFAULT NULL
                COMMENT 'patient, doctor, nurse, admin, receptionist',
  `action`      VARCHAR(255)    NOT NULL
                COMMENT 'e.g. login_success, login_failed, record_viewed',
  `object_type` VARCHAR(100)    DEFAULT NULL
                COMMENT 'e.g. appointment, medical_record, patient',
  `object_id`   CHAR(36)        DEFAULT NULL,
  `ip_address`  VARCHAR(45)     DEFAULT NULL,
  `user_agent`  TEXT            DEFAULT NULL,
  `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_audit_user`   (`user_id`),
  INDEX `idx_audit_action` (`action`),
  INDEX `idx_audit_date`   (`created_at`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- ============================================================
-- SECTION 3 — RELATIONSHIP SUMMARY
-- ============================================================
-- ============================================================
--
--  users (1) ──────────────────────────── (1) human_patients
--  users (1) ──────────────────────────── (1) pet_owners
--  users (1) ──────────────────────────── (1) doctors
--  users (1) ──────────────────────────── (1) nurses
--  users (1) ──────────────────────────── (1) admins
--  users (1) ──────────────────────────── (1) receptionists
--
--  human_patients (1) ─────────────── (M) human_appointments
--  human_patients (1) ─────────────── (M) human_medical_records
--  human_patients (1) ─────────────── (M) reviews
--  human_patients (1) ─────────────── (M) payments
--
--  pet_owners (1) ─────────────────── (M) pets
--  pet_owners (1) ─────────────────── (M) payments
--  pets       (1) ─────────────────── (M) pet_appointments
--  pets       (1) ─────────────────── (M) pet_medical_records
--
--  doctors (1) ────────────────────── (M) human_appointments
--  doctors (1) ────────────────────── (M) pet_appointments
--  doctors (1) ────────────────────── (M) human_medical_records
--  doctors (1) ────────────────────── (M) pet_medical_records
--  doctors (1) ────────────────────── (M) reviews
--
--  nurses (1) ─────────────────────── (M) nurse_checklists
--
--  specialities (1) ───────────────── (M) human_appointments
--  specialities (1) ───────────────── (M) pet_appointments
--
--  human_appointments (1) ─────────── (M) human_medical_records
--  human_appointments (1) ─────────── (M) reviews
--  human_appointments (1) ─────────── (M) payments
-- ============================================================


-- ============================================================
-- SECTION 4 — SAMPLE DATA (INSERT)
-- ============================================================

-- ----- Specialities -----
INSERT INTO `specialities` (`name`, `clinic_type`) VALUES
('General Medicine',    'human'),
('Cardiology',          'human'),
('Dermatology',         'human'),
('Pediatrics',          'human'),
('Orthopedics',         'human'),
('General Veterinary',  'pet'),
('Veterinary Surgery',  'pet'),
('Animal Dentistry',    'pet');


-- ----- Users -----
INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role`, `clinic_type`, `terms_agreed`) VALUES
('u-admin-001',  'admin',       'admin@vitalcare.com',    '$2y$12$exampleHashAdmin123',   'admin',        'both',  1),
('u-doc-001',    'dr.alice',    'alice@vitalcare.com',    '$2y$12$exampleHashDoctor123',  'doctor',       'human', 1),
('u-doc-002',    'dr.bob',      'bob@vitalcare.com',      '$2y$12$exampleHashDoctor456',  'doctor',       'human', 1),
('u-doc-003',    'dr.vet',      'vet@vitalcare.com',      '$2y$12$exampleHashVet123',     'doctor',       'pet',   1),
('u-nurse-001',  'nurse.joy',   'joy@vitalcare.com',      '$2y$12$exampleHashNurse123',   'nurse',        'human', 1),
('u-pat-001',    'john.doe',    'john@example.com',       '$2y$12$exampleHashPat123',     'patient',      'human', 1),
('u-pat-002',    'jane.smith',  'jane@example.com',       '$2y$12$exampleHashPat456',     'patient',      'human', 1),
('u-owner-001',  'bobowner',    'bob@example.com',        '$2y$12$exampleHashOwner123',   'patient',      'pet',   1);


-- ----- Admins -----
INSERT INTO `admins` (`id`, `user_id`, `full_name`, `email`, `clinic_type`) VALUES
('adm-001', 'u-admin-001', 'System Administrator', 'admin@vitalcare.com', 'both');


-- ----- Doctors -----
INSERT INTO `doctors` (`id`, `user_id`, `doctor_code`, `full_name`, `speciality`, `email`, `education`, `address_line1`, `experience_years`, `consultation_fee`, `about`, `clinic_type`) VALUES
('doc-001', 'u-doc-001', 'DR-00001', 'Dr. Alice Dubois',    'General Medicine', 'alice@vitalcare.com', 'MBBS, University of Mauritius', 'Royal Road, Réduit', 8,  1500.00, 'Experienced general practitioner with a focus on preventive care.', 'human'),
('doc-002', 'u-doc-002', 'DR-00002', 'Dr. Bob Ramkhelawon','Cardiology',       'bob@vitalcare.com',   'MD Cardiology, London',         'Royal Road, Réduit', 12, 2500.00, 'Senior cardiologist specialising in heart disease management.', 'human'),
('doc-003', 'u-doc-003', 'DR-00003', 'Dr. Vicky Naidoo',   'General Veterinary','vet@vitalcare.com',  'BVSc, University of Pretoria',  'Royal Road, Ebène',  6,  1200.00, 'Caring vet with expertise in dogs and cats.', 'pet');


-- ----- Nurses -----
INSERT INTO `nurses` (`id`, `user_id`, `nurse_code`, `full_name`, `speciality`, `email`, `address_line1`, `experience_years`, `clinic_type`) VALUES
('nur-001', 'u-nurse-001', 'NR-00001', 'Nurse Johnson', 'General Care', 'joy@vitalcare.com', 'Royal Road, Réduit', 5, 'human');


-- ----- Human Patients -----
INSERT INTO `human_patients` (`id`, `user_id`, `first_name`, `last_name`, `gender`, `date_of_birth`, `phone`, `email`, `address`) VALUES
('pat-001', 'u-pat-001', 'John',  'Doe',   'male',   '1993-05-15', '+230 5123 4567', 'john@example.com', '12 Cascade Road, Port Louis, Mauritius'),
('pat-002', 'u-pat-002', 'Jane',  'Smith', 'female', '1995-11-22', '+230 5234 5678', 'jane@example.com', '45 Royal Road, Curepipe, Mauritius');


-- ----- Pet Owners -----
INSERT INTO `pet_owners` (`id`, `user_id`, `full_name`, `gender`, `date_of_birth`, `phone`, `email`, `address`) VALUES
('own-001', 'u-owner-001', 'Bob Owner', 'male', '1988-03-10', '+230 5345 6789', 'bob@example.com', '7 Beach Road, Flic en Flac, Mauritius');


-- ----- Pets -----
INSERT INTO `pets` (`id`, `owner_id`, `pet_name`, `species`, `breed`, `date_of_birth`, `gender`, `vaccination_status`) VALUES
('pet-001', 'own-001', 'Rex',  'Dog', 'Labrador Retriever', '2020-04-10', 'male',   'fully_vaccinated'),
('pet-002', 'own-001', 'Luna', 'Cat', 'Siamese',            '2022-01-15', 'female', 'partially_vaccinated');


-- ----- Human Appointments -----
INSERT INTO `human_appointments` (`id`, `patient_id`, `doctor_id`, `speciality_id`, `patient_full_name`, `contact_number`, `appointment_date`, `appointment_time`, `symptoms`, `status`, `payment_status`, `fee_charged`) VALUES
('ha-001', 'pat-001', 'doc-001', 1, 'John Doe',  '+230 5123 4567', '2025-08-30', '08:00:00', 'Persistent headache and mild fever for 3 days.', 'confirmed',  'unpaid',   1500.00),
('ha-002', 'pat-002', 'doc-002', 2, 'Jane Smith','+230 5234 5678', '2025-05-05', '10:30:00', 'Chest tightness during exercise.',              'completed', 'paid',     2500.00),
('ha-003', 'pat-001', 'doc-001', 1, 'John Doe',  '+230 5123 4567', '2025-09-15', '14:00:00', 'Follow-up for hypertension management.',        'pending',   'unpaid',   1500.00);


-- ----- Pet Appointments -----
INSERT INTO `pet_appointments` (`id`, `pet_id`, `owner_id`, `doctor_id`, `speciality_id`, `owner_contact`, `appointment_date`, `appointment_time`, `symptoms`, `status`, `payment_status`, `fee_charged`) VALUES
('pa-001', 'pet-001', 'own-001', 'doc-003', 6, '+230 5345 6789', '2025-09-01', '09:00:00', 'Rex has been limping on his right front leg.',       'confirmed', 'unpaid',  1200.00),
('pa-002', 'pet-002', 'own-001', 'doc-003', 6, '+230 5345 6789', '2025-08-20', '11:00:00', 'Luna is not eating and seems lethargic.',             'completed', 'paid',    1200.00);


-- ----- Human Medical Records -----
INSERT INTO `human_medical_records` (`id`, `patient_id`, `doctor_id`, `appointment_id`, `record_date`, `diagnosis`, `treatment`, `prescription`, `notes`) VALUES
('hmr-001', 'pat-002', 'doc-002', 'ha-002', '2025-05-05', 'Mild hypertension with exertional chest tightness.', 'Prescribed medication and lifestyle changes — low-sodium diet, 30 min daily exercise.', 'Lisinopril 10mg once daily', 'Follow-up in 3 months. Refer to cardiologist if symptoms worsen.'),
('hmr-002', 'pat-001', 'doc-001', 'ha-001', '2025-04-13', 'Tension headache, mild viral fever.', 'Rest, hydration, and analgesics.', '1000mg Panadol every 6 hours as needed', 'Follow up in 1 week if not resolved.');


-- ----- Pet Medical Records -----
INSERT INTO `pet_medical_records` (`id`, `pet_id`, `doctor_id`, `appointment_id`, `record_date`, `diagnosis`, `treatment`, `prescription`, `notes`, `weight_kg`) VALUES
('pmr-001', 'pet-002', 'doc-003', 'pa-002', '2025-08-20', 'Mild gastroenteritis and dehydration.', 'IV fluids, bland diet for 5 days.', 'Metronidazole 50mg twice daily for 5 days', 'Return if not eating by day 3.', 4.20);


-- ----- Reviews -----
INSERT INTO `reviews` (`id`, `patient_id`, `doctor_id`, `appointment_id`, `rating_overall`, `rating_appointment`, `rating_professionalism`, `rating_staff_friendliness`, `rating_cleanliness`, `comment`) VALUES
('rev-001', 'pat-002', 'doc-002', 'ha-002', 5, 5, 5, 4, 5, 'Wonderful experience. Dr. Bob was very thorough and explained everything clearly.');


-- ----- Contact Messages -----
INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`) VALUES
('msg-001', 'Alice Ng',    'alice@gmail.com', 'appointment', 'I would like to reschedule my appointment on August 30th.'),
('msg-002', 'Raj Kumar',   'raj@gmail.com',   'billing',     'I have a question about my last invoice for Rs 2,500.'),
('msg-003', 'Sophie Martin','sophie@mail.com', 'feedback',   'The clinic was very clean and the staff were incredibly friendly!');


-- ----- Payments -----
INSERT INTO `payments` (`id`, `patient_id`, `appointment_id`, `description`, `amount`, `payment_status`, `payment_method`, `paid_at`) VALUES
('pay-001', 'pat-002', 'ha-002', 'Consultation Fee - Dr. Bob Ramkhelawon', 2500.00, 'paid', 'card', '2025-05-05 11:30:00'),
('pay-002', 'pat-001', NULL,     'Blood Test — Full Blood Count',           800.00,  'paid', 'cash', '2025-04-14 10:00:00'),
('pay-003', 'pat-001', NULL,     'ECG — Resting Electrocardiogram',         1500.00, 'paid', 'cash', '2025-04-14 10:05:00'),
('pay-004', 'pat-001', 'ha-001', 'Consultation Fee - Dr. Alice Dubois',     1500.00, 'pending', NULL, NULL);


-- ----- Nurse Checklist -----
INSERT INTO `nurse_checklists` (`id`, `nurse_id`, `patient_id`, `task_description`, `room_number`, `scheduled_at`, `is_completed`) VALUES
('ncl-001', 'nur-001', 'pat-001', 'Blood pressure check', 'Room 101', '2025-08-30 09:00:00', 0),
('ncl-002', 'nur-001', 'pat-002', 'Medication administration — Lisinopril 10mg', 'Room 102', '2025-08-30 10:30:00', 0),
('ncl-003', 'nur-001', NULL,      'Wound dressing change — post-surgical patient', 'Room 103', '2025-08-30 11:15:00', 0);


COMMIT;

-- ============================================================
-- END OF vitalcare_db.sql
-- Total tables: 19
--   users, admins, human_patients, pet_owners, pets,
--   doctors, nurses, receptionists, specialities,
--   human_appointments, pet_appointments,
--   human_medical_records, pet_medical_records,
--   reviews, contact_messages,
--   nurse_checklists, payments, audit_logs
-- ============================================================
