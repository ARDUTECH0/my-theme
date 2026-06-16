@echo off
setlocal

REM ECM Theme - publish a new release
REM Double-click = auto bump patch (3.0.0 -> 3.0.1)
REM Or run:  release.bat 3.1.0 "update notes"

set "BASH=%PROGRAMFILES%\Git\bin\bash.exe"
if not exist "%BASH%" set "BASH=%PROGRAMFILES(x86)%\Git\bin\bash.exe"
if not exist "%BASH%" set "BASH=%LOCALAPPDATA%\Programs\Git\bin\bash.exe"
if not exist "%BASH%" for %%i in (bash.exe) do set "BASH=%%~$PATH:i"

if not exist "%BASH%" goto :nobash

"%BASH%" "%~dp0release.sh" %1 %2
echo.
pause
exit /b 0

:nobash
echo.
echo Git Bash not found. Install "Git for Windows" from https://git-scm.com
echo.
pause
exit /b 1
