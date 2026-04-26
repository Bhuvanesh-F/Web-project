# VitalCare Clinic | Ayman Muhammad Shaikh 

> **ICT2213Y(3) – Web Technologies and Security | Week 20 Submission**  
> Faculty of Information and Digital Technologies, University of Mauritius

---

## Student Information

| Field       | Details                                      |
|-------------|----------------------------------------------|
| **Name**    | Ayman Muhammad Shaikh                        |
| **Student ID**    | 2412575                                |
| **Course**  | ICT2213Y(3) – Web Technologies and Security  |
| **Instructors** | Mr Anwar Chutoo & Mrs Begum Durgahee     |
| **Branch**  | `Shaikh_Ayman_2412575`                       |
| **Submission** | Week 20                                   |

---

## Project Overview

**VitalCare** is a full-stack clinic management web application built to digitise and secure the daily operations of a dual-purpose healthcare facility — one serving human patients and one serving pet owners and their animals. The system consolidates patient registration, appointment management, medical records, staff dashboards, and administrative oversight into a single and role-aware platform.

### Objectives

- Deliver a fully functional web application with both a **RESTful JSON API** (consumed via AJAX) and a **Blade-rendered web front end**.
- Implement **role-based access control** across seven distinct user roles: `admin`, `doctor`, `vet`, `nurse`, `receptionist`, `patient`, and `pet_owner`.
- Demonstrate real-world mitigation of the four core security threats outlined in the assignment brief: **SQL Injection**, **Session Hijacking**, **XSS**, and **CSRF**.
- Apply the **Laravel MVC framework**, **Sanctum token authentication**, and proper software engineering principles (PSR-4 autoloading, FormRequest validation, Service layer, custom Middleware).

### Assignment Context

This submission satisfies the Week 20 deliverables: a live project demonstration with AJAX, JSON, JSON Schema (via Laravel validation), and full Laravel integration which is built upon the Week 10 baseline of HTML, PHP, jQuery, and database connectivity.

---

## Features Implemented

### Authentication & Session Management
- **Dual authentication layer**: Session-based login for web dashboards (Blade views) and token-based login for the REST API (Laravel Sanctum).
- **Multi-step registration flow**: Patient and pet owner registration is split across three conceptual steps, collected client-side and submitted to a single validated endpoint.
- **Role-based post-login redirect**: After authentication, users are automatically sent to the correct dashboard for their role (`/admin/dashboard`, `/doctor/dashboard`, `/patient/dashboard`, etc.).
- **Single active session enforcement**: On API login, all previous tokens for the user are revoked before a new one is issued, preventing concurrent session abuse.
- **Remember me** support on web login.
- **Secure logout**: Full session invalidation and CSRF token regeneration on both web and API logout paths.

### Role-Based Access Control (RBAC)
- Seven roles enforced at the route level via a custom `RoleCheck` middleware.
- Role abilities are embedded directly into Sanctum tokens at issuance (e.g., `admin:*`, `doctor:*`, `patient:read`).
- API routes are grouped by role with layered middleware: `auth:sanctum` + `role:<allowed_roles>`.
- Web routes use `auth` + `role:<role>` + `secure.session` middleware stacks.
- Unauthorised users are redirected to their own dashboard rather than shown a generic error page.
- The `User` model exposes `hasRole()`, `hasAnyRole()`, `isAdmin()`, and `isMedicalStaff()` helper methods for clean programmatic role checks.

### REST API (JSON / AJAX)
- Full CRUD REST API covering: **authentication**, **admin**, **doctors**, **patients**, **appointments**, **medical records**, **nurses**, **receptionists**, **pet owners**, **pets**, **reviews**, and a **contact** endpoint.
- All responses follow a consistent JSON envelope: `{ success, message, data }`.
- Public health-check endpoint (`GET /api/health`) returns system status and timestamp.
- API routes are versioned under the `/api` prefix via `RouteServiceProvider`.
- Rate limiting applied: `throttle:5,1` on login (5 requests/minute), `throttle:10,1` on registration, `throttle:60,1` on all other protected endpoints.

### Admin Dashboard
- `GET /api/admin/statistics` returns live system-wide counters (total patients, doctors, vets, nurses, receptionists, pet owners, and users) for AJAX-driven dashboard widgets.
- `GET /api/admin/audit-logs` provides **paginated** audit log entries with metadata (`current_page`, `last_page`, `per_page`, `total`) for the admin panel table.
- User CRUD operations: create, read, update, and delete users via the admin API.
- Admin web routes are protected by an additional `secure.session` middleware layer.

### Security Mitigations

