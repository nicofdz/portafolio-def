<?php
// admin/certifications/index.php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config/db.php';

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

try {
    $stmt = $pdo->query("SELECT * FROM certifications ORDER BY display_order ASC, created_at DESC");
    $certifications = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error al cargar las certificaciones: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Certificaciones - Panel Admin</title>
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
                    <li><a href="../projects/index.php">Proyectos</a></li>
                    <li><a href="index.php" class="active">Certificaciones</a></li>
                </ul>
            </nav>
            <a href="../logout.php" class="btn-logout">Cerrar Sesión</a>
        </div>
    </header>

    <main class="container">
        
        <div class="admin-section-header">
            <h2>Gestionar Certificaciones</h2>
            <a href="form.php" class="btn-brutal-primary" style="font-size: 0.9rem; padding: 0.6rem 1.2rem;">+ Agregar Certificado</a>
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
                        <th>Nombre</th>
                        <th>Institución</th>
                        <th>Fecha de Emisión</th>
                        <th style="width: 100px; text-align: center;">Orden</th>
                        <th style="width: 180px; text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($certifications)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted);">No hay certificaciones registradas.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($certifications as $cert): ?>
                            <tr>
                                <td style="font-weight: bold; color: white;">
                                    <?= htmlspecialchars($cert['name']) ?>
                                </td>
                                <td><?= htmlspecialchars($cert['institution']) ?></td>
                                <td><?= htmlspecialchars($cert['issued_date']) ?></td>
                                <td style="text-align: center;"><?= (int)$cert['display_order'] ?></td>
                                <td>
                                    <div class="actions-cell" style="justify-content: center;">
                                        <a href="form.php?id=<?= urlencode($cert['id']) ?>" class="btn-admin-action edit">Editar</a>
                                        <a href="delete.php?id=<?= urlencode($cert['id']) ?>" class="btn-admin-action delete" onclick="return confirm('¿Estás seguro de que deseas eliminar esta certificación?');">Eliminar</a>
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
