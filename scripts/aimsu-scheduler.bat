@echo off
REM ============================================================
REM  AIMS - Laravel scheduler runner for Windows Task Scheduler
REM
REM  Task Scheduler runs this file every minute. Laravel decides
REM  what is due: the database backup to Google Drive runs daily
REM  at 00:05 (see routes/console.php).
REM
REM  Register once from an Administrator Command Prompt:
REM    schtasks /create /tn "AIMS Laravel Scheduler" /tr "C:\xampp\htdocs\aims\scripts\aimsu-scheduler.bat" /sc minute /mo 1 /ru SYSTEM /rl HIGHEST /f
REM
REM  Output is appended to storage\logs\scheduler.log
REM ============================================================

set "APP_DIR=C:\xampp\htdocs\aims"

REM Herd PHP. After a PHP upgrade, get the new path with: php -r "echo PHP_BINARY;"
set "PHP_BIN=C:\Users\aims-pg\.config\herd\bin\php84\php.exe"

set "LOG_FILE=%APP_DIR%\storage\logs\scheduler.log"

if not exist "%PHP_BIN%" (
    echo [%date% %time%] PHP not found at "%PHP_BIN%" >> "%LOG_FILE%"
    exit /b 1
)

cd /d "%APP_DIR%" || exit /b 1
"%PHP_BIN%" artisan schedule:run >> "%LOG_FILE%" 2>&1
exit /b %ERRORLEVEL%