#### SQL Injection Prevention
- All database interactions use **Laravel's Eloquent ORM** and the **Query Builder** with parameterised bindings. No raw SQL string concatenation is used.
- The `ReviewController` demonstrates the Query Builder approach (`DB::table()->join()->where()->select()->get()`), with all user-supplied values bound as parameters.

#### XSS Prevention
- A custom `SanitizeInput` middleware is applied globally to all `POST`, `PUT`, and `PATCH` requests. It recursively strips HTML/PHP tags via `strip_tags()`, converts special characters to HTML entities via `htmlspecialchars(ENT_QUOTES | ENT_HTML5)`, and removes null bytes.
- Password and token fields are explicitly exempted from sanitisation to prevent corruption of bcrypt hashes.
- Blade's `{{ }}` syntax auto-escapes all output in views.

#### Session Hijacking Prevention
- A custom `SecureSession` middleware is applied to all authenticated web routes. It builds a **session fingerprint** from a SHA-256 hash of the user's IP address and User-Agent string.
- On every authenticated request, the fingerprint is compared against the stored value. A mismatch triggers immediate logout, session invalidation, and a `401` response.
- Session ID is **regenerated** on first bind and at every login to prevent session fixation attacks.
- An **idle timeout** (30 minutes) automatically invalidates sessions for inactive users.

#### CSRF Prevention
- Laravel's built-in `VerifyCsrfToken` middleware is active for all web routes.
- API routes (`api/*`) are correctly excluded because they are protected by Sanctum Bearer tokens instead — this is explicitly documented in the middleware to prevent future accidental exemptions.
- CSRF tokens are regenerated after every login and logout event.

### Audit Logging
- A dedicated `AuditService` (Service layer) and `AuditLog` model provide append-only audit trail records for all significant actions.
- Each log entry captures: `action_type`, `performed_by` (user ID), `performed_by_role`, `affected_table`, `affected_record_id`, `description`, and `ip_address`.
- The `AuditLog` model disables `UPDATED_AT` to enforce immutability — audit records cannot be modified after creation.
- Audit log writes are wrapped in `try/catch` so that a logging failure never interrupts the main request flow.

### Input Validation
- Dedicated `FormRequest` classes (`LoginRequest`, `RegisterRequest`) centralise validation logic away from controllers.
- `RegisterRequest` enforces a **strong password policy** using Laravel's `Password::min(8)->letters()->numbers()->mixedCase()` rule.
- Phone number input is validated against a regex pattern (`/^\+?[\d\s\-]{7,15}$/`).
- Email uniqueness is enforced at both the validation layer and the database level (`UNIQUE` constraint).
- All `FormRequest` classes override `failedValidation()` to return a structured JSON error response consistent with the API envelope format.

### Database
- **MySQL** database (`vitalcare_db`) designed to 3NF with `utf8mb4` charset for full Unicode support.
- Schema includes tables for: `users`, `human_patients`, appointments, medical records, pets, pet owners, audit logs, reviews, and password reset tokens.
- Laravel migrations are provided for `users`, `audit_logs`, and `password_reset_tokens`, with `db:seed` support to bootstrap default admin, doctor, and patient accounts.
- The `users` table uses an `ENUM` role column with a database index on `role` for fast middleware lookups.

### Contact & Reviews
- A `ContactController` handles the contact form submission via `POST /api/contact`.
- A `ReviewController` allows authenticated patients and pet owners to submit reviews (pending admin moderation) and retrieves approved reviews for public display, joined with user name and role.

---

## Technologies Used

| Category          | Technology / Tool                          |
|-------------------|--------------------------------------------|
| **Backend**       | PHP 8.1+, Laravel 10                       |
| **Authentication**| Laravel Sanctum (API tokens), Laravel Auth (web sessions) |
| **Database**      | MySQL 8, Laravel Eloquent ORM, Query Builder |
| **Frontend**      | Blade templating engine, HTML5, CSS3       |
| **HTTP Client**   | Guzzle 7 (`guzzlehttp/guzzle`)             |
| **API Standard**  | RESTful JSON API                           |
| **Security**      | Custom Middleware (RBAC, XSS sanitisation, session fingerprinting), CSRF tokens, bcrypt hashing |
| **Testing**       | PHPUnit 10, Mockery (configured)           |
| **Dev Tools**     | Laravel Pint (code style), Laravel Sail, Faker, Spatie Ignition |
| **Version Control**| Git / GitHub (`feature/auth-laravel` branch) |

---

## System Design / Architecture

The project follows Laravel's **MVC (Model-View-Controller)** architecture with an additional **Service layer** for cross-cutting concerns such as audit logging.

