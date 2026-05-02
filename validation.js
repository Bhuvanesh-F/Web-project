// Enhanced validation and security for VitalCare Portal (Week 20 - Laravel Ready)

const API_BASE = "/api";

// ========================
// VITALCARE SECURITY CLASS
// ========================
class VitalCareSecurity {

    // ========================
    // VALIDATION FUNCTIONS
    // ========================

    static validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    static validatePhone(phone) {
        const phoneRegex = /^(\+230\s?)?\d{3}\s?\d{4}$/;
        return phoneRegex.test(phone);
    }

    static validatePassword(password) {
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
        return passwordRegex.test(password);
    }

    static validateFutureDate(dateString) {
        if (!dateString) return false;
        const selectedDate = new Date(dateString);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return selectedDate >= today;
    }

    static validatePastDate(dateString) {
        if (!dateString) return false;
        const selectedDate = new Date(dateString);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return selectedDate <= today;
    }

    static validateRequired(value) {
        return value !== null && value !== undefined && value.toString().trim() !== '';
    }

    static validateName(name) {
        const nameRegex = /^[A-Za-zÀ-ÿ\s'-]+$/;
        return nameRegex.test(name) && name.length >= 2;
    }

    static validateAddress(address) {
        return address && address.trim().length >= 10;
    }

    // ========================
    // SECURITY FUNCTIONS
    // ========================

    static sanitizeInput(input) {
        if (typeof input !== 'string') return input;

        return input.trim()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#x27;')
            .replace(/\//g, '&#x2F;')
            .replace(/\\/g, '&#x5C;')
            .replace(/`/g, '&#96;');
    }

    static sanitizeTextArea(text) {
        return text
            .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
            .replace(/on\w+="[^"]*"/g, '')
            .replace(/on\w+='[^']*'/g, '')
            .replace(/javascript:/gi, '');
    }

    // Laravel CSRF token (REAL)
    static getCSRFToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    }

    // ========================
    // API HANDLER (IMPORTANT)
    // ========================

    static apiRequest(url, method = "GET", data = null) {
        return fetch(API_BASE + url, {
            method: method,
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": this.getCSRFToken()
            },
            body: data ? JSON.stringify(data) : null
        }).then(async res => {
            if (!res.ok) {
                const error = await res.json();
                throw error;
            }
            return res.json();
        });
    }

    // ========================
    // PASSWORD STRENGTH
    // ========================

    static getPasswordStrength(password) {
        let strength = 0;

        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;

        return Math.min(strength, 5);
    }

    static displayPasswordStrength(password, elementId) {
        const strength = this.getPasswordStrength(password);

        const text = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong', 'Very Strong'];
        const colors = ['#ff0000', '#ff4000', '#ff8000', '#ffbf00', '#80ff00', '#00ff00'];

        const el = document.getElementById(elementId);
        if (el) {
            el.textContent = `Strength: ${text[strength]}`;
            el.style.color = colors[strength];
        }
    }

    // ========================
    // UI HELPERS
    // ========================

    static showError(input, message) {
        const parent = input.parentElement;

        const old = parent.querySelector('.error-message');
        if (old) old.remove();

        input.classList.add('error');
        input.classList.remove('success');

        const error = document.createElement('div');
        error.className = 'error-message';
        error.textContent = message;
        error.style.color = 'red';
        error.style.fontSize = '12px';

        parent.appendChild(error);
    }

    static showSuccess(input) {
        input.classList.remove('error');
        input.classList.add('success');

        const parent = input.parentElement;
        const old = parent.querySelector('.error-message');
        if (old) old.remove();
    }

    static showNotification(message, type = "info") {
        const colors = {
            success: "#28a745",
            error: "#dc3545",
            warning: "#ffc107",
            info: "#17a2b8"
        };

        const box = document.createElement("div");
        box.textContent = message;

        box.style.position = "fixed";
        box.style.top = "20px";
        box.style.right = "20px";
        box.style.padding = "15px";
        box.style.color = "white";
        box.style.background = colors[type];
        box.style.zIndex = "9999";
        box.style.borderRadius = "5px";

        document.body.appendChild(box);

        setTimeout(() => box.remove(), 4000);
    }
}

// ========================
// SESSION MANAGER
// ========================
class SessionManager {

    static set(key, value) {
        sessionStorage.setItem("vc_" + key, JSON.stringify(value));
    }

    static get(key) {
        const item = sessionStorage.getItem("vc_" + key);
        return item ? JSON.parse(item) : null;
    }

    static clear() {
        sessionStorage.clear();
    }
}

// ========================
// FORM SECURITY (AJAX READY)
// ========================
class FormSecurity {

    constructor(formId) {
        this.form = document.getElementById(formId);
        if (!this.form) return;

        this.init();
    }

    init() {
        this.form.addEventListener("submit", (e) => this.handleSubmit(e));
    }

    handleSubmit(e) {
        e.preventDefault();

        const data = Object.fromEntries(new FormData(this.form));

        VitalCareSecurity.apiRequest("/patients", "POST", data)
            .then(res => {
                VitalCareSecurity.showNotification("Success!", "success");
                console.log(res);
                this.form.reset();
            })
            .catch(err => {
                VitalCareSecurity.showNotification("Error occurred", "error");
                console.error(err);
            });
    }
}

// ========================
// GLOBAL HELPERS
// ========================
function validateAndSubmit(formId) {
    const form = document.getElementById(formId);
    if (form) form.dispatchEvent(new Event("submit"));
}

function showPassword(id) {
    const field = document.getElementById(id);
    if (field) {
        field.type = field.type === "password" ? "text" : "password";
    }
}

// ========================
// INIT
// ========================
document.addEventListener("DOMContentLoaded", () => {

    document.querySelectorAll("form").forEach(f => {
        if (f.id) new FormSecurity(f.id);
    });

    document.querySelectorAll("input[type='password']").forEach(p => {
        p.addEventListener("input", function () {
            VitalCareSecurity.displayPasswordStrength(this.value, this.id + "-strength");
        });
    });
});

// expose globally
window.VitalCareSecurity = VitalCareSecurity;
window.SessionManager = SessionManager;
window.FormSecurity = FormSecurity;
