<?php

require_once '../../config/config.php';
require_once '../../includes/fonctions-auth.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== ROLE_SUPER_ADMIN) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

$tous = lireUtilisateurs();
$users = array_filter($tous, function($u) {
    return $u['role'] === ROLE_CAISSIER || $u['role'] === ROLE_MANAGER;
});

// Trier par statut actif (les actifs en premier) pour meilleure lisibilité
usort($users, function($a, $b) {
    if ($a['actif'] == $b['actif']) return 0;
    return ($a['actif'] > $b['actif']) ? -1 : 1;
});

include '../../includes/header.php';
?>
<div class="main-content">
    <div class="container">
        <h1>Gestion des comptes</h1>
        <p class="subtitle">Caissiers & Managers</p>

        <div class="action-bar">
            <a href="ajouter-compte.php" class="btn btn-primary">
                <i class='bx bx-user-plus'></i> Ajouter un compte
            </a>
        </div>

        <?php 
        if (isset($_GET['msg'])) {
            if ($_GET['msg'] === 'toggled') {
                echo '<div class="alert alert-success"><i class="bx bx-check-circle"></i> Statut du compte modifié avec succès.</div>';
            } elseif ($_GET['msg'] === 'self') {
                echo '<div class="alert alert-error"><i class="bx bx-error-circle"></i> Vous ne pouvez pas désactiver votre propre compte.</div>';
            } elseif ($_GET['msg'] === 'error') {
                echo '<div class="alert alert-error"><i class="bx bx-error-circle"></i> Une erreur est survenue.</div>';
            }
        }
        ?>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Identifiant</th>
                        <th>Nom complet</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Date création</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['identifiant']) ?></td>
                        <td><?= htmlspecialchars($u['nom_complet']) ?></td>
                        <td><?= htmlspecialchars($u['role']) ?></td>
                        <td>
                            <?php if ($u['actif']): ?>
                                <span class="badge badge-success">Actif</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Inactif</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($u['date_creation']) ?></td>
                        <td class="actions">
                            <a href="modifier-compte.php?identifiant=<?= urlencode($u['identifiant']) ?>" class="btn-icon" title="Modifier">
                                <i class='bx bx-edit-alt'></i>
                            </a>
                            <a href="activer-desactiver-compte.php?identifiant=<?= urlencode($u['identifiant']) ?>" 
                               class="btn-icon <?= $u['actif'] ? 'warning' : 'success' ?>" 
                               title="<?= $u['actif'] ? 'Désactiver' : 'Activer' ?>"
                               onclick="return confirm('<?= $u['actif'] ? 'Désactiver' : 'Activer' ?> ce compte ?')">
                                <i class='bx <?= $u['actif'] ? 'bx-lock' : 'bx-lock-open-alt' ?>'></i>
                            </a>
                            <a href="supprimer-compte.php?identifiant=<?= urlencode($u['identifiant']) ?>" 
                               class="btn-icon danger" 
                               onclick="return confirm('Supprimer définitivement ce compte ?')" 
                               title="Supprimer">
                                <i class='bx bx-trash'></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>