// validations.js
// Basic client-side validation helpers

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(String(email).toLowerCase());
    }
    
    function validatePassword(pw) {
    // Example: min 8 chars, at least one number, one uppercase
    const re = /(?=.*\d)(?=.*[A-Z]).{8,}/;
    return re.test(pw);
    }
    
    function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    const email = form.querySelector('[name="email"]');
    const password = form.querySelector('[name="password"]');
    
    if (email && !validateEmail(email.value)) {
    alert('Please enter a valid email.');
    email.focus();
    return false;
    }
    
    if (password && !validatePassword(password.value)) {
    alert('Password must be at least 8 characters and include a number and uppercase letter.');
    password.focus();
    return false;
    }
    
    return true;
    }
    
    // Example: attach to a form
    // document.getElementById('loginForm').addEventListener('submit', function(e){
    // if(!validateForm('loginForm')) e.preventDefault();
    // });