@echo off
chcp 65001 >nul
REM ════════════════════════════════════════════════════════
REM ECM Theme — نشر تحديث جديد (دبل-كليك)
REM   دبل-كليك = يزوّد آخر رقم تلقائيًا
REM   أو من cmd:  release.bat 3.1.0  "نص التحديث"
REM ════════════════════════════════════════════════════════
setlocal

set "BASH=%PROGRAMFILES%\Git\bin\bash.exe"
if not exist "%BASH%" set "BASH=%PROGRAMFILES(x86)%\Git\bin\bash.exe"
if not exist "%BASH%" set "BASH=%LOCALAPPDATA%\Programs\Git\bin\bash.exe"

if not exist "%BASH%" (
  echo.
  echo [!] Git Bash مش لاقيه — ثبّت "Git for Windows" من git-scm.com
  echo.
  pause
  exit /b 1
)

"%BASH%" "%~dp0release.sh" %1 %2

echo.
pause
