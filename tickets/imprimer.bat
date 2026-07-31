@echo off
setlocal
set imprimante=%1

copy /b "C:\xampp\htdocs\caisse-backend\bar\caisse\commande.txt" "\\%computername%\%imprimante%"
@REM  copy /b "C:\xampp\htdocs\caisse-backend\bar\caisse\commande.txt" "\\%computername%\EML400l"

@REM pause
endlocal
exit

