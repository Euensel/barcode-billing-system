<?php
// index.php
require_once 'config/config.php';
require_once 'includes/fonctions-auth.php';
require_once 'includes/fonctions-produits.php';
require_once 'includes/fonctions-factures.php';

if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit();
}

$role = $_SESSION['user']['role'];
$nom = $_SESSION['user']['nom_complet'];

// --- Statistiques ---
$today = date('Y-m-d');
$factures = lireFactures();
$factures_jour = array_filter($factures, function($f) use ($today) {
    return $f['date'] === $today;
});
$nb_factures_jour = count($factures_jour);
$ca_journalier = array_sum(array_column($factures_jour, 'total_ttc'));

$produits = lireProduits();
$nb_produits = count($produits);
$stock_total = array_sum(array_column($produits, 'quantite_stock'));

if ($role === ROLE_SUPER_ADMIN) {
    $utilisateurs = lireUtilisateurs();
    $nb_utilisateurs = count($utilisateurs);
    // Compter les utilisateurs actifs
    $nb_actifs = count(array_filter($utilisateurs, function($u) { return $u['actif']; }));
}

include 'includes/header.php';
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>Tableau de bord</h1>
        <p class="welcome-text">
            <i class='bx bx-smile'></i> Bienvenue, <?= htmlspecialchars($nom) ?> !
        </p>
    </div>

    <!-- Cartes principales -->
    <div class="dashboard-grid">
        <?php if (in_array($role, [ROLE_CAISSIER, ROLE_MANAGER, ROLE_SUPER_ADMIN])): ?>
            <div class="dashboard-card">
                <div class="card-icon"><i class='bx bx-receipt'></i></div>
                <div class="card-content">
                    <h3>Factures aujourd'hui</h3>
                    <div class="number"><?= $nb_factures_jour ?></div>
                    <div class="sub">CA : <?= number_format($ca_journalier, 0) ?> CDF</div>
                    <a href="<?= BASE_URL ?>/modules/facturation/nouvelle-facture.php" class="card-link">+ Nouvelle facture</a>
                </div>
            </div>
        <?php endif; ?>

        <?php if (in_array($role, [ROLE_MANAGER, ROLE_SUPER_ADMIN])): ?>
            <div class="dashboard-card">
                <div class="card-icon"><i class='bx bx-package'></i></div>
                <div class="card-content">
                    <h3>Produits en stock</h3>
                    <div class="number"><?= $nb_produits ?></div>
                    <div class="sub">Total unités : <?= $stock_total ?></div>
                    <a href="<?= BASE_URL ?>/modules/produits/liste.php" class="card-link">Gérer le catalogue</a>
                </div>
            </div>
            <div class="dashboard-card">
                <div class="card-icon"><i class='bx bx-stats'></i></div>
                <div class="card-content">
                    <h3>Rapports</h3>
                    <div class="number"><?= date('F Y') ?></div>
                    <div class="sub">Ventes mensuelles</div>
                    <a href="<?= BASE_URL ?>/rapports/rapport-mensuel.php" class="card-link">Voir les rapports</a>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($role === ROLE_SUPER_ADMIN): ?>
            <div class="dashboard-card">
                <div class="card-icon"><i class='bx bx-group'></i></div>
                <div class="card-content">
                    <h3>Utilisateurs</h3>
                    <div class="number"><?= $nb_utilisateurs ?? 0 ?></div>
                    <div class="sub">Actifs : <?= $nb_actifs ?? 0 ?></div>
                    <a href="<?= BASE_URL ?>/modules/admin/gestion-comptes.php" class="card-link">Gérer les comptes</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Section supplémentaire : dernières factures (pour tout le monde) -->
    <?php if ($nb_factures_jour > 0): ?>
    <div class="recent-invoices">
        <h2>Dernières factures du jour</h2>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr><th>N° facture</th><th>Heure</th><th>Total TTC</th><th>Détail</th></tr>
                </thead>
                <tbody>
                    <?php 
                    $recent = array_slice($factures_jour, 0, 5);
                    usort($recent, function($a, $b) {
                        return strtotime($b['heure']) - strtotime($a['heure']);
                    });
                    foreach ($recent as $f): ?>
                    <tr>
                        <td><?= htmlspecialchars($f['id_facture']) ?></td>
                        <td><?= htmlspecialchars($f['heure']) ?></td>
                        <td><?= number_format($f['total_ttc'], 2) ?> CDF</td>
                        <td><a href="<?= BASE_URL ?>/modules/facturation/afficher-facture.php?id=<?= urlencode($f['id_facture']) ?>" class="btn-view">Voir</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Alertes stock faible (pour manager/admin) -->
    <?php if (in_array($role, [ROLE_MANAGER, ROLE_SUPER_ADMIN])): 
        $stock_faible = array_filter($produits, function($p) { return $p['quantite_stock'] <= 5; });
        if (!empty($stock_faible)): ?>
        <div class="alert-warning">
            <i class='bx bx-error-circle'></i>
            <strong>Attention :</strong> <?= count($stock_faible) ?> produit(s) ont un stock ≤ 5 unités.
            <a href="<?= BASE_URL ?>/modules/produits/liste.php">Voir la liste</a>
        </div>
    <?php endif; endif; ?>
</div>

<?php include 'includes/footer.php'; ?>