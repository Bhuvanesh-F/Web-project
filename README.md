# VitalCare — Individual Contribution README
### Ayman Muhammad Shaikh · Student ID: 2412575
### ICT2213Y — Web Technologies & Security | University of Mauritius

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [My Individual Contribution](#2-my-individual-contribution)
3. [Technical Implementation](#3-technical-implementation)
4. [Features Implemented](#4-features-implemented)
5. [Setup & Installation](#5-setup--installation)
6. [Challenges & Solutions](#6-challenges--solutions)
7. [Testing & Validation](#7-testing--validation)
8. [Screenshots / Demo](#8-screenshots--demo)
9. [Conclusion](#9-conclusion)

---

## 1. Project Overview

**VitalCare** is a fully-functional web application designed to streamline the management of a combined human and pet healthcare clinic. The system serves six distinct user roles like Admin, Human Doctor, Vet (Pet Doctor), Nurse, Receptionist and Patient/Pet Owner, Moreover, each with their own secure, role-isolated dashboard and feature set.

The Week 20 deliverable represents a complete architectural upgrade from the Week 10 PHP/MySQL prototype to a production-grade **Laravel MVC** application. This migration introduces RESTful API design, AJAX-powered dynamic UIs, Eloquent ORM, Blade templating, JSON Schema validation, and a hardened security layer which are all in direct alignment with the ICT2213Y assignment brief.

**Technology Stack (Project-Wide):** Laravel 10, PHP 8.1, MySQL, Laravel Sanctum, Blade, Bootstrap 5, jQuery/AJAX, Ajv.js, Git/GitHub.

---

## 2. My Individual Contribution

**Role:** Full Stack — Security Lead, Laravel Architect, Modularisation & Advanced Techniques

My responsibility covered the broadest cross-cutting concerns of the project: setting up the entire Laravel application skeleton that the whole team builds upon, implementing the authentication system every other member depends on, and owning all three areas explicitly cited by the assignment brief for **additional/bonus marks**:

| Assignment Bonus Criterion | My Deliverable |
|---|---|
| Project Modularisation | Module-per-role folder structure with zero cross-module leakage |
| Naming Conventions | `NAMING_CONVENTIONS.md` — enforced across the entire codebase |
| Self-Learned Technology | Bootstrap 5 integrated as the primary CSS framework |

### Scope Summary

- **Laravel Project Setup** — scaffolded the entire application structure, `.env` configuration, service providers, and master Blade layout
- **Laravel Sanctum Authentication API** — all four auth endpoints (`login`, `register`, `logout`, `me`) with role-based token abilities
- **Security Middleware** — three custom middleware classes: `RoleCheck`, `SanitizeInput`, `SecureSession`
- **Route Architecture** — all web and API routes, grouped by role with middleware stacks
- **Modularisation** — module-per-role controller/route/view separation documented in `MODULE_STRUCTURE.md`
- **Naming Conventions** — project-wide standards documented in `NAMING_CONVENTIONS.md`
- **Bootstrap 5** — self-learned and integrated as the primary responsive CSS framework

---

## 3. Technical Implementation

### Technologies & Tools

| Technology | Purpose |
|---|---|
| **Laravel 10** | MVC framework, routing, middleware, Blade |
| **Laravel Sanctum** | API token-based authentication |
| **PHP 8.1** | Match expressions, named arguments, typed properties |
| **MySQL** | Relational database (configured via `.env`) |
| **Bootstrap 5** (self-learned) | Responsive UI framework — navbar, grid, cards, modals |
| **Git / GitHub** | Version control, branch `feature/auth-laravel` |

### Authentication Flow

```
POST /api/auth/login
  └─> LoginRequest (validates email + password format)
  └─> Auth::attempt() — bcrypt comparison
  └─> Previous tokens revoked (single-session enforcement)
  └─> Sanctum token issued with role-based abilities
  └─> JSON response: { token, token_type, user, dashboard_url }

POST /api/auth/register
  └─> RegisterRequest (validates name, email, password, role)
  └─> User::create() — password hashed via Hash::make()
  └─> Token issued immediately
  └─> JSON response: { token, user }

POST /api/auth/logout  [auth:sanctum]
  └─> currentAccessToken()->delete()
  └─> JSON response: { success: true }

GET /api/auth/me  [auth:sanctum]
  └─> Returns authenticated user profile (no password hash exposed)
```

### Role-Based Token Abilities

Each user role receives a scoped set of token abilities at login, preventing privilege escalation at the API layer:

```php
'admin'        => ['admin:*', 'doctor:read', 'patient:read', 'appointment:*'],
'doctor'       => ['doctor:*', 'patient:read', 'appointment:read', 'record:*'],
'vet'          => ['vet:*', 'pet:read', 'pet-appointment:read', 'pet-record:*'],
'nurse'        => ['nurse:*', 'patient:read', 'appointment:read'],
'receptionist' => ['receptionist:*', 'appointment:*', 'patient:read'],
'pet_owner'    => ['pet-owner:*', 'pet:*', 'pet-appointment:*'],
'patient'      => ['patient:read', 'appointment:read', 'record:read'],
```

### Middleware Stack

Every authenticated web route passes through three middleware layers applied in `Kernel.php`:

```
auth           — Laravel's built-in authentication check
role:{role}    — RoleCheck: verifies user's role matches the route's requirement
secure.session — SecureSession: fingerprint binding + idle timeout
```

### Key Files

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── AuthController.php       ← All 4 auth endpoints
│   ├── Middleware/
│   │   ├── RoleCheck.php                ← RBAC enforcement
│   │   ├── SanitizeInput.php            ← XSS stripping on all POST/PUT/PATCH
│   │   └── SecureSession.php           ← Session hijacking prevention
│   ├── Kernel.php                       ← Middleware registration
│   └── Requests/
│       ├── LoginRequest.php             ← Validated login input
│       └── RegisterRequest.php          ← Validated registration input
routes/
├── web.php                              ← Role-grouped Blade routes
└── api.php                              ← REST API routes with auth guards
resources/views/
└── layouts/
    └── app.blade.php                    ← Master Blade layout (Bootstrap 5)
config/
└── sanctum.php                          ← Token expiry, guard configuration
public/
└── robots.txt                           ← Prevents crawler indexing of sensitive routes
NAMING_CONVENTIONS.md
MODULE_STRUCTURE.md
```

---

## 4. Features Implemented

### A. Laravel Project Scaffolding

Initialised the full Laravel project structure used by all six team members. Configured `.env` for MySQL connectivity, registered all service providers, and defined the application-level middleware pipeline in `Kernel.php`. This foundational setup was a prerequisite for every other team member's work.

### B. Sanctum API Authentication (`AuthController.php`)

Implemented all four authentication endpoints as a clean, documented RESTful API:

- **`POST /api/auth/login`** — validates credentials via `LoginRequest`, enforces single active session by revoking prior tokens, issues a scoped Sanctum token with role-based abilities, and logs the event.
- **`POST /api/auth/register`** — accepts new `patient` or `pet_owner` registrations via `RegisterRequest`, hashes the password with `Hash::make()`, and returns a token on creation.
- **`POST /api/auth/logout`** — performs targeted token revocation (`currentAccessToken()->delete()`) rather than revoking all user sessions.
- **`GET /api/auth/me`** — returns the authenticated user's profile, explicitly excluding the password hash from the response.

### C. Role-Based Access Control (`RoleCheck.php`)

Designed and implemented the `RoleCheck` middleware, which enforces role-based access on every authenticated route. The middleware accepts variadic role arguments, enabling flexible multi-role permissions on a single route. On failure, JSON consumers receive a structured `403` error while Blade consumers are redirected to their own dashboard — a UX-aware approach that avoids dead-end error pages.

### D. XSS Input Sanitisation (`SanitizeInput.php`)

Built a defence-in-depth sanitisation layer that runs on all `POST`, `PUT`, and `PATCH` requests before they reach any controller or validation layer. The middleware:

- Strips all HTML/PHP tags with `strip_tags()`
- Converts special characters to HTML entities with `htmlspecialchars(ENT_QUOTES | ENT_HTML5)`
- Removes null bytes (a secondary injection vector)
- Recursively sanitises nested arrays
- Exempts sensitive fields (`password`, `_token`) that must not be mutated

### E. Session Hijacking Prevention (`SecureSession.php`)

Implemented a custom `SecureSession` middleware that mitigates session hijacking through three mechanisms:

1. **Session Fingerprinting** — binds the session to a SHA-256 hash of the user's IP address and `User-Agent`. Any mismatch (indicating a stolen session token) triggers immediate logout and session invalidation.
2. **Session Fixation Prevention** — regenerates the session ID on the first authenticated request.
3. **Idle Timeout Enforcement** — invalidates sessions idle for more than 30 minutes.

### F. Route Architecture (`web.php` & `api.php`)

Designed and implemented the complete routing layer for the application:

- **`web.php`**: Seven role-isolated route groups (Admin, Doctor, Nurse, Patient, Pet Owner, Receptionist, Public), each with a `prefix`, `middleware` stack (`auth` + `role:{role}` + `secure.session`), and named route prefix.
- **`api.php`**: RESTful API routes separated into public (auth) and protected (Sanctum-guarded) groups, with rate limiting (`throttle:5,1` on login/register, `throttle:60,1` on general API).

### G. Master Blade Layout (`layouts/app.blade.php`)

Created the master Blade layout consumed via `@extends('layouts.app')` by all team members' views. Integrated Bootstrap 5 grid, navbar with `@auth`/`@guest` conditional rendering, and role-aware navigation highlighting.

### H. Project Modularisation *(Bonus Marks)*

Enforced a module-per-role folder structure across the entire project, ensuring zero cross-module logic leakage. Each role has its own Controller namespace, route group, and Blade view directory. Documented in `MODULE_STRUCTURE.md` with a full folder tree and module diagram for the presentation.

### I. Naming Conventions *(Bonus Marks)*

Defined and documented project-wide naming standards in `NAMING_CONVENTIONS.md`, adopted by all team members:

| Artefact | Convention | Example |
|---|---|---|
| Controllers | PascalCase | `AuthController.php` |
| Routes/URLs | kebab-case | `/api/medical-records` |
| Blade views | snake_case | `doctor_dashboard.blade.php` |
| DB columns | snake_case | `appointment_date` |
| JS files | kebab-case filenames | `ajax-doctor.js` |
| JS functions | camelCase | `loadAppointments()` |

### J. Bootstrap 5 Integration *(Self-Learned, Bonus Marks)*

Self-studied and integrated Bootstrap 5 as the primary CSS framework, replacing all custom CSS components. Applied the Bootstrap grid system for responsive layouts, and replaced custom components with Bootstrap equivalents (navbar, cards, modals, forms, badges). This was explicitly flagged as a self-learned technology in the presentation, as cited in the assignment brief.

### K. Security Hardening (Additional)

- `robots.txt` published to `public/` to prevent crawlers from indexing sensitive authenticated routes
- `NOINDEX` meta tags applied via Blade to all non-public pages
- Brute-force protection via route-level `throttle` middleware on all auth endpoints
- Failed login attempts logged with IP and User-Agent for security auditing

---

## 5. Setup & Installation

### Prerequisites

- PHP 8.1+
- Composer
- MySQL 8.0+
- Node.js 18+ (for frontend assets)
- Git

### Step-by-Step Installation

**1. Clone the repository**
```bash
git clone https://github.com/your-org/vitalcare.git
cd vitalcare
git checkout feature/auth-laravel   # My branch
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

Edit `.env` with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vitalcare
DB_USERNAME=root
DB_PASSWORD=your_password

SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
SESSION_LIFETIME=30
```

**4. Run database migrations and seed test data**
```bash
php artisan migrate
php artisan db:seed          # Seeds staff, patients, and demo accounts
```

**5. Install and build frontend assets (Bootstrap 5)**
```bash
npm install
npm run build
```

**6. Publish Sanctum configuration**
```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

**7. Start the development server**
```bash
php artisan serve
# Application available at: http://127.0.0.1:8000
```

### Demo Credentials (Seeded)

| Role | Email | Password |
|---|---|---|
| Admin | admin@vitalcare.mu | password |
| Doctor | doctor@vitalcare.mu | password |
| Nurse | nurse@vitalcare.mu | password |
| Receptionist | receptionist@vitalcare.mu | password |
| Patient | patient@vitalcare.mu | password |
| Pet Owner | petowner@vitalcare.mu | password |

### Testing Auth API with cURL

```bash
# Login
curl -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"admin@vitalcare.mu","password":"password"}'

# Get profile (replace TOKEN with the token received above)
curl -X GET http://127.0.0.1:8000/api/auth/me \
  -H "Authorization: Bearer TOKEN" \
  -H "Accept: application/json"

# Logout
curl -X POST http://127.0.0.1:8000/api/auth/logout \
  -H "Authorization: Bearer TOKEN" \
  -H "Accept: application/json"
```

---

## 6. Challenges & Solutions

### Challenge 1: Single-Session Enforcement Without Locking Users Out

**Problem:** Laravel Sanctum issues multiple tokens per user by default, meaning a user could hold simultaneous sessions across devices — a security risk for a healthcare system.

**Solution:** In `AuthController::login()`, all previous tokens are revoked with `$user->tokens()->delete()` before a new token is issued. This enforces a single active session per user while remaining transparent to the user experience.

---

### Challenge 2: Sanitising Input Without Breaking Password Hashing

**Problem:** If `SanitizeInput` middleware processed the `password` field through `htmlspecialchars()`, characters like `>`, `<`, and `&` would be encoded as HTML entities before reaching the bcrypt hasher — causing login failures for passwords containing those characters.

**Solution:** Introduced an `$exemptFields` array in `SanitizeInput` that explicitly skips `password`, `password_confirmation`, `current_password`, and `_token`. This ensures bcrypt always receives the raw password string.

---

### Challenge 3: Session Fingerprinting and Middleware Ordering

**Problem:** Binding the session to an IP/User-Agent fingerprint needs to happen after authentication is confirmed but before the controller runs. Middleware ordering in `Kernel.php` initially caused `SecureSession` to fire before `Auth` populated, resulting in `Auth::user()` returning `null`.

**Solution:** Registered `SecureSession` as a route-level middleware (not global) applied only to authenticated route groups, ensuring it runs after Laravel's `auth` middleware has already resolved the user. The middleware guard with `Auth::check()` provides a secondary safeguard.

---

### Challenge 4: Bootstrap 5 vs. Custom CSS Conflicts

**Problem:** Migrating from custom CSS to Bootstrap 5 introduced specificity conflicts where existing custom classes overrode Bootstrap's utility classes unpredictably.

**Solution:** Adopted a clean-break approach — removed conflicting custom CSS rules and rebuilt components using Bootstrap's component classes exclusively. Where custom styling was genuinely needed (brand colours, clinic-specific cards), scoped CSS custom properties were used alongside Bootstrap rather than against it.

---

## 7. Testing & Validation

### Manual API Testing

All four auth endpoints were tested using both cURL and Postman across the following scenarios:

| Scenario | Expected | Result |
|---|---|---|
| Valid credentials | 200 + token | ✅ Pass |
| Invalid password | 401 + error message | ✅ Pass |
| Unregistered email | 401 + error message | ✅ Pass |
| Register with duplicate email | 422 + validation error | ✅ Pass |
| Access protected route without token | 401 Unauthenticated | ✅ Pass |
| Access admin route as patient | 403 Forbidden | ✅ Pass |
| Login 6+ times in 1 minute | 429 Too Many Requests | ✅ Pass |
| Logout + reuse old token | 401 Unauthenticated | ✅ Pass |

### Middleware Edge Cases

- **`RoleCheck`**: Verified that a doctor token cannot access `/api/admin/*` endpoints; tested all six role combinations.
- **`SanitizeInput`**: Submitted XSS payloads (`<script>alert(1)</script>`, `"><img src=x onerror=alert(1)>`) and confirmed they were stripped before reaching controllers.
- **`SecureSession`**: Simulated session hijacking by copying the session cookie and replaying it from a different `User-Agent` header — confirmed the session was invalidated and the user was logged out.

### Form Request Validation

`LoginRequest` and `RegisterRequest` were tested with missing fields, malformed emails, weak passwords, and invalid role values — all returning structured `422` JSON validation errors with field-level messages.

### GitHub Commit Verification

Individual contributions are verifiable on the `Shaikh_Ayman_2412575` branch. Commit history reflects incremental development of each deliverable with descriptive commit messages.

---

## 8. Screenshots / Demo

> **Note:** Screenshots are stored in `/docs/screenshots/` in the repository. The following captures should be included for the Week 20 presentation:

| Screenshot | File |
|---|---|
| Successful login response in Postman (JSON token) | `docs/screenshots/auth-login-response.png` |
| 403 Forbidden response (role mismatch) | `docs/screenshots/rbac-403-response.png` |
| XSS payload stripped by SanitizeInput | `docs/screenshots/xss-sanitized-input.png` |
| Session invalidation on fingerprint mismatch | `docs/screenshots/session-hijack-blocked.png` |
| DevTools Network tab — login AJAX call | `docs/screenshots/network-tab-login.png` |
| Bootstrap 5 master layout (responsive navbar) | `docs/screenshots/bootstrap-layout.png` |
| GitHub branch commit history | `docs/screenshots/github-commits-ayman.png` |
| Role-based dashboard redirect demo | `docs/screenshots/role-redirect-demo.png` |

**To reproduce the live demo during the Week 20 presentation:**
1. Open DevTools → Network tab
2. Login as `doctor@vitalcare.mu` — show the JSON token response
3. Attempt to `GET /api/admin/users` with the doctor token — show the 403 response
4. Login as `admin@vitalcare.mu` — show access granted
5. Demonstrate XSS input being sanitised in the Network tab request payload

---

## 9. Conclusion

My individual contribution to VitalCare Week 20 centred on three interconnected responsibilities: **security**, **architecture**, and **standards**.

On the security side, I designed and implemented a layered defence strategy — Sanctum token authentication with role-scoped abilities, custom middleware for RBAC, XSS prevention, and session hijacking mitigation — ensuring that VitalCare handles sensitive patient and medical data responsibly.

On the architecture side, setting up the Laravel scaffolding, route structure, and master layout gave the entire team a consistent, production-ready foundation to build on. The modular folder structure and documented naming conventions reduced integration friction significantly across all six branches.

On the self-learning side, integrating Bootstrap 5 beyond the course syllabus demonstrates the ability to independently research and apply new technologies — a key evaluation criterion in the assignment brief.

**Key learning outcomes from this project:**

- Deepened practical understanding of **token-based authentication** and the security tradeoffs of stateless vs. stateful sessions
- Gained hands-on experience with **Laravel Sanctum's token ability system** as a lightweight authorisation mechanism
- Developed an appreciation for **defence-in-depth** — how multiple security layers (middleware, Eloquent parameterisation, Blade escaping, CSRF, rate limiting) work together rather than relying on any single mechanism
- Strengthened skills in **technical documentation** through `NAMING_CONVENTIONS.md` and `MODULE_STRUCTURE.md`, which benefited the whole team

---

