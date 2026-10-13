<?php
// src/Database.php
declare(strict_types=1);

class Database {
    private static ?PDO $instance = null;
    private static bool $initialized = false;

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $cfg = require __DIR__ . '/../config/config.php';
            $db  = $cfg['db'];
            $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['dbname']};charset={$db['charset']}";
            self::$instance = new PDO($dsn, $db['user'], $db['password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);

            self::ensureInitialized(self::$instance, $cfg);
        }
        return self::$instance;
    }

    /**
     * Self-healing database bootstrap:
     * 1. Ensures tables exist (runs schema.sql if whitelist_users is missing).
     * 2. Auto-seeds/syncs any admin emails specified in ADMIN_EMAILS environment variable.
     */
    private static function ensureInitialized(PDO $pdo, array $cfg): void {
        if (self::$initialized) return;
        self::$initialized = true;

        try {
            // Check if core table exists
            $res = $pdo->query("SHOW TABLES LIKE 'whitelist_users'")->fetch();
            $tableExists = !empty($res);

            if (!$tableExists) {
                $schemaFile = dirname(__DIR__) . '/schema.sql';
                if (file_exists($schemaFile)) {
                    $pdo->exec((string)file_get_contents($schemaFile));
                }
            }

            // Synchronize admin emails from environment
            $adminEmails = $cfg['auth']['admin_emails'] ?? [];
            if (!empty($adminEmails)) {
                $stmt = $pdo->prepare("
                    INSERT INTO whitelist_users (email, name, role_label, is_admin, created_by_email)
                    VALUES (?, ?, 'Administrator', 1, 'SYSTEM_ENV')
                    ON DUPLICATE KEY UPDATE is_admin = 1, role_label = 'Administrator'
                ");
                foreach ($adminEmails as $email) {
                    $email = strtolower(trim($email));
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $name = ucfirst(explode('@', $email)[0]);
                        $stmt->execute([$email, $name]);
                    }
                }
            }
        } catch (\Throwable $e) {
            error_log('[Database::ensureInitialized] ' . $e->getMessage());
        }
    }
}
