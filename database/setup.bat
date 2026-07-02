@echo off
REM S.I.M.S Database Setup Script for Windows
REM This script imports the database schema into MySQL

echo ====================================
echo S.I.M.S - Database Setup
echo ====================================
echo.

REM Check if schema.sql exists
if not exist "database\schema.sql" (
    echo Error: database\schema.sql not found!
    exit /b 1
)

setlocal enabledelayedexpansion
set /p db_user="Enter MySQL username (default: root): "
if "!db_user!"=="" set db_user=root

set /p db_pass="Enter MySQL password: "

echo.
echo Importing database schema...
mysql -u "!db_user!" -p"!db_pass!" < database\schema.sql

if %errorlevel% equ 0 (
    echo.
    echo Database setup completed successfully!
    echo Database: internship_db
    echo Tables created
) else (
    echo.
    echo Error importing database schema
    exit /b 1
)

echo.
echo ====================================
echo Setup Complete!
echo ====================================
echo.
echo Access the system at:
echo http://localhost/S-I-M-S/frontend/index.php
echo.
pause
