@echo off
echo SiGAP - Development Environment
echo =================================
echo.

:: Check if node_modules exists
if not exist "node_modules" (
    echo Installing Node.js dependencies...
    npm install
    echo.
)

:: Check if vendor exists
if not exist "vendor" (
    echo Installing PHP dependencies...
    composer install
    echo.
)

:: Check if .env exists
if not exist ".env" (
    echo Creating .env file...
    copy .env.example .env
    php artisan key:generate
    echo.
    echo Please configure your database settings in .env file
    echo.
)

echo Starting development servers...
echo.
echo Using Laravel 12 concurrent development script...
echo This will start Laravel server, Queue worker, and Vite simultaneously.
echo.
echo Available at: http://localhost:8000
echo.

:: Use the new Laravel 12 dev script
composer run dev 