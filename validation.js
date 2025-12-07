// Enhanced validation and security for VitalCare Portal
class VitalCareSecurity {
    // ========== VALIDATION FUNCTIONS ==========
    
    // Email validation
    static validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Phone number validation (Mauritian format)
    static validatePhone(phone) {
        // Accepts: +230 5123456, 5123456, +2305123456
        const phoneRegex = /^(\+230\s?)?\d{3}\s?\d{4}$/;
        return phoneRegex.test(phone);
    }

    // Password strength validation
    static validatePassword(password) {
        // At least 8 chars, 1 uppercase, 1 lowercase, 1 number
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
        return passwordRegex.test(password);
    }

    // Date validation - must be in future for appointments
    static validateFutureDate(dateString) {
        if (!dateString) return false;
        const selectedDate = new Date(dateString);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return selectedDate >= today;
    }

    // Date validation - must be in past for birth dates
    static validatePastDate(dateString) {
        if (!dateString) return false;
        const selectedDate = new Date(dateString);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return selectedDate <= today;
    }

    // Required field validation
    static validateRequired(value) {
        return value !== null && value !== undefined && value.toString().trim() !== '';
    }

    // Numeric validation
    static validateNumeric(value) {
        return !isNaN(parseFloat(value)) && isFinite(value);
    }

    // Age validation
    static validateAge(dobString) {
        const dob = new Date(dobString);
        const today = new Date();
        let age = today.getFullYear() - dob.getFullYear();
        const monthDiff = today.getMonth() - dob.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
            age--;
        }
        
