# Code promo de 1 € par article

Cette fonctionnalité ajoute un bouton **Code promo** à la caisse restaurant. Après confirmation, une remise de 1 € est appliquée à chaque unité présente dans le panier. Le prix d’un produit ne peut pas devenir négatif.

## Période de validité

La période est volontairement définie dans le code, dans `caisse/promo/PromoCode.php` :

```php
const START_AT = '2026-08-01 00:00:00';
const END_AT = '2026-08-31 23:59:59';
```

Les dates utilisent le fuseau `Indian/Reunion`. Le bouton est automatiquement désactivé hors de cette période.

## Stockage sans migration SQL

La fonctionnalité réutilise la colonne `remise_euro` déjà présente dans le panier. Elle ne modifie pas le schéma de la base de données.

Les fichiers d’exécution sont créés dans `caisse/data/promo-code/` :

- `active/{caisse}_{table}.json` conserve l’état temporaire permettant l’annulation avant encaissement ;
- `YYYY-MM-DD.json` conserve les tickets du jour, la quantité de produits et le montant total remisé.

Le compte qui exécute PHP doit avoir les droits d’écriture sur `caisse/data/promo-code/` et son sous-dossier `active/`.

## Règles métier

- un seul code promo par panier ;
- les produits ajoutés après l’activation reçoivent aussi la remise ;
- l’annulation restaure les remises initiales tant qu’aucun ticket n’a été créé ;
- un paiement fractionné est tracé ticket par ticket ;
- le total caisse affiche le nombre de tickets, le nombre de produits, le montant remisé et le détail par ticket.
