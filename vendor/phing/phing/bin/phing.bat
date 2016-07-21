@echo off

rem *********************************************************************
rem ** the phing build script for Windows based systems
rem ** $Id: 993cd1840af4f9d5e08d858efb60832b074fb9da $
rem *********************************************************************

rem This script will do the following:
rem - check for PHP_COMMAND env, if found, use it.
rem   - if not found detect php, if found use it, otherwise err and terminate
rem - check for PHING_HOME evn, if found use it
rem   - if not found error and leave
rem - check for PHP_CLASSPATH, if found use it
rem   - if not found set it using PHING_HOME/classes

if "%OS%"=="Windows_NT" @setlocal

rem %~dp0 is expanded pathname of the current script under NT
set DEFAULT_PHING_HOME=%~dp0..

goto init
goto cleanup

:init

if "%PHING_HOME%" == "" set PHING_HOME=%DEFAULT_PHING_HOME%
set DEFAULT_PHING_HOME=

if "%PHP_COMMAND%" == "" goto no_phpcommand
if "%PHP_CLASSPATH%" == "" goto set_classpath

goto run
goto cleanup

:run
"%PHP_COMMAND%" -d phar.readonly=off -d html_errors=off -qC "%PHING_HOME%\bin\phing.php" %*
goto cleanup

:no_phpcommand
REM echo ------------------------------------------------------------------------
REM echo WARNING: Set environment var PHP_COMMAND to the location of your php.exe
REM echo          executable (e.g. C:\PHP\php.exe).  (Assuming php.exe on Path)
REM echo ------------------------------------------------------------------------
set PHP_COMMAND=php.exe
goto init

:err_home
echo ERROR: Environment var PHING_HOME not set. Please point this
echo variable to your local phing installation!
goto cleanup

:set_classpath
set PHP_CLASSPATH=%PHING_HOME%\classes
goto init

:cleanup
if "%OS%"=="Windows_NT" @endlocal
REM pause
