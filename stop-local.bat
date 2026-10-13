@echo off
title Hentikan TOTPVault
cd /d "%~dp0"

echo ========================================================
echo         Menghentikan TOTPVault Local
echo ========================================================

echo [*] Menghentikan MariaDB...
if exist "mariadb\bin\mariadb-admin.exe" (
    "mariadb\bin\mariadb-admin.exe" -h 127.0.0.1 -P 3306 -u root shutdown >nul 2>&1
)
taskkill /F /IM mysqld.exe >nul 2>&1
taskkill /F /IM mariadbd.exe >nul 2>&1

echo [*] Menghentikan Web Server PHP...
taskkill /F /IM php.exe >nul 2>&1

echo ========================================================
echo  TOTPVault berhasil dihentikan.
echo ========================================================
pause
