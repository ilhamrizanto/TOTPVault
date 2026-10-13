<?php
// config/config.php

// Auto-load .env file if present
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (strpos($line, '=') !== false) {
            list($key, $val) = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val, " \t\n\r\0\x0B\"'");
            if (getenv($key) === false || getenv($key) === '') {
                putenv("{$key}={$val}");
                $_ENV[$key] = $val;
            }
        }
    }
}

return [
    'db' => [
        'host'     => getenv('DB_HOST')     ?: '127.0.0.1',
        'port'     => (int)(getenv('DB_PORT') ?: 3306),
        'dbname'   => getenv('DB_NAME')     ?: 'totpvault',
        'user'     => getenv('DB_USER')     ?: 'root',
        'password' => getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '',
        'charset'  => 'utf8mb4',
    ],
    'encryption_key' => getenv('ENCRYPTION_KEY') ?: 'ThisIsA32ByteKeyForLocalTesting!',
    'app_name'   => 'TOTPVault',
    'app_url'    => rtrim(getenv('APP_URL') ?: 'http://localhost:8000', '/'),
    'app_env'    => getenv('APP_ENV') ?: 'development',
    'mail' => [
        'from_email'     => getenv('MAIL_FROM')        ?: 'noreply@localhost',
        'from_name'      => getenv('MAIL_FROM_NAME')   ?: 'TOTPVault',
        'mailersend_key' => getenv('MAILERSEND_KEY')   ?: '',
    ],
    'auth' => [
        'admin_emails'     => array_values(array_filter(array_map('trim', explode(',', strtolower(getenv('ADMIN_EMAILS') ?: getenv('INITIAL_ADMIN_EMAILS') ?: ''))))),
        'allowed_emails'   => array_values(array_filter(array_map('trim', explode(',', strtolower(getenv('ALLOWED_EMAILS') ?: ''))))),
        'allowed_domains'  => array_values(array_filter(array_map('trim', explode(',', strtolower(getenv('ALLOWED_DOMAINS') ?: ''))))),
        'allow_magic_link' => filter_var(getenv('ALLOW_MAGIC_LINK') ?: 'false', FILTER_VALIDATE_BOOLEAN),
    ],
    'oauth' => [
        'google' => [
            'client_id'     => getenv('GOOGLE_CLIENT_ID')     ?: '',
            'client_secret' => getenv('GOOGLE_CLIENT_SECRET') ?: '',
            'redirect_uri'  => (getenv('APP_URL') ?: 'http://localhost:8000') . '/auth/callback/google',
            'auth_url'      => 'https://accounts.google.com/o/oauth2/v2/auth',
            'token_url'     => 'https://oauth2.googleapis.com/token',
            'userinfo_url'  => 'https://www.googleapis.com/oauth2/v3/userinfo',
            'scope'         => 'openid email profile',
        ],
        'microsoft' => [
            'client_id'     => getenv('MICROSOFT_CLIENT_ID')     ?: '',
            'client_secret' => getenv('MICROSOFT_CLIENT_SECRET') ?: '',
            'redirect_uri'  => (getenv('APP_URL') ?: 'http://localhost:8000') . '/auth/callback/microsoft',
            'auth_url'      => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
            'token_url'     => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
            'userinfo_url'  => 'https://graph.microsoft.com/v1.0/me',
            'scope'         => 'openid email profile User.Read',
        ],
        'github' => [
            'client_id'     => getenv('GITHUB_CLIENT_ID')     ?: '',
            'client_secret' => getenv('GITHUB_CLIENT_SECRET') ?: '',
            'redirect_uri'  => (getenv('APP_URL') ?: 'http://localhost:8000') . '/auth/callback/github',
            'auth_url'      => 'https://github.com/login/oauth/authorize',
            'token_url'     => 'https://github.com/login/oauth/access_token',
            'userinfo_url'  => 'https://api.github.com/user',
            'scope'         => 'read:user user:email',
        ],
        'keycloak' => [
            'client_id'     => getenv('KEYCLOAK_CLIENT_ID')     ?: '',
            'client_secret' => getenv('KEYCLOAK_CLIENT_SECRET') ?: '',
            'base_url'      => rtrim(getenv('KEYCLOAK_BASE_URL') ?: '', '/'),
            'internal_base_url' => rtrim(getenv('KEYCLOAK_INTERNAL_BASE_URL') ?: '', '/'),
            'realm'         => getenv('KEYCLOAK_REALM') ?: '',
            'redirect_uri'  => getenv('KEYCLOAK_REDIRECT_URI') ?: ((rtrim(getenv('APP_URL') ?: 'http://localhost:8000', '/')) . '/auth/callback/keycloak'),
            'scope'         => getenv('KEYCLOAK_SCOPE') ?: 'openid email profile',
        ],
    ],
    'session' => [
        'lifetime'    => (int)(getenv('SESSION_LIFETIME') ?: (86400 * 30)),
        'cookie_name' => getenv('SESSION_COOKIE_NAME') ?: 'totpvault_session',
    ],
];
