@echo off
REM PHP_CodeSniffer - Análisis de código PHP
REM Equivalente a: composer global require squizlabs/php_codesniffer

php "%~dp0phpcs-tools\phpcs\bin\phpcs.php" %*
