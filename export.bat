@echo off
c:\xampp\mysql\bin\mysqldump.exe -u root --default-character-set=utf8mb4 practice scategory sitem smanagement sorder > c:\xampp\htdocs\smartorder\data_fix.sql 2>&1
echo Done
pause
