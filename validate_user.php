<?php
#validate_user.php

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($email) <= 100;
}

function validateUsername($username) {
    return preg_match('/^[a-zA-Z0-9]{3,20}$/', $username);
}

function validatePassword($password) {
    //  Strong password: 8+ chars, 1 upper, 1 lower, 1 number
    return strlen($password) >= 8 && 
           preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password);
}

function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function displayErrors($errors) {
    if (!empty($errors)) {
        echo '<div style="color:#d00;padding:10px;border:1px solid #d00;background:#fee;">' . 
             implode('<br>', $errors) . '</div>';
    }
}
