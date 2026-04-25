/* ============================================================
   VitalCare – Main JavaScript  (js/main.js)
   ============================================================ */

'use strict';

// ── Helpers ──────────────────────────────────────────────────
const $ = id => document.getElementById(id);
const showEl  = el => el && (el.style.display = 'block');
const hideEl  = el => el && (el.style.display = 'none');

function showError(inputId, msg) {
    const input = $(inputId);
    if (!input) return;
    input.classList.add('input-error');
    const err = input.parentElement.querySelector('.form-error');
    if (err) { err.textContent = msg; err.classList.add('show'); }
}

function clearError(inputId) {
    const input = $(inputId);
    if (!input) return;
    input.classList.remove('input-error');
    const err = input.parentElement.querySelector('.form-error');
    if (err) { err.textContent = ''; err.classList.remove('show'); }
}

function clearAllErrors(formId) {
    const form = $(formId) || document.querySelector('.' + formId);
    if (!form) return;
    form.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));
    form.querySelectorAll('.form-error.show').forEach(el => { el.textContent = ''; el.classList.remove('show'); });
}

function flashAlert(msg, type = 'success', containerId = 'flash-msg') {
    const container = $(containerId) || document.querySelector('.flash-area');
    if (!container) { console.log(msg); return; }
    container.innerHTML = `<div class="alert alert-${type}">${msg}</div>`;
    container.scrollIntoView({ behavior: 'smooth', block: 'start' });
    if (type === 'success') {
        setTimeout(() => { container.innerHTML = ''; }, 5000);
    }
}

function setButtonLoading(btn, loading = true) {
    if (!btn) return;
    if (loading) {
        btn.dataset.origText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner"></span> Please wait…';
        btn.disabled = true;
    } else {
        btn.innerHTML = btn.dataset.origText || 'Submit';
        btn.disabled = false;
    }
}

// ── Validation helpers ───────────────────────────────────────
const validators = {
    email:    v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v),
    phone:    v => /^(\+230\s?)?\d{3}\s?\d{4}$/.test(v),
    password: v => /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/.test(v),
    name:     v => /^[A-Za-zÀ-ÿ\s''-]+$/.test(v) && v.trim().length >= 2,
    futureDate: v => { if (!v) return false; return new Date(v) >= new Date(new Date().toDateString()); },
};

// ── Appointment Selection ────────────────────────────────────
window.selectAppointmentType = function(type) {
    const selSection = document.querySelector('.appointment-selection');
    const formSection = document.querySelector('.appointment-form-section');
    const typeInput = $('appointment_type');
    if (typeInput) typeInput.value = type;
    if (selSection) hideEl(selSection);
    if (formSection) showEl(formSection);
};

window.goBackToSelection = function() {
    const selSection = document.querySelector('.appointment-selection');
    const formSection = document.querySelector('.appointment-form-section');
    if (selSection) showEl(selSection);
    if (formSection) hideEl(formSection);
};

// ── AJAX Appointment Booking ─────────────────────────────────
window.submitAppointmentAjax = function() {
    const form = $('bookingForm');
    if (!form) return;

    clearAllErrors('bookingForm');
    let valid = true;

    const fields = {
        full_name:        { label: 'Full name',    fn: v => validators.name(v)       },
        contact_number:   { label: 'Phone',        fn: v => validators.phone(v)      },
        doctor_speciality:{ label: 'Speciality',   fn: v => v.trim() !== ''          },
        preferred_date:   { label: 'Date',         fn: v => validators.futureDate(v) },
        preferred_time:   { label: 'Time slot',    fn: v => v.trim() !== ''          },
    };

    for (const [id, rule] of Object.entries(fields)) {
        const el = $(id);
        if (!el || !rule.fn(el.value)) {
            const msg = id === 'preferred_date' ? 'Please pick a future date.'
                       : id === 'contact_number' ? 'Enter a valid Mauritian number (e.g. 5123 4567).'
                       : `${rule.label} is required.`;
            showError(id, msg);
            valid = false;
        }
    }

    if (!valid) return;

    const btn = form.querySelector('button[type="button"][onclick]') || form.querySelector('.btn-submit');
    setButtonLoading(btn, true);

    const data = new FormData(form);

    fetch('/vitalcare/api/book-appointment.php', { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
            setButtonLoading(btn, false);
            if (res.success) {
                flashAlert('✅ Appointment booked! We will confirm shortly.', 'success');
                form.reset();
                goBackToSelection();
            } else {
                flashAlert('❌ ' + (res.message || 'Booking failed. Please try again.'), 'danger');
            }
        })
        .catch(() => {
            setButtonLoading(btn, false);
            flashAlert('❌ Network error. Please check your connection.', 'danger');
        });
};

