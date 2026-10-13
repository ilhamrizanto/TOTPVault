<?php
// src/Profile.php

class Profile {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // ── CRUD ──────────────────────────────────────────────────────────────

    public function create(int $userId, array $data): int {
        $secret = strtoupper(preg_replace('/\s+/', '', $data['secret']));
        $stmt = $this->db->prepare(
            'INSERT INTO otp_profiles (user_id, name, issuer, secret_encrypted, algorithm, digits, period, color, icon, hide_code)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            trim($data['name']),
            trim($data['issuer'] ?? ''),
            Crypto::encrypt($secret),
            $data['algorithm'] ?? 'SHA1',
            (int)($data['digits'] ?? 6),
            (int)($data['period'] ?? 30),
            $data['color']     ?? '#10b981',
            $data['icon']      ?? 'fa-shield-halved',
            (int)($data['hide_code'] ?? 0),
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function createBatch(int $userId, array $items): int {
        if (empty($items)) return 0;

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO otp_profiles (user_id, name, issuer, secret_encrypted, algorithm, digits, period, color, icon, hide_code)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );

            $count = 0;
            foreach ($items as $data) {
                if (empty($data['name']) || empty($data['secret'])) continue;
                $secret = strtoupper(preg_replace('/\s+/', '', (string)$data['secret']));
                $stmt->execute([
                    $userId,
                    trim((string)$data['name']),
                    trim((string)($data['issuer'] ?? '')),
                    Crypto::encrypt($secret),
                    $data['algorithm'] ?? 'SHA1',
                    (int)($data['digits'] ?? 6),
                    (int)($data['period'] ?? 30),
                    $data['color']     ?? '#10b981',
                    $data['icon']      ?? 'fa-shield-halved',
                    (int)($data['hide_code'] ?? 0),
                ]);
                $count++;
            }

            $this->db->commit();
            return $count;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function update(int $profileId, int $userId, array $data): bool {
        if (!$this->canEdit($profileId, $userId)) return false;

        $fields = [];
        $params = [];

        foreach (['name', 'issuer', 'algorithm', 'color', 'icon'] as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "{$f} = ?";
                $params[] = $data[$f];
            }
        }
        foreach (['digits', 'period', 'hide_code'] as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "{$f} = ?";
                $params[] = (int)$data[$f];
            }
        }
        if (!empty($data['secret'])) {
            $fields[] = 'secret_encrypted = ?';
            $params[] = Crypto::encrypt(strtoupper(preg_replace('/\s+/', '', $data['secret'])));
        }

        if (empty($fields)) return true;
        $params[] = $profileId;

        $stmt = $this->db->prepare('UPDATE otp_profiles SET ' . implode(', ', $fields) . ' WHERE id = ?');
        $stmt->execute($params);
        return true;
    }