        return age >= 0 && age <= 120;
    }

    // Name validation (letters, spaces, hyphens, apostrophes)
    static validateName(name) {
        const nameRegex = /^[A-Za-zÀ-ÿ\s'-]+$/;
        return nameRegex.test(name) && name.length >= 2;
    }

    // Address validation
    static validateAddress(address) {
        return address.trim().length >= 10;
    }

    // Medical ID validation
    static validateMedicalID(id) {
        // Format: VC-XXXXX where X is digit
        const idRegex = /^VC-\d{5}$/;
        return idRegex.test(id);
    }

    // ========== SECURITY FUNCTIONS ==========
    
    // Input sanitization
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

    // XSS prevention for text areas
    static sanitizeTextArea(text) {
        // Remove script tags and event handlers
        return text.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
                   .replace(/on\w+="[^"]*"/g, '')
                   .replace(/on\w+='[^']*'/g, '')
                   .replace(/javascript:/gi, '');
    }

    // Generate CSRF token
    static generateCSRFToken() {
        return 'vc_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now();
    }

    // Validate CSRF token
    static validateCSRFToken(token) {
        return token && token.startsWith('vc_');
    }

    // Rate limiting simulation
    static checkRateLimit(key) {
        const now = Date.now();
        const attempts = JSON.parse(localStorage.getItem(`rate_limit_${key}`) || '[]');
        
        // Remove attempts older than 15 minutes
        const recentAttempts = attempts.filter(time => now - time < 15 * 60 * 1000);
        
        if (recentAttempts.length >= 5) {
            return false; // Too many attempts
        }
        
        recentAttempts.push(now);
        localStorage.setItem(`rate_limit_${key}`, JSON.stringify(recentAttempts));
        return true;
    }

    // Password strength calculation
    static getPasswordStrength(password) {
        let strength = 0;
        
        // Length check
        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        
        // Character type checks
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;
        
        return Math.min(strength, 5); // Max 5 for display
    }

    // Display password strength
    static displayPasswordStrength(password, elementId) {
        const strength = this.getPasswordStrength(password);
        const strengthText = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong', 'Very Strong'];
        const strengthColors = ['#ff0000', '#ff4000', '#ff8000', '#ffbf00', '#80ff00', '#00ff00'];
        
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = `Strength: ${strengthText[strength]}`;
            element.style.color = strengthColors[strength];
        }
        
        // Update strength bar if exists
        const strengthBar = document.getElementById(`${elementId}-bar`);
        if (strengthBar) {
            strengthBar.style.width = `${strength * 20}%`;
            strengthBar.style.backgroundColor = strengthColors[strength];
        }
    }

    // Generate CAPTCHA
    static generateCaptcha(length = 5) {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        let captcha = '';
        for (let i = 0; i < length; i++) {
            captcha += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return captcha;
    }

    // Validate CAPTCHA
    static validateCaptcha(input, expected) {
        return input.toUpperCase() === expected.toUpperCase();
    }

    // ========== UI HELPER FUNCTIONS ==========
    
    // Show error message
    static showError(inputElement, message) {
        const parent = inputElement.parentElement;
        
        // Remove existing error
        const existingError = parent.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
        
        // Add error class to input
        inputElement.classList.add('error');
        inputElement.classList.remove('success');
        
        // Create error message
        const errorElement = document.createElement('div');
        errorElement.className = 'error-message';
        errorElement.textContent = message;
        errorElement.style.cssText = 'color: #dc3545; font-size: 12px; margin-top: 5px;';
        
        parent.appendChild(errorElement);
        
        // Update icon
        this.updateInputIcon(inputElement, false);
        
        return errorElement;
    }

    // Show success state
    static showSuccess(inputElement) {
        inputElement.classList.remove('error');
        inputElement.classList.add('success');
        
        const parent = inputElement.parentElement;
        const existingError = parent.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
        
        // Update icon
        this.updateInputIcon(inputElement, true);
    }

    // Clear validation state
    static clearValidation(inputElement) {
        inputElement.classList.remove('error', 'success');
        
        const parent = inputElement.parentElement;
        const existingError = parent.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
        
        // Clear icon
        this.updateInputIcon(inputElement, null);
    }

    // Update input icon
    static updateInputIcon(inputElement, isValid) {
        let icon = inputElement.parentElement.querySelector('.input-icon');
        if (!icon) {
            // Create icon if doesn't exist
            icon = document.createElement('span');
            icon.className = 'input-icon';
            inputElement.parentElement.style.position = 'relative';
            icon.style.cssText = 'position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 16px;';
            inputElement.parentElement.appendChild(icon);
            inputElement.style.paddingRight = '30px';
        }
        
        if (isValid === true) {
            icon.textContent = '✓';
            icon.style.color = '#28a745';
        } else if (isValid === false) {
            icon.textContent = '✗';
            icon.style.color = '#dc3545';
        } else {
            icon.textContent = '';
        }
    }

    // Disable form buttons during submission
    static disableFormButtons(form, text = 'Processing...') {
        const buttons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
        buttons.forEach(button => {
            button.disabled = true;
            const originalText = button.dataset.originalText || button.innerHTML;
            button.dataset.originalText = originalText;
            button.innerHTML = `<span class="spinner"></span> ${text}`;
        });
    }

    // Enable form buttons
    static enableFormButtons(form) {
        const buttons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
        buttons.forEach(button => {
            button.disabled = false;
            button.innerHTML = button.dataset.originalText || button.innerHTML;
        });
    }

    // Show notification
    static showNotification(message, type = 'info') {
        const colors = {
            success: '#28a745',
            error: '#dc3545',
            warning: '#ffc107',
            info: '#17a2b8'
        };
        
        const notification = document.createElement('div');
        notification.className = 'security-notification';
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 5px;
            color: white;
            background: ${colors[type] || colors.info};
            z-index: 9999;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            animation: notificationSlideIn 0.3s ease;
            max-width: 400px;
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.style.animation = 'notificationSlideOut 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 5000);
        
        // Add CSS animations if not already present
        if (!document.getElementById('notification-styles')) {
            const style = document.createElement('style');
            style.id = 'notification-styles';
            style.textContent = `
                @keyframes notificationSlideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes notificationSlideOut {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }
                .spinner {
                    display: inline-block;
                    width: 16px;
                    height: 16px;
                    border: 2px solid rgba(255,255,255,.3);
                    border-radius: 50%;
                    border-top-color: #fff;
                    animation: spin 1s ease-in-out infinite;
                }
                @keyframes spin {
                    to { transform: rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
        }
    }

    // ========== FORM VALIDATORS ==========
    
    // Patient registration validator
    static validatePatientRegistration(formData) {
        const errors = [];
        
        // Required fields
        const required = ['fullName', 'email', 'password', 'dob', 'phone'];
        required.forEach(field => {
            if (!this.validateRequired(formData[field])) {
                errors.push(`${field} is required`);
            }
        });
        
        // Email validation
        if (formData.email && !this.validateEmail(formData.email)) {
            errors.push('Invalid email format');
        }
        
        // Password validation
        if (formData.password && !this.validatePassword(formData.password)) {
            errors.push('Password must be at least 8 characters with uppercase, lowercase, and number');
        }
        
        // Date of birth validation
        if (formData.dob && !this.validatePastDate(formData.dob)) {
            errors.push('Date of birth must be in the past');
        }
        
        // Phone validation
        if (formData.phone && !this.validatePhone(formData.phone)) {
            errors.push('Invalid phone number format');
        }
        
        return errors;
    }

    // Appointment booking validator
    static validateAppointment(formData) {
        const errors = [];
        
        // Required fields
        const required = ['patientName', 'appointmentDate', 'appointmentTime', 'doctor'];
        required.forEach(field => {
            if (!this.validateRequired(formData[field])) {
                errors.push(`${field} is required`);
            }
        });
        
        // Date validation
        if (formData.appointmentDate && !this.validateFutureDate(formData.appointmentDate)) {
            errors.push('Appointment date must be in the future');
        }
        
        return errors;
    }

    // Medical record validator
    static validateMedicalRecord(formData) {
        const errors = [];
        
        // Required fields
        const required = ['patient', 'diagnosis', 'treatment'];
        required.forEach(field => {
            if (!this.validateRequired(formData[field])) {
                errors.push(`${field} is required`);
            }
        });
        
        return errors;
    }
}

// ========== SESSION MANAGER ==========
class SessionManager {
    static set(key, value) {
        try {
            sessionStorage.setItem(`vc_${key}`, JSON.stringify({
                data: value,
                timestamp: Date.now()
            }));
        } catch (e) {
            console.error('Session storage error:', e);
        }
    }

    static get(key) {
        try {
            const item = sessionStorage.getItem(`vc_${key}`);
            if (!item) return null;
            
            const parsed = JSON.parse(item);
            
            // Check if expired (24 hours)
            if (Date.now() - parsed.timestamp > 24 * 60 * 60 * 1000) {
                this.remove(key);
                return null;
            }
            
            return parsed.data;
        } catch (e) {
            return null;
        }
    }

    static remove(key) {
        sessionStorage.removeItem(`vc_${key}`);
    }

    static clear() {
        Object.keys(sessionStorage).forEach(key => {
            if (key.startsWith('vc_')) {
                sessionStorage.removeItem(key);
            }
        });
    }

    static login(role, userData) {
        this.set('loggedInRole', role);
        this.set('userData', userData);
        this.set('lastActivity', Date.now());
        
        // Set session expiry (8 hours)
        setTimeout(() => {
            this.logout();
            window.location.href = 'index.html?session=expired';
        }, 8 * 60 * 60 * 1000);
    }

    static logout() {
        this.clear();
    }

    static isLoggedIn() {
        return !!this.get('loggedInRole');
    }

    static getCurrentRole() {
        return this.get('loggedInRole');
    }

    static getUserData() {
        return this.get('userData');
    }

    static updateActivity() {
        this.set('lastActivity', Date.now());
    }

    static checkTimeout() {
        const lastActivity = this.get('lastActivity');
        if (lastActivity && Date.now() - lastActivity > 30 * 60 * 1000) { // 30 minutes
            this.logout();
            return false;
        }
        return true;
    }
}

// ========== FORM SECURITY MANAGER ==========
class FormSecurity {
    constructor(formId) {
        this.form = document.getElementById(formId);
        this.csrfToken = VitalCareSecurity.generateCSRFToken();
        this.init();
    }

    init() {
        if (!this.form) return;
        
        // Add CSRF token field
        const csrfField = document.createElement('input');
        csrfField.type = 'hidden';
        csrfField.name = 'csrf_token';
        csrfField.value = this.csrfToken;
        this.form.appendChild(csrfField);
        
        // Add form submission handler
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        
        // Add real-time validation
        this.addRealTimeValidation();
        
        // Add rate limiting for login forms
        if (this.form.id.includes('login') || this.form.id.includes('Login')) {
            this.addLoginProtection();
        }
    }

    handleSubmit(e) {
        e.preventDefault();
        
        // Validate CSRF token
        const formToken = this.form.querySelector('[name="csrf_token"]').value;
        if (!VitalCareSecurity.validateCSRFToken(formToken)) {
            VitalCareSecurity.showNotification('Security token invalid. Please refresh the page.', 'error');
            return;
        }
        
        // Validate form
        if (!this.validateForm()) {
            VitalCareSecurity.showNotification('Please fix the errors in the form.', 'error');
            return;
        }
        
        // Disable buttons during submission
        VitalCareSecurity.disableFormButtons(this.form);
        
        // Simulate submission (in real app, this would be fetch/AJAX)
        setTimeout(() => {
            VitalCareSecurity.showNotification('Form submitted successfully!', 'success');
            VitalCareSecurity.enableFormButtons(this.form);
            
            // Generate new CSRF token for next submission
            this.refreshCSRFToken();
            
            // Reset form if needed
            if (this.form.dataset.resetOnSuccess) {
                this.form.reset();
            }
        }, 1500);
    }

    validateForm() {
        let isValid = true;
        const requiredFields = this.form.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            VitalCareSecurity.clearValidation(field);
            
            if (!VitalCareSecurity.validateRequired(field.value)) {
                VitalCareSecurity.showError(field, 'This field is required');
                isValid = false;
            }
            
            // Field-specific validation
            this.validateField(field);
        });
        
        return isValid;
    }

    validateField(field) {
        const value = field.value.trim();
        
        switch (field.type) {
            case 'email':
                if (value && !VitalCareSecurity.validateEmail(value)) {
                    VitalCareSecurity.showError(field, 'Please enter a valid email address');
                } else if (value) {
                    VitalCareSecurity.showSuccess(field);
                }
                break;
                
            case 'tel':
                if (value && !VitalCareSecurity.validatePhone(value)) {
                    VitalCareSecurity.showError(field, 'Please enter a valid phone number');
                } else if (value) {
                    VitalCareSecurity.showSuccess(field);
                }
                break;
                
            case 'password':
                if (value && value.length < 8) {
                    VitalCareSecurity.showError(field, 'Password must be at least 8 characters');
                } else if (value) {
                    VitalCareSecurity.showSuccess(field);
                }
                break;
                
            case 'date':
                if (field.hasAttribute('data-future-date') && value) {
                    if (!VitalCareSecurity.validateFutureDate(value)) {
                        VitalCareSecurity.showError(field, 'Date must be in the future');
                    } else {
                        VitalCareSecurity.showSuccess(field);
                    }
                }
                if (field.hasAttribute('data-past-date') && value) {
                    if (!VitalCareSecurity.validatePastDate(value)) {
                        VitalCareSecurity.showError(field, 'Date must be in the past');
                    } else {
                        VitalCareSecurity.showSuccess(field);
                    }
                }
                break;
        }
        
        // Pattern validation
        if (field.pattern && value) {
            const regex = new RegExp(field.pattern);
            if (!regex.test(value)) {
                VitalCareSecurity.showError(field, field.title || 'Invalid format');
            }
        }
    }

    addRealTimeValidation() {
        this.form.querySelectorAll('input, textarea, select').forEach(field => {
            field.addEventListener('blur', () => this.validateField(field));
            field.addEventListener('input', () => {
                if (field.classList.contains('error')) {
                    this.validateField(field);
                }
            });
        });
    }

    addLoginProtection() {
        const emailField = this.form.querySelector('input[type="email"]');
        const submitBtn = this.form.querySelector('button[type="submit"], input[type="submit"]');
        
        if (emailField && submitBtn) {
            this.form.addEventListener('submit', () => {
                const email = emailField.value;
                if (!VitalCareSecurity.checkRateLimit(email)) {
                    VitalCareSecurity.showNotification('Too many attempts. Please try again in 15 minutes.', 'error');
                    submitBtn.disabled = true;
                    setTimeout(() => {
                        submitBtn.disabled = false;
                    }, 15 * 60 * 1000);
                    return false;
                }
            });
        }
    }

    refreshCSRFToken() {
        this.csrfToken = VitalCareSecurity.generateCSRFToken();
        const csrfField = this.form.querySelector('[name="csrf_token"]');
        if (csrfField) {
            csrfField.value = this.csrfToken;
        }
    }
}

// ========== INITIALIZATION ==========
document.addEventListener('DOMContentLoaded', function() {
    // Check session timeout
    if (!SessionManager.checkTimeout()) {
        VitalCareSecurity.showNotification('Your session has expired. Please login again.', 'warning');
    }
    
    // Update activity on user interaction
    ['click', 'keypress', 'mousemove', 'scroll'].forEach(event => {
        document.addEventListener(event, () => SessionManager.updateActivity());
    });
    
    // Initialize form security for all forms
    document.querySelectorAll('form').forEach(form => {
        if (form.id) {
            new FormSecurity(form.id);
        }
    });
    
    // Add password strength indicators
    document.querySelectorAll('input[type="password"]').forEach(passwordField => {
        passwordField.addEventListener('input', function() {
            const strengthId = this.id + '-strength';
            VitalCareSecurity.displayPasswordStrength(this.value, strengthId);
        });
    });
    
    // Add show/hide password toggles
    document.querySelectorAll('.password-toggle input[type="checkbox"]').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const passwordField = document.getElementById(this.dataset.target);
            if (passwordField) {
                passwordField.type = this.checked ? 'text' : 'password';
            }
        });
    });
    
    // Initialize CAPTCHA if exists
    const captchaDisplay = document.getElementById('captchaDisplay');
    if (captchaDisplay) {
        const captcha = VitalCareSecurity.generateCaptcha();
        captchaDisplay.textContent = captcha;
        captchaDisplay.dataset.expected = captcha;
        
        // Add refresh button functionality
        const refreshBtn = document.querySelector('[onclick*="refreshCaptcha"]');
        if (refreshBtn) {
            refreshBtn.onclick = function() {
                const newCaptcha = VitalCareSecurity.generateCaptcha();
                captchaDisplay.textContent = newCaptcha;
                captchaDisplay.dataset.expected = newCaptcha;
                document.getElementById('captcha-input').value = '';
            };
        }
    }
    
    // Add input masking for phone numbers
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = this.value.replace(/\D/g, '');
            
            if (value.length > 0) {
                if (!value.startsWith('230')) {
                    value = '230' + value;
                }
                
                if (value.length > 3) {
                    value = value.substring(0, 3) + ' ' + value.substring(3);
                }
                if (value.length > 7) {
                    value = value.substring(0, 7) + ' ' + value.substring(7);
                }
                
                this.value = '+ ' + value;
            }
        });
    });
    
    // Add date restrictions
    const futureDateInputs = document.querySelectorAll('input[type="date"][data-future-date]');
    futureDateInputs.forEach(input => {
        const today = new Date().toISOString().split('T')[0];
        input.min = today;
    });
    
    const pastDateInputs = document.querySelectorAll('input[type="date"][data-past-date]');
    pastDateInputs.forEach(input => {
        const today = new Date().toISOString().split('T')[0];
        input.max = today;
    });
});

// Global helper functions
function validateAndSubmit(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.dispatchEvent(new Event('submit'));
    }
}

function showPassword(fieldId) {
    const field = document.getElementById(fieldId);
    if (field) {
        field.type = field.type === 'password' ? 'text' : 'password';
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        VitalCareSecurity.showNotification('Copied to clipboard!', 'success');
    });
}

// Export for use in other scripts
window.VitalCareSecurity = VitalCareSecurity;
window.SessionManager = SessionManager;
window.FormSecurity = FormSecurity;
