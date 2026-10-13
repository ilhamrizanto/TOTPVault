<?php
// tools/health-check.php — Comprehensive Production Medical Checkup Suite for TOTPVault
declare(strict_types=1);

define('CLI_MODE', php_sapi_name() === 'cli');

class MedicalCheckup {
    private array $results = [];
    private int $passed = 0;
    private int $failed = 0;
    private int $warnings = 0;

    public function run(): array {
        $this->printHeader("🩺 TOTPVault Production Medical Checkup & Diagnostic Suite");

        $this->section("1. PHP Runtime & Extensions", function() {
            $this->assert('PHP Version >= 8.1 (Current: ' . PHP_VERSION . ')', version_compare(PHP_VERSION, '8.1.0', '>='));
            $this->assert('Extension: openssl (AES-256-GCM)', extension_loaded('openssl') && in_array('aes-256-gcm', openssl_get_cipher_methods(), true));
            $this->assert('Extension: pdo', extension_loaded('pdo'));
            $this->assert('Extension: pdo_mysql', extension_loaded('pdo_mysql'));
            $this->assert('Extension: json', extension_loaded('json'));
            $this->assert('Extension: mbstring', extension_loaded('mbstring'));
            $this->assert('Extension: curl', extension_loaded('curl'));
        });

        $this->section("2. Configuration & Cryptography", function() {
            $baseDir = dirname(__DIR__);
            $envPath = $baseDir . '/.env';
            $this->assert('.env file exists', file_exists($envPath));

            require_once $baseDir . '/src/Crypto.php';
            $cfg = require $baseDir . '/config/config.php';

            $encKey = (string)($cfg['encryption_key'] ?? '');
            $this->assert('ENCRYPTION_KEY is set and non-empty', !empty($encKey));
            $this->assert('ENCRYPTION_KEY is at least 16 characters', strlen($encKey) >= 16);

            $isDev = ($cfg['app_env'] ?? 'production') === 'development';
            if (!$isDev) {
                $this->assert('ENCRYPTION_KEY is not default test key in production', $encKey !== 'ThisIsA32ByteKeyForLocalTesting!');
            } else {
                $this->warn('Running in development mode (APP_ENV=development)');
            }

            // Test Crypto roundtrip
            $sampleSecret = 'JBSWY3DPEHPK3PXP';
            $encrypted = Crypto::encrypt($sampleSecret);
            $decrypted = Crypto::decrypt($encrypted);
            $this->assert('Crypto::encrypt / decrypt roundtrip matches exactly', $sampleSecret === $decrypted);

            // Test Tamper resistance
            $replaceChar = ($encrypted[14] ?? '') === 'X' ? 'Y' : 'X';
            $tampered = substr_replace($encrypted, $replaceChar, 14, 1);
            $tamperCaught = false;
            try {
                Crypto::decrypt($tampered);
            } catch (\Throwable $e) {
                $tamperCaught = true;
            }
            $this->assert('Crypto::decrypt detects tampering and rejects payload', $tamperCaught);
        });

        $this->section("3. RFC 6238 TOTP Engine", function() {
            $baseDir = dirname(__DIR__);
            require_once $baseDir . '/src/TOTP.php';

            // Standard RFC 6238 test: secret '12345678901234567890' in Base32 = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ'
            // At timestamp 59: 94287082 (for 8 digits) or 287082 (6 digits)
            $rfcBase32 = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';
            $code6 = TOTP::generate($rfcBase32, 'SHA1', 6, 30, 59);
            $this->assert('RFC 6238 Test Vector: timestamp 59 produces 287082', $code6 === '287082');

            // Test generateSecret produces valid Base32 string
            $newSec = TOTP::generateSecret(32);
            $this->assert('TOTP::generateSecret produces 32-char Base32 string', strlen($newSec) === 32 && preg_match('/^[A-Z2-7]+$/', $newSec) === 1);
        });

        $this->section("4. Database Connectivity & Schema Integrity", function() {
            $baseDir = dirname(__DIR__);
            require_once $baseDir . '/src/Database.php';

            $start = microtime(true);
            $db = Database::getInstance();
            $stmt = $db->query('SELECT 1');
            $latency = round((microtime(true) - $start) * 1000, 2);
            $this->assert("Database responds (latency: {$latency}ms)", $stmt->fetchColumn() == 1);

            $tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
            $requiredTables = ['users', 'otp_profiles', 'profile_shares', 'whitelist_users', 'oauth_identities', 'magic_links'];
            foreach ($requiredTables as $tbl) {
                $this->assert("Table exists: {$tbl}", in_array($tbl, $tables, true));
            }

            // Check orphan shares
            $orphans = (int)$db->query('SELECT COUNT(*) FROM profile_shares ps LEFT JOIN otp_profiles p ON p.id = ps.profile_id WHERE p.id IS NULL')->fetchColumn();
            $this->assert('Zero orphan records in profile_shares', $orphans === 0);

            // Check foreign keys
            $fks = $db->query("SELECT CONSTRAINT_NAME, TABLE_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);
            $fkNames = array_column($fks, 'CONSTRAINT_NAME');
            $this->assert('Foreign Key: fk_otp_profiles_user active', in_array('fk_otp_profiles_user', $fkNames, true));
            $this->assert('Foreign Key: fk_profile_shares_profile active', in_array('fk_profile_shares_profile', $fkNames, true));
            $this->assert('Foreign Key: fk_profile_shares_shared_by active', in_array('fk_profile_shares_shared_by', $fkNames, true));
        });

        $this->section("5. Rate Limiting & DoS Engine", function() {
            $baseDir = dirname(__DIR__);
            require_once $baseDir . '/src/RateLimiter.php';

            $testKey = 'test_checkup_' . bin2hex(random_bytes(4));
            RateLimiter::clear($testKey);

            $this->assert('RateLimiter: initial status is clean', !RateLimiter::tooManyAttempts($testKey, 3, 10));
            RateLimiter::hit($testKey, 10);
            RateLimiter::hit($testKey, 10);
            $this->assert('RateLimiter: under limit (2 hits of 3)', !RateLimiter::tooManyAttempts($testKey, 3, 10));
            RateLimiter::hit($testKey, 10);
            $this->assert('RateLimiter: threshold enforced (3 hits of 3)', RateLimiter::tooManyAttempts($testKey, 3, 10));
            RateLimiter::clear($testKey);
            $this->assert('RateLimiter: clean up resets key', !RateLimiter::tooManyAttempts($testKey, 3, 10));
        });

        $this->section("6. Live HTTP Endpoints & Security Headers", function() {
            $healthUrl = 'http://localhost:8000/api/health';
            $ctx = stream_context_create(['http' => ['timeout' => 3, 'ignore_errors' => true]]);
            $body = @file_get_contents($healthUrl, false, $ctx);
            $headers = $http_response_header ?? [];

            $statusLine = $headers[0] ?? '';
            $this->assert('Health probe HTTP status 200 OK', str_contains($statusLine, '200'));

            if ($body) {
                $json = json_decode($body, true);
                $this->assert('Health probe returns {"status":"healthy"}', ($json['status'] ?? '') === 'healthy');
                $this->assert('Health probe reports database connected', ($json['database'] ?? '') === 'connected');
            }

            // Verify Security Headers
            $headerMap = [];
            foreach ($headers as $h) {
                if (strpos($h, ':') !== false) {
                    list($k, $v) = explode(':', $h, 2);
                    $headerMap[strtolower(trim($k))] = trim($v);
                }
            }
            $this->assert('Header X-Frame-Options: SAMEORIGIN', ($headerMap['x-frame-options'] ?? '') === 'SAMEORIGIN');
            $this->assert('Header X-Content-Type-Options: nosniff', ($headerMap['x-content-type-options'] ?? '') === 'nosniff');
            $this->assert('Header Referrer-Policy: strict-origin-when-cross-origin', ($headerMap['referrer-policy'] ?? '') === 'strict-origin-when-cross-origin');
        });

        $this->section("7. Backup & Recovery Infrastructure", function() {
            $baseDir = dirname(__DIR__);
            $backupScript = $baseDir . '/tools/backup-vault.php';
            $backupDir = $baseDir . '/backups';
            $htaccess = $backupDir . '/.htaccess';

            $this->assert('Backup tool exists (tools/backup-vault.php)', file_exists($backupScript));
            $this->assert('Backup storage directory exists', is_dir($backupDir));
            $this->assert('Backup directory has .htaccess protection', file_exists($htaccess) && str_contains(file_get_contents($htaccess), 'Deny from all'));

            // Execute backup to temporary test file
            $tmpFile = $backupDir . '/test_healthcheck_' . bin2hex(random_bytes(4)) . '.json.enc';
            $cmdBackup = sprintf('php %s --encrypt --out %s 2>&1', escapeshellarg($backupScript), escapeshellarg($tmpFile));
            exec($cmdBackup, $outBackup, $rcBackup);

            $this->assert('Backup tool executes successfully with --encrypt', $rcBackup === 0 && file_exists($tmpFile) && filesize($tmpFile) > 0);

            // Execute verification on the test file
            $cmdVerify = sprintf('php %s --verify %s 2>&1', escapeshellarg($backupScript), escapeshellarg($tmpFile));
            exec($cmdVerify, $outVerify, $rcVerify);

            $this->assert('Backup verification validates integrity and decrypts successfully', $rcVerify === 0);

            // Cleanup test backup
            if (file_exists($tmpFile)) {
                @unlink($tmpFile);
            }
        });

        $this->section("8. UI/UX, Multi-Auth & Client Modules", function() {
            $baseDir = dirname(__DIR__);

            $landingContent = (string)file_get_contents($baseDir . '/templates/landing.php');
            $this->assert('Landing page supports Google OAuth', str_contains($landingContent, 'auth/login/google'));
            $this->assert('Landing page supports Microsoft Entra ID OAuth', str_contains($landingContent, 'auth/login/microsoft'));
            $this->assert('Landing page enforces Enterprise SSO (Magic Link decommissioned)', str_contains($landingContent, 'auth/login/google') && !str_contains($landingContent, 'auth/magic-request'));

            // Check dashboard.php
            $dashContent = (string)file_get_contents($baseDir . '/templates/dashboard.php');
            $this->assert('Dashboard includes jsQR library', str_contains($dashContent, 'jsQR'));
            $this->assert('Dashboard includes handleQrImageUpload & parseOtpauthUri', str_contains($dashContent, 'handleQrImageUpload') && str_contains($dashContent, 'parseOtpauthUri'));
            $this->assert('Dashboard includes responsive mobile & desktop search targeting', str_contains($dashContent, 'mobile-search') && str_contains($dashContent, 'global-search'));

            // Check import-google-auth.php
            $importContent = (string)file_get_contents($baseDir . '/tools/import-google-auth.php');
            $this->assert('Import tool features glassmorphism mobile-bottom-nav', str_contains($importContent, 'mobile-bottom-nav') && str_contains($importContent, 'import-bottom-spacer'));
        });

        $this->printSummary();
        return [
            'passed'   => $this->passed,
            'failed'   => $this->failed,
            'warnings' => $this->warnings,
            'results'  => $this->results,
        ];
    }

    private function section(string $title, callable $fn): void {
        if (CLI_MODE) {
            echo "\n\033[1;36m▶ {$title}\033[0m\n";
        }
        $fn();
    }

    private function assert(string $label, bool $condition): void {
        if ($condition) {
            $this->passed++;
            $this->results[] = ['status' => 'PASS', 'label' => $label];
            if (CLI_MODE) {
                echo "  \033[32m✔ PASS\033[0m  {$label}\n";
            }
        } else {
            $this->failed++;
            $this->results[] = ['status' => 'FAIL', 'label' => $label];
            if (CLI_MODE) {
                echo "  \033[31m✖ FAIL\033[0m  {$label}\n";
            }
        }
    }

    private function warn(string $label): void {
        $this->warnings++;
        $this->results[] = ['status' => 'WARN', 'label' => $label];
        if (CLI_MODE) {
            echo "  \033[33m▲ WARN\033[0m  {$label}\n";
        }
    }

    private function printHeader(string $text): void {
        if (CLI_MODE) {
            echo "\n\033[1;32m====================================================================\033[0m\n";
            echo "\033[1;32m  {$text}\033[0m\n";
            echo "\033[1;32m====================================================================\033[0m\n";
        }
    }

    private function printSummary(): void {
        if (CLI_MODE) {
            echo "\n\033[1;32m====================================================================\033[0m\n";
            echo "  \033[1mDIAGNOSTIC SUMMARY\033[0m\n";
            echo "  Total Passed:   \033[32m{$this->passed}\033[0m\n";
            echo "  Total Failed:   \033[" . ($this->failed > 0 ? "31" : "32") . "m{$this->failed}\033[0m\n";
            echo "  Total Warnings: \033[33m{$this->warnings}\033[0m\n";
            if ($this->failed === 0) {
                echo "\n  \033[1;32m🎉 ALL SYSTEMS GO — TOTPVault IS 100% PRODUCTION READY!\033[0m\n";
            } else {
                echo "\n  \033[1;31m❌ SYSTEM DEFECTS DETECTED — PLEASE RESOLVE FAILED CHECKS.\033[0m\n";
            }
            echo "\033[1;32m====================================================================\033[0m\n\n";
        }
    }
}

// Execute checkup if run directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $checkup = new MedicalCheckup();
    $summary = $checkup->run();
    exit($summary['failed'] > 0 ? 1 : 0);
}
