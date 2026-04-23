<?php
// rapports/rapport-journalier.php
require_once '../config/config.php';
require_once '../includes/fonctions-auth.php';
require_once '../includes/fonctions-factures.php';

// Accès réservé aux managers et super admin
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], [ROLE_MANAGER, ROLE_SUPER_ADMIN])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
// Validation simple du format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $date = date('Y-m-d');
}

$factures = lireFactures();
// Filtrer les factures du jour
$factures_jour = array_filter($factures, function($f) use ($date) {
    return $f['date'] === $date;
});

$nb_factures = count($factures_jour);
$total_ht = array_sum(array_column($factures_jour, 'total_ht'));
$total_tva = array_sum(array_column($factures_jour, 'tva'));
$total_ttc = array_sum(array_column($factures_jour, 'total_ttc'));

// Trier par heure décroissante
usort($factures_jour, function($a, $b) {
    return strtotime($b['heure']) - strtotime($a['heure']);
});

include '../includes/header.php';
?>
<div class="main-content">
    <div class="container">
        <h1>Rapport journalier</h1>

        <!-- Formulaire de sélection de date -->
        <div class="filter-bar">
            <form method="get" class="filter-form">
                <div class="form-group">
                    <label for="date">Date :</label>
                    <input type="date" name="date" id="date" value="<?= htmlspecialchars($date) ?>">
                </div>
                <button type="submit" class="btn-submit"><i class='bx bx-calendar'></i> Voir</button>
            </form>
        </div>

        <!-- Résumé des ventes -->
        <div class="stats-cards">
            <div class="stat-card">
                <i class='bx bx-receipt'></i>
                <h3>Factures</h3>
                <div class="number"><?= $nb_factures ?></div>
            </div>
            <div class="stat-card">
                <i class='bx bx-dollar'></i>
                <h3>Total HT</h3>
                <div class="number"><?= number_format($total_ht, 2) ?> CDF</div>
            </div>
            <div class="stat-card">
                <i class='bx bx-tax'></i>
                <h3>TVA (18%)</h3>
                <div class="number"><?= number_format($total_tva, 2) ?> CDF</div>
            </div>
            <div class="stat-card">
                <i class='bx bx-money'></i>
                <h3>Net à payer</h3>
                <div class="number"><?= number_format($total_ttc, 2) ?> CDF</div>
            </div>
        </div>

        <!-- Liste des factures du jour -->
        <?php if ($nb_factures > 0): ?>
            <div class="invoices-list">
                <h2>Factures du <?= date('d/m/Y', strtotime($date)) ?></h2>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>N° facture</th>
                                <th>Heure</th>
                                <th>Caissier</th>
                                <th>Total TTC</th>
                                <th>Détail</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($factures_jour as $f): ?>
                            <tr>
                                <td><?= htmlspecialchars($f['id_facture']) ?></td>
                                <td><?= htmlspecialchars($f['heure']) ?></td>
                                <td><?= htmlspecialchars($f['caissier']) ?></td>
                                <td><?= number_format($f['total_ttc'], 2) ?> CDF</td>
                                <td><a href="<?= BASE_URL ?>/modules/facturation/facture_detail.php?id=<?= urlencode($f['id_facture']) ?>" class="btn-view"><i class='bx bx-show'></i> Voir</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="alert-info">
                <i class='bx bx-info-circle'></i> Aucune facture trouvée pour cette date.
            </div>
        <?php endif; ?>
        <!-- Lien vers rapport mensuel -->
        <div class="navigation-links">
            <a href="rapport-mensuel.php" class="btn-link"><i class='bx bx-calendar-week'></i> Voir rapport mensuel</a>
        </div>
    </div>
</div>


<?php include '../includes/footer.php'; ?>