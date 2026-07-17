<?php
// admin/index.php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

try {
    $projectCount = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
    $certCount = $pdo->query("SELECT COUNT(*) FROM certifications")->fetchColumn();
    $settings = $pdo->query("SELECT hero_title, view_count FROM portfolio_settings LIMIT 1")->fetch();
    
    // Limpiar sesiones inactivas y contar usuarios en línea
    $pdo->exec("DELETE FROM active_sessions WHERE last_activity < NOW() - INTERVAL '1 minute'");
    $activeCount = $pdo->query("SELECT COUNT(*) FROM active_sessions")->fetchColumn();
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Nicolás Fernández</title>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600&family=Outfit:wght@700;800;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>

    <header class="admin-navbar">
        <div class="container admin-nav-wrapper">
            <a href="index.php" class="logo">
                NF<span>.admin</span>
            </a>
            <nav>
                <ul class="admin-menu">
                    <li><a href="index.php" class="active">Inicio</a></li>
                    <li><a href="settings.php">Ajustes</a></li>
                    <li><a href="projects/index.php">Proyectos</a></li>
                    <li><a href="certifications/index.php">Certificaciones</a></li>
                </ul>
            </nav>
            <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
        </div>
    </header>

    <main class="container">
        <div class="admin-section-header">
            <h2>Bienvenido, Administrador</h2>
            <a href="../" target="_blank" class="btn-brutal-secondary" style="font-size: 0.85rem; padding: 0.5rem 1rem;">Ver Sitio Público</a>
        </div>

        <?php if (!empty($success)): ?>
            <div class="alert success" style="margin-bottom: 1.5rem; background: rgba(30, 136, 229, 0.08); border: 2px solid var(--accent-blue); color: var(--accent-blue); padding: 1rem; font-family: 'Fira Code', monospace; font-size: 0.9rem; font-weight: bold;"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert error" style="margin-bottom: 1.5rem; background: rgba(239, 68, 68, 0.08); border: 2px solid var(--accent-red); color: var(--accent-red); padding: 1rem; font-family: 'Fira Code', monospace; font-size: 0.9rem; font-weight: bold;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="admin-dashboard-grid">
            
            <div class="brutal-card">
                <div class="box-header">
                    <span class="dot red"></span><span class="dot yellow"></span><span class="dot green"></span>
                    <span class="box-title">settings_info.sh</span>
                </div>
                <div class="admin-card-body">
                    <h3>Ajustes Generales</h3>
                    <p>Modifica el título principal, tu biografía, redes sociales y archivo de currículum.</p>
                    <a href="settings.php" class="btn-brutal-primary">Editar Ajustes</a>
                </div>
            </div>

            <div class="brutal-card">
                <div class="box-header">
                    <span class="dot red"></span><span class="dot yellow"></span><span class="dot green"></span>
                    <span class="box-title">projects_count.sh</span>
                </div>
                <div class="admin-card-body">
                    <div class="dashboard-stat"><?= (int)$projectCount ?></div>
                    <h3>Proyectos Destacados</h3>
                    <p>Agrega, edita o elimina los proyectos que se muestran en el carrusel de tu portafolio.</p>
                    <a href="projects/index.php" class="btn-brutal-primary">Gestionar Proyectos</a>
                </div>
            </div>

            <div class="brutal-card">
                <div class="box-header">
                    <span class="dot red"></span><span class="dot yellow"></span><span class="dot green"></span>
                    <span class="box-title">certifications_count.sh</span>
                </div>
                <div class="admin-card-body">
                    <div class="dashboard-stat"><?= (int)$certCount ?></div>
                    <h3>Certificaciones</h3>
                    <p>Administra las certificaciones obtenidas y su orden de prioridad en la tabla.</p>
                    <a href="certifications/index.php" class="btn-brutal-primary">Gestionar Certificados</a>
                </div>
            </div>

            <div class="brutal-card">
                <div class="box-header">
                    <span class="dot red"></span><span class="dot yellow"></span><span class="dot green"></span>
                    <span class="box-title">traffic_monitor.sh</span>
                </div>
                <div class="admin-card-body">
                    <div class="dashboard-stat" style="font-size: 1.8rem; display: flex; justify-content: space-around; font-family: 'Fira Code', monospace; margin-bottom: 1rem;">
                        <span style="color: white; font-weight: 900; font-size: 2.2rem;"><?= number_format($settings['view_count'] ?? 0) ?><span style="display:block; font-size: 0.8rem; color: var(--text-muted); font-weight: normal; margin-top: 0.2rem;">Visitas</span></span>
                        <span style="color: #25D366; font-weight: 900; font-size: 2.2rem;"><?= (int)$activeCount ?><span style="display:block; font-size: 0.8rem; color: var(--text-muted); font-weight: normal; margin-top: 0.2rem;">En Línea</span></span>
                    </div>
                    <h3>Monitor de Tráfico</h3>
                    <p>Monitorea las visitas totales acumuladas y los navegadores conectados actualmente en tiempo real.</p>
                    <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                        <button onclick="window.location.reload();" class="btn-brutal-secondary" style="flex: 1; padding: 0.6rem; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 0.3rem; cursor: pointer;"><i class="ph-bold ph-arrows-clockwise"></i> Actualizar</button>
                        <a href="reset_views.php" class="btn-brutal-secondary" onclick="return confirm('¿Estás seguro de que deseas reiniciar el conteo de visitas a 0? Esta acción no se puede deshacer.');" style="flex: 1; padding: 0.6rem; font-size: 0.85rem; text-decoration: none; color: var(--accent-red); border-color: rgba(239, 68, 68, 0.4); display: flex; align-items: center; justify-content: center; gap: 0.3rem; text-align: center; cursor: pointer;"><i class="ph-bold ph-trash"></i> Reiniciar</a>
                    </div>
                </div>
            </div>

        </div>
    </main>

</body>
</html>
