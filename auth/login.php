<?php

require_once '../config/config.php';   // définit les constantes et démarre la session
require_once '../includes/fonctions-auth.php';

$error = '';

initialiserFichierUtilisateurs(); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = isset($_POST['identifiant']) ? trim($_POST['identifiant']) : '';
    $mot_de_passe = isset($_POST['mot_de_passe']) ? $_POST['mot_de_passe'] : '';

    if (empty($identifiant) || empty($mot_de_passe)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        $user = trouverUtilisateurParIdentifiant($identifiant);
        if ($user && $user['actif'] && password_verify($mot_de_passe, $user['mot_de_passe'])) {
            $_SESSION['user'] = [
                'identifiant' => $user['identifiant'],
                'nom_complet' => $user['nom_complet'],
                'role'        => $user['role']
            ];
            // Tous les utilisateurs vont vers index.php
            header('Location: ../index.php');
            exit();
        } else {
            $error = "Identifiant ou mot de passe incorrect, ou compte inactif.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Système de facturation</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body class="body-login">
    <div class="login-container">
        <h1>Connexion</h1>
        <?php if (!empty($error)): ?>
            <div class="error-message">
                <i class='bx bx-error-circle'></i>
                <span><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        <?php endif; ?>
        <form method="post" class="login-form">
            <div class="form-group">
                <label for="identifiant">Identifiant</label>
                <div class="input-icon">
                    <i class='bx bx-user'></i>
                    <input type="text" name="identifiant" id="identifiant" required>
                </div>
            </div>
            <div class="form-group">
                <label for="mot_de_passe">Mot de passe</label>
                <div class="input-icon">
                    <i class='bx bx-lock-alt'></i>
                    <input type="password" name="mot_de_passe" id="mot_de_passe" required>
                </div>
            </div>
            <button type="submit" class="btn-login">Se connecter</button>
        </form>
    </div>
</body>
</html>