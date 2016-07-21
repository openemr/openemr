@ECHO OFF

:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
:: The phing build script for Windows based systems
:: $Id: ede20276d318656862d744a37380a15b08c7f55a $
:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

::----------------------------------------------------------------------------------
:: Please set following to PHP's CLI
:: NOTE: In PHP 4.2.x the PHP-CLI used to be named php-cli.exe. 
::       PHP 4.3.x names it php.exe but stores it in a subdir called /cli/php.exe
::       E.g. for PHP 4.2 C:\phpdev\php-4.2-Win32\php-cli.exe
::            for PHP 4.3 C:\phpdev\php-4.3-Win32\cli\php.exe
  
SET phpCli=@PHP-BIN@

::---------------------------------------------------------------------------------
::---------------------------------------------------------------------------------
:: Do not modify below this line!! (Unless you know what your doing :)
::---------------------------------------------------------------------------------
::---------------------------------------------------------------------------------

:: Check existence of php.exe
IF EXIST "%phpCli%" (
  SET doNothing=
) ELSE GOTO :NoPhpCli

"%phpCli%" -d html_errors=off -qC "@PEAR-DIR@\phing.php" %*
GOTO :EOF

::
:: php.exe not found error  
GOTO :PAUSE_END
:NoPhpCli
ECHO ** ERROR *****************************************************************
ECHO * Sorry, can't find the php.exe file.
ECHO * You must edit this file (%~s0) to point to your php.exe (CLI)
ECHO *    [Currently set to %phpCli%]
ECHO **************************************************************************

GOTO :PAUSE_END

:PAUSE_END
PAUSE