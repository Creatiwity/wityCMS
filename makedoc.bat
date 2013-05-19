@echo off
set PWD=%~dp0
%PHP_PEAR_BIN_DIR%\phpdoc -d %PWD% -t %PWD%doc --title WityCMS -i libraries\ -i apps\