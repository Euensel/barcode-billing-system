<?php
require_once __DIR__ . 'session.php';

// Vérifier que l'utilisateur est Super Administrateur
verifier_role([ROLE_SUPER_ADMIN]);

$message = '';
$erreur = '';

// Ajout d'un utilisateur
if (isset($_POST['btn_ajouter'])) {
    
    if (isset($_POST['identifiant']) && isset($_POST['mot_de_passe']) && isset($_POST['nom_complet']) && isset($_POST['role'])) {
        
        $identifiant = htmlspecialchars(trim($_POST['identifiant']));
        $mot_de_passe = trim($_POST['mot_de_passe']);
        $nom_complet = htmlspecialchars(trim($_POST['nom_complet']));
        $role = htmlspecialchars(trim($_POST['role']));
        
        if (empty($identifiant) || empty($mot_de_passe) || empty($nom_complet) || empty($role)) {
            $erreur = 'Tous les champs sont obligatoires';
        } elseif (!in_array($role, [ROLE_CAISSIER, ROLE_MANAGER, ROLE_SUPER_ADMIN])) {
            $erreur = 'Rôle invalide';
        } else {
            $utilisateurs = json_decode(file_get_contents(USERS_FILE), true);
            
            // Vérifier si identifiant existe déjà
            $existe = false;
            foreach ($utilisateurs as $u) {
                if ($u['identifiant'] === $identifiant) {
                    $existe = true;
                    break;
                }
            }
            
            if ($existe) {
                $erreur = 'Cet identifiant existe déjà';
            } else {
                $nouvel_utilisateur = [
                    'identifiant' => $identifiant,
                    'mot_de_passe' => password_hash($mot_de_passe, PASSWORD_DEFAULT),
                    'role' => $role,
                    'nom_complet' => $nom_complet,
                    'date_creation' => date('Y-m-d'),
                    'actif' => true
                ];
                
                $utilisateurs[] = $nouvel_utilisateur;
                file_put_contents(USERS_FILE, json_encode($utilisateurs, JSON_PRETTY_PRINT));
                $message = 'Utilisateur ajouté avec succès';
            }
        }
    } else {
        $erreur = 'Formulaire incomplet';
    }
}

// Suppression d'un utilisateur
if (isset($_GET['supprimer'])) {
    $identifiant_suppr = htmlspecialchars(trim($_GET['supprimer']));
    
    if ($identifiant_suppr === $_SESSION['user']['identifiant']) {
        $erreur = 'Vous ne pouvez pas supprimer votre propre compte';
    } else {
        $utilisateurs = json_decode(file_get_contents(USERS_FILE), true);
        $nouvelle_liste = [];
        foreach ($utilisateurs as $u) {
            if ($u['identifiant'] !== $identifiant_suppr) {
                $nouvelle_liste[] = $u;
            }
        }
        file_put_contents(USERS_FILE, json_encode($nouvelle_liste, JSON_PRETTY_PRINT));
        $message = 'Utilisateur supprimé';
    }
}

// Activation/Désactivation
if (isset($_GET['activer'])) {
    $identifiant_toggle = htmlspecialchars(trim($_GET['activer']));
    $utilisateurs = json_decode(file_get_contents(USERS_FILE), true);
    
    foreach ($utilisateurs as &$u) {
        if ($u['identifiant'] === $identifiant_toggle) {
            $u['actif'] = !$u['actif'];
            break;
        }
    }
    file_put_contents(USERS_FILE, json_encode($utilisateurs, JSON_PRETTY_PRINT));
    $message = 'Statut modifié';
}

$utilisateurs = json_decode(file_get_contents(USERS_FILE), true);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestion des utilisateurs</title>
</head>
<body>
    <h2>Gestion des utilisateurs</h2>
    
    <?php if (!empty($message)): ?>
        <p style="color:green"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    
    <?php if (!empty($erreur)): ?>
        <p style="color:red"><?= htmlspecialchars($erreur) ?></p>
    <?php endif; ?>
    
    <h3>Ajouter un utilisateur</h3>
    <form method="POST">
        <input type="text" name="identifiant" placeholder="Identifiant" required><br><br>
        <input type="password" name="mot_de_passe" placeholder="Mot de passe" required><br><br>
        <input type="text" name="nom_complet" placeholder="Nom complet" required><br><br>
        <select name="role">
            <option value="<?= ROLE_CAISSIER ?>">Caissier</option>
            <option value="<?= ROLE_MANAGER ?>">Manager</option>
        </select><br><br>
        <button type="submit" name="btn_ajouter">Ajouter</button>
    </form>
    
    <h3>Liste des utilisateurs</h3>
    <table border="1">
        <tr><th>Identifiant</th><th>Nom complet</th><th>Rôle</th><th>Statut</th><th>Actions</th></tr>
        <?php foreach ($utilisateurs as $u): ?>
        <tr>
            <td><?= htmlspecialchars($u['identifiant']) ?></td>
            <td><?= htmlspecialchars($u['nom_complet']) ?></td>
            <td><?= htmlspecialchars($u['role']) ?></td>
            <td><?= $u['actif'] ? 'Actif' : 'Inactif' ?></td>
            <td>
                <a href="?activer=<?= urlencode($u['identifiant']) ?>">Activer/Désactiver</a>
                <?php if ($u['identifiant'] !== $_SESSION['user']['identifiant']): ?>
                    | <a href="?supprimer=<?= urlencode($u['identifiant']) ?>" onclick="return confirm('Supprimer ?')">Supprimer</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <a href="../index.php">Retour à l'accueil</a>
</body>
</html>