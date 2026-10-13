<?php
// public/index.php — Front controller / router
declare(strict_types=1);

 

$uri = strtok(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '?');



define('BASE_DIR', __DIR__);

// Load application config
$cfg = require BASE_DIR . '/config/config.php';
$isDev = ($cfg['app_env'] ?? 'production') === 'development';

// Safe error reporting: never leak sensitive traces to client in production
error_reporting(E_ALL);
ini_set('display_errors', $isDev ? '1' : '0');
ini_set('log_errors', '1');

// Global Exception Handler
set_exception_handler(function (\Throwable $e) use ($isDev): void {
    error_log(sprintf('[TOTPVault] %s in %s:%d', $e->getMessage(), $e->getFile(), $e->getLine()));
    $isApi = str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/');
    if ($isApi) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => $isDev ? $e->getMessage() : 'Internal Server Error. Please contact your system administrator.'
        ]);
        exit;
    }
    http_response_code(500);
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>500 Server Error — TOTPVault</title><link rel="icon" type="image/png" href="/favicon.png"><meta name="viewport" content="width=device-width, initial-scale=1"><style>body{background:#050508;color:#f8fafc;font-family:system-ui,-apple-system,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:1rem;box-sizing:border-box}.box{background:#0c0d12;border:1px solid rgba(255,255,255,0.08);border-radius:20px;padding:2.5rem 2rem;max-width:440px;width:100%;text-align:center;box-shadow:0 20px 50px rgba(0,0,0,0.8)}.btn{display:inline-block;padding:.65rem 1.5rem;background:#10b981;color:#04100c;text-decoration:none;border-radius:9999px;font-weight:700;font-size:.875rem}</style></head><body><div class="box"><div style="width:52px;height:52px;border-radius:50%;background:rgba(239,68,68,0.12);color:#ef4444;display:inline-flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:bold;margin-bottom:1rem">!</div><h1 style="font-size:1.35rem;font-weight:800;margin:0 0 .5rem;color:#ffffff">500 Server Error</h1><p style="color:#94a3b8;font-size:.875rem;line-height:1.6;margin:0 0 1.5rem">' . ($isDev ? htmlspecialchars($e->getMessage()) : 'Terjadi kendala teknis pada server. Tim administrator telah diberitahu.') . '</p><a href="/tokens" class="btn">Kembali ke Dashboard</a></div></body></html>';
    exit;
});

// Standard HTTP Security Headers
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), camera=(), microphone=()');
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

require_once BASE_DIR . '/src/Database.php';
require_once BASE_DIR . '/src/Crypto.php';
require_once BASE_DIR . '/src/TOTP.php';
require_once BASE_DIR . '/src/Auth.php';
require_once BASE_DIR . '/src/OAuthP.php';
require_once BASE_DIR . '/src/Profile.php';
require_once BASE_DIR . '/src/MagicLink.php';
require_once BASE_DIR . '/src/RateLimiter.php';

