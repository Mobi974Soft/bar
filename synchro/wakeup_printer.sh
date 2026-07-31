#!/bin/bash

# Récupérer la liste des imprimantes désactivées
paused_printers=$(lpstat -p | grep "disabled" | awk '{print $2}')

# Vérifier s'il y en a
if [ -z "$paused_printers" ]; then
    echo "Aucune imprimante en pause."
else
    echo "Réactivation des imprimantes en pause..."
    for printer in $paused_printers; do
        echo "Réactivation de : $printer"
        /usr/sbin/cupsenable "$printer"
        /usr/sbin/accept "$printer"
    done
    echo "Toutes les imprimantes ont été réactivées."
fi

