<?php
// tools/init-db.php — Database Schema Initialiser & Admin Seeder
// Usage: php tools/init-db.php
declare(strict_types=1);

define('BASE_DIR', dirname(__DIR__));

// Load configuration (which loads .env or Docker environment)
$cfg = require BASE_DIR . '/config/config.php';
$dbCfg = $cfg['db'] ?? [];

$host     = $dbCfg['host'] ?? '127.0.0.1';
$port     = (int)($dbCfg['port'] ?? 3306);
$dbname   = $dbCfg['dbname'] ?? 'totpvault';
$user     = $dbCfg['user'] ?? 'root';
$password = $dbCfg['password'] ?? '';
$charset  = $dbCfg['charset'] ?? 'utf8mb4';

echo "====================================================\n";
echo "  TOTPVault Database Initialiser & Admin Seeder\n";
echo "====================================================\n";
echo "[INFO] Target Database: {$dbname} on {$host}:{$port}\n";

// ── 1. Connect to MySQL with retry loop (for Docker cold boots) ─────────────
$maxRetries = 30;
$retryDelay = 1;
$pdo = null;

for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
    try {
        // First try connecting directly to the target database
        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        echo "[OK] Connected to database '{$dbname}'.\n";
        break;
    } catch (\PDOException $e) {
        // If the database itself doesn't exist yet, connect to server root and create it
        if ($e->getCode() == 1049 || str_contains($e->getMessage(), 'Unknown database')) {
            try {
                echo "[INFO] Database '{$dbname}' does not exist. Creating it now...\n";
                $rootDsn = "mysql:host={$host};port={$port};charset={$charset}";
                $rootPdo = new PDO($rootDsn, $user, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);
                $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                echo "[OK] Database '{$dbname}' created successfully.\n";

                // Connect to newly created database
                $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
                $pdo = new PDO($dsn, $user, $password, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                break;
            } catch (\Throwable $createErr) {
                // Continue retry if server is still starting
            }
        }

        if ($attempt < $maxRetries) {
            echo "[WAIT] Database not ready yet (attempt {$attempt}/{$maxRetries}). Retrying in {$retryDelay}s...\n";
            sleep($retryDelay);
        } else {
            echo "[ERROR] Failed to connect to database after {$maxRetries} attempts: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
}

if (!$pdo) {
    echo "[FATAL] Unable to establish database connection.\n";
    exit(1);
}

// ── 2. Run schema.sql if tables are missing ──────────────────────────────────
$schemaFile = BASE_DIR . '/schema.sql';
if (!file_exists($schemaFile)) {
    echo "[ERROR] Schema file not found at: {$schemaFile}\n";
    exit(1);
}

$schemaSql = (string)file_get_contents($schemaFile);

// Execute schema creation statements
try {
    // Split on semicolons or execute multi-statement
    $pdo->exec($schemaSql);
    echo "[OK] Database schema verified & synchronised.\n";
} catch (\Throwable $e) {
    echo "[WARN] Schema batch execution note: " . $e->getMessage() . "\n";
}

// ── 3. Seed Administrators from ENV ──────────────────────────────────────────
$adminEmails = $cfg['auth']['admin_emails'] ?? [];

// Fallback: check getenv directly
if (empty($adminEmails)) {
    $rawAdmin = getenv('ADMIN_EMAILS') ?: getenv('INITIAL_ADMIN_EMAILS') ?: '';
    if (!empty($rawAdmin)) {
        $adminEmails = array_values(array_filter(array_map('trim', explode(',', strtolower($rawAdmin)))));
    }
}

if (!empty($adminEmails)) {
    echo "[INFO] Processing " . count($adminEmails) . " administrator account(s) from environment:\n";

    $stmtUpsert = $pdo->prepare("
        INSERT INTO whitelist_users (email, name, role_label, is_admin, created_by_email)
        VALUES (?, ?, 'Administrator', 1, 'SYSTEM_ENV_SEED')
        ON DUPLICATE KEY UPDATE is_admin = 1, role_label = 'Administrator'
    ");

    foreach ($adminEmails as $email) {
        $email = strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "  [SKIP] Invalid email format: '{$email}'\n";
            continue;
        }

        $name = ucfirst(explode('@', $email)[0]);
        $stmtUpsert->execute([$email, $name]);
        echo "  [ADMIN SEEDED] {$email} (Role: Administrator, Status: Active)\n";
    }
} else {
    echo "[INFO] No ADMIN_EMAILS specified in environment. Skipping admin seeding.\n";
}

// ── 4. Seed Regular Members from ALLOWED_EMAILS (if any) ─────────────────────
$memberEmails = $cfg['auth']['allowed_emails'] ?? [];
if (empty($memberEmails)) {
    $rawAllowed = getenv('ALLOWED_EMAILS') ?: '';
    if (!empty($rawAllowed)) {
        $memberEmails = array_values(array_filter(array_map('trim', explode(',', strtolower($rawAllowed)))));
    }
}

if (!empty($memberEmails)) {
    $stmtMember = $pdo->prepare("
        INSERT IGNORE INTO whitelist_users (email, name, role_label, is_admin, created_by_email)
        VALUES (?, ?, 'Member', 0, 'SYSTEM_ENV_SEED')
    ");

    foreach ($memberEmails as $email) {
        $email = strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) continue;
        if (in_array($email, $adminEmails, true)) continue; // already admin

        $name = ucfirst(explode('@', $email)[0]);
        $stmtMember->execute([$email, $name]);
        echo "  [MEMBER SEEDED] {$email} (Role: Member)\n";
    }
}

// ── 5. Summary Report ────────────────────────────────────────────────────────
$totalWhitelisted = (int)$pdo->query("SELECT COUNT(*) FROM whitelist_users")->fetchColumn();
$totalAdmins      = (int)$pdo->query("SELECT COUNT(*) FROM whitelist_users WHERE is_admin = 1")->fetchColumn();

echo "----------------------------------------------------\n";
echo "[SUCCESS] Database is fully initialised & ready!\n";
echo "          Total Whitelisted Accounts: {$totalWhitelisted}\n";
echo "          Total Active Administrators: {$totalAdmins}\n";
echo "====================================================\n";
exit(0);
