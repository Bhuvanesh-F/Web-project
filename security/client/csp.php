<?php
// csp.php - include at the top of each PHP page before any output
// Tailor to your asset origins (CDNs, analytics, fonts etc.)

$nonce = bin2hex(random_bytes(16)); // optional if you use inline scripts with nonce
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-".$nonce."'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self'; frame-ancestors 'none'; base-uri 'self';");

// Optionally expose the nonce to templates for inline scripts
// echo "<!-- CSP Nonce: $nonce -->\n";

?>