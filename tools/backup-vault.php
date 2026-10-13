<?php
/**
 * tools/backup-vault.php — Automated Vault Backup Utility
 *
 * Usage:
 *   php tools/backup-vault.php [--encrypt]
 *
 * Saves a timestamped database backup to backups/ directory.
 * The backups/ directory is locked down with .htaccess.
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "Forbidden: CLI execution only.\n";
    exit(1);
}

$baseDir = dirname(__DIR__);
$cfg = require $baseDir . '/config/config.php';
require_once $baseDir . '/src/Database.php';
require_once $baseDir . '/src/Crypto.php';

$backupDir = $baseDir . '/backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0700, true);
}

// Ensure .htaccess inside backups prevents any HTTP access
$htaccess = $backupDir . '/.htaccess';
if (!file_exists($htaccess)) {
    file_put_contents($htaccess, "Require all denied\nDeny from all\n");
}

$timestamp = date('Y-m-d_H-i-s');
$filename  = "totpvault-backup-{$timestamp}.json";
$destPath  = "{$backupDir}/{$filename}";

echo "======================================================\n";
echo "  📦 TOTPVault Automated Database Backup Utility\n";
echo "======================================================\n\n";

if (in_array('--verify', $argv, true)) {
    $idx = array_search('--verify', $argv, true);
    $targetFile = $argv[$idx + 1] ?? '';
    if (!$targetFile || !file_exists($targetFile)) {
        echo "  ❌ File not found: {$targetFile}\n";
        exit(1);
    }
    try {
        $raw = file_get_contents($targetFile);
        if (str_ends_with($targetFile, '.enc')) {
            echo "  🔓 Decrypting with master ENCRYPTION_KEY...\n";
            $raw = Crypto::decrypt($raw);
        }
        $data = json_decode($raw, true);
        if (!$data || empty($data['tables'])) {
            echo "  ❌ Corrupt backup payload!\n";
            exit(1);
        }
        echo "  ✅ Backup Verified OK!\n";
        echo "  📅 Timestamp: " . ($data['timestamp'] ?? 'unknown') . "\n";
        foreach ($data['tables'] as $tbl => $rows) {
            echo "  ✔ Table {$tbl}: " . count($rows) . " records verified\n";
        }
        echo "======================================================\n";
        exit(0);
    } catch (Exception $e) {
        echo "  ❌ Verification failed: " . $e->getMessage() . "\n";
        exit(1);
    }
}

try {
    $pdo = Database::getInstance();

    $tables = [
        'users',
        'otp_profiles',
        'profile_shares',
        'whitelist_users',
        'oauth_identities',
        'magic_links'
    ];

    $dump = [
        'timestamp'   => date('c'),
        'app_version' => '2.4',
        'environment' => $cfg['app_env'] ?? 'production',
        'tables'      => [],
    ];

    foreach ($tables as $tbl) {
        $stmt = $pdo->query("SELECT * FROM `{$tbl}`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $dump['tables'][$tbl] = $rows;
        echo "  ✔ Dumped table: {$tbl} (" . count($rows) . " rows)\n";
    }

    $json = json_encode($dump, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    $isEncrypt = in_array('--encrypt', $argv, true);

    if (in_array('--out', $argv, true)) {
        $outIdx = array_search('--out', $argv, true);
        if (!empty($argv[$outIdx + 1])) {
            $destPath = $argv[$outIdx + 1];
        }
    } else {
        if ($isEncrypt && !str_ends_with($destPath, '.enc')) {
            $destPath .= '.enc';
        }
    }

    if ($isEncrypt) {
        $encrypted = Crypto::encrypt($json);
        file_put_contents($destPath, $encrypted);
        echo "\n  🔒 Backup encrypted with master ENCRYPTION_KEY\n";
    } else {
        file_put_contents($destPath, $json);
    }

    $size = filesize($destPath);
    echo "\n  ✅ Backup saved successfully!\n";
    echo "  📁 File: {$destPath} ({$size} bytes)\n";
    echo "======================================================\n";
    exit(0);

} catch (Exception $e) {
    echo "  ❌ Backup failed: " . $e->getMessage() . "\n";
    exit(1);
}
