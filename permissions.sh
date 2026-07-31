#!/bin/bash

# Script de vérification et correction des permissions
DESTINATION_BASE="/var/www/localhost/caisse-backend/bar/"
DIRECTORIES=(
    "tickets"
    "avoir"
    "journaux" 
    "facture"
    "jsons"
    "synchro"
    "caisse/devis"
    "caisse/"
)

echo "Vérification des permissions des dossiers..."

for DIR in "${DIRECTORIES[@]}"; do
    FULL_PATH="$DESTINATION_BASE/$DIR"
    
    # Vérifier si le dossier existe
    if [ ! -d "$FULL_PATH" ]; then
        echo "⚠️  Le dossier $FULL_PATH n'existe pas"
        continue
    fi
    

    
    # Vérifier les permissions actuelles
    CURRENT_PERM=$(stat -c "%a" "$FULL_PATH")
    
    if [ "$CURRENT_PERM" -eq 777 ]; then
        echo "✅ $FULL_PATH : permissions déjà correctes (777)"
    else
        echo "🔧 $FULL_PATH : permissions incorrectes ($CURRENT_PERM) → application de 777"
        sudo chmod -R 777 "$FULL_PATH"
        
        # Vérification après modification
        NEW_PERM=$(stat -c "%a" "$FULL_PATH")
        if [ "$NEW_PERM" -eq 777 ]; then
            echo "✅ $FULL_PATH : permissions corrigées avec succès (777)"
        else
            echo "❌ $FULL_PATH : échec de la modification des permissions"
        fi
    fi
done

echo "Vérification terminée."
