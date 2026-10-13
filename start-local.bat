@echo off
title TOTPVault - Local Server Runner
cd /d "%~dp0"

echo ========================================================
echo         Memulai TOTPVault (Local Development)
echo ========================================================

REM 1. Pastikan PHP tersedia di PATH
where php >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    set "PATH=%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe;%PATH%"
)

REM 2. Cek apakah MariaDB port 3306 sudah jalan
netstat -ano | findstr ":3306" | findstr "LISTENING" >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [*] Menjalankan MariaDB lokal di port 3306...
    start /b "" "mariadb\bin\mysqld.exe" "--datadir=%~dp0mariadb\data" "--port=3306"
    timeout /t 2 /nobreak >nul
) else (
    echo [*] MariaDB sudah aktif di port 3306.
)

REM 3. Cek apakah PHP Server port 8000 sudah jalan
netstat -ano | findstr ":8000" | findstr "LISTENING" >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [*] Menjalankan Web Server PHP di http://localhost:8000...
    start /b "" php -S localhost:8000 router.php
    timeout /t 1 /nobreak >nul
) else (
    echo [*] Web Server PHP sudah aktif di http://localhost:8000.
)

echo ========================================================
echo  TOTPVault SIAP DIGUNAKAN!
echo  Buka di Browser: http://localhost:8000
echo ========================================================
start http://localhost:8000
pause
