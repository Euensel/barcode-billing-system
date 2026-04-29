<?php
require_once __DIR__ . '/../config/config.php';

// Vérifier si le bouton de déconnexion existe ou accès direct
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    
    // Vider la session
    $_SESSION = array();
    
    // Détruire le cookie de session
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Détruire la session
    session_destroy();
}

// Redirection systématique
header('Location: login.php');
exit;
?>