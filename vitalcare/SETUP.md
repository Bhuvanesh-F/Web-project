# VitalCare – Setup Guide (XAMPP)
## ICT2213 Web Technologies & Security – Week 10 Project

---

## 📁 Folder Structure

```
vitalcare/
├── index.php                  ← Homepage
├── login.php                  ← Patient login
├── register.php               ← Patient registration
├── logout.php
├── admin-login.php            ← Admin login
├── admin-logout.php
├── book-appointment.php       ← Appointment booking (AJAX)
├── services.php
├── contact.php                ← Contact form (saves to DB)
│
├── includes/
│   ├── db.php                 ← Database connection
│   ├── auth.php               ← Session & CSRF helpers
│   ├── header.php             ← Reusable header
│   └── footer.php             ← Reusable footer
│
├── css/
│   └── style.css              ← External stylesheet
│
├── js/
│   └── main.js                ← External JavaScript (AJAX, validation)
│
├── api/
│   ├── book-appointment.php   ← AJAX: save appointment to DB
│   └── get-doctors.php        ← AJAX: fetch doctors by speciality
│
├── patient/
│   ├── dashboard.php          ← Patient dashboard (DB data)
│   ├── appointments.php       ← View/cancel appointments
│   ├── medical-records.php    ← View medical records
│   └── profile.php            ← Edit profile
│
├── admin/
│   ├── dashboard.php          ← Admin dashboard (stats from DB)
│   ├── appointments.php       ← Manage all appointments
│   ├── staff.php              ← View/remove doctors & nurses
│   ├── add-doctor.php         ← Add doctor (saves to DB)
│   └── add-nurse.php          ← Add nurse (saves to DB)
│
└── vitalcare_db.sql           ← Full database schema + seed data
```

---

## ⚙️ Step-by-Step XAMPP Setup

### Step 1 — Start XAMPP
1. Open XAMPP Control Panel
2. Start **Apache** and **MySQL**

### Step 2 — Copy Project Files
1. Copy the entire `vitalcare/` folder to:
   ```
   C:\xampp\htdocs\vitalcare\
   ```

### Step 3 — Import the Database
1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Click **"New"** in the left sidebar
3. Create a database named: `vitalcare_db`  *(or let the SQL file create it)*
4. Click **"Import"** at the top
5. Choose the file: `vitalcare/vitalcare_db.sql`
6. Click **"Go"**

### Step 4 — Verify Database Config
Open `includes/db.php` and confirm the settings match your XAMPP:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');    // default XAMPP username
define('DB_PASS', '');        // default XAMPP has no password
define('DB_NAME', 'vitalcare_db');
```
If you have a password on MySQL, update `DB_PASS` accordingly.

### Step 5 — Run the Application
Open your browser and go to:
```
http://localhost/vitalcare/
```

---

## 🔑 Default Login Credentials

### Admin Account
| Field    | Value               |
|----------|---------------------|
| URL      | http://localhost/vitalcare/admin-login.php |
| Username | `admin`             |
| Password | `password`          |

> ⚠️ The seeded password hash in the SQL uses `password` as the value. For demo, use this credential and change it after.

### Patient Registration
Go to `http://localhost/vitalcare/register.php` and create a new account.

### Sample Doctors (added via seed data)
- Dr. Alice Martin – General Medicine
- Dr. Robert Chen – Cardiology
- Dr. Priya Sharma – Dermatology
- Dr. James Okonkwo – Pediatrics

---

## ✅ Demonstration Checklist (Week 10 Demo)

### Patient Flow
- [ ] Go to `/register.php` → fill form → account saved in DB
- [ ] Go to `/login.php` → login with new account → redirected to dashboard
- [ ] Dashboard shows DB data (appointments, records)
- [ ] Go to `/book-appointment.php` → select Human → fill form → **AJAX call** saves to DB
- [ ] Go to `/patient/appointments.php` → see your booked appointment
- [ ] Cancel an appointment → status updates in DB

### Admin Flow
- [ ] Go to `/admin-login.php` → login as admin
- [ ] Dashboard shows live stats (patient count, doctor count, pending appts)
- [ ] Go to Appointments → see all appointments → approve one → status changes in DB
- [ ] Go to Add Doctor → fill form → doctor saved to DB
- [ ] Go to Add Nurse → fill form → nurse saved to DB
- [ ] Go to Manage Staff → see list from DB → remove a staff member

### Security Features to Point Out
- [ ] **Prepared statements** – all DB queries use `$stmt->bind_param()`
- [ ] **Password hashing** – `password_hash()` + `password_verify()` (bcrypt)
- [ ] **CSRF tokens** – every POST form has a hidden `csrf_token` field validated server-side
- [ ] **XSS prevention** – all output uses `h()` / `htmlspecialchars()`
- [ ] **Session regeneration** – `session_regenerate_id(true)` on login
- [ ] **Input validation** – both client-side (JS) and server-side (PHP)
- [ ] **AJAX** – appointment booking & doctor dropdown use `fetch()` API

---

## 🏗️ Technologies Used

| Layer      | Technology            |
|------------|-----------------------|
| Frontend   | HTML5, CSS3, JavaScript (ES6) |
| Backend    | PHP 8.x               |
| Database   | MySQL (MariaDB via XAMPP) |
| AJAX       | Fetch API (JSON)      |
| Security   | Prepared statements, bcrypt, CSRF tokens, XSS escaping |
| Structure  | MVC-inspired (views, includes, API endpoints) |

---

## 🐞 Troubleshooting

| Problem | Solution |
|---------|----------|
| Blank page | Enable PHP error display: add `ini_set('display_errors',1);` to top of db.php |
| DB connection failed | Check `DB_USER`/`DB_PASS` in `includes/db.php` |
| 404 errors | Ensure files are in `C:\xampp\htdocs\vitalcare\` |
| CSRF error | Clear browser cookies and retry |
| Appointment not saving | Make sure you are logged in as a patient first |
