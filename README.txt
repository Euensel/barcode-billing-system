================================================================================
SYSTÈME DE FACTURATION AVEC LECTURE DE CODES-BARRES
================================================================================

Version : 1.0
Année académique : 2025-2026
Langages : PHP (procédural), HTML5, CSS3, JavaScript
Bibliothèque de scan : ZXing
Base de données : Aucune (persistance via fichiers JSON)

================================================================================
PRÉREQUIS
================================================================================

1. Serveur local (un des suivants) :
   - XAMPP (PHP ≥ 8.0)
   - WAMP
   - MAMP
   - Laragon

2. Navigateur web moderne avec caméra (pour la lecture des codes-barres) :
   - Google Chrome (recommandé)
   - Firefox
   - Edge

3. Extension PHP : JSON (activée par défaut)

================================================================================
INSTALLATION
================================================================================

1. Décompressez l'archive du projet dans le dossier racine de votre serveur :
   - XAMPP : C:\xampp\htdocs\
   - WAMP : C:\wamp64\www\
   - MAMP : /Applications/MAMP/htdocs/
   - Linux : /var/www/html/

2. Renommez le dossier (optionnel) :
   Par exemple : facturation-systeme

3. Vérifiez la structure des dossiers :
   facturation-systeme/
   ├── config/
   ├── auth/
   ├── modules/
   ├── includes/
   ├── assets/
   ├── data/
   ├── rapports/
   └── index.php

4. Assurez-vous que le dossier "data" a les droits en écriture :
   - Windows : droits par défaut (hérités)
   - Mac/Linux : chmod 755 data/
   (Le système crée automatiquement les fichiers JSON si nécessaires)

================================================================================
CONFIGURATION
================================================================================

1. Ouvrez le fichier config/config.php

2. Si le projet n'est pas à la racine du serveur, modifiez BASE_URL :
   - Racine du serveur (http://localhost/) : define('BASE_URL', '');
   - Sous-dossier : define('BASE_URL', '/votre-dossier');

3. Ajustez le taux de TVA si nécessaire (par défaut 18%) :
   define('TVA', 0.18);

================================================================================
BASE DE DONNÉES (fichiers JSON)
================================================================================

Le système utilise des fichiers JSON pour la persistance. Ils sont créés
automatiquement au premier lancement :

- data/utilisateurs.json : comptes utilisateurs
- data/produits.json : catalogue produits
- data/factures.json : historique des factures

Premier utilisateur (créé automatiquement) :
   Identifiant : superadmin
   Mot de passe : admin123

IMPORTANT : Changez ce mot de passe après la première connexion.

================================================================================
LANCEMENT
================================================================================

1. Démarrez votre serveur local :
   - XAMPP/WAMP : Apache + PHP
   - MAMP : Start Servers

2. Ouvrez votre navigateur et accédez à :
   http://localhost/nom-du-dossier/

   (exemple : http://localhost/facturation-systeme/)

3. Connectez-vous avec le compte superadmin/admin123

================================================================================
STRUCTURE DES URLS (BASE_URL)
================================================================================

Pour que les liens fonctionnent correctement, BASE_URL doit correspondre
au chemin d'accès depuis la racine du serveur :

Exemples :
- Projet à la racine : http://localhost/ → BASE_URL = ''
- Projet dans un sous-dossier : http://localhost/factu/ → BASE_URL = '/factu'

================================================================================
FONCTIONNALITÉS PAR RÔLE
================================================================================

CAISSIER :
   - Nouvelle facture (scan de codes-barres)
   - Mes factures (consultation)

MANAGER (tous droits Caissier +) :
   - Enregistrement de produits (avec scan)
   - Liste des produits (modification/suppression)
   - Rapports journalier et mensuel

SUPER ADMINISTRATEUR (tous droits Manager +) :
   - Gestion des comptes (ajout/modification/suppression/désactivation)
   - Accès à toutes les factures

================================================================================
DÉPENDANCES EXTERNES
================================================================================

Le projet charge les ressources suivantes depuis des CDN :

- Boxicons : https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css
- ZXing (scan codes-barres) : https://unpkg.com/@zxing/library@0.19.1/umd/index.min.js

================================================================================
DÉPANNAGE
================================================================================

PROBLÈME : La caméra ne s'active pas au scan
SOLUTION : Autorisez l'accès à la caméra dans le navigateur. Vérifiez que vous
êtes en HTTPS ou localhost (les navigateurs exigent une connexion sécurisée
ou localhost pour la caméra).

PROBLÈME : Erreur "BASE_URL non définie"
SOLUTION : Vérifiez que BASE_URL est bien définie dans config/config.php.

PROBLÈME : Les fichiers JSON ne se créent pas
SOLUTION : Vérifiez les droits d'écriture du dossier data/ (chmod 755 sur Mac/Linux).

PROBLÈME : Session expirée trop rapidement
SOLUTION : La durée de session est gérée par PHP. Par défaut la session dure
jusqu'à fermeture du navigateur.

================================================================================
TESTS EFFECTUÉS
================================================================================

✓ Authentification et contrôle d'accès par rôles
✓ Enregistrement de produits (scan + manuel)
✓ Modification et suppression de produits
✓ Création de factures (panier, validation, stock)
✓ Consultation des factures par rôle
✓ Rapports journalier et mensuel
✓ Gestion complète des utilisateurs (CRUD)
✓ Responsive design (mobile/tablette/desktop)
✓ Persistance JSON sans base de données

================================================================================
CONTACT & RAPPORT
================================================================================

Le rapport technique (format PDF) est disponible séparément.
Le code source complet est sur GitHub : https://github.com/Euensel/barcode-billing-system.git

En cas de problème : eurekansombaoleka@gmail.com

================================================================================
================================================================================