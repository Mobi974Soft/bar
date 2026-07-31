#!/bin/sh
if [ $2 = "usb" ]; then
	lpr -P $1 -o raw "/var/www/localhost/caisse-backend/bar/caisse/commande.txt"
else
	cat "/var/www/localhost/caisse-backend/bar/caisse/commande.txt" | nc -w 1 $1 $2
fi
# cat "/var/www/localhost/caisse-backend/admin/etiquette.txt" | nc -w 1 $1 $2