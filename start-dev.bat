@echo off
echo Starting development environment...

:: Start the Laravel server in the background
start cmd /k "php artisan serve"

:: Start Vite development server with HMR
start cmd /k "npm run dev"

echo Development servers started.
echo - Laravel: http://localhost:8000
echo - Vite: http://localhost:5173
echo.
echo Press any key to shut down the servers.
pause>nul

:: Kill processes when user exits
taskkill /f /im php.exe >nul 2>&1
taskkill /f /im node.exe >nul 2>&1

echo Development servers stopped. 