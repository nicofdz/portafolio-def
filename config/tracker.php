<?php
// config/tracker.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';

$sessionId = session_id();

try {
    // 1. Registrar o actualizar sesión activa del visitante
    $stmt = $pdo->prepare("
        INSERT INTO active_sessions (session_id, last_activity) 
        VALUES (:session_id, NOW()) 
        ON CONFLICT (session_id) 
        DO UPDATE SET last_activity = NOW()
    ");
    $stmt->execute([':session_id' => $sessionId]);

    // 2. Limpiar sesiones que lleven más de 1 minuto inactivas
    $pdo->exec("DELETE FROM active_sessions WHERE last_activity < NOW() - INTERVAL '1 minute'");

    // 3. Incrementar visitas totales (una sola vez por sesión del navegador)
    if (!isset($_SESSION['view_counted'])) {
        $pdo->exec("UPDATE portfolio_settings SET view_count = COALESCE(view_count, 0) + 1");
        $_SESSION['view_counted'] = true;
    }

    // 4. Obtener datos actuales
    $activeUsers = $pdo->query("SELECT COUNT(*) FROM active_sessions")->fetchColumn();
    $totalViews = $pdo->query("SELECT view_count FROM portfolio_settings LIMIT 1")->fetchColumn();

} catch (PDOException $e) {
    error_log("Error en Tracker: " . $e->getMessage());
    $activeUsers = 1;
    $totalViews = 0;
}
?>
