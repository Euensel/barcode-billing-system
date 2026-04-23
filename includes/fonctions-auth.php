<?php
// includes/fonctions_utilisateurs.php
require_once __DIR__ . '/../config/config.php';  // Correction du chemin

/**
 * Lit le fichier JSON des utilisateurs et retourne un tableau associatif.
 */
function lireUtilisateurs() {
    if (!file_exists(USERS_FILE)) {
        return [];
    }
    $content = file_get_contents(USERS_FILE);
    if ($content === false) {
        return [];
    }
    $data = json_decode($content, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        return [];
    }
    return $data;
}

/**
 * Écrit le tableau des utilisateurs dans le fichier JSON.
 */
function ecrireUtilisateurs($utilisateurs) {
    $dir = dirname(USERS_FILE);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    $json = json_encode($utilisateurs, JSON_PRETTY_PRINT);
    if ($json === false) {
        return false;
    }
    return file_put_contents(USERS_FILE, $json) !== false;
}

/**
 * Trouve un utilisateur par son identifiant.
 */
function trouverUtilisateurParIdentifiant($identifiant) {
    $utilisateurs = lireUtilisateurs();
    foreach ($utilisateurs as $utilisateur) {
        if (isset($utilisateur['identifiant']) && $utilisateur['identifiant'] === $identifiant) {
            return $utilisateur;
        }
    }
    return null;
}

function identifiantExiste($identifiant) {
    return trouverUtilisateurParIdentifiant($identifiant) !== null;
}

function ajouterUtilisateur($newUser) {
    $utilisateurs = lireUtilisateurs();
    $required = ['identifiant', 'mot_de_passe', 'role', 'nom_complet'];
    foreach ($required as $field) {
        if (!isset($newUser[$field]) || empty($newUser[$field])) {
            return false;
        }
    }
    if (identifiantExiste($newUser['identifiant'])) {
        return false;
    }
    if (!isset($newUser['date_creation'])) {
        $newUser['date_creation'] = date('Y-m-d');
    }
    if (!isset($newUser['actif'])) {
        $newUser['actif'] = true;
    }
    $utilisateurs[] = $newUser;
    return ecrireUtilisateurs($utilisateurs);
}

function desactiverUtilisateur($identifiant) {
    $utilisateurs = lireUtilisateurs();
    $found = false;
    foreach ($utilisateurs as $key => $user) {
        if (isset($user['identifiant']) && $user['identifiant'] === $identifiant) {
            $utilisateurs[$key]['actif'] = false;
            $found = true;
            break;
        }
    }
    if (!$found) {
        return false;
    }
    return ecrireUtilisateurs($utilisateurs);
}

/**
 * Initialise le fichier utilisateurs avec un super-admin si nécessaire.
 */
function initialiserFichierUtilisateurs() {
    if (!file_exists(USERS_FILE)) {
        $default_password = password_hash('admin123', PASSWORD_DEFAULT);
        $default_user = [
            'identifiant'    => 'superadmin',
            'mot_de_passe'   => $default_password,
            'role'           => ROLE_SUPER_ADMIN,
            'nom_complet'    => 'Super Administrateur',
            'date_creation'  => date('Y-m-d'),
            'actif'          => true
        ];
        ecrireUtilisateurs([$default_user]);
    } else {
        $users = lireUtilisateurs();
        if (empty($users)) {
            $default_password = password_hash('admin123', PASSWORD_DEFAULT);
            $default_user = [
                'identifiant'    => 'superadmin',
                'mot_de_passe'   => $default_password,
                'role'           => ROLE_SUPER_ADMIN,
                'nom_complet'    => 'Super Administrateur',
                'date_creation'  => date('Y-m-d'),
                'actif'          => true
            ];
            ecrireUtilisateurs([$default_user]);
        }
    }
}

// Supprimer définitivement un utilisateur par identifiant
function supprimerUtilisateur($identifiant) {
    $utilisateurs = lireUtilisateurs();
    $nouvelle_liste = array_filter($utilisateurs, function($u) use ($identifiant) {
        return $u['identifiant'] !== $identifiant;
    });
    if (count($nouvelle_liste) === count($utilisateurs)) {
        return false; // non trouvé
    }
    return ecrireUtilisateurs(array_values($nouvelle_liste));
}

// Modifier un utilisateur (champs: nom_complet, role, mot_de_passe optionnel)
function modifierUtilisateur($identifiant, $nom_complet, $role, $new_password = null) {
    $utilisateurs = lireUtilisateurs();
    foreach ($utilisateurs as &$u) {
        if ($u['identifiant'] === $identifiant) {
            $u['nom_complet'] = $nom_complet;
            $u['role'] = $role;
            if ($new_password !== null && !empty($new_password)) {
                $u['mot_de_passe'] = password_hash($new_password, PASSWORD_DEFAULT);
            }
            return ecrireUtilisateurs($utilisateurs);
        }
    }
    return false;
}
?>