```
vitalcare-fixed/
├── app/
│   ├── Console/            # Artisan command kernel
│   ├── Exceptions/         # Global exception handler
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/        # JSON API controllers (AuthController, AdminApiController, ...)
│   │   │   ├── Admin/      # Blade web controllers — Admin
│   │   │   ├── Doctor/     # Blade web controllers — Doctor
│   │   │   ├── Nurse/      # Blade web controllers — Nurse
│   │   │   ├── Patient/    # Blade web controllers — Patient
│   │   │   ├── PetOwner/   # Blade web controllers — Pet Owner
│   │   │   ├── Receptionist/
│   │   │   ├── PublicController.php   # Home, About, Contact pages
│   │   │   └── WebAuthController.php  # Session login/logout/register
│   │   ├── Middleware/
│   │   │   ├── RoleCheck.php          # RBAC enforcement
│   │   │   ├── SecureSession.php      # Session hijacking prevention
│   │   │   ├── SanitizeInput.php      # XSS input sanitisation
│   │   │   └── VerifyCsrfToken.php    # CSRF protection
│   │   ├── Requests/
│   │   │   ├── LoginRequest.php       # API login validation
│   │   │   └── RegisterRequest.php    # API registration validation
│   │   └── Kernel.php                 # Middleware registration
│   ├── Models/
│   │   ├── User.php                   # Central auth model (Sanctum + RBAC helpers)
│   │   └── AuditLog.php               # Immutable audit trail model
│   ├── Providers/                     # Service providers (Auth, Route, Event)
│   └── Services/
│       └── AuditService.php           # Centralised audit logging service
├── config/                            # App, auth, database, session, sanctum configs
├── database/
│   ├── migrations/                    # Schema version control
│   ├── seeders/                       # Default user seeding
│   └── (vitalcare_db.sql)             # Full MySQL schema export
├── resources/views/                   # Blade templates
├── routes/
│   ├── api.php                        # All /api/* routes
│   └── web.php                        # All session-based web routes
├── public/                            # Web root (index.php)
└── composer.json                      # Dependency manifest
```

### Component Interaction

1. **HTTP Request** → hits `public/index.php` → Laravel bootstraps via `bootstrap/app.php`.
2. **Middleware pipeline** executes in order: `SanitizeInput` (XSS) → `VerifyCsrfToken` (web only) → `Authenticate` → `RoleCheck` → `SecureSession` (web only).
3. **Router** (`api.php` / `web.php`) dispatches to the appropriate **Controller**.
4. **FormRequest** classes intercept and validate input before the controller method is invoked.
5. **Controllers** interact with **Models** (Eloquent) or **Services** (AuditService) and return either a Blade `view()` or a `response()->json()`.
6. **AuditService** writes append-only records to the `audit_logs` table; failures are caught and logged without disrupting the primary response.

---

## Implementation Details

### Dual Authentication Architecture

The system implements two parallel authentication flows to satisfy both web and API consumers:

- **Web (session-based)**: `WebAuthController` uses `Auth::attempt()`, `Session::regenerate()`, and role-based redirect. Protected routes carry the `secure.session` middleware which fingerprints each request.
- **API (token-based)**: `AuthController` uses `Auth::attempt()` followed by `$user->createToken()` with role-scoped abilities. The `auth:sanctum` guard validates the `Authorization: Bearer <token>` header on subsequent requests.

### Role-Scoped Token Abilities

When a Sanctum token is issued, it carries a set of abilities derived from the user's role via `getRoleAbilities()`. For example, an admin token carries `['admin:*', 'doctor:read', 'patient:read', 'appointment:*']`, while a patient token carries `['patient:read', 'appointment:read', 'record:read']`. This provides a second layer of authorisation beyond route-level role middleware.

### Session Fingerprinting

The `SecureSession` middleware computes `hash('sha256', $ip . '|' . $userAgent)` on each request and compares it to the stored fingerprint. This binds the session to a specific client context without storing any sensitive raw data. If the fingerprint changes — as it would when a stolen session cookie is used from a different network or browser — the session is immediately destroyed.

### SanitizeInput Middleware — Defence in Depth

While Eloquent's parameterised queries already prevent SQL injection and Blade's `{{ }}` escapes output, the `SanitizeInput` middleware adds a proactive layer by stripping tags and encoding entities on all inbound POST/PUT/PATCH data before it reaches the controller. Password fields are whitelisted to prevent bcrypt hash corruption.

### Paginated Audit Log API

