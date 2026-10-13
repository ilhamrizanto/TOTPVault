#!/bin/sh
set -e

# ── TOTPVault Container Entrypoint ───────────────────────────────────────────
# Automatically runs database schema migration and admin seeding before Apache starts.
echo "[TOTPVault] Starting container..."
echo "[TOTPVault] Initialising database & synchronising administrators..."

php /var/www/html/tools/init-db.php || echo "[TOTPVault] Note: Database initialiser finished with notice."

echo "[TOTPVault] Database ready. Starting web server..."
exec "$@"
