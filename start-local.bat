@echo off
setlocal enabledelayedexpansion
title TOTPVault - Local Server Runner
cd /d "%~dp0"

set "PORT=8088"
set "DB_PORT=3306"

echo ========================================================
echo         Memulai TOTPVault (Local Development)
echo ========================================================

REM 1. Pastikan PHP tersedia di PATH
where php >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    set "PATH=%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe;%PATH%"
)

REM 2. Cek apakah MariaDB port 3306 sudah jalan
netstat -ano | findstr ":%DB_PORT%" | findstr "LISTENING" >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [*] Menjalankan MariaDB lokal di port %DB_PORT%...
    if exist "mariadb\bin\mysqld.exe" (
        start "TOTPVault MariaDB" /b "mariadb\bin\mysqld.exe" "--datadir=%~dp0mariadb\data" "--port=%DB_PORT%"
        timeout /t 2 /nobreak >nul
    ) else (
        echo [!] Direktori mariadb\bin\mysqld.exe tidak ditemukan.
    )
) else (
    echo [*] MariaDB sudah aktif di port %DB_PORT%.
)

REM 3. Inisialisasi skema database jika diperlukan
if exist "tools\init-db.php" (
    php tools\init-db.php >nul 2>&1
)

REM 4. Cek apakah PHP Server di port target sudah jalan
netstat -ano | findstr ":%PORT%" | findstr "LISTENING" >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [*] Menjalankan Web Server PHP di http://localhost:%PORT%...
    start "TOTPVault Web Server" /b php -S localhost:%PORT% router.php
    timeout /t 1 /nobreak >nul
) else (
    echo [*] Web Server PHP sudah aktif di http://localhost:%PORT%.
)

echo ========================================================
echo  TOTPVault SIAP DIGUNAKAN!
echo  Buka di Browser: http://localhost:%PORT%
echo ========================================================
start http://localhost:%PORT%
pause
