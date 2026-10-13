<?php
// src/RateLimiter.php — Lightweight atomic file-based rate limiter
declare(strict_types=1);

class RateLimiter {
    private static ?string $storageDir = null;

    private static function getStorageDir(): string {
        if (self::$storageDir === null) {
            self::$storageDir = dirname(__DIR__) . '/sessions/rate_limits';
            if (!is_dir(self::$storageDir)) {
                @mkdir(self::$storageDir, 0750, true);
            }
        }
        return self::$storageDir;
    }

    private static function getFilePath(string $key): string {
        $hash = hash('sha256', $key);
        return self::getStorageDir() . '/' . $hash . '.limit';
    }

    /**
     * Check if the given key has exceeded $maxAttempts within $decaySeconds.
     */
    public static function tooManyAttempts(string $key, int $maxAttempts, int $decaySeconds = 60): bool {
        $file = self::getFilePath($key);
        if (!file_exists($file)) {
            return false;
        }

        $fp = @fopen($file, 'r');
        if (!$fp) return false;

        $now = time();
        $tooMany = false;

        if (flock($fp, LOCK_SH)) {
            $contents = stream_get_contents($fp);
            flock($fp, LOCK_UN);
            if ($contents) {
                $data = json_decode($contents, true);
                if (is_array($data) && isset($data['expires_at'], $data['hits'])) {
                    if ($data['expires_at'] > $now && $data['hits'] >= $maxAttempts) {
                        $tooMany = true;
                    }
                }
            }
        }
        fclose($fp);
        return $tooMany;
    }

    /**
     * Increment the hit count for a key. Returns the new hit count.
     */
    public static function hit(string $key, int $decaySeconds = 60): int {
        $file = self::getFilePath($key);
        $now  = time();
        $hits = 1;

        $fp = @fopen($file, 'c+');
        if (!$fp) {
            return 1;
        }

        if (flock($fp, LOCK_EX)) {
            $contents = stream_get_contents($fp);
            $data = is_string($contents) && $contents !== '' ? json_decode($contents, true) : null;

            if (is_array($data) && isset($data['expires_at']) && $data['expires_at'] > $now) {
                $hits = ($data['hits'] ?? 0) + 1;
                $expiresAt = (int)$data['expires_at'];
            } else {
                $hits = 1;
                $expiresAt = $now + $decaySeconds;
            }

            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode([
                'hits'       => $hits,
                'expires_at' => $expiresAt,
            ]));
            fflush($fp);
            flock($fp, LOCK_UN);
        }
        fclose($fp);

        // Opportunistic garbage collection (1% chance)
        if (random_int(1, 100) === 1) {
            self::purgeExpired();
        }

        return $hits;
    }

    /**
     * Get the number of seconds until the key is available again.
     */
    public static function availableIn(string $key): int {
        $file = self::getFilePath($key);
        if (!file_exists($file)) {
            return 0;
        }

        $now = time();
        $fp = @fopen($file, 'r');
        if (!$fp) return 0;

        $remaining = 0;
        if (flock($fp, LOCK_SH)) {
            $contents = stream_get_contents($fp);
            flock($fp, LOCK_UN);
            if ($contents) {
                $data = json_decode($contents, true);
                if (is_array($data) && isset($data['expires_at'])) {
                    $remaining = max(0, (int)$data['expires_at'] - $now);
                }
            }
        }
        fclose($fp);
        return $remaining;
    }

    /**
     * Clear the rate limit for a key.
     */
    public static function clear(string $key): void {
        $file = self::getFilePath($key);
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    /**
     * Cleanup old expired rate limit files.
     */
    public static function purgeExpired(): void {
        $dir = self::getStorageDir();
        $files = @glob($dir . '/*.limit');
        if (!$files) return;

        $now = time();
        foreach ($files as $f) {
            if ($now - @filemtime($f) > 86400) {
                @unlink($f);
                continue;
            }
            $content = @file_get_contents($f);
            if ($content) {
                $data = json_decode($content, true);
                if (isset($data['expires_at']) && $data['expires_at'] < $now) {
                    @unlink($f);
                }
            }
        }
    }

    /**
     * Helper to get client IP address safely.
     */
    public static function clientIp(): string {
        return $_SERVER['HTTP_CF_CONNECTING_IP']
            ?? $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['REMOTE_ADDR']
            ?? '127.0.0.1';
    }
}
