<?php
// src/Crypto.php — AES-256-GCM encryption for TOTP secrets
declare(strict_types=1);

class Crypto {
    private static function key(): string {
        $cfg = require __DIR__ . '/../config/config.php';
        $key = (string)($cfg['encryption_key'] ?? '');
        $isDev = ($cfg['app_env'] ?? 'production') === 'development';

        if (empty($key)) {
            throw new RuntimeException('FATAL: ENCRYPTION_KEY is not configured in .env.');
        }

        if (!$isDev && ($key === 'ThisIsA32ByteKeyForLocalTesting!' || strlen($key) < 16)) {
            throw new RuntimeException('SECURITY ERROR: Default testing ENCRYPTION_KEY detected in production mode. Please set a strong, unique 32-byte key in .env before running in production.');
        }

        // Pad or trim to exactly 32 bytes
        return substr(str_pad($key, 32, "\0"), 0, 32);
    }

    public static function encrypt(string $plaintext): string {
        $key    = self::key();
        $iv     = random_bytes(12); // 96-bit nonce for GCM
        $tag    = '';
        $cipher = openssl_encrypt($plaintext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, '', 16);
        if ($cipher === false) throw new RuntimeException('Encryption failed.');
        // Store: base64(iv || tag || ciphertext)
        return base64_encode($iv . $tag . $cipher);
    }

    public static function decrypt(string $encoded): string {
        $key  = self::key();
        $raw  = base64_decode($encoded, true);
        if ($raw === false || strlen($raw) < 28) {
            throw new RuntimeException('Invalid encrypted payload structure.');
        }
        $iv   = substr($raw, 0, 12);
        $tag  = substr($raw, 12, 16);
        $data = substr($raw, 28);
        $plain = openssl_decrypt($data, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        if ($plain === false) throw new RuntimeException('Decryption failed — data may be tampered or key mismatched.');
        return $plain;
    }
}
