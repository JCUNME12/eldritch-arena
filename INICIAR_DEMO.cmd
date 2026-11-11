@echo off
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\demo-local.ps1" -OpenBrowser
if errorlevel 1 pause
