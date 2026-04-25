# VitalCare — Week 20 (Fixed)

## 🐛 Bugs Fixed

| # | File | Problem | Fix Applied |
|---|------|---------|-------------|
| 1 | `routes/api.php` | **MISSING** — Root cause of "The route api/auth/login could not be found" | Created complete API routes file |
| 2 | `routes/web.php` | **MISSING** — All web routes absent | Created complete web routes with correct named routes |
| 3 | `database/migrations/` | **MISSING** — No migrations existed | Created migrations for users, audit_logs, password_reset_tokens |
| 4 | `audit_logs` migration | Column mismatch with AuditLog model $fillable | Fixed to match model exactly |
| 5 | `DatabaseSeeder.php` | **MISSING** — No seed data | Created seeder with admin/doctor/patient accounts |
| 6 | `.env` `APP_KEY` | Placeholder value (not a real key) | Cleared so php artisan key:generate works |

---

## 🚀 Setup Instructions (XAMPP)

### Step 1 — Copy project
Place folder at: `C:\xampp\htdocs\vitalcare-ayman\`

### Step 2 — Create database
Open phpMyAdmin → create database named: `vitalcare_db`

### Step 3 — Install dependencies
```bash
composer install
```

### Step 4 — Generate app key
```bash
php artisan key:generate
```

### Step 5 — Run migrations and seed
```bash
php artisan migrate
php artisan db:seed
```

### Step 6 — Serve
```bash
php artisan serve
```

---

## Test Accounts (after seeding)

| Role    | Email                  | Password    |
|---------|------------------------|-------------|
| Admin   | admin@vitalcare.com    | Admin@1234  |
| Doctor  | doctor@vitalcare.com   | Doctor@1234 |
| Patient | patient@vitalcare.com  | Patient@1234|

---

## API Endpoints

### Public (no token)
- POST `/api/auth/login`    — returns Bearer token
- POST `/api/auth/register` — creates account

### Protected (add header: `Authorization: Bearer YOUR_TOKEN`)
- POST `/api/auth/logout`   — revokes token
- GET  `/api/auth/me`       — returns profile

### Example Login Body
```json
{
    "email": "admin@vitalcare.com",
    "password": "Admin@1234"
}
```