The `GET /api/admin/audit-logs` endpoint uses `AuditLog::paginate($perPage)` with a maximum cap of 100 records per page. The JSON response includes both `data` (the log entries) and a `meta` block with pagination metadata, enabling the frontend to build a paginated admin table via AJAX without page reloads.

### Immutable Audit Log Model

The `AuditLog` model sets `const UPDATED_AT = null`, disabling Laravel's automatic update timestamp. This is a deliberate design choice: audit records must remain unchanged after creation to be trustworthy as a security trail.

### Admin Statistics Endpoint

`GET /api/admin/statistics` aggregates user counts by role using Eloquent's `where('role', ...)->count()` and includes the five most recent audit log entries (with their performer's name via eager loading) for display on the admin dashboard. This powers live AJAX dashboard counters without needing a full page reload.

---

## Challenges Faced & Solutions

**1. Session Security Without Breaking Usability**

Implementing session fingerprinting (IP + User-Agent binding) risked locking out legitimate users whose IP changes mid-session (e.g., mobile users switching between Wi-Fi and mobile data). The solution was to use `SecureSession` only on protected web dashboards rather than public pages, and to provide a clear re-login prompt with a descriptive error message rather than a silent failure.

**2. Dual Auth Patterns in a Single Application**

Supporting both Sanctum token auth (for the API) and Laravel session auth (for web dashboards) in parallel required careful middleware separation. API routes use `auth:sanctum` while web routes use `auth` (session guard). The `VerifyCsrfToken` middleware correctly excludes `api/*` routes because those are already protected by Bearer tokens.

**3. Password Sanitisation Conflict**

Applying HTML entity encoding to all POST data would corrupt password inputs before bcrypt comparison. This was resolved by maintaining an explicit `$exemptFields` array in `SanitizeInput` that passes `password`, `password_confirmation`, `current_password`, and `_token` through untouched.

**4. Consistent JSON Error Responses**

Laravel's default validation failure redirects back with session errors, which is incompatible with a JSON API client. This was solved by overriding `failedValidation()` in each `FormRequest` to throw an `HttpResponseException` with a structured JSON payload, ensuring consistent `{ success, message, errors }` responses across all endpoints.

**5. Audit Log Reliability**

An audit log write failure (e.g., database timeout) must never crash the request that triggered it. The `AuditService` wraps all writes in a `try/catch` block and silently logs the error to Laravel's log channel, ensuring the primary business operation always completes successfully.

---

## Testing & Validation

### Manual Testing

All authentication flows were manually tested against the following scenarios:

- Valid login with each seeded role (admin, doctor, patient) verifying correct dashboard redirect.
- Invalid credentials returning a `401` JSON response with the appropriate message.
- Duplicate email registration returning a `422` validation error.
- Accessing an admin route while logged in as a patient resulting in a `403` redirect to the patient's own dashboard.
- Submitting a form with an XSS payload (`<script>alert(1)</script>`) and verifying the output is entity-encoded in the stored value.
- Manually expiring an idle session (30-minute threshold) and confirming the user is redirected to login.

### Validation Layer Testing

- `RegisterRequest` was tested with passwords that do not meet the `letters()`, `numbers()`, and `mixedCase()` requirements, each producing the correct `422` error.
- Phone number validation regex was tested against valid Mauritian formats (`+230 5xxx xxxx`) and invalid strings.
- `unique:users,email` constraint was tested by attempting to register an already-existing email.

### Database Seeder

Running `php artisan db:seed` creates three verifiable test accounts (admin, doctor, patient) with known credentials, providing a repeatable test baseline.

### Configured Testing Framework

PHPUnit 10 and Mockery are listed as dev dependencies in `composer.json`, and the `tests/` directory is registered under PSR-4 autoloading, providing the foundation for future automated test suites.

---

## How to Run the Project

### Prerequisites

- PHP 8.1 or higher
- Composer
- MySQL 8.0+
- A local server environment (Laravel Sail, XAMPP, or native PHP)

### Setup Instructions

**1. Extract and enter the project directory**
```bash
unzip vitalcare_zip.zip
cd vitalcare-fixed
```

**2. Install PHP dependencies**
```bash
composer install
```

**3. Configure environment**
```bash
cp .env.example .env
php artisan key:generate
```

**4. Update `.env` with your database credentials**
```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vitalcare_db
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

**5. Create the database and run migrations**
```bash
# Create the database in MySQL first, then:
php artisan migrate
```

*Alternatively, import the full schema directly:*
```bash
mysql -u your_db_user -p vitalcare_db < vitalcare_db.sql
```

**6. Seed default users**
```bash
php artisan db:seed
```

This creates the following accounts:

| Role    | Email                      | Password       |
|---------|----------------------------|----------------|
| Admin   | admin@vitalcare.com        | Admin@1234     |
| Doctor  | doctor@vitalcare.com       | Doctor@1234    |
| Patient | patient@vitalcare.com      | Patient@1234   |

**7. Start the development server**
```bash
php artisan serve
```

The application will be accessible at `http://127.0.0.1:8000`.

