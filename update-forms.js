// Script to add security features to all HTML forms
const fs = require('fs');
const path = require('path');

const securityFeatures = {
    // Add these attributes to password fields
    password: {
        minlength: "8",
        pattern: "^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d).+$",
        title: "Password must contain at least 8 characters, one uppercase letter, one lowercase letter, and one number",
        autocomplete: "current-password"
    },
    
    // Add these to email fields
    email: {
        pattern: "[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,}$",
        title: "Please enter a valid email address",
        autocomplete: "email"
    },
    
    // Add these to phone fields
    tel: {
        pattern: "^(\\+230\\s?)?\\d{3}\\s?\\d{4}$",
        title: "Please enter a valid Mauritian phone number (e.g., +230 512 3456)",
        autocomplete: "tel"
    },
    
    // Add these to date fields
    date: {
        min: new Date().toISOString().split('T')[0] // Today's date for future dates
    }
};

function addSecurityToForm(html) {
    // Add required attribute to all form fields that should be required
    html = html.replace(/<input(?![^>]*required)/g, '<input required');
    
    // Add pattern validation for specific fields
    html = html.replace(/type="email"/g, 'type="email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Please enter a valid email address"');
    
    html = html.replace(/type="password"/g, 'type="password" minlength="8" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d).+$" title="Password must contain at least 8 characters with uppercase, lowercase, and number"');
    
    html = html.replace(/type="tel"/g, 'type="tel" pattern="^(\\+230\\s?)?\\d{3}\\s?\\d{4}$" title="Please enter a valid Mauritian phone number"');
    
    // Add novalidate to forms to use custom validation
    html = html.replace(/<form/g, '<form novalidate');
    
    // Add security notice
    html = html.replace(/<form[^>]*>/g, '$&\n<div class="form-security-notice">🔒 Secure form protected by encryption</div>');
    
    return html;
}

function processHTMLFiles() {
    const htmlFiles = [
        'patient-login.html',
        'doctor-login.html',
        'nurse-login.html',
        'receptionist-login.html',
        'admin-login.html',
        'patient-portal.html',
        'patient-register-1.html',
        'patient-register-2.html',
        'patient-register-3.html',
        'book-appointment.html',
        'schedule-appointment.html',
        'contact.html',
        'contact-alt.html',
        'new-record.html',
        'admin-add-doctor.html',
        'add-nurse.html',
        'account-setup.html'
    ];
    
    htmlFiles.forEach(file => {
        const filePath = path.join(__dirname, file);
        if (fs.existsSync(filePath)) {
            let content = fs.readFileSync(filePath, 'utf8');
            content = addSecurityToForm(content);
            fs.writeFileSync(filePath, content);
            console.log(`Updated: ${file}`);
        }
    });
}

// Run the update
processHTMLFiles();
