@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../phing/phing/bin/phing
php "%BIN_TARGET%" %*