### Key URLs

| URL                           | Description                      |
|-------------------------------|----------------------------------|
| `GET /`                       | Public home page                 |
| `GET /login`                  | Web login form                   |
| `GET /register`               | Patient / pet owner registration |
| `GET /admin/dashboard`        | Admin dashboard (role-protected) |
| `GET /doctor/dashboard`       | Doctor dashboard                 |
| `GET /patient/dashboard`      | Patient dashboard                |
| `POST /api/auth/login`        | API login → returns Bearer token |
| `GET /api/admin/statistics`   | AJAX dashboard statistics        |
| `GET /api/health`             | Public health check              |

---

## Conclusion

This submission delivers a production-structured Laravel 10 web application that addresses all Week 20 requirements: a complete REST API consumed via AJAX, JSON responses with structured schemas enforced through Laravel's validation layer, full Laravel MVC architecture, and a Blade-rendered web interface.

Beyond the functional requirements, the project places deliberate emphasis on the security objectives of ICT2213Y. Each of the four mandated threat mitigations — SQL Injection, Session Hijacking, XSS, and CSRF — is addressed through dedicated, documented, and testable code: custom middleware, FormRequest validation, Sanctum token scoping, and session fingerprinting.

### Key Learning Outcomes

- Practical experience implementing **dual authentication** (session + token) in a single Laravel application.
- Deep understanding of **middleware pipelines** and their role in layered security.
- Application of the **Service layer pattern** for cross-cutting concerns (audit logging) that must not interfere with the primary request flow.
- Designing **role-scoped token abilities** with Laravel Sanctum to enforce least-privilege access at the API level.
- Writing **FormRequest** classes that produce consistent, client-friendly JSON validation errors.
- Building a **RESTful API** with appropriate HTTP status codes, consistent response envelopes, and rate limiting.

---

*Developed as part of ICT2213Y(3) – Web Technologies and Security, University of Mauritius.*

