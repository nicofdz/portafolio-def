<?php
// admin/projects/index.php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config/db.php';

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

try {
    $stmt = $pdo->query("SELECT * FROM projects ORDER BY created_at DESC");
    $projects = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error al cargar los proyectos: " . $e->getMessage();
}

// Helper para parsear Postgres array {} a comas para mostrar brevemente
function getTagsString($postgresArray) {
    if (empty($postgresArray)) return '';
    $elements = str_getcsv(trim($postgresArray, '{}'));
    return implode(', ', array_map(function($el) { return trim($el, '" '); }, $elements));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Proyectos - Panel Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600&family=Outfit:wght@700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

    <header class="admin-navbar">
        <div class="container admin-nav-wrapper">
            <a href="../index.php" class="logo">
                NF<span>.admin</span>
            </a>
            <nav>
                <ul class="admin-menu">
                    <li><a href="../index.php">Inicio</a></li>
                    <li><a href="../settings.php">Ajustes</a></li>
                    <li><a href="index.php" class="active">Proyectos</a></li>
                    <li><a href="../certifications/index.php">Certificaciones</a></li>
                </ul>
            </nav>
            <a href="../logout.php" class="btn-logout">Cerrar Sesión</a>
        </div>
    </header>

    <main class="container">
        
        <div class="admin-section-header">
            <h2>Gestionar Proyectos Destacados</h2>
            <a href="form.php" class="btn-brutal-primary" style="font-size: 0.9rem; padding: 0.6rem 1.2rem;">+ Agregar Proyecto</a>
        </div>

        <?php if (!empty($success)): ?>
            <div class="alert-box success">
                <strong>ÉXITO:</strong> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert-box error">
                <strong>ERROR:</strong> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Tecnologías (Tags)</th>
                        <th>URLs de Enlace</th>
                        <th style="width: 100px; text-align: center;">Visible</th>
                        <th style="width: 180px; text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($projects)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted);">No hay proyectos registrados en este momento.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($projects as $project): ?>
                            <tr>
                                <td style="font-weight: bold; color: white;"><?= htmlspecialchars($project['title']) ?></td>
                                <td>
                                    <?php 
                                    $tags = !empty($project['tags']) ? str_getcsv(trim($project['tags'], '{}')) : [];
                                    foreach ($tags as $tag): 
                                    ?>
                                        <span class="tag-pill">#<?= htmlspecialchars(trim($tag, '" ')) ?></span>
                                    <?php endforeach; ?>
                                </td>
                                <td>
                                    <?php if (!empty($project['github_url'])): ?>
                                        <a href="<?= htmlspecialchars($project['github_url']) ?>" target="_blank" style="color: var(--accent-blue);">GitHub</a>
                                    <?php endif; ?>
                                    <?php if (!empty($project['github_url']) && !empty($project['live_url'])): ?> | <?php endif; ?>
                                    <?php if (!empty($project['live_url'])): ?>
                                        <a href="<?= htmlspecialchars($project['live_url']) ?>" target="_blank" style="color: #55a826;">Demo</a>
                                    <?php endif; ?>
                                <td style="text-align: center; vertical-align: middle;">
                                    <form method="POST" action="toggle_visibility.php" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($project['id']) ?>">
                                        <input type="checkbox" name="is_visible" value="1" <?= (!isset($project['is_visible']) || $project['is_visible']) ? 'checked' : '' ?> onchange="this.form.submit()" style="width: 18px; height: 18px; cursor: pointer; vertical-align: middle;">
                                    </form>
                                </td>
                                <td>
                                    <div class="actions-cell" style="justify-content: center;">
                                        <a href="form.php?id=<?= urlencode($project['id']) ?>" class="btn-admin-action edit">Editar</a>
                                        <a href="delete.php?id=<?= urlencode($project['id']) ?>" class="btn-admin-action delete" onclick="return confirm('¿Estás seguro de que deseas eliminar este proyecto?');">Eliminar</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>

</body>
</html>
