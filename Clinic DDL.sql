
-- ------------------------------------------------------
-- VISITOR / CONTACT
-- ------------------------------------------------------
CREATE TABLE contact_messages (
  id CHAR(36) PRIMARY KEY,
  name VARCHAR(255),
  email VARCHAR(255),
  subject VARCHAR(255),
  message TEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------
-- HUMAN PATIENTS
-- ------------------------------------------------------
CREATE TABLE human_patients (
  patient_id CHAR(36) PRIMARY KEY,
  first_name VARCHAR(255) NOT NULL,
  last_name VARCHAR(255) NOT NULL,
  dob DATE,
  nic_passport VARCHAR(255),
  gender ENUM('male','female','other','unknown') DEFAULT 'unknown',
  phone VARCHAR(50),
  email VARCHAR(255) UNIQUE,
  address TEXT,
  password_hash TEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------
-- HUMAN STAFF: DOCTOR / NURSE / ADMIN
-- ------------------------------------------------------
CREATE TABLE human_doctors (
  doctor_id CHAR(36) PRIMARY KEY,
  full_name VARCHAR(255) NOT NULL,
  speciality VARCHAR(255),
  email VARCHAR(255) UNIQUE,
  education TEXT,
  password_hash TEXT NOT NULL,
  address TEXT,
  experience INT CHECK (experience >= 0),
  fees DECIMAL(10,2) DEFAULT 0,
  about TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE human_nurses (
  nurse_id CHAR(36) PRIMARY KEY,
  full_name VARCHAR(255) NOT NULL,
  speciality VARCHAR(255),
  email VARCHAR(255) UNIQUE,
  education TEXT,
  password_hash TEXT NOT NULL,
  address TEXT,
  experience INT CHECK (experience >= 0),
  about TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE human_admins (
  admin_id CHAR(36) PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password_hash TEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------
-- RECEPTIONISTS
-- ------------------------------------------------------
CREATE TABLE receptionists (
  receptionist_id CHAR(36) PRIMARY KEY,
  full_name VARCHAR(255) NOT NULL,
  email VARCHAR(255) UNIQUE,
  password_hash TEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------
-- HUMAN APPOINTMENTS & RECORDS & REVIEWS
-- ------------------------------------------------------
CREATE TABLE human_appointments (
  appointment_id CHAR(36) PRIMARY KEY,
  patient_id CHAR(36) NOT NULL,
  doctor_id CHAR(36),
  speciality VARCHAR(255),
  appointment_date DATE NOT NULL,
  start_time DATETIME NOT NULL,
  end_time DATETIME NOT NULL,
  notes TEXT,
  status ENUM('pending','approved','done','cancelled') DEFAULT 'pending',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_id) REFERENCES human_patients(patient_id) ON DELETE CASCADE,
  FOREIGN KEY (doctor_id) REFERENCES human_doctors(doctor_id) ON DELETE SET NULL
);

CREATE TABLE human_medical_records (
  record_id CHAR(36) PRIMARY KEY,
  doctor_id CHAR(36),
  patient_id CHAR(36),
  record_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  diagnosis TEXT,
  treatment TEXT,
  prescription TEXT,
  notes TEXT,
  FOREIGN KEY (doctor_id) REFERENCES human_doctors(doctor_id) ON DELETE SET NULL,
  FOREIGN KEY (patient_id) REFERENCES human_patients(patient_id) ON DELETE CASCADE
);

CREATE TABLE human_reviews (
  review_id CHAR(36) PRIMARY KEY,
  patient_id CHAR(36),
  doctor_id CHAR(36),
  rating TINYINT CHECK (rating BETWEEN 1 AND 5),
  comment TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_id) REFERENCES human_patients(patient_id) ON DELETE SET NULL,
  FOREIGN KEY (doctor_id) REFERENCES human_doctors(doctor_id) ON DELETE SET NULL
);

-- ------------------------------------------------------
-- PET OWNERS AND PETS
-- ------------------------------------------------------
CREATE TABLE pet_owners (
  owner_id CHAR(36) PRIMARY KEY,
  full_name VARCHAR(255) NOT NULL,
  gender ENUM('male','female','other','unknown') DEFAULT 'unknown',
  dob DATE,
  contact VARCHAR(50),
  email VARCHAR(255) UNIQUE,
  address TEXT,
  username VARCHAR(255) UNIQUE,
  password_hash TEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pets (
  pet_id CHAR(36) PRIMARY KEY,
  owner_id CHAR(36) NOT NULL,
  pet_name VARCHAR(255) NOT NULL,
  species VARCHAR(255),
  breed VARCHAR(255),
  age INT CHECK (age >= 0),
  gender ENUM('male','female','other','unknown') DEFAULT 'unknown',
  vaccination_status ENUM('unknown','not_vaccinated','partially_vaccinated','fully_vaccinated')
      DEFAULT 'unknown',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (owner_id) REFERENCES pet_owners(owner_id) ON DELETE CASCADE
);

-- ------------------------------------------------------
-- PET STAFF: DOCTOR / NURSE / ADMIN
-- ------------------------------------------------------
CREATE TABLE pet_doctors (
  doctor_id CHAR(36) PRIMARY KEY,
  full_name VARCHAR(255) NOT NULL,
  speciality VARCHAR(255),
  email VARCHAR(255) UNIQUE,
  education TEXT,
  password_hash TEXT NOT NULL,
  address TEXT,
  experience INT CHECK (experience >= 0),
  fees DECIMAL(10,2),
  about TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pet_nurses (
  nurse_id CHAR(36) PRIMARY KEY,
  full_name VARCHAR(255) NOT NULL,
  speciality VARCHAR(255),
  email VARCHAR(255) UNIQUE,
  education TEXT,
  password_hash TEXT NOT NULL,
  address TEXT,
  experience INT CHECK (experience >= 0),
  about TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pet_admins (
  admin_id CHAR(36) PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password_hash TEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------
-- PET APPOINTMENTS & RECORDS
-- ------------------------------------------------------
CREATE TABLE pet_appointments (
  appointment_id CHAR(36) PRIMARY KEY,
  pet_id CHAR(36) NOT NULL,
  doctor_id CHAR(36),
  speciality VARCHAR(255),
  appointment_date DATE NOT NULL,
  start_time DATETIME NOT NULL,
  end_time DATETIME NOT NULL,
  notes TEXT,
  status ENUM('pending','approved','done','cancelled') DEFAULT 'pending',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pet_id) REFERENCES pets(pet_id) ON DELETE CASCADE,
  FOREIGN KEY (doctor_id) REFERENCES pet_doctors(doctor_id) ON DELETE SET NULL
);

CREATE TABLE pet_medical_records (
  record_id CHAR(36) PRIMARY KEY,
  doctor_id CHAR(36),
  pet_id CHAR(36),
  record_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  diagnosis TEXT,
  treatment TEXT,
  prescription TEXT,
  notes TEXT,
  FOREIGN KEY (doctor_id) REFERENCES pet_doctors(doctor_id) ON DELETE SET NULL,
  FOREIGN KEY (pet_id) REFERENCES pets(pet_id) ON DELETE CASCADE
);

-- ------------------------------------------------------
-- NURSE CHECKLIST
-- ------------------------------------------------------
CREATE TABLE nurse_checklist (
  checklist_id CHAR(36) PRIMARY KEY,
  nurse_id CHAR(36) NOT NULL,
  role ENUM(
    'human_patient','pet_owner','human_doctor','pet_doctor',
    'human_nurse','pet_nurse','human_admin','pet_admin','receptionist'
  ) NOT NULL,
  patient_id CHAR(36) NULL,
  pet_id CHAR(36) NULL,
  task_description TEXT NOT NULL,
  completed BOOLEAN DEFAULT FALSE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  completed_at DATETIME NULL
);

-- ------------------------------------------------------
-- SIMPLE AUDIT LOGS
-- ------------------------------------------------------
CREATE TABLE audit_logs (
  id CHAR(36) PRIMARY KEY,
  actor_role VARCHAR(50),
  actor_id CHAR(36),
  action TEXT NOT NULL,
  object_type VARCHAR(255),
  object_id CHAR(36),
  ip_address VARCHAR(45),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);




-- ==========================
-- SAMPLE DATA
-- ==========================
INSERT INTO human_patients (patient_id, first_name, last_name, email, password_hash)
VALUES 
('p1', 'John', 'Doe', 'john@example.com', '$2y$10$examplehash1'),
('p2', 'Jane', 'Smith', 'jane@example.com', '$2y$10$examplehash2');

INSERT INTO human_doctors (doctor_id, full_name, email, password_hash, speciality)
VALUES 
('d1','Dr Alice','alice@example.com','$2y$10$examplehash3','General'),
('d2','Dr Bob','bob@example.com','$2y$10$examplehash4','Cardiology');

INSERT INTO pet_owners (owner_id, full_name, email, username, password_hash)
VALUES 
('o1','Bob Owner','bob@example.com','bobowner','$2y$10$examplehash5'),
('o2','Alice Owner','alice@example.com','aliceowner','$2y$10$examplehash6');

INSERT INTO pet_doctors (doctor_id, full_name, email, password_hash, speciality)
VALUES 
('pd1','Dr Vet','vet@example.com','$2y$10$examplehash7','Veterinary');

INSERT INTO pets (pet_id, owner_id, pet_name, species, breed, age)
VALUES 
('pet1','o1','Rex','Dog','Labrador',5),
('pet2','o2','Milo','Cat','Siamese',3);