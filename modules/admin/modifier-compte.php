<?php
// modules/administration/modifier-compte.php
require_once '../../config/config.php';
require_once '../../includes/fonctions-auth.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== ROLE_SUPER_ADMIN) {
    header('Location: ../../index.php');
    exit();
}

$identifiant = $_GET['identifiant'] ?? '';
if (empty($identifiant)) {
    header('Location: gestion-comptes.php?msg=Identifiant+manquant');
    exit();
}

$user = trouverUtilisateurParIdentifiant($identifiant);
if (!$user || $user['role'] === ROLE_SUPER_ADMIN) {
    header('Location: gestion-comptes.php?msg=Modification+non+autorisée');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_complet = trim($_POST['nom_complet'] ?? '');
    $role = $_POST['role'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    
    if (empty($nom_complet) || empty($role)) {
        $error = "Nom complet et rôle obligatoires.";
    } elseif (!in_array($role, [ROLE_CAISSIER, ROLE_MANAGER])) {
        $error = "Rôle invalide.";
    } else {
        if (modifierUtilisateur($identifiant, $nom_complet, $role, $new_password)) {
            $success = "Modification enregistrée.";
        } else {
            $error = "Erreur lors de la modification.";
        }
    }
}

include '../../includes/header.php';
?>

<main style="padding-top: 80px;">
    <div class="admin-container">
        <h1>Modifier le compte : <?= htmlspecialchars($identifiant) ?></h1>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?> <a href="gestion-comptes.php">Retour</a></div>
        <?php endif; ?>
        <form method="post" class="modify-form">
            <div class="form-group">
                <label>Nom complet</label>
                <input type="text" name="nom_complet" value="<?= htmlspecialchars($user['nom_complet']) ?>" required>
            </div>
            <div class="form-group">
                <label>Rôle</label>
                <select name="role" required>
                    <option value="<?= ROLE_CAISSIER ?>" <?= $user['role'] === ROLE_CAISSIER ? 'selected' : '' ?>>Caissier</option>
                    <option value="<?= ROLE_MANAGER ?>" <?= $user['role'] === ROLE_MANAGER ? 'selected' : '' ?>>Manager</option>
                </select>
            </div>
            <div class="form-group">
                <label>Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                <input type="password" name="new_password">
            </div>
            <button type="submit" class="btn-submit">Enregistrer les modifications</button>
        </form>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>