---
---
# VitalCare – Week 10 Individual Contribution
### ICT2213Y(3) – Web Technologies and Security
**Student:** Ayman  
**Module:** ICT2213Y(3) – Web Technologies and Security  
**Institution:** University of Mauritius, Faculty of Information and Digital Technologies  
**Instructors:** Anwar Chutoo and Begum Durgahee

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [My Contributions](#2-my-contributions)
3. [Database Design](#3-database-design)
4. [Implementation (Coding)](#4-implementation-coding)
5. [PowerPoint Summary](#5-powerpoint-summary)
6. [How to Run the Project](#6-how-to-run-the-project)
7. [Reflection](#7-reflection)

---

## 1. Project Overview

**VitalCare** is a full-stack web application developed as the group project for ICT2213Y(3). It simulates a healthcare management system that supports both **human patient care** and **pet care** services under one platform. The system allows patients and pet owners to register, log in, book appointments, and view medical records, while administrators can manage staff, approve appointments, and monitor system activity.

The Week 10 milestone required the team to demonstrate:
- A working HTML/PHP/jQuery front end connected to a live MySQL database
- Core features: registration, login, appointment booking, and medical records
- Evidence of security practices: SQL injection prevention, session management, XSS protection, and CSRF mitigation
- A PowerPoint presentation covering the system overview, functional specifications, and individual contributions

---

## 2. My Contributions

### 2.1 — Initially Assigned Work

As the **Database Developer** for the team (as defined in the Week 10 task distribution document), my formally assigned responsibilities were:

- Design and implement the full MySQL relational database schema
- Define all tables, data types, constraints, and foreign key relationships
- Export the complete `database.sql` file
- Produce an ERD diagram
- Insert sample/seed data for demonstration purposes
- Contribute to the group PowerPoint presentation (all slides except slides 14–15)

> **Note:** Slides 14 and 15 of the Week 10 PowerPoint were completed by teammate Sehun as part of his documentation lead role.

### 2.2 — Additional Work (Beyond Assigned Tasks)

After fulfilling my assigned responsibilities, I independently undertook and completed the **full coding implementation** of the VitalCare Week 10 application. This work was not part of my original role but was completed to ensure the team had a functional, demonstrable system. It covers:

- All PHP backend logic (authentication, CRUD, AJAX endpoints, session handling)
- All frontend pages (HTML, CSS, JavaScript)
- Security implementation across the entire codebase
- A complete, structured project with modular includes and a consistent MVC-inspired architecture

The full implementation is provided in `vitalcare_week10.zip`.

---

## 3. Database Design

### 3.1 Schema Overview

The database (`vitalcare_db`) was designed from scratch to support a dual-service healthcare platform. It is structured around two parallel domains — **human healthcare** and **pet care** — each with its own dedicated set of tables.

**Human Healthcare Tables:**

| Table | Purpose |
|---|---|
| `human_patients` | Stores patient registration data including hashed passwords |
| `human_doctors` | Doctor profiles with speciality, fees, and experience |
| `human_nurses` | Nurse profiles |
| `human_admins` | Admin accounts for system management |
| `receptionists` | Receptionist accounts |
| `human_appointments` | Appointment records linked to patients and doctors |
| `human_medical_records` | Diagnosis, treatment, and prescription records |
| `human_reviews` | Patient reviews and star ratings for doctors |

**Pet Care Tables:**

| Table | Purpose |
|---|---|
| `pet_owners` | Pet owner registration and credentials |
| `pets` | Individual pet profiles linked to their owners |
| `pet_doctors` | Vet profiles with speciality and fees |
| `pet_nurses` | Vet nurse profiles |
| `pet_admins` | Admin accounts for the pet care side |
| `pet_appointments` | Appointment records linked to pets and vets |
| `pet_medical_records` | Veterinary diagnosis, treatment, and prescription records |

**System-Wide Tables:**

| Table | Purpose |
|---|---|
| `contact_messages` | Stores visitor enquiries submitted via the contact form |
| `nurse_checklist` | Task management for nurses across both domains |
| `audit_logs` | Logs actions by role, actor ID, IP address, and timestamp |

### 3.2 Key Design Decisions

- **UUID primary keys (`CHAR(36)`):** All primary keys use UUID v4 format rather than auto-increment integers. This avoids sequential ID enumeration attacks, which is especially relevant for a healthcare system with sensitive personal data.

- **Referential integrity via foreign keys:** All relationships between entities are enforced at the database level. For example, an appointment references both a valid patient/pet and a valid doctor. `ON DELETE CASCADE` is used where child records should be removed with the parent (e.g., a patient's appointments), and `ON DELETE SET NULL` is used where historical records should be preserved even if the linked entity is deleted (e.g., medical records retaining doctor reference after a doctor is removed).

- **Separation of human and pet domains:** Rather than a single generic schema, the human and pet sides each have their own independent table sets. This avoids complex polymorphic joins and keeps the schema readable and maintainable.

- **Audit logging:** The `audit_logs` table records who performed what action, on which object, and from which IP address. This is a critical security feature that supports accountability and post-incident investigation.

- **Data validation at schema level:** Constraints such as `CHECK (experience >= 0)`, `CHECK (rating BETWEEN 1 AND 5)`, `ENUM` types for status and gender fields, and `UNIQUE` on email columns enforce data integrity at the database layer, complementing application-level validation.

- **Password storage:** The `password_hash` column uses `TEXT` type, designed to store bcrypt hashes produced by PHP's `password_hash()` function. Plain-text passwords are never stored.

### 3.3 Entity Relationships (Summary)

- A `human_patient` can have many `human_appointments`, `human_medical_records`, and `human_reviews`
- A `human_doctor` can be linked to many appointments, records, and reviews
- A `pet_owner` can own many `pets`; each `pet` can have many `pet_appointments` and `pet_medical_records`
- A `nurse_checklist` entry links to either a patient or a pet, with a role discriminator field

---

## 4. Implementation (Coding)

### 4.1 System Overview

The coding implementation is a fully functional PHP/MySQL web application running on XAMPP. It follows an **MVC-inspired modular structure**, separating concerns into reusable includes, API endpoints, role-specific subdirectories, and a shared stylesheet.

### 4.2 Directory Structure

```
vitalcare/
├── index.php                  ← Public homepage
├── login.php                  ← Patient login
├── register.php               ← Patient registration
├── logout.php / admin-logout.php
├── book-appointment.php       ← Appointment booking (AJAX-driven)
├── services.php               ← Services listing page
├── contact.php                ← Contact form (saves to DB)
│
├── includes/
│   ├── db.php                 ← Database connection & utility functions
│   ├── auth.php               ← Session management & CSRF helpers
│   ├── header.php             ← Shared page header
│   └── footer.php             ← Shared page footer
│
├── css/
│   └── style.css              ← External stylesheet
│
├── js/
│   └── main.js                ← Client-side validation & AJAX calls
│
├── api/
│   ├── book-appointment.php   ← AJAX endpoint: save appointment
│   └── get-doctors.php        ← AJAX endpoint: fetch doctors by speciality
│
├── patient/
│   ├── dashboard.php          ← Patient dashboard with live DB data
│   ├── appointments.php       ← View and cancel appointments
│   ├── medical-records.php    ← View medical history
│   └── profile.php            ← Edit profile information
│
├── admin/
│   ├── dashboard.php          ← Admin dashboard with live statistics
│   ├── appointments.php       ← Manage and approve all appointments
│   ├── staff.php              ← View and remove doctors and nurses
│   ├── add-doctor.php         ← Add a doctor (saves to DB)
│   └── add-nurse.php          ← Add a nurse (saves to DB)
│
└── vitalcare_db.sql           ← Full schema + seed data
```

### 4.3 Key Features Implemented

**Patient Features:**
- Self-registration with server-side validation and bcrypt password hashing
- Secure login with session regeneration on authentication
- Dashboard displaying appointment history and medical records from the database
- AJAX-powered appointment booking — speciality selection dynamically populates available doctors without page reload
- Appointment cancellation with real-time status update in the database
- Profile editing

**Admin Features:**
- Separate admin login portal with role-based session enforcement
- Dashboard displaying live statistics (patient count, doctor count, pending appointments)
- Full appointment management: view all appointments, approve or update status
- Staff management: add doctors and nurses, view all staff, remove staff members

**General Features:**
- Contact form saving messages to the `contact_messages` table
- Services listing page
- Reusable header and footer components included across all pages

### 4.4 Security Implementation

Security was treated as a first-class concern throughout the implementation, directly addressing the project brief's requirements:

| Threat | Mitigation Implemented |
|---|---|
| **SQL Injection** | All database queries use prepared statements with `$stmt->bind_param()` — no raw user input is concatenated into SQL strings |
| **XSS (Cross-Site Scripting)** | All output rendered in HTML passes through `htmlspecialchars()` via the `h()` helper function in `db.php` |
| **CSRF (Cross-Site Request Forgery)** | Every POST form includes a hidden `csrf_token` field; the server validates it using `hash_equals()` before processing the request |
| **Session Hijacking** | `session_regenerate_id(true)` is called on login; sessions use `cookie_httponly` and `cookie_samesite: Strict` flags |
| **Password Security** | Passwords are hashed using PHP's `password_hash()` (bcrypt) and verified with `password_verify()` |
| **Role-Based Access Control** | `requirePatient()` and `requireAdmin()` guard functions in `auth.php` enforce that protected pages are only accessible to the correct authenticated role |

### 4.5 Technologies Used

| Layer | Technology |
|---|---|
| Frontend | HTML5, CSS3, JavaScript (ES6) |
| Backend | PHP 8.x |
| Database | MySQL (MariaDB via XAMPP) |
| AJAX | Fetch API (JSON responses) |
| Security | Prepared statements, bcrypt, CSRF tokens, XSS escaping, session hardening |
| Architecture | Modular includes (MVC-inspired) |

### 4.6 Challenges and Solutions

**Challenge: AJAX appointment booking with dynamic doctor population**  
The appointment booking form required the doctor dropdown to update automatically based on the selected speciality, without a full page reload. This was solved by creating a dedicated API endpoint (`api/get-doctors.php`) that accepts a speciality parameter via a `fetch()` call and returns a JSON array of matching doctors. The JavaScript in `main.js` then dynamically rebuilds the dropdown from the response.

**Challenge: Consistent CSRF protection across all forms**  
Implementing CSRF protection consistently across every POST form required a centralised approach. By placing the `csrfToken()` and `verifyCsrf()` functions in `includes/auth.php` and including this file on every page, CSRF tokens were generated and validated uniformly without duplicating logic.

**Challenge: Maintaining role separation**  
The dual-domain nature of the system (human and pet) alongside multiple staff roles required careful session and access control design. The `requirePatient()` and `requireAdmin()` functions in `auth.php` provide lightweight but effective guards that redirect unauthorised users before any sensitive page logic runs.

---

## 5. PowerPoint Summary

The Week 10 group presentation was prepared by me with the exception of slides 14 and 15, which were contributed by teammate Sehun.

**Key sections covered in the presentation:**

- **Project Introduction:** Overview of the VitalCare concept, the business problem it solves, and the target user base (patients, pet owners, and healthcare staff)
- **System Architecture:** High-level overview of the three-tier structure (frontend, backend, database) and how the components interact
- **Functional Requirements:** Summary of the core use cases — registration, login, appointment booking, medical records management, and admin controls
- **Database Design:** Walkthrough of the ERD, table structure, and the reasoning behind key design decisions (UUID keys, foreign key constraints, audit logging)
- **Security Features:** Explanation of the security techniques implemented, directly mapped to the threats identified in the project brief (SQL injection, XSS, CSRF, session hijacking)
- **Individual Contribution:** Clear breakdown of each team member's assigned tasks and deliverables
- **Live Demonstration Outline:** Checklist of features to be demonstrated live during the presentation

> Slides 14–15 (covering use case descriptions and functional specifications documentation) were prepared by Sehun.

---

## 6. How to Run the Project

The application runs on a local XAMPP stack. Follow the steps below to set it up.

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) installed (PHP 8.x, Apache, MySQL/MariaDB)

### Step 1 — Start XAMPP
1. Open the XAMPP Control Panel
2. Start **Apache** and **MySQL**

### Step 2 — Copy Project Files
1. Extract `vitalcare_week10.zip`
2. Copy the `vitalcare/` folder into:
   ```
   C:\xampp\htdocs\vitalcare\
   ```

### Step 3 — Import the Database
1. Open your browser and navigate to `http://localhost/phpmyadmin`
2. Click **New** in the left sidebar and create a database named `vitalcare_db`
3. Select the new database, click **Import** at the top
4. Choose the file `vitalcare/vitalcare_db.sql` and click **Go**

### Step 4 — Verify Database Configuration
Open `includes/db.php` and confirm the credentials match your XAMPP setup:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');    // default XAMPP username
define('DB_PASS', '');        // default XAMPP has no password
define('DB_NAME', 'vitalcare_db');
```
Update `DB_PASS` if you have set a MySQL root password.

### Step 5 — Launch the Application
Open your browser and go to:
```
http://localhost/vitalcare/
```

### Default Login Credentials

**Admin:**
- URL: `http://localhost/vitalcare/admin-login.php`
- Email: `admin`
- Password: `password`

**Patient:**
- Register a new account at `http://localhost/vitalcare/register.php`

**Sample Doctors (seeded in database):**
- Dr. Alice Martin – General Medicine
- Dr. Robert Chen – Cardiology
- Dr. Priya Sharma – Dermatology
- Dr. James Okonkwo – Pediatrics

### Troubleshooting

| Problem | Solution |
|---|---|
| Blank page | Add `ini_set('display_errors', 1);` to the top of `db.php` to see PHP errors |
| Database connection failed | Verify `DB_USER` and `DB_PASS` in `includes/db.php` |
| 404 errors | Confirm the folder is at `C:\xampp\htdocs\vitalcare\` |
| CSRF validation error | Clear browser cookies and try again |
| Appointment not saving | Ensure you are logged in as a patient before booking |

---

## 7. Reflection

### What I Learned

Working on VitalCare across both the database design and the full coding implementation gave me a significantly broader understanding of how all layers of a web application connect and depend on each other.

On the **database side**, designing a schema for a multi-role, dual-domain system forced me to think carefully about data integrity, referential constraints, and the security implications of schema design choices — such as using UUID keys to prevent sequential ID enumeration and using `ON DELETE CASCADE` vs `ON DELETE SET NULL` intentionally rather than arbitrarily.

On the **security side**, implementing CSRF protection, prepared statements, and XSS escaping not as isolated exercises but as features woven into a real application helped me understand why these techniques matter in practice, not just in theory.

### Skills Developed

- Designing normalised relational database schemas for complex, multi-entity systems
- Writing secure PHP applications using prepared statements, session hardening, and CSRF tokens
- Building AJAX-driven interactions using the Fetch API and JSON endpoints
- Structuring a PHP project modularly with shared includes and role-based access guards
- Reading and understanding how application logic, session state, and database queries interact at runtime

### How This Task Improved My Understanding

Before this project, my understanding of web security was largely theoretical. Implementing CSRF tokens on every form, writing the `verifyCsrf()` and `csrfToken()` functions, and seeing how a missing token causes a 403 response made the protection mechanism concrete and intuitive. Similarly, tracing how a SQL injection would fail against a prepared statement — because the user input is bound as a parameter rather than spliced into the query string — gave me a much clearer mental model of why parameterised queries are the correct approach.

Going beyond my assigned role and completing the full implementation also reinforced the importance of having end-to-end ownership of a feature: understanding the database schema made it significantly easier to write correct, efficient PHP queries, and thinking about security at the schema level (UUID keys, audit logs) made the application-level security measures feel like a natural continuation rather than an afterthought.