// Start session securely
$cookieName = $cfg['session']['cookie_name'] ?? 'totpvault_session';
session_name($cookieName);
session_set_cookie_params([
    'lifetime' => $cfg['session']['lifetime'] ?? 86400 * 30,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
]);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helpers
function jsonResponse(mixed $data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function redirect(string $url): never {
    $base = rtrim((require __DIR__ . '/config/config.php')['app_url'], '/');
    if (str_starts_with($url, '/')) {
        $url = $base . $url;
    }
    header('Location: ' . $url);
    exit;
}

function view(string $tpl, array $vars = []): never {
    extract($vars);
    require BASE_DIR . '/templates/' . $tpl . '.php';
    exit;
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function verify_csrf(): void {
    $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
        jsonResponse(['error' => 'Invalid CSRF token'], 403);
    }
}

function check_rate_limit(string $key, int $maxAttempts, int $decaySeconds = 60): void {
    if (RateLimiter::tooManyAttempts($key, $maxAttempts, $decaySeconds)) {
        $retryAfter = RateLimiter::availableIn($key);
        header('Retry-After: ' . $retryAfter);
        jsonResponse([
            'error' => 'Terlalu banyak permintaan (Rate limit exceeded). Silakan tunggu ' . $retryAfter . ' detik.'
        ], 429);
    }
    RateLimiter::hit($key, $decaySeconds);
}

// ── Routing ────────────────────────────────────────────────────────────────

$method = $_SERVER['REQUEST_METHOD'];
$uri    = strtok(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '?');
$auth   = new Auth();
$user   = $auth->currentUser();

// ── Public routes ──────────────────────────────────────────────────────────

// ── Health Check (Public DevOps & Monitoring Probe) ────────────────────────
if (($uri === '/api/health' || $uri === '/healthz' || $uri === '/health') && $method === 'GET') {
    $start = microtime(true);
    $status = 'healthy';
    $dbStatus = 'connected';
    $dbError = null;

    try {
        $db = Database::getInstance();
        $db->query('SELECT 1')->fetch();
    } catch (\Throwable $e) {
        $status = 'unhealthy';
        $dbStatus = 'disconnected';
        $dbError = $isDev ? $e->getMessage() : 'Database connection error';
    }

    $latency = round((microtime(true) - $start) * 1000, 2);
    $httpCode = ($status === 'healthy') ? 200 : 503;

    jsonResponse([
        'status'       => $status,
        'app'          => 'TOTPVault',
        'version'      => '2.4',
        'environment'  => $cfg['app_env'] ?? 'production',
        'database'     => $dbStatus,
        'db_error'     => $dbError,
        'latency_ms'   => $latency,
        'php_version'  => PHP_VERSION,
        'timestamp'    => time(),
    ], $httpCode);
}

if (($uri === '/' || $uri === '/landing') && $method === 'GET') {
    if ($user && $uri !== '/landing') redirect('/dashboard');
    view('landing', ['user' => $user]);
}

// ── Magic link: request ────────────────────────────────────────────────────

if ($uri === '/auth/magic-request' && $method === 'POST') {
    $clientIp = RateLimiter::clientIp();
    if (RateLimiter::tooManyAttempts('magic:' . $clientIp, 5, 300)) {
        $sec = RateLimiter::availableIn('magic:' . $clientIp);
        redirect('/?error=' . urlencode("Terlalu banyak permintaan login. Silakan coba lagi dalam {$sec} detik."));
    }
    RateLimiter::hit('magic:' . $clientIp, 300);

    $cfg = require __DIR__ . '/config/config.php';
    if (!($cfg['auth']['allow_magic_link'] ?? false)) {
        redirect('/?error=' . urlencode('Login via Magic Link dinonaktifkan. Silakan gunakan Single Sign-On (SSO).'));
    }
    $email = strtolower(trim($_POST['email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirect('/?error=' . urlencode('Please enter a valid email address.'));
    }
    try {
        $magicLink = new MagicLink();
        $url       = $magicLink->create($email);
        $subject   = 'Your sign-in link for OTP Vault';
        $body      = "Sign in to OTP Vault\n\nClick this link (expires in 15 minutes):\n{$url}\n\nIf you didn't request this, ignore this email.";
  
 
        $cfg = require __DIR__ . '/config/config.php';
        if (empty($cfg['mail']['mailersend_key']) || ($cfg['app_env'] ?? '') === 'development') {
            @file_put_contents(__DIR__ . '/sessions/last_magic_link.txt', $url);
            redirect($url);
        }

        if (!sendMail($email, $subject, $body)) {
            throw new RuntimeException('Email could not be sent. Check your MailerSend configuration.');
        }
    } catch (RuntimeException $e) {
        redirect('/?error=' . urlencode($e->getMessage()));
    }
    redirect('/?magic=sent&email=' . urlencode($email));
}

// ── Magic link: verify ─────────────────────────────────────────────────────

if ($uri === '/auth/magic' && $method === 'GET') {
    $token = $_GET['token'] ?? '';
    $email = $_GET['email'] ?? '';
    try {
        $magicLink     = new MagicLink();
        $verifiedEmail = $magicLink->verify($token, $email);
        $dbUser        = $magicLink->findOrCreateUser($verifiedEmail, $auth);
        $auth->loginUser($dbUser['id']);
        redirect('/dashboard');
    } catch (RuntimeException $e) {
        redirect('/?error=' . urlencode($e->getMessage()));
    }
}


// ── OAuth ──────────────────────────────────────────────────────────────────

if (preg_match('#^/auth/login/(\w+)$#', $uri, $m) && $method === 'GET') {
    $provider = $m[1];
    try {
        $oauth = new OAuthP($provider);
        redirect($oauth->getAuthUrl());
    } catch (Exception $e) {
        redirect('/?error=' . urlencode($e->getMessage()));
    }
}

if (preg_match('#^/auth/callback/(\w+)$#', $uri, $m) && $method === 'GET') {
    $provider = $m[1];
    try {
        if (!empty($_GET['error'])) throw new RuntimeException($_GET['error_description'] ?? $_GET['error']);
        $oauth    = new OAuthP($provider);
        $userData = $oauth->handleCallback($_GET['code'] ?? '', $_GET['state'] ?? '');
        $dbUser   = $auth->findOrCreateUser($userData);
        $auth->loginUser($dbUser['id']);
        redirect('/tokens');
    } catch (Exception $e) {
        $msg = $e->getMessage();
        if (str_contains($msg, 'tidak terdaftar dalam whitelist') || str_contains($msg, 'Akses Ditolak')) {
            preg_match('/Akun \(([^)]+)\)/', $msg, $emailMatches);
            $errEmail = $emailMatches[1] ?? '';
            view('unauthorized', [
                'user' => null,
                'email' => $errEmail,
                'message' => $msg
            ]);
        }
        redirect('/?error=' . urlencode($msg));
    }
}

if ($uri === '/auth/unauthorized' && $method === 'GET') {
    view('unauthorized', [
        'user' => null,
        'email' => $_GET['email'] ?? '',
        'message' => $_GET['message'] ?? 'Akun Anda belum terdaftar dalam whitelist sistem.'
    ]);
}


function sendMail(string $to, string $subject, string $body): bool {
	$config = require __DIR__ . '/config/config.php';
	$mail   = $config['mail'];
	
	
    $apiKey = $mail['mailersend_key'];
    $from   = $mail['from_email'];
    $fromName = $mail['from_name'] ;

    $payload = json_encode([
        'from'     => ['email' => $from, 'name' => $fromName],
        'to'       => [['email' => $to]],
        'subject'  => $subject,
        'text'     => $body,
    ]);

    $ch = curl_init('https://api.mailersend.com/v1/email');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_TIMEOUT        => 15,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error    = curl_error($ch);
    curl_close($ch);

    if ($error) {
        error_log("MailerSend cURL error: {$error}");
        return false;
    }
    if ($httpCode !== 202) {
        error_log("MailerSend failed: HTTP {$httpCode} — {$response}");
        return false;
    }
    return true;
}


if ($uri === '/auth/logout' && $method === 'GET') {
    $auth->logout();
    redirect('/');
}

if ($uri === '/auth/dev-login' && $method === 'GET') {
    $cfg = require __DIR__ . '/config/config.php';
    if (($cfg['app_env'] ?? '') === 'development') {
        $loginId = (int)($_GET['id'] ?? 1);
        $auth->loginUser($loginId);
        redirect('/tokens');
    }
}

// ── Require login from here ────────────────────────────────────────────────

if (!$user) {
    if ($method === 'GET') {
        redirect('/');
    }
    jsonResponse(['error' => 'Unauthorized'], 401);
}

// ── Dashboard ──────────────────────────────────────────────────────────────

if (($uri === '/dashboard' || $uri === '/tokens') && $method === 'GET') {
    $profileSvc = new Profile();
    $profiles   = $profileSvc->listForUser($user['id']);
    foreach ($profiles as &$p) {
        try {
            $otpData = $profileSvc->generateOTP((int)$p['id'], $user['id']);
            $p['initial_code'] = $otpData['code'];
            $p['initial_remaining'] = $otpData['remaining'];
        } catch (\Throwable $e) {
            $p['initial_code'] = '';
            $p['initial_remaining'] = 30;
        }
    }
    unset($p);
    view('dashboard', ['user' => $user, 'profiles' => $profiles, 'currentView' => 'tokens']);
}

if ($uri === '/whitelist' && $method === 'GET') {
    if (empty($user['is_admin'])) {
        redirect('/tokens');
    }
    $profileSvc = new Profile();
    $profiles   = $profileSvc->listForUser($user['id']);
    view('dashboard', ['user' => $user, 'profiles' => $profiles, 'currentView' => 'whitelist']);
}

if ($uri === '/access' && $method === 'GET') {
    if (empty($user['is_admin'])) {
        redirect('/tokens');
    }
    $profileSvc = new Profile();
    $profiles   = $profileSvc->listForUser($user['id']);
    view('dashboard', ['user' => $user, 'profiles' => $profiles, 'currentView' => 'access']);
}

//Import Tools
if (preg_match('#^/tools/([\w-]+)$#', $uri, $m)) {
    $tool = $m[1];
    $file = BASE_DIR . '/tools/' . $tool . '.php';
    if (!file_exists($file)) {
        http_response_code(404);
        view('404', ['user' => $user]);
    }
    require $file;
    exit;
}


// ── API: OTP ───────────────────────────────────────────────────────────────

if ((preg_match('#^/api/(?:otp|totp)(?:/(\d+))?$#', $uri, $m)) && $method === 'GET') {
    check_rate_limit('otp:uid_' . $user['id'], 180, 60);
    $profileId = (int)($m[1] ?? $_GET['id'] ?? 0);
    try {
        $profileSvc = new Profile();
        jsonResponse($profileSvc->generateOTP($profileId, $user['id']));
    } catch (Exception $e) {
        jsonResponse(['error' => $e->getMessage()], 403);
    }
}

// ── API: Profiles ──────────────────────────────────────────────────────────

if ($uri === '/api/profiles/batch' && $method === 'POST') {
    verify_csrf();
    check_rate_limit('prof_batch:uid_' . $user['id'], 30, 60);
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $items = (array)($data['profiles'] ?? []);
    if (empty($items)) {
        jsonResponse(['error' => 'No profiles provided in payload.'], 400);
    }
    try {
        $profileSvc = new Profile();
        $importedCount = $profileSvc->createBatch($user['id'], $items);
        jsonResponse(['success' => true, 'imported_count' => $importedCount]);
    } catch (Exception $e) {
        jsonResponse(['error' => $e->getMessage()], 400);
    }
}

if ($uri === '/api/profiles' && $method === 'POST') {
    verify_csrf();
    check_rate_limit('prof_create:uid_' . $user['id'], 60, 60);
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    try {
        $profileSvc = new Profile();
        $id = $profileSvc->create($user['id'], $data);
        jsonResponse(['success' => true, 'id' => $id]);
    } catch (Exception $e) {
        jsonResponse(['error' => $e->getMessage()], 400);
    }
}

if (preg_match('#^/api/profiles/(\d+)$#', $uri, $m)) {
    $profileId  = (int)$m[1];
    $profileSvc = new Profile();

    if ($method === 'PUT') {
        verify_csrf();
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $ok = $profileSvc->update($profileId, $user['id'], $data);
        jsonResponse(['success' => $ok]);
    }

    if ($method === 'DELETE') {
        verify_csrf();
        $ok = $profileSvc->delete($profileId, $user['id']);
        jsonResponse(['success' => $ok]);
    }

    if ($method === 'GET') {
        $profile = $profileSvc->get($profileId, $user['id']);
        if (!$profile) jsonResponse(['error' => 'Not found'], 404);
        unset($profile['secret_encrypted']);
        jsonResponse($profile);
    }
}

// ── API: Shares ────────────────────────────────────────────────────────────

if (preg_match('#^/api/profiles/(\d+)/shares$#', $uri, $m)) {
    $profileId  = (int)$m[1];
    $profileSvc = new Profile();

    if ($method === 'GET') {
        jsonResponse($profileSvc->getShares($profileId, $user['id']));
    }

    if ($method === 'POST') {
        verify_csrf();
        $data    = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $email   = trim(strtolower($data['email'] ?? ''));
        $canEdit = !empty($data['can_edit']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['error' => 'Invalid email address'], 400);
        }
        if ($email === strtolower($user['email'])) {
            jsonResponse(['error' => 'You cannot share with yourself'], 400);
        }
        $ok = $profileSvc->share($profileId, $user['id'], $email, $canEdit);
        jsonResponse(['success' => $ok]);
    }

    if ($method === 'DELETE') {
        verify_csrf();
        $data  = json_decode(file_get_contents('php://input'), true) ?? [];
        $email = trim(strtolower($data['email'] ?? ''));
        $ok    = $profileSvc->unshare($profileId, $user['id'], $email);
        jsonResponse(['success' => $ok]);
    }
}

// ── API: Export profiles as CSV ────────────────────────────────────────

if ($uri === '/api/export-profiles' && $method === 'GET') {
    try {
        check_rate_limit('export:uid_' . $user['id'], 10, 60);
        $profileSvc = new Profile();
        $rows = $profileSvc->exportForUser($user['id']);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="totp-export-' . date('Y-m-d') . '.csv"');
        header('Cache-Control: no-cache, no-store, must-revalidate');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['profile', 'seed', 'title', 'algorithm', 'digits', 'period']);
        foreach ($rows as $row) {
            fputcsv($out, [
                $row['profile'],
                $row['seed'],
                $row['title'],
                $row['algorithm'],
                $row['digits'],
                $row['period'],
            ]);
        }
        fclose($out);
        exit;
    } catch (Exception $e) {
        jsonResponse(['error' => $e->getMessage()], 500);
    }
}

// ── API: Generate secret ───────────────────────────────────────────────────

if ($uri === '/api/generate-secret' && $method === 'GET') {
    check_rate_limit('gensec:uid_' . $user['id'], 60, 60);
    jsonResponse(['secret' => TOTP::generateSecret()]);
}

// ── API: Whitelist Management ──────────────────────────────────────────────

if ($uri === '/api/whitelist') {
    if (empty($user['is_admin'])) {
        jsonResponse(['error' => 'Akses ditolak: Hanya Administrator yang berhak mengelola whitelist.'], 403);
    }
    $db = Database::getInstance();

    if ($method === 'GET') {
        $stmt = $db->query('SELECT id, email, name, role_label, is_admin, created_by_email, created_at FROM whitelist_users ORDER BY is_admin DESC, created_at ASC');
        jsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    if ($method === 'POST') {
        verify_csrf();
        $data    = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $email   = strtolower(trim($data['email'] ?? ''));
        $name    = trim($data['name'] ?? '');
        $isAdmin = !empty($data['is_admin']) ? 1 : 0;
        $role    = trim($data['role_label'] ?? ($isAdmin ? 'Administrator' : 'Pengguna Standar'));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['error' => 'Format email tidak valid'], 400);
        }

        try {
            $stmt = $db->prepare('INSERT INTO whitelist_users (email, name, role_label, is_admin, created_by_email) VALUES (?, ?, ?, ?, ?)
                                  ON DUPLICATE KEY UPDATE name = VALUES(name), role_label = VALUES(role_label), is_admin = VALUES(is_admin)');
            $stmt->execute([$email, $name, $role, $isAdmin, $user['email']]);
            jsonResponse(['success' => true, 'id' => (int)$db->lastInsertId()]);
        } catch (Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }
}

if (preg_match('#^/api/whitelist/(\d+)$#', $uri, $m) && $method === 'DELETE') {
    if (empty($user['is_admin'])) {
        jsonResponse(['error' => 'Akses ditolak: Hanya Administrator yang berhak menghapus akun dari whitelist.'], 403);
    }
    verify_csrf();
    $id = (int)$m[1];
    $db = Database::getInstance();

    $stmt = $db->prepare('SELECT email, is_admin FROM whitelist_users WHERE id = ?');
    $stmt->execute([$id]);
    $target = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($target && strtolower($target['email']) === strtolower($user['email'])) {
        jsonResponse(['error' => 'Anda tidak dapat menghapus akun Anda sendiri dari whitelist'], 400);
    }

    $stmt = $db->prepare('DELETE FROM whitelist_users WHERE id = ?');
    $stmt->execute([$id]);
    jsonResponse(['success' => true]);
}

// ── API: Access & Sharing Manager (Admin Only) ────────────────────────────

if (str_starts_with($uri, '/api/access')) {
    if (empty($user['is_admin'])) {
        jsonResponse(['error' => 'Akses ditolak: Hanya Administrator yang berhak mengelola hak akses.'], 403);
    }
    $profileSvc = new Profile();

    // 1. Overview metrics
    if ($uri === '/api/access/metrics' && $method === 'GET') {
        jsonResponse($profileSvc->getAccessOverviewMetrics());
    }

    // 2. User Access Matrix
    if ($uri === '/api/access/matrix' && $method === 'GET') {
        $email = strtolower(trim($_GET['email'] ?? ''));
        if (!$email) {
            jsonResponse(['error' => 'Parameter email wajib disertakan'], 400);
        }
        try {
            $matrix = $profileSvc->getUserAccessMatrix($email);
            jsonResponse($matrix);
        } catch (Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    // 2b. Token Access Matrix
    if ($uri === '/api/access/token-matrix' && $method === 'GET') {
        $profileId = (int)($_GET['profile_id'] ?? 0);
        if ($profileId <= 0) {
            jsonResponse(['error' => 'Parameter profile_id wajib disertakan'], 400);
        }
        try {
            $matrix = $profileSvc->getTokenAccessMatrix($profileId);
            jsonResponse($matrix);
        } catch (Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    // 2c. Sync Token Shares (Grant / Revoke users for a single token)
    if ($uri === '/api/access/sync-token' && $method === 'POST') {
        verify_csrf();
        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $profileId = (int)($data['profile_id'] ?? 0);
        $shares = (array)($data['shares'] ?? []);

        if ($profileId <= 0) {
            jsonResponse(['error' => 'Parameter profile_id tidak valid'], 400);
        }

        try {
            $res = $profileSvc->syncTokenShares((int)$user['id'], $profileId, $shares);
            jsonResponse($res);
        } catch (Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    // 3. Sync User Shares (Bulk Add / Update / Remove for a target user)
    if ($uri === '/api/access/sync' && $method === 'POST') {
        verify_csrf();
        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $email = strtolower(trim($data['email'] ?? ''));
        $shares = (array)($data['shares'] ?? []);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['error' => 'Format email tidak valid'], 400);
        }

        try {
            $res = $profileSvc->syncUserShares((int)$user['id'], $email, $shares);
            jsonResponse($res);
        } catch (Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    // 4. Revoke All Shares for User (Offboarding 1-click)
    if ($uri === '/api/access/revoke-all' && $method === 'POST') {
        verify_csrf();
        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $email = strtolower(trim($data['email'] ?? ''));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['error' => 'Format email tidak valid'], 400);
        }

        try {
            $count = $profileSvc->revokeAllUserShares($email);
            jsonResponse(['success' => true, 'revoked_count' => $count, 'email' => $email]);
        } catch (Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    // 5. All Active Shares Audit Table
    if ($uri === '/api/access/shares' && $method === 'GET') {
        jsonResponse($profileSvc->getAllActiveShares());
    }

    // 6. Bulk Revoke Shares by IDs
    if ($uri === '/api/access/bulk-revoke' && $method === 'POST') {
        verify_csrf();
        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $ids = (array)($data['share_ids'] ?? []);

        try {
            $count = $profileSvc->bulkRevokeShares($ids);
            jsonResponse(['success' => true, 'revoked_count' => $count]);
        } catch (Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    // 7. Get All Tokens for Multi-Share picker
    if ($uri === '/api/access/all-tokens' && $method === 'GET') {
        jsonResponse($profileSvc->getAllProfilesForAdmin());
    }

    // 8. Bulk Multi-Token to Multi-User Sharing
    if ($uri === '/api/access/bulk-multi-share' && $method === 'POST') {
        verify_csrf();
        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $profileIds = (array)($data['profile_ids'] ?? []);
        $emails = (array)($data['emails'] ?? []);
        $action = (string)($data['action'] ?? 'grant');
        $canEdit = !empty($data['can_edit']);

        try {
            $result = $profileSvc->bulkMultiShare((int)$user['id'], $profileIds, $emails, $action, $canEdit);
            jsonResponse($result);
        } catch (Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }
}

// ── 404 ────────────────────────────────────────────────────────────────────

http_response_code(404);
view('404', ['user' => $user]);
