@echo off
REM PHP Code Beautifier and Fixer - Corrección automática de código PHP
REM Equivalente a: composer global require squizlabs/php_codesniffer

php "%~dp0phpcs-tools\phpcs\bin\phpcbf.php" %*
