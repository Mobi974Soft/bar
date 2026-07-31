@echo off
@REM "C:\xampp\php\php.exe" -f "C:\xampp\htdocs\caisse-backend\maj\syncAjout.php"
@REM "C:\xampp\php\php.exe" -f "C:\xampp\htdocs\caisse-backend\maj\syncUpdate.php"
"C:\xampp\php\php.exe" -f "C:\xampp\htdocs\caisse-backend\bar\synchro\synchroTicket2.php" >> "C:\xampp\htdocs\caisse-backend\bar\synchro\error.txt" 2>&1
@REM "C:\xampp\php\php.exe" -f "C:\xampp\htdocs\caisse-backend\maj\updateOption.php"