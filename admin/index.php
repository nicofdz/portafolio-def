<?php
// admin/index.php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';

try {
    $projectCount = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
    $certCount = $pdo->query("SELECT COUNT(*) FROM certifications")->fetchColumn();
    $settings = $pdo->query("SELECT hero_title FROM portfolio_settings LIMIT 1")->fetch();
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

        </div>
    </main>

</body>
</html>
