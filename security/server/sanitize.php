<?php
// sanitize.php

function esc_html($s) {
return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function get_post($k) {
return isset($_POST[$k]) ? trim($_POST[$k]) : null;
}

function safe_int($v) {
return filter_var($v, FILTER_VALIDATE_INT);
}

// Use prepared statements for DB; never build SQL with user input.

?>