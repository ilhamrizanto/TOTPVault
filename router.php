<?php
// router.php — Development router for PHP built-in web server
// Usage: php -S localhost:8000 router.php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $uri;

// If a static file exists and is not PHP, serve it directly
if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    // Block direct access to protected directories and files
    if (preg_match('#^/(config|src|sessions|templates|migrations|backups|mariadb|\.git|\.env)/#i', $uri) ||
        preg_match('#\.(sql|log|ini|env|md|lock|yml|yaml)$#i', $uri)) {
        http_response_code(403);
        echo 'Access denied';
        exit;
    }
    return false; // Built-in server serves static file
}

// Route all requests to index.php
require __DIR__ . '/index.php';