// ── Client-side login validation ─────────────────────────────
window.validateLoginForm = function(e) {
    if (e) e.preventDefault();
    clearAllErrors('loginForm');
    let valid = true;
    const emailEl = $('login_email'), passEl = $('login_password');
    if (!emailEl || !validators.email(emailEl.value)) { showError('login_email', 'Enter a valid email address.'); valid = false; }
    if (!passEl  || passEl.value.trim() === '')        { showError('login_password', 'Password is required.'); valid = false; }
    if (valid && $('loginForm')) $('loginForm').submit();
};

// ── Client-side register validation ──────────────────────────
window.validateRegisterForm = function(e) {
    if (e) e.preventDefault();
    clearAllErrors('registerForm');
    let valid = true;

    const check = (id, fn, msg) => {
        const el = $(id);
        if (!el || !fn(el.value)) { showError(id, msg); valid = false; }
    };

    check('first_name',      validators.name,   'Enter a valid first name (letters only, min 2 chars).');
    check('last_name',       validators.name,   'Enter a valid last name.');
    check('reg_email',       validators.email,  'Enter a valid email address.');
    check('reg_phone',       validators.phone,  'Enter a valid Mauritian phone number.');
    check('reg_password',    validators.password,'Password must be 8+ chars with uppercase, lowercase and a number.');

    const pw = $('reg_password')?.value;
    const cp = $('reg_confirm_password')?.value;
    if (pw && cp && pw !== cp) { showError('reg_confirm_password', 'Passwords do not match.'); valid = false; }

    if (valid && $('registerForm')) $('registerForm').submit();
};

// ── Password strength indicator ──────────────────────────────
function attachPasswordStrength(inputId, barId) {
    const input = $(inputId), bar = $(barId);
    if (!input || !bar) return;
    input.addEventListener('input', () => {
        const v = input.value;
        let strength = 0;
        if (v.length >= 8) strength++;
        if (v.length >= 12) strength++;
        if (/[a-z]/.test(v)) strength++;
        if (/[A-Z]/.test(v)) strength++;
        if (/\d/.test(v)) strength++;
        if (/[^A-Za-z0-9]/.test(v)) strength++;
        const pct = Math.round((strength / 6) * 100);
        const colours = ['#dc3545','#fd7e14','#ffc107','#20c997','#28a745'];
        bar.style.width = pct + '%';
        bar.style.background = colours[Math.min(Math.floor(strength / 1.5), 4)];
    });
}

// ── Admin: Add Doctor/Nurse validation ───────────────────────
window.validateStaffForm = function(e) {
    if (e) e.preventDefault();
    clearAllErrors('staffForm');
    let valid = true;

    const check = (id, fn, msg) => {
        const el = $(id);
        if (!el) return;
        if (!fn(el.value)) { showError(id, msg); valid = false; }
    };

    check('staff_name',  validators.name,     'Enter a valid full name.');
    check('staff_email', validators.email,    'Enter a valid email address.');
    check('staff_pass',  validators.password, 'Password: 8+ chars, upper, lower, number.');

    if (valid && $('staffForm')) $('staffForm').submit();
};

// ── Init on DOM ready ────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    // Attach password strength bars
    attachPasswordStrength('reg_password', 'pw-strength-bar');
    attachPasswordStrength('staff_pass',   'staff-pw-bar');

    // Auto-dismiss success alerts
    document.querySelectorAll('.alert-success').forEach(el => {
        setTimeout(() => { el.style.transition = 'opacity .5s'; el.style.opacity = '0'; setTimeout(() => el.remove(), 500); }, 5000);
    });

    // Confirm dangerous actions
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', e => {
            if (!confirm(el.dataset.confirm || 'Are you sure?')) e.preventDefault();
        });
    });
});
