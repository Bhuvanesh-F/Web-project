# VitalCare — Ayman Muhammad Shaikh 

> **ICT2213Y(3) – Web Technologies and Security**
>
> University of Mauritius · Faculty of Information and Digital Technologies
>
> Instructors: Mr. Anwar Chutoo & Mrs. Begum Durgahee

---

## Student Information

| Field | Details |
|---|---|
| **Name** | Ayman Muhammad Shaikh |
| **Student ID** | 2412575 |
| **Course** | ICT2213Y(3) – Web Technologies and Security |
| **Branch** | `Shaikh_Ayman_2412575` |

---

## Table of Contents

**Part A — Week 20 Submission (Laravel) - Individual Contribution**
1. [Project Overview](#1-project-overview)
2. [Assignment Context](#2-assignment-context)
3. [Features Implemented](#3-features-implemented)
4. [Security Mitigations](#4-security-mitigations)
5. [Technologies Used](#5-technologies-used)
6. [System Architecture](#6-system-architecture)
7. [Implementation Details](#7-implementation-details)
8. [Challenges & Solutions](#8-challenges--solutions)
9. [Testing & Validation](#9-testing--validation)
10. [How to Run — Week 20 (Laravel)](#10-how-to-run--week-20-laravel)
11. [Conclusion](#11-conclusion)

**Part B — Week 10 Individual Contribution**

12. [Week 10 — Project Overview](#12-week-10--project-overview)
13. [My Contributions](#13-my-contributions)
14. [Database Design](#14-database-design)
15. [Coding Implementation](#15-coding-implementation)
16. [PowerPoint Summary](#16-powerpoint-summary)
17. [How to Run — Week 10 (XAMPP)](#17-how-to-run--week-10-xampp)
18. [Reflection](#18-reflection)

---

# PART A — Week 20 Submission (Laravel)

---

## 1. Project Overview

**VitalCare** is a production-structured, full-stack clinic management web application that digitises and secures the daily operations of a dual-purpose healthcare facility which serves both human patients and pet owners. The system consolidates patient registration, appointment management, medical records, staff dashboards, and administrative oversight into a single, role-aware platform.

### Objectives

- Deliver a fully functional web application with both a **RESTful JSON API** (consumed via AJAX) and a **Blade-rendered web front end**.
- Implement **role-based access control** across seven distinct user roles: `admin`, `doctor`, `vet`, `nurse`, `receptionist`, `patient`, and `pet_owner`.
- Demonstrate real-world mitigation of the four core security threats from the assignment brief: **SQL Injection**, **Session Hijacking**, **XSS**, and **CSRF**.
- Apply the **Laravel MVC framework**, **Sanctum token authentication**, and professional software engineering principles (PSR-4 autoloading, FormRequest validation, Service layer, custom Middleware).

---

## 2. Assignment Context

This submission satisfies all **Week 20 deliverables**: a live project demonstration with AJAX, JSON, JSON Schema (via Laravel validation), and full Laravel integration which is built upon the Week 10 baseline of HTML, PHP, jQuery, and database connectivity.

| Brief Requirement | How It Is Met |
|---|---|
| Functional web application | Full Laravel 10 + Blade + REST API |
| Security mitigations (SQLi, XSS, CSRF, Session Hijacking) | Custom middleware: `SanitizeInput`, `SecureSession`, `VerifyCsrfToken`, Eloquent ORM |
| AJAX / JSON / JSON Schema | AJAX dashboards, consistent JSON envelope, Laravel FormRequest validation |
| Laravel integration | Full MVC, Sanctum, Eloquent, Artisan migrations & seeders |
| Git contributions | All commits tracked on branch `Shaikh_Ayman_2412575` |

---

## 3. Features Implemented

### 3.1 Authentication & Session Management

- **Dual authentication layer** — Session-based login for web dashboards (Blade) and token-based login for the REST API (Laravel Sanctum).
- **Multi-step registration flow** — Patient and pet owner registration is collected client-side across three steps and submitted to a single validated endpoint.
- **Role-based post-login redirect** — Users are automatically routed to the correct dashboard after login (`/admin/dashboard`, `/doctor/dashboard`, `/patient/dashboard`, etc.).
- **Single active session enforcement** — On API login, all previous tokens are revoked before a new one is issued, preventing concurrent session abuse.
- **Remember Me** — Supported on the web login form.
- **Secure logout** — Full session invalidation and CSRF token regeneration on both web and API logout paths.

### 3.2 Role-Based Access Control (RBAC)

- Seven roles enforced at the route level via a custom `RoleCheck` middleware.
- Role abilities are embedded into Sanctum tokens at issuance (e.g. `admin:*`, `doctor:*`, `patient:read`).
- API routes use layered middleware: `auth:sanctum` + `role:<allowed_roles>`.
- Web routes use `auth` + `role:<role>` + `secure.session` stacks.
- Unauthorised users are redirected to their own dashboard rather than shown a generic error page.
- The `User` model exposes `hasRole()`, `hasAnyRole()`, `isAdmin()`, and `isMedicalStaff()` helpers for clean programmatic role checks.

### 3.3 REST API (JSON / AJAX)

- Full CRUD API covering: **authentication**, **admin**, **doctors**, **patients**, **appointments**, **medical records**, **nurses**, **receptionists**, **pet owners**, **pets**, **reviews**, and **contact**.
- All responses follow a consistent JSON envelope: `{ success, message, data }`.
- Public health-check endpoint: `GET /api/health` — returns system status and timestamp.
- Rate limiting: `throttle:5,1` on login, `throttle:10,1` on registration, `throttle:60,1` on all other protected endpoints.

### 3.4 Admin Dashboard

- `GET /api/admin/statistics` — returns live system-wide counters (patients, doctors, vets, nurses, receptionists, pet owners) for AJAX dashboard widgets.
- `GET /api/admin/audit-logs` — returns paginated audit log entries with full pagination metadata (`current_page`, `last_page`, `per_page`, `total`).
- Full user CRUD operations via the admin API.
- Admin web routes are additionally protected by the `secure.session` middleware.

### 3.5 Audit Logging

- A dedicated `AuditService` (Service layer) and `AuditLog` model provide an append-only audit trail for all significant actions.
- Each log entry captures: `action_type`, `performed_by`, `performed_by_role`, `affected_table`, `affected_record_id`, `description`, and `ip_address`.
- The `AuditLog` model sets `const UPDATED_AT = null` to enforce immutability — records cannot be modified after creation.
- All writes are wrapped in `try/catch` so a logging failure never disrupts the main request.

### 3.6 Input Validation

- Dedicated `FormRequest` classes (`LoginRequest`, `RegisterRequest`) centralise validation away from controllers.
- Strong password policy enforced via `Password::min(8)->letters()->numbers()->mixedCase()`.
- Phone numbers validated against `/^\+?[\d\s\-]{7,15}$/`.
- Email uniqueness enforced at both validation layer and database level (`UNIQUE` constraint).
- All `FormRequest` classes override `failedValidation()` to return a consistent JSON error envelope.

### 3.7 Database

- **MySQL** (`vitalcare_db`) designed to 3NF with `utf8mb4` charset for full Unicode support.
- Schema covers: `users`, `human_patients`, appointments, medical records, pets, pet owners, audit logs, reviews, and password reset tokens.
- Laravel migrations provided for `users`, `audit_logs`, and `password_reset_tokens`.
- `db:seed` bootstraps default admin, doctor, and patient accounts.
- The `users` table uses an `ENUM` role column with a database index on `role` for fast middleware lookups.

### 3.8 Contact & Reviews

- `POST /api/contact` — handles contact form submissions via `ContactController`.
- `ReviewController` — allows authenticated patients and pet owners to submit reviews (pending admin moderation); retrieves approved reviews for public display, joined with user name and role.

---

## 4. Security Mitigations

All four threats mandated by the assignment brief are addressed through dedicated, testable, and documented code.

### 4.1 SQL Injection Prevention

All database interactions use **Laravel's Eloquent ORM** and the **Query Builder** with parameterised bindings. No raw SQL string concatenation exists anywhere in the codebase. The `ReviewController` demonstrates the Query Builder approach (`DB::table()->join()->where()->select()->get()`), with all user-supplied values bound as parameters.

### 4.2 XSS Prevention

A custom `SanitizeInput` middleware is applied globally to all `POST`, `PUT`, and `PATCH` requests. It recursively strips HTML/PHP tags via `strip_tags()`, converts special characters via `htmlspecialchars(ENT_QUOTES | ENT_HTML5)`, and removes null bytes. Password and token fields are explicitly exempted to prevent corruption of bcrypt hashes. Blade's `{{ }}` syntax provides an additional auto-escape layer on all rendered output.

### 4.3 Session Hijacking Prevention

A custom `SecureSession` middleware is applied to all authenticated web routes. It computes a **session fingerprint** as `hash('sha256', $ip . '|' . $userAgent)` on every request and compares it to the stored value. A mismatch — as would occur when a stolen session cookie is replayed from a different network or browser — triggers immediate logout and session invalidation. The session ID is **regenerated** on first bind and at every login to prevent session fixation. An **idle timeout** of 30 minutes automatically invalidates inactive sessions.

### 4.4 CSRF Prevention

Laravel's built-in `VerifyCsrfToken` middleware is active for all web routes. API routes (`api/*`) are correctly excluded because they are protected by Sanctum Bearer tokens — this exclusion is explicitly documented in the middleware to prevent future accidental removal. CSRF tokens are regenerated after every login and logout.

---

## 5. Technologies Used

| Category | Technology / Tool |
|---|---|
| **Backend** | PHP 8.1+, Laravel 10 |
| **Authentication** | Laravel Sanctum (API tokens), Laravel Auth (web sessions) |
| **Database** | MySQL 8, Laravel Eloquent ORM, Query Builder |
| **Frontend** | Blade templating, HTML5, CSS3 |
| **HTTP Client** | Guzzle 7 |
| **API Standard** | RESTful JSON API |
| **Security** | Custom Middleware (RBAC, XSS, session fingerprinting), CSRF tokens, bcrypt |
| **Testing** | PHPUnit 10, Mockery |
| **Dev Tools** | Laravel Pint, Laravel Sail, Faker, Spatie Ignition |
| **Version Control** | Git / GitHub (`Shaikh_Ayman_2412575` branch) |

---

## 6. System Architecture

The project follows Laravel's **MVC** architecture with an additional **Service layer** for cross-cutting concerns.

```
vitalcare-fixed/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/                    # JSON API controllers
│   │   │   ├── Admin/                  # Blade web controllers — Admin
│   │   │   ├── Doctor/                 # Blade web controllers — Doctor
│   │   │   ├── Nurse/                  # Blade web controllers — Nurse
│   │   │   ├── Patient/                # Blade web controllers — Patient
│   │   │   ├── PetOwner/               # Blade web controllers — Pet Owner
│   │   │   ├── Receptionist/
│   │   │   ├── PublicController.php    # Home, About, Contact pages
│   │   │   └── WebAuthController.php   # Session login/logout/register
│   │   ├── Middleware/
│   │   │   ├── RoleCheck.php           # RBAC enforcement
│   │   │   ├── SecureSession.php       # Session hijacking prevention
│   │   │   ├── SanitizeInput.php       # XSS input sanitisation
│   │   │   └── VerifyCsrfToken.php     # CSRF protection
│   │   ├── Requests/
│   │   │   ├── LoginRequest.php
│   │   │   └── RegisterRequest.php
│   │   └── Kernel.php
│   ├── Models/
│   │   ├── User.php                    # Auth model (Sanctum + RBAC helpers)
│   │   └── AuditLog.php                # Immutable audit trail model
│   └── Services/
│       └── AuditService.php            # Centralised audit logging
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── vitalcare_db.sql
├── resources/views/                    # Blade templates
├── routes/
│   ├── api.php                         # All /api/* routes
│   └── web.php                         # All session-based web routes
├── public/                             # Web root
└── composer.json
```

### Request Lifecycle

```
HTTP Request
    └── public/index.php
        └── Middleware Pipeline
            ├── SanitizeInput        (XSS — all requests)
            ├── VerifyCsrfToken      (web routes only)
            ├── Authenticate
            ├── RoleCheck
            └── SecureSession        (web routes only)
                └── Controller → FormRequest validation
                    └── Eloquent Model / AuditService
                        └── JSON response or Blade view
```

---

## 7. Implementation Details

### 7.1 Dual Authentication Architecture

| Flow | Guard | Mechanism |
|---|---|---|
| Web (Blade dashboards) | `auth` (session) | `Auth::attempt()` + `Session::regenerate()` + role redirect |
| API (REST clients) | `auth:sanctum` | `Auth::attempt()` + `createToken()` + `Authorization: Bearer` header |

### 7.2 Role-Scoped Token Abilities

Sanctum tokens carry role-specific abilities issued via `getRoleAbilities()`:

| Role | Token Abilities |
|---|---|
| Admin | `admin:*`, `doctor:read`, `patient:read`, `appointment:*` |
| Doctor | `doctor:*`, `appointment:read`, `record:*` |
| Patient | `patient:read`, `appointment:read`, `record:read` |

### 7.3 Session Fingerprinting

```php
hash('sha256', $request->ip() . '|' . $request->userAgent())
```

Computed on every authenticated web request and compared against the stored fingerprint. A mismatch triggers immediate session destruction and logout.

### 7.4 Paginated Audit Log API

`GET /api/admin/audit-logs` uses `AuditLog::paginate($perPage)` (max 100 per page) and returns both `data` and a `meta` pagination block, enabling an AJAX-driven admin table without full page reloads.

### 7.5 Admin Statistics Endpoint

`GET /api/admin/statistics` aggregates role-based user counts via Eloquent and eager-loads the five most recent audit entries with their performer's name — powering live AJAX dashboard counters.

---

## 8. Challenges & Solutions

| Challenge | Solution |
|---|---|
| Session fingerprinting locking out mobile users on network switch | Applied `SecureSession` only to protected dashboards; surfaces a descriptive re-login prompt instead of a silent failure |
| Running Sanctum token auth and Laravel session auth in the same app | Separated middleware guards: `auth:sanctum` on API routes, `auth` on web routes; `VerifyCsrfToken` correctly excludes `api/*` |
| HTML entity encoding corrupting bcrypt password hashes | Maintained an `$exemptFields` whitelist in `SanitizeInput` for `password`, `password_confirmation`, `current_password`, and `_token` |
| Laravel's default validation redirect incompatible with JSON API clients | Overrode `failedValidation()` in each `FormRequest` to throw an `HttpResponseException` with a `{ success, message, errors }` JSON payload |
| Audit log write failure crashing the originating request | Wrapped all `AuditService` writes in `try/catch`; failures are silently sent to Laravel's log channel without affecting the primary response |

---

## 9. Testing & Validation

### Manual Testing — Authentication & RBAC

| Scenario | Expected Result | Verified |
|---|---|---|
| Valid login per seeded role | Correct dashboard redirect | ✓ |
| Invalid credentials | `401` JSON response | ✓ |
| Duplicate email registration | `422` validation error | ✓ |
| Patient accessing admin route | `403` redirect to patient dashboard | ✓ |
| XSS payload in form input | Output is entity-encoded in stored value | ✓ |
| Idle session after 30 minutes | Redirect to login | ✓ |

### Validation Layer Testing

- `RegisterRequest` tested with passwords failing `letters()`, `numbers()`, and `mixedCase()` — each returns a correct `422` error.
- Phone number regex tested against valid Mauritian formats (`+230 5xxx xxxx`) and invalid strings.
- `unique:users,email` tested by re-registering an existing email address.

### Database Seeder

`php artisan db:seed` creates three verifiable accounts (admin, doctor, patient) with known credentials for a repeatable test baseline.

### Testing Framework

PHPUnit 10 and Mockery are configured as dev dependencies in `composer.json`, with the `tests/` directory registered under PSR-4 autoloading, ready for future automated test suites.

---

## 10. How to Run — Week 20 (Laravel)

### Prerequisites

- PHP 8.1+
- Composer
- MySQL 8.0+
- Laravel Sail, XAMPP with PHP 8.1, or native PHP

### Setup Steps

```bash
# 1. Extract and enter the project
unzip vitalcare_zip.zip
cd vitalcare-fixed

# 2. Install dependencies
composer install

# 3. Configure environment
cp .env.example .env
php artisan key:generate
```

**4. Update `.env` with your database credentials:**

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vitalcare_db
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

```bash
# 5. Run migrations
php artisan migrate

# Or import the full schema directly:
mysql -u your_db_user -p vitalcare_db < vitalcare_db.sql

# 6. Seed default users
php artisan db:seed

# 7. Start the server
php artisan serve
```

Application accessible at: `http://127.0.0.1:8000`

### Seeded Accounts

| Role | Email | Password |
|---|---|---|
| Admin | admin@vitalcare.com | Admin@1234 |
| Doctor | doctor@vitalcare.com | Doctor@1234 |
| Patient | patient@vitalcare.com | Patient@1234 |

### Key URLs

| URL | Description |
|---|---|
| `GET /` | Public home page |
| `GET /login` | Web login |
| `GET /register` | Patient / pet owner registration |
| `GET /admin/dashboard` | Admin dashboard (role-protected) |
| `GET /doctor/dashboard` | Doctor dashboard |
| `GET /patient/dashboard` | Patient dashboard |
| `POST /api/auth/login` | API login → returns Bearer token |
| `GET /api/admin/statistics` | AJAX dashboard statistics |
| `GET /api/health` | Public health check |

---

## 11. Conclusion

This submission delivers a production-structured Laravel 10 application addressing all Week 20 requirements: a complete REST API consumed via AJAX, JSON responses with structured schemas enforced through Laravel's validation layer, full MVC architecture, and a Blade-rendered web interface.

Each of the four mandated security mitigations — SQL Injection, Session Hijacking, XSS, and CSRF — is addressed through dedicated, documented, and testable code: custom middleware, FormRequest validation, Sanctum token scoping, and session fingerprinting.

### Key Learning Outcomes

- Implementing **dual authentication** (session + token) in a single Laravel application.
- Understanding **middleware pipelines** and their role in layered security.
- Applying the **Service layer pattern** for audit logging that never interferes with the primary request.
- Designing **role-scoped token abilities** with Sanctum for least-privilege API access.
- Writing **FormRequest** classes that produce consistent, client-friendly JSON validation errors.
- Building a **RESTful API** with correct HTTP status codes, consistent response envelopes, and rate limiting.

---

---

# PART B — Week 10 Individual Contribution

---

## 12. Week 10 — Project Overview

**VitalCare** is a full-stack web application developed as the group project for ICT2213Y(3). It simulates a healthcare management system supporting both **human patient care** and **pet care** under one platform. Patients and pet owners can register, log in, book appointments, and view medical records; administrators can manage staff, approve appointments, and monitor system activity.

The **Week 10 milestone** required the team to demonstrate:

- A working HTML/PHP/jQuery front end connected to a live MySQL database
- Core features: registration, login, appointment booking, and medical records
- Evidence of security practices: SQL injection prevention, session management, XSS protection, and CSRF mitigation
- A PowerPoint covering system overview, functional specs, and individual contributions

---

## 13. My Contributions

### 13.1 Assigned Role: Database Developer

As the **Database Developer** for the team (per the Week 10 task distribution document), my formally assigned responsibilities were:

- Design and implement the full MySQL relational database schema
- Define all tables, data types, constraints, and foreign key relationships
- Export the complete `database.sql` file
- Produce an ERD diagram
- Insert sample/seed data for demonstration
- Contribute to the group PowerPoint (all slides except 14–15)

> Slides 14 and 15 were completed by teammate Sehun as part of his documentation lead role.

### 13.2 Additional Work (Beyond Assigned Tasks)

After completing my assigned responsibilities, I independently undertook the **full coding implementation** of the VitalCare Week 10 application to ensure the team had a functional and demonstrable system. This included:

- All PHP backend logic (authentication, CRUD, AJAX endpoints, session handling)
- All frontend pages (HTML, CSS, JavaScript)
- Security implementation across the entire codebase
- A complete, modular project structure with shared includes and role-based access guards

The full implementation is provided in `vitalcare_week10.zip`.

---

## 14. Database Design

### 14.1 Schema Overview

The database (`vitalcare_db`) was designed from scratch for a dual-service healthcare platform and structured around two parallel domains.

**Human Healthcare Tables:**

| Table | Purpose |
|---|---|
| `human_patients` | Patient registration data including hashed passwords |
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
| `contact_messages` | Visitor enquiries from the contact form |
| `nurse_checklist` | Task management for nurses across both domains |
| `audit_logs` | Actions logged by role, actor ID, IP address, and timestamp |

### 14.2 Key Design Decisions

- **UUID primary keys (`CHAR(36)`)** — Prevents sequential ID enumeration attacks, critical for a healthcare system with sensitive personal data.
- **Referential integrity via foreign keys** — `ON DELETE CASCADE` where child records should be removed with the parent; `ON DELETE SET NULL` where historical records must be preserved (e.g. medical records after a doctor is removed).
- **Separate human and pet domains** — Independent table sets for each domain avoid complex polymorphic joins and keep the schema maintainable.
- **Audit logging** — Records who performed what action, on which object, and from which IP — supporting accountability and post-incident investigation.
- **Schema-level validation** — `CHECK` constraints, `ENUM` types for status/gender fields, and `UNIQUE` on email columns enforce data integrity at the database layer.
- **Password storage** — The `password_hash` column stores bcrypt output from PHP's `password_hash()`. Plain-text passwords are never stored.

### 14.3 Entity Relationships

- A `human_patient` can have many `human_appointments`, `human_medical_records`, and `human_reviews`
- A `human_doctor` can be linked to many appointments, records, and reviews
- A `pet_owner` can own many `pets`; each `pet` can have many `pet_appointments` and `pet_medical_records`
- A `nurse_checklist` entry links to either a patient or a pet via a role discriminator field

---

## 15. Coding Implementation

### 15.1 Directory Structure

```
vitalcare/
├── index.php                   ← Public homepage
├── login.php                   ← Patient login
├── register.php                ← Patient registration
├── logout.php / admin-logout.php
├── book-appointment.php        ← AJAX-driven appointment booking
├── services.php                ← Services listing
├── contact.php                 ← Contact form (saves to DB)
│
├── includes/
│   ├── db.php                  ← Database connection & utility functions
│   ├── auth.php                ← Session management & CSRF helpers
│   ├── header.php              ← Shared page header
│   └── footer.php              ← Shared page footer
│
├── css/
│   └── style.css
│
├── js/
│   └── main.js                 ← Client-side validation & AJAX
│
├── api/
│   ├── book-appointment.php    ← AJAX endpoint: save appointment
│   └── get-doctors.php         ← AJAX endpoint: fetch doctors by speciality
│
├── patient/
│   ├── dashboard.php
│   ├── appointments.php
│   ├── medical-records.php
│   └── profile.php
│
├── admin/
│   ├── dashboard.php
│   ├── appointments.php
│   ├── staff.php
│   ├── add-doctor.php
│   └── add-nurse.php
│
└── vitalcare_db.sql            ← Full schema + seed data
```

### 15.2 Features Implemented

**Patient Features:**
- Self-registration with server-side validation and bcrypt password hashing
- Secure login with session regeneration on authentication
- Dashboard showing appointment history and medical records from the live database
- AJAX-powered appointment booking — speciality selection dynamically populates available doctors without a page reload
- Appointment cancellation with real-time status update in the database
- Profile editing

**Admin Features:**
- Separate admin login portal with role-based session enforcement
- Dashboard with live statistics (patient count, doctor count, pending appointments)
- Full appointment management — view, approve, and update appointment status
- Staff management — add doctors and nurses, view all staff, remove staff members

**General Features:**
- Contact form saving submissions to `contact_messages`
- Services listing page
- Reusable header and footer components across all pages

### 15.3 Security Implementation

| Threat | Mitigation |
|---|---|
| **SQL Injection** | All queries use prepared statements with `$stmt->bind_param()` — no raw user input is concatenated into SQL strings |
| **XSS** | All HTML output passes through `htmlspecialchars()` via the `h()` helper in `db.php` |
| **CSRF** | Every POST form includes a hidden `csrf_token`; server validates it with `hash_equals()` before processing |
| **Session Hijacking** | `session_regenerate_id(true)` called on login; sessions use `cookie_httponly` and `cookie_samesite: Strict` |
| **Password Security** | Passwords hashed with `password_hash()` (bcrypt) and verified with `password_verify()` |
| **RBAC** | `requirePatient()` and `requireAdmin()` guard functions in `auth.php` enforce role access before any page logic runs |

### 15.4 Technologies Used

| Layer | Technology |
|---|---|
| Frontend | HTML5, CSS3, JavaScript (ES6) |
| Backend | PHP 8.x |
| Database | MySQL (MariaDB via XAMPP) |
| AJAX | Fetch API (JSON responses) |
| Security | Prepared statements, bcrypt, CSRF tokens, XSS escaping, session hardening |
| Architecture | Modular includes (MVC-inspired) |

### 15.5 Challenges & Solutions

**AJAX appointment booking with dynamic doctor population**

The doctor dropdown needed to update automatically based on the selected speciality without a full page reload. A dedicated API endpoint (`api/get-doctors.php`) accepts a speciality parameter via `fetch()` and returns a JSON array of matching doctors. `main.js` then rebuilds the dropdown dynamically from the response.

**Consistent CSRF protection across all forms**

Rather than duplicating CSRF logic on every page, the `csrfToken()` and `verifyCsrf()` functions were centralised in `includes/auth.php` and included on every page — generating and validating tokens uniformly throughout the application.

**Maintaining role separation across a dual-domain system**

The `requirePatient()` and `requireAdmin()` guard functions in `auth.php` redirect unauthorised users before any sensitive page logic runs, providing lightweight but effective access control across both the human and pet domains.

---

## 16. PowerPoint Summary

The Week 10 group presentation was prepared entirely by me, with the exception of slides 14 and 15 (contributed by teammate Sehun).

| Slides | Content |
|---|---|
| Project Introduction | VitalCare concept, business problem, and target users |
| System Architecture | Three-tier structure and component interaction |
| Functional Requirements | Registration, login, appointment booking, medical records, admin controls |
| Database Design | ERD walkthrough, table structure, and key design decisions |
| Security Features | Techniques mapped directly to the project brief threats |
| Individual Contributions | Breakdown of each team member's assigned tasks |
| Demonstration Outline | Checklist of features shown live during the presentation |

> Slides 14–15 (use case descriptions and functional specifications) — prepared by Sehun.

---

## 17. How to Run — Week 10 (XAMPP)

### Prerequisites

- [XAMPP](https://www.apachefriends.org/) with PHP 8.x, Apache, and MySQL/MariaDB

### Step 1 — Start XAMPP

Open the XAMPP Control Panel and start both **Apache** and **MySQL**.

### Step 2 — Copy Project Files

Extract `vitalcare_week10.zip` and copy the `vitalcare/` folder to:
```
C:\xampp\htdocs\vitalcare\
```

### Step 3 — Import the Database

1. Navigate to `http://localhost/phpmyadmin`
2. Click **New** and create a database named `vitalcare_db`
3. Select it, click **Import**, choose `vitalcare/vitalcare_db.sql`, and click **Go**

### Step 4 — Verify Database Configuration

Open `includes/db.php` and confirm:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');    // default XAMPP username
define('DB_PASS', '');        // default XAMPP — no password
define('DB_NAME', 'vitalcare_db');
```

Update `DB_PASS` if you have set a MySQL root password.

### Step 5 — Launch the Application

Open your browser and go to: `http://localhost/vitalcare/`

### Default Login Credentials

| Account | URL | Credentials |
|---|---|---|
| Admin | `/vitalcare/admin-login.php` | Email: `admin` / Password: `password` |
| Patient | `/vitalcare/register.php` | Register a new account |

**Sample Doctors (seeded):**
Dr. Alice Martin (General Medicine), Dr. Robert Chen (Cardiology), Dr. Priya Sharma (Dermatology), Dr. James Okonkwo (Pediatrics)

### Troubleshooting

| Problem | Solution |
|---|---|
| Blank page | Add `ini_set('display_errors', 1);` to the top of `db.php` |
| Database connection failed | Verify `DB_USER` and `DB_PASS` in `includes/db.php` |
| 404 errors | Confirm the folder is at `C:\xampp\htdocs\vitalcare\` |
| CSRF validation error | Clear browser cookies and retry |
| Appointment not saving | Ensure you are logged in as a patient before booking |

---

## 18. Reflection

### What I Learned

Working on VitalCare across both database design and the full coding implementation gave me a significantly broader understanding of how all layers of a web application connect and depend on each other.

On the **database side**, designing a schema for a multi-role, dual-domain system required careful thought about data integrity, referential constraints, and the security implications of schema decisions, such as using UUID keys to prevent sequential ID enumeration, and choosing `ON DELETE CASCADE` vs `ON DELETE SET NULL` deliberately rather than arbitrarily.

On the **security side**, implementing CSRF protection, prepared statements, and XSS escaping as features woven into a real application rather than isolated exercises. Therefore, this made it clear why these techniques matter in practice. Tracing how a SQL injection attempt would fail against a prepared statement, because user input is bound as a parameter rather than spliced into the query string, gave me a concrete mental model of why parameterised queries are the correct approach.

### Skills Developed

- Designing normalised relational schemas for complex, multi-entity systems
- Writing secure PHP using prepared statements, session hardening, and CSRF tokens
- Building AJAX-driven interactions with the Fetch API and JSON endpoints
- Structuring a PHP project modularly with shared includes and role-based access guards
- Understanding how application logic, session state, and database queries interact at runtime

### How This Task Improved My Understanding

Going beyond my assigned role and completing the full implementation reinforced the value of end-to-end ownership: understanding the database schema made it significantly easier to write correct, efficient PHP queries, and thinking about security at the schema level (UUID keys, audit logs) made the application-level security measures feel like a natural continuation rather than an afterthought.

