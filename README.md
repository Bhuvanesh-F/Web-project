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
