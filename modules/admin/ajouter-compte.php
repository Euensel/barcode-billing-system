<?php
// modules/administration/ajouter-compte.php
require_once '../../config/config.php';
require_once '../../includes/fonctions-auth.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== ROLE_SUPER_ADMIN) {
    header('Location: ../../index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = trim($_POST['identifiant'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    $nom_complet = trim($_POST['nom_complet'] ?? '');
    
    if (empty($identifiant) || empty($password) || empty($role) || empty($nom_complet)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif (!in_array($role, [ROLE_CAISSIER, ROLE_MANAGER])) {
        $error = "Rôle non autorisé (choisir Caissier ou Manager).";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $newUser = [
            'identifiant' => $identifiant,
            'mot_de_passe' => $hashed,
            'role' => $role,
            'nom_complet' => $nom_complet,
            'date_creation' => date('Y-m-d'),
            'actif' => true
        ];
        if (ajouterUtilisateur($newUser)) {
            $success = "Compte ajouté avec succès.";
        } else {
            $error = "Identifiant déjà existant ou erreur d'écriture.";
        }
    }
}

include '../../includes/header.php';
?>

<main style="padding-top: 80px;">
    <div class="admin-container">
        <h1>Ajouter un compte</h1>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?> <a href="gestion-comptes.php">Retour à la liste</a></div>
        <?php endif; ?>
        <form method="post" class="admin-form">
            <div class="form-group">
                <label>Identifiant</label>
                <input type="text" name="identifiant" required>
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Nom complet</label>
                <input type="text" name="nom_complet" required>
            </div>
            <div class="form-group">
                <label>Rôle</label>
                <select name="role" required>
                    <option value="<?= ROLE_CAISSIER ?>">Caissier</option>
                    <option value="<?= ROLE_MANAGER ?>">Manager</option>
                </select>
            </div>
            <button type="submit" class="btn-submit">Créer le compte</button>
        </form>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>