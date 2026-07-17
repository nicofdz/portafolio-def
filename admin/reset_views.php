<?php
// admin/reset_views.php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';

try {
    // Poner contador de visitas a 0
    $pdo->exec("UPDATE portfolio_settings SET view_count = 0");
    
    header('Location: index.php?success=' . urlencode('El contador de visitas se ha reiniciado correctamente.'));
    exit;
} catch (PDOException $e) {
    header('Location: index.php?error=' . urlencode('Error al reiniciar el contador de visitas: ' . $e->getMessage()));
    exit;
}
?>
