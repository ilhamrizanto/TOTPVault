<?php
// src/Auth.php

class Auth {
    private PDO $db;
    private array $cfg;

    public function __construct() {
        $this->db  = Database::getInstance();
        $this->cfg = require __DIR__ . '/../config/config.php';
    }

    // ── Session management ─────────────────────────────────────────────────

    public function startSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            $cookieName = $this->cfg['session']['cookie_name'];
            session_name($cookieName);
            $isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
                || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
                || str_starts_with($this->cfg['app_url'] ?? '', 'https://');
            session_set_cookie_params([
                'lifetime' => $this->cfg['session']['lifetime'],
                'path'     => '/',
                'secure'   => $isHttps,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    public function currentUser(): ?array {
        $this->startSession();
        if (empty($_SESSION['user_id'])) return null;
        $stmt = $this->db->prepare('
            SELECT u.*, COALESCE(w.is_admin, 0) AS is_admin, w.role_label
            FROM users u
            LEFT JOIN whitelist_users w ON LOWER(w.email) = LOWER(u.email)
            WHERE u.id = ?
        ');
        $stmt->execute([$_SESSION['user_id']]);
        $u = $stmt->fetch();
        if (!$u) return null;

        // Security check: Verify that the user is still in the active whitelist
        if (!$this->isEmailAllowed((string)$u['email'])) {
            $this->logout();
            return null;
        }

        $u['is_admin'] = (int)($u['is_admin'] ?? 0);
        return $u;
    }

    public function requireLogin(): array {
        $user = $this->currentUser();
        if (!$user) {
            header('Location: /');
            exit;
        }
        return $user;
    }

    public function loginUser(int $userId): void {
        $this->startSession();
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
    }

    public function logout(): void {
        $this->startSession();
        session_destroy();
        setcookie($this->cfg['session']['cookie_name'], '', time() - 3600, '/');
    }

    // ── User upsert ────────────────────────────────────────────────────────

    public function findOrCreateUser(array $data): array {
        // $data: email, name, avatar_url, provider, provider_id
        $provider   = $this->normalizeProviderName($data['provider'] ?? '');
        $providerId = (string)($data['provider_id'] ?? '');
        $email      = strtolower(trim((string)($data['email'] ?? '')));

        if ($providerId === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('OAuth provider returned incomplete user information.');
        }

        if (!$this->isEmailAllowed($email)) {
            throw new RuntimeException("Akses Ditolak: Akun ({$email}) tidak terdaftar dalam whitelist sistem. Hubungi Administrator untuk didaftarkan.");
        }

        $this->db->beginTransaction();
        try {
            $user = $this->findUserByOAuthIdentity($provider, $providerId)
                ?? $this->findUserByLegacyProviderColumn($provider, $providerId)
                ?? $this->findByEmail($email);

            if ($user) {
                $stmt = $this->db->prepare(
                    'UPDATE users SET name = COALESCE(?, name),
                     avatar_url = COALESCE(?, avatar_url), updated_at = NOW()
                     WHERE id = ?'
                );
                $stmt->execute([$data['name'] ?? null, $data['avatar_url'] ?? null, $user['id']]);
                $userId = (int)$user['id'];
            } else {
                $stmt = $this->db->prepare(
                    'INSERT INTO users (email, name, avatar_url) VALUES (?, ?, ?)'
                );
                $stmt->execute([$email, $data['name'] ?? null, $data['avatar_url'] ?? null]);
                $userId = (int)$this->db->lastInsertId();
                $this->resolvePendingShares($email, $userId);
            }

            $this->upsertOAuthIdentity($userId, $provider, $providerId);
            $this->updateLegacyProviderColumn($userId, $provider, $providerId);

            $this->db->commit();
            $savedUser = $this->findById($userId);
            if (!$savedUser) {
                throw new RuntimeException('Failed to load authenticated user.');
            }
            return $savedUser;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    private function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Check whether an email is allowed to access this vault.
     * Evaluates allowed_emails, allowed_domains, and pre-registered users.
     */
    public function isEmailAllowed(string $email): bool {
        $email = strtolower(trim($email));
        $allowedEmails  = $this->cfg['auth']['allowed_emails'] ?? [];
        $allowedDomains = $this->cfg['auth']['allowed_domains'] ?? [];

        // 1. Cek tabel database whitelist_users (Daftar Otorisasi Akun Internal)
        if ($this->tableExists('whitelist_users')) {
            $hasEntries = (int)$this->db->query('SELECT COUNT(*) FROM whitelist_users')->fetchColumn() > 0;
            if ($hasEntries) {
                $stmt = $this->db->prepare('SELECT COUNT(*) FROM whitelist_users WHERE LOWER(email) = ?');
                $stmt->execute([$email]);
                if ((int)$stmt->fetchColumn() > 0) {
                    return true;
                }
                // Cek juga fallback dari .env jika ada
                if (in_array($email, $allowedEmails, true)) {
                    return true;
                }
                $domain = substr(strrchr($email, "@"), 1);
                if ($domain && in_array(strtolower($domain), $allowedDomains, true)) {
                    return true;
                }
                // Tidak ada di whitelist_users maupun .env -> TOLAK!
                return false;
            }
        }

        // 2. Jika whitelist_users belum ada/kosong, cek konfigurasi .env
        if (empty($allowedEmails) && empty($allowedDomains)) {
            return true;
        }

        if (in_array($email, $allowedEmails, true)) {
            return true;
        }

        $domain = substr(strrchr($email, "@"), 1);
        if ($domain && in_array(strtolower($domain), $allowedDomains, true)) {
            return true;
        }

        return false;
    }

    private function findUserByOAuthIdentity(string $provider, string $providerId): ?array {
        if (!$this->tableExists('oauth_identities')) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT u.* FROM users u
             INNER JOIN oauth_identities oi ON oi.user_id = u.id
             WHERE oi.provider = ? AND oi.provider_id = ?
             LIMIT 1'
        );
        $stmt->execute([$provider, $providerId]);
        return $stmt->fetch() ?: null;
    }

    private function findUserByLegacyProviderColumn(string $provider, string $providerId): ?array {
        $column = $this->legacyProviderColumn($provider);
        if ($column === null) {
            return null;
        }

        $stmt = $this->db->prepare("SELECT * FROM users WHERE {$column} = ? LIMIT 1");
        $stmt->execute([$providerId]);
        return $stmt->fetch() ?: null;
    }

    private function upsertOAuthIdentity(int $userId, string $provider, string $providerId): void {
        if (!$this->tableExists('oauth_identities')) {
            return;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO oauth_identities (user_id, provider, provider_id)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE user_id = VALUES(user_id), updated_at = NOW()'
        );
        $stmt->execute([$userId, $provider, $providerId]);
    }

    private function updateLegacyProviderColumn(int $userId, string $provider, string $providerId): void {
        $column = $this->legacyProviderColumn($provider);
        if ($column === null) {
            return;
        }

        $stmt = $this->db->prepare("UPDATE users SET {$column} = ? WHERE id = ?");
        $stmt->execute([$providerId, $userId]);
    }

    private function normalizeProviderName(string $provider): string {
        $provider = strtolower(trim($provider));
        if (!preg_match('/^[a-z0-9_-]{1,64}$/', $provider)) {
            throw new RuntimeException('Invalid OAuth provider name.');
        }
        return $provider;
    }

    private function legacyProviderColumn(string $provider): ?string {
        $legacyColumns = [
            'google'    => 'google_id',
            'microsoft' => 'microsoft_id',
            'github'    => 'github_id',
        ];
        $column = $legacyColumns[$provider] ?? null;
        if ($column === null || !$this->columnExists('users', $column)) {
            return null;
        }
        return $column;
    }

    private function tableExists(string $table): bool {
        static $cache = [];
        if (!array_key_exists($table, $cache)) {
            $stmt = $this->db->prepare(
                'SELECT COUNT(*) FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
            );
            $stmt->execute([$table]);
            $cache[$table] = (int)$stmt->fetchColumn() > 0;
        }
        return $cache[$table];
    }

    private function columnExists(string $table, string $column): bool {
        static $cache = [];
        $key = "{$table}.{$column}";
        if (!array_key_exists($key, $cache)) {
            $stmt = $this->db->prepare(
                'SELECT COUNT(*) FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
            );
            $stmt->execute([$table, $column]);
            $cache[$key] = (int)$stmt->fetchColumn() > 0;
        }
        return $cache[$key];
    }

    private function resolvePendingShares(string $email, int $userId): void {
        $stmt = $this->db->prepare(
            'UPDATE profile_shares SET shared_with_user_id = ? WHERE shared_with_email = ? AND shared_with_user_id IS NULL'
        );
        $stmt->execute([$userId, $email]);
    }
}