    public function delete(int $profileId, int $userId): bool {
        $this->db->beginTransaction();
        try {
            // Proactively remove any associated shares first
            $stmtShares = $this->db->prepare('DELETE FROM profile_shares WHERE profile_id = ?');
            $stmtShares->execute([$profileId]);

            $stmt = $this->db->prepare('DELETE FROM otp_profiles WHERE id = ? AND user_id = ?');
            $stmt->execute([$profileId, $userId]);
            $deleted = $stmt->rowCount() > 0;

            $this->db->commit();
            return $deleted;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function get(int $profileId, int $userId): ?array {
        $stmt = $this->db->prepare(
            'SELECT p.*, u.name AS owner_name, u.email AS owner_email,
                    IF(p.user_id = :uid, 1, 0) AS is_owner,
                    COALESCE(s.can_edit, IF(p.user_id = :uid2, 1, 0)) AS can_edit
             FROM otp_profiles p
             JOIN users u ON u.id = p.user_id
             LEFT JOIN profile_shares s ON s.profile_id = p.id AND s.shared_with_user_id = :uid3
             WHERE p.id = :pid AND (p.user_id = :uid4 OR s.shared_with_user_id = :uid5)'
        );
        $stmt->execute([':uid'=>$userId,':uid2'=>$userId,':uid3'=>$userId,':pid'=>$profileId,':uid4'=>$userId,':uid5'=>$userId]);
        return $stmt->fetch() ?: null;
    }

    public function listForUser(int $userId): array {
        $stmt = $this->db->prepare(
            'SELECT p.*, u.name AS owner_name, u.email AS owner_email,
                    IF(p.user_id = :uid, 1, 0) AS is_owner,
                    COALESCE(s.can_edit, IF(p.user_id = :uid2, 1, 0)) AS can_edit
             FROM otp_profiles p
             JOIN users u ON u.id = p.user_id
             LEFT JOIN profile_shares s ON s.profile_id = p.id AND s.shared_with_user_id = :uid3
             WHERE p.user_id = :uid4 OR s.shared_with_user_id = :uid5
             ORDER BY p.user_id = :uid6 DESC, p.name ASC'
        );
        $stmt->execute([':uid'=>$userId,':uid2'=>$userId,':uid3'=>$userId,':uid4'=>$userId,':uid5'=>$userId,':uid6'=>$userId]);
        return $stmt->fetchAll();
    }

    // ── Sharing ───────────────────────────────────────────────────────────

    public function share(int $profileId, int $ownerUserId, string $email, bool $canEdit = false): bool {
        $stmt = $this->db->prepare('SELECT id FROM otp_profiles WHERE id = ? AND user_id = ?');
        $stmt->execute([$profileId, $ownerUserId]);
        if (!$stmt->fetch()) return false;

        $stmt2 = $this->db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt2->execute([$email]);
        $targetUser = $stmt2->fetch();
        $targetUserId = $targetUser ? $targetUser['id'] : null;

        $stmt3 = $this->db->prepare(
            'INSERT INTO profile_shares (profile_id, shared_by_user_id, shared_with_email, shared_with_user_id, can_edit)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE shared_with_user_id = VALUES(shared_with_user_id), can_edit = VALUES(can_edit)'
        );
        $stmt3->execute([$profileId, $ownerUserId, $email, $targetUserId, $canEdit ? 1 : 0]);
        return true;
    }

    public function unshare(int $profileId, int $ownerUserId, string $email): bool {
        $stmt = $this->db->prepare(
            'DELETE ps FROM profile_shares ps
             JOIN otp_profiles p ON p.id = ps.profile_id
             WHERE ps.profile_id = ? AND p.user_id = ? AND ps.shared_with_email = ?'
        );
        $stmt->execute([$profileId, $ownerUserId, $email]);
        return $stmt->rowCount() > 0;
    }

    public function getShares(int $profileId, int $ownerUserId): array {
        $stmt = $this->db->prepare(
            'SELECT ps.*, u.name AS user_name, u.avatar_url
             FROM profile_shares ps
             LEFT JOIN users u ON u.id = ps.shared_with_user_id
             WHERE ps.profile_id = ? AND ps.shared_by_user_id = ?
             ORDER BY ps.created_at DESC'
        );
        $stmt->execute([$profileId, $ownerUserId]);
        return $stmt->fetchAll();
    }

    // ── Bulk & User-Centric Sharing Management (Admin) ────────────────────

    public function getUserAccessMatrix(string $targetEmail): array {
        $targetEmail = strtolower(trim($targetEmail));
        
        $stmtProfiles = $this->db->query(
            'SELECT p.id, p.name, p.issuer, p.icon, p.color, p.algorithm, p.digits, p.period, p.user_id,
                    u.name AS owner_name, u.email AS owner_email
             FROM otp_profiles p
             JOIN users u ON u.id = p.user_id
             ORDER BY p.issuer ASC, p.name ASC'
        );
        $profiles = $stmtProfiles->fetchAll(PDO::FETCH_ASSOC);

        $stmtShares = $this->db->prepare(
            'SELECT profile_id, can_edit, id AS share_id, created_at
             FROM profile_shares
             WHERE LOWER(shared_with_email) = ?'
        );
        $stmtShares->execute([$targetEmail]);
        $sharesMap = [];
        foreach ($stmtShares->fetchAll(PDO::FETCH_ASSOC) as $s) {
            $sharesMap[(int)$s['profile_id']] = $s;
        }

        $matrix = [];
        foreach ($profiles as $p) {
            $pid = (int)$p['id'];
            $isOwner = (strtolower($p['owner_email']) === $targetEmail);
            $isShared = isset($sharesMap[$pid]);
            $share = $sharesMap[$pid] ?? null;

            $matrix[] = [
                'profile_id'   => $pid,
                'name'         => $p['name'],
                'issuer'       => $p['issuer'],
                'icon'         => $p['icon'],
                'color'        => $p['color'],
                'owner_name'   => $p['owner_name'],
                'owner_email'  => $p['owner_email'],
                'is_owner'     => $isOwner,
                'is_shared'    => $isShared,
                'can_edit'     => $isOwner ? 1 : ($share ? (int)$share['can_edit'] : 0),
                'share_id'     => $share ? (int)$share['share_id'] : null,
                'created_at'   => $share ? $share['created_at'] : null,
            ];
        }

        return $matrix;
    }

    public function getTokenAccessMatrix(int $profileId): array {
        $stmtP = $this->db->prepare(
            'SELECT p.id, p.name, p.issuer, p.icon, p.color, p.algorithm, p.digits, p.period, p.user_id,
                    u.name AS owner_name, u.email AS owner_email
             FROM otp_profiles p
             JOIN users u ON u.id = p.user_id
             WHERE p.id = ?'
        );
        $stmtP->execute([$profileId]);
        $profile = $stmtP->fetch(PDO::FETCH_ASSOC);
        if (!$profile) {
            throw new InvalidArgumentException('Token tidak ditemukan.');
        }

        $stmtUsers = $this->db->query(
            'SELECT w.email, w.name, w.role_label, w.is_admin, u.id AS user_id, u.avatar_url
             FROM whitelist_users w
             LEFT JOIN users u ON LOWER(u.email) = LOWER(w.email)
             ORDER BY w.name ASC, w.email ASC'
        );
        $users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

        $stmtShares = $this->db->prepare(
            'SELECT shared_with_email, can_edit, id AS share_id, created_at
             FROM profile_shares
             WHERE profile_id = ?'
        );
        $stmtShares->execute([$profileId]);
        $sharesMap = [];
        foreach ($stmtShares->fetchAll(PDO::FETCH_ASSOC) as $s) {
            $sharesMap[strtolower($s['shared_with_email'])] = $s;
        }

        $matrixUsers = [];
        $ownerEmail = strtolower($profile['owner_email']);

        foreach ($users as $u) {
            $email = strtolower($u['email']);
            $isOwner = ($email === $ownerEmail);
            $share = $sharesMap[$email] ?? null;
            $hasAccess = $isOwner || ($share !== null);
            $canEdit = $isOwner ? true : ($share ? (bool)$share['can_edit'] : false);

            $matrixUsers[] = [
                'email'        => $u['email'],
                'name'         => $u['name'] ?: $u['email'],
                'role'         => !empty($u['is_admin']) ? 'admin' : 'member',
                'role_label'   => $u['role_label'] ?? 'Member',
                'user_id'      => $u['user_id'] ? (int)$u['user_id'] : null,
                'avatar_url'   => $u['avatar_url'] ?? null,
                'is_owner'     => $isOwner,
                'has_access'   => $hasAccess,
                'can_edit'     => $canEdit,
                'share_id'     => $share ? (int)$share['share_id'] : null,
                'created_at'   => $share ? $share['created_at'] : null,
            ];
        }

        return [
            'profile' => $profile,
            'users'   => $matrixUsers,
        ];
    }

    public function syncTokenShares(int $adminUserId, int $profileId, array $shares): array {
        $stmtP = $this->db->prepare('SELECT id, user_id, (SELECT email FROM users WHERE id = otp_profiles.user_id) AS owner_email FROM otp_profiles WHERE id = ?');
        $stmtP->execute([$profileId]);
        $prof = $stmtP->fetch(PDO::FETCH_ASSOC);
        if (!$prof) {
            throw new InvalidArgumentException('Token tidak ditemukan.');
        }
        $ownerEmail = strtolower($prof['owner_email'] ?? '');

        $usersAll = $this->db->query('SELECT id, LOWER(email) AS email FROM users')->fetchAll(PDO::FETCH_ASSOC);
        $userMap = [];
        foreach ($usersAll as $u) {
            $userMap[$u['email']] = (int)$u['id'];
        }

        $this->db->beginTransaction();
        try {
            $added = 0;
            $updated = 0;
            $removed = 0;

            foreach ($shares as $item) {
                $email = strtolower(trim($item['email'] ?? ''));
                if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) continue;
                if ($email === $ownerEmail) continue;

                $enabled = !empty($item['enabled']);
                $canEdit = !empty($item['can_edit']) ? 1 : 0;
                $targetUserId = $userMap[$email] ?? null;

                if ($enabled) {
                    $stmtUpsert = $this->db->prepare(
                        'INSERT INTO profile_shares (profile_id, shared_by_user_id, shared_with_email, shared_with_user_id, can_edit)
                         VALUES (?, ?, ?, ?, ?)
                         ON DUPLICATE KEY UPDATE shared_with_user_id = VALUES(shared_with_user_id), can_edit = VALUES(can_edit)'
                    );
                    $stmtUpsert->execute([$profileId, $adminUserId, $email, $targetUserId, $canEdit]);
                    if ($stmtUpsert->rowCount() === 1) {
                        $added++;
                    } elseif ($stmtUpsert->rowCount() === 2) {
                        $updated++;
                    }
                } else {
                    $stmtDel = $this->db->prepare('DELETE FROM profile_shares WHERE profile_id = ? AND LOWER(shared_with_email) = ?');
                    $stmtDel->execute([$profileId, $email]);
                    if ($stmtDel->rowCount() > 0) {
                        $removed++;
                    }
                }
            }

            $this->db->commit();
            return [
                'success'    => true,
                'profile_id' => $profileId,
                'added'      => $added,
                'updated'    => $updated,
                'removed'    => $removed,
            ];
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function syncUserShares(int $adminUserId, string $targetEmail, array $shares): array {
        $targetEmail = strtolower(trim($targetEmail));
        if (!filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Format email tidak valid.');
        }

        $stmtU = $this->db->prepare('SELECT id, name FROM users WHERE LOWER(email) = ?');
        $stmtU->execute([$targetEmail]);
        $targetUser = $stmtU->fetch(PDO::FETCH_ASSOC);
        $targetUserId = $targetUser ? (int)$targetUser['id'] : null;

        $this->db->beginTransaction();
        try {
            $added = 0;
            $updated = 0;
            $removed = 0;

            foreach ($shares as $item) {
                $profileId = (int)($item['profile_id'] ?? 0);
                if ($profileId <= 0) continue;

                $stmtCheck = $this->db->prepare('SELECT user_id, (SELECT email FROM users WHERE id = otp_profiles.user_id) AS owner_email FROM otp_profiles WHERE id = ?');
                $stmtCheck->execute([$profileId]);
                $prof = $stmtCheck->fetch(PDO::FETCH_ASSOC);
                if (!$prof) continue;

                if (strtolower($prof['owner_email'] ?? '') === $targetEmail) {
                    continue;
                }

                $enabled = !empty($item['enabled']);
                $canEdit = !empty($item['can_edit']) ? 1 : 0;

                if ($enabled) {
                    $stmtUpsert = $this->db->prepare(
                        'INSERT INTO profile_shares (profile_id, shared_by_user_id, shared_with_email, shared_with_user_id, can_edit)
                         VALUES (?, ?, ?, ?, ?)
                         ON DUPLICATE KEY UPDATE shared_with_user_id = VALUES(shared_with_user_id), can_edit = VALUES(can_edit)'
                    );
                    $stmtUpsert->execute([$profileId, $adminUserId, $targetEmail, $targetUserId, $canEdit]);
                    if ($stmtUpsert->rowCount() === 1) {
                        $added++;
                    } elseif ($stmtUpsert->rowCount() === 2) {
                        $updated++;
                    }
                } else {
                    $stmtDel = $this->db->prepare('DELETE FROM profile_shares WHERE profile_id = ? AND LOWER(shared_with_email) = ?');
                    $stmtDel->execute([$profileId, $targetEmail]);
                    if ($stmtDel->rowCount() > 0) {
                        $removed++;
                    }
                }
            }

            $this->db->commit();
            return [
                'success' => true,
                'target_email' => $targetEmail,
                'added' => $added,
                'updated' => $updated,
                'removed' => $removed,
            ];
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function revokeAllUserShares(string $targetEmail): int {
        $targetEmail = strtolower(trim($targetEmail));
        $stmt = $this->db->prepare('DELETE FROM profile_shares WHERE LOWER(shared_with_email) = ?');
        $stmt->execute([$targetEmail]);
        return $stmt->rowCount();
    }

    public function getAllActiveShares(): array {
        $stmt = $this->db->query(
            'SELECT ps.id AS share_id, ps.profile_id, ps.shared_with_email, ps.can_edit, ps.created_at,
                    p.name AS profile_name, p.issuer, p.icon, p.color,
                    u_owner.name AS owner_name, u_owner.email AS owner_email,
                    COALESCE(u_target.name, w.name, "Pengguna") AS target_name
             FROM profile_shares ps
             JOIN otp_profiles p ON p.id = ps.profile_id
             JOIN users u_owner ON u_owner.id = p.user_id
             LEFT JOIN users u_target ON u_target.id = ps.shared_with_user_id
             LEFT JOIN whitelist_users w ON LOWER(w.email) = LOWER(ps.shared_with_email)
             ORDER BY ps.created_at DESC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function bulkRevokeShares(array $shareIds): int {
        $ids = array_values(array_filter(array_map('intval', $shareIds), fn($id) => $id > 0));
        if (empty($ids)) return 0;

        $inClause = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("DELETE FROM profile_shares WHERE id IN ({$inClause})");
        $stmt->execute($ids);
        return $stmt->rowCount();
    }

    public function getAllProfilesForAdmin(): array {
        $stmt = $this->db->query(
            'SELECT p.id, p.name, p.issuer, p.icon, p.color, p.algorithm, p.digits, p.period,
                    u.name AS owner_name, u.email AS owner_email
             FROM otp_profiles p
             JOIN users u ON u.id = p.user_id
             ORDER BY p.issuer ASC, p.name ASC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function bulkMultiShare(int $adminUserId, array $profileIds, array $targetEmails, string $action = 'grant', bool $canEdit = false): array {
        $profileIds = array_values(array_unique(array_filter(array_map('intval', $profileIds), fn($id) => $id > 0)));
        if (empty($profileIds)) {
            throw new InvalidArgumentException('Pilih minimal satu token.');
        }

        $targetEmails = array_values(array_unique(array_filter(array_map(fn($e) => strtolower(trim($e)), $targetEmails), fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL))));
        if (empty($targetEmails)) {
            throw new InvalidArgumentException('Pilih minimal satu akun pengguna tujuan.');
        }

        $action = in_array($action, ['grant', 'revoke'], true) ? $action : 'grant';

        $this->db->beginTransaction();
        try {
            // 1. Fetch profile owners to prevent sharing to token owner
            $inProfiles = implode(',', array_fill(0, count($profileIds), '?'));
            $stmtP = $this->db->prepare(
                "SELECT p.id, LOWER(u.email) AS owner_email 
                 FROM otp_profiles p 
                 JOIN users u ON u.id = p.user_id 
                 WHERE p.id IN ({$inProfiles})"
            );
            $stmtP->execute($profileIds);
            $profileOwnerMap = [];
            foreach ($stmtP->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $profileOwnerMap[(int)$row['id']] = $row['owner_email'];
            }

            // 2. Fetch existing user IDs for target emails
            $inEmails = implode(',', array_fill(0, count($targetEmails), '?'));
            $stmtU = $this->db->prepare(
                "SELECT id, LOWER(email) AS email 
                 FROM users 
                 WHERE LOWER(email) IN ({$inEmails})"
            );
            $stmtU->execute($targetEmails);
            $userMap = [];
            foreach ($stmtU->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $userMap[$row['email']] = (int)$row['id'];
            }

            $added = 0;
            $updated = 0;
            $removed = 0;
            $skippedSelf = 0;

            if ($action === 'grant') {
                $stmtUpsert = $this->db->prepare(
                    'INSERT INTO profile_shares (profile_id, shared_by_user_id, shared_with_email, shared_with_user_id, can_edit)
                     VALUES (?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE shared_with_user_id = VALUES(shared_with_user_id), can_edit = VALUES(can_edit)'
                );

                foreach ($targetEmails as $email) {
                    $targetUserId = $userMap[$email] ?? null;
                    foreach ($profileIds as $pid) {
                        $ownerEmail = $profileOwnerMap[$pid] ?? null;
                        if ($ownerEmail === $email) {
                            $skippedSelf++;
                            continue; // Owner already has full control
                        }

                        $stmtUpsert->execute([$pid, $adminUserId, $email, $targetUserId, $canEdit ? 1 : 0]);
                        $rc = $stmtUpsert->rowCount();
                        if ($rc === 1) {
                            $added++;
                        } elseif ($rc === 2) {
                            $updated++;
                        }
                    }
                }
            } else { // revoke
                $stmtDel = $this->db->prepare(
                    "DELETE FROM profile_shares 
                     WHERE profile_id IN ({$inProfiles}) AND LOWER(shared_with_email) IN ({$inEmails})"
                );
                $stmtDel->execute(array_merge($profileIds, $targetEmails));
                $removed = $stmtDel->rowCount();
            }

            $this->db->commit();

            return [
                'success' => true,
                'action' => $action,
                'tokens_count' => count($profileIds),
                'users_count' => count($targetEmails),
                'added' => $added,
                'updated' => $updated,
                'removed' => $removed,
                'skipped_self' => $skippedSelf,
            ];
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getAccessOverviewMetrics(): array {
        $collaboratorsCount = (int)$this->db->query('SELECT COUNT(DISTINCT LOWER(shared_with_email)) FROM profile_shares')->fetchColumn();
        $sharesCount = (int)$this->db->query('SELECT COUNT(*) FROM profile_shares')->fetchColumn();
        $tokensCount = (int)$this->db->query('SELECT COUNT(*) FROM otp_profiles')->fetchColumn();
        $usersCount = (int)$this->db->query('SELECT COUNT(*) FROM whitelist_users')->fetchColumn();

        return [
            'total_collaborators' => $collaboratorsCount,
            'total_shares'        => $sharesCount,
            'total_tokens'        => $tokensCount,
            'total_users'         => $usersCount,
        ];
    }

    // ── OTP generation (secret stays server-side) ─────────────────────────

    public function generateOTP(int $profileId, int $userId): array {
        $profile = $this->get($profileId, $userId);
        if (!$profile) throw new RuntimeException('Profile not found or access denied.');

        $secret = Crypto::decrypt($profile['secret_encrypted']);
        $now    = time();
        $period = (int)$profile['period'];

        return [
            'code'      => TOTP::generate($secret, $profile['algorithm'], (int)$profile['digits'], $period, $now),
            'remaining' => TOTP::secondsRemaining($period, $now),
            'progress'  => TOTP::progress($period, $now),
            'period'    => $period,
            'digits'    => (int)$profile['digits'],
            'algorithm' => $profile['algorithm'],
            'hide_code' => (int)$profile['hide_code'],
        ];
    }

    // ── Export ─────────────────────────────────────────────────────────────

    /**
     * Return profiles the user can edit, with decrypted secrets, for CSV export.
     * Only profiles where the user is owner OR has can_edit=1 are included.
     */
    public function exportForUser(int $userId): array {
        $stmt = $this->db->prepare(
            'SELECT p.id, p.name, p.secret_encrypted, p.algorithm, p.digits, p.period,
                    IF(p.user_id = :uid, 1, 0) AS is_owner,
                    COALESCE(s.can_edit, IF(p.user_id = :uid2, 1, 0)) AS can_edit
             FROM otp_profiles p
             LEFT JOIN profile_shares s ON s.profile_id = p.id AND s.shared_with_user_id = :uid3
             WHERE (p.user_id = :uid4 OR (s.shared_with_user_id = :uid5 AND s.can_edit = 1))
             ORDER BY p.id ASC'
        );
        $stmt->execute([':uid'=>$userId,':uid2'=>$userId,':uid3'=>$userId,':uid4'=>$userId,':uid5'=>$userId]);
        $rows = $stmt->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'profile'   => (int)$row['id'],
                'seed'      => Crypto::decrypt($row['secret_encrypted']),
                'title'     => $row['name'],
                'algorithm' => $row['algorithm'],
                'digits'    => (int)$row['digits'],
                'period'    => (int)$row['period'],
            ];
        }
        return $result;
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function canEdit(int $profileId, int $userId): bool {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM otp_profiles WHERE id = ? AND user_id = ?
             UNION
             SELECT 1 FROM profile_shares WHERE profile_id = ? AND shared_with_user_id = ? AND can_edit = 1'
        );
        $stmt->execute([$profileId, $userId, $profileId, $userId]);
        return (bool)$stmt->fetch();
    }
}
