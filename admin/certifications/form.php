<?php
// admin/certifications/form.php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../upload_helper.php';

$id = $_GET['id'] ?? '';
$cert = null;
$error = '';

if (!empty($id)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM certifications WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $cert = $stmt->fetch();
        if (!$cert) {
            header('Location: index.php?error=' . urlencode('Certificación no encontrada.'));
            exit;
        }
    } catch (PDOException $e) {
        $error = "Error al buscar la certificación: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $institution = $_POST['institution'] ?? '';
    $issuedDate = $_POST['issued_date'] ?? '';
    $displayOrder = (int)($_POST['display_order'] ?? 10);

    // Procesar archivo de certificación
    $fileUrl = $_POST['file_url_existing'] ?? '';
    if (isset($_FILES['cert_file']) && $_FILES['cert_file']['error'] === UPLOAD_ERR_OK) {
        $uploadRes = uploadFile($_FILES['cert_file'], ['pdf', 'jpg', 'jpeg', 'png', 'webp'], 'certifications');
        if ($uploadRes['success']) {
            $fileUrl = $uploadRes['url'];
        } else {
            $error .= " Error certificado: " . $uploadRes['message'];
        }
    }

    try {
        if ($cert) {
            // Actualizar
            $updateStmt = $pdo->prepare("UPDATE certifications SET 
                name = :name,
                institution = :institution,
                issued_date = :issued_date,
                file_url = :file_url,
                display_order = :display_order
                WHERE id = :id");
            
            $updateStmt->execute([
                ':name' => $name,
                ':institution' => $institution,
                ':issued_date' => $issuedDate,
                ':file_url' => $fileUrl,
                ':display_order' => $displayOrder,
                ':id' => $id
            ]);
            
            header('Location: index.php?success=' . urlencode('Certificación actualizada con éxito.'));
            exit;
        } else {
            // Insertar nuevo
            $insertStmt = $pdo->prepare("INSERT INTO certifications (
                name, institution, issued_date, file_url, display_order, created_at
            ) VALUES (
                :name, :institution, :issued_date, :file_url, :display_order, NOW()
            )");
            
            $insertStmt->execute([
                ':name' => $name,
                ':institution' => $institution,
                ':issued_date' => $issuedDate,
                ':file_url' => $fileUrl,
                ':display_order' => $displayOrder
            ]);

            header('Location: index.php?success=' . urlencode('Certificación agregada con éxito.'));
            exit;
        }
    } catch (PDOException $e) {
        $error = "Error al guardar la certificación: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $cert ? 'Editar' : 'Agregar' ?> Certificación - Panel Admin</title>
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
            <h2><?= $cert ? 'Editar Certificación: ' . htmlspecialchars($cert['name']) : 'Agregar Nueva Certificación' ?></h2>
            <a href="index.php" class="btn-brutal-secondary" style="font-size: 0.85rem; padding: 0.5rem 1rem;">Volver a la lista</a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-box error">
                <strong>ERROR:</strong> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="brutal-card" style="margin-bottom: 3rem;">
            <div class="box-header">
                <span class="dot red"></span><span class="dot yellow"></span><span class="dot green"></span>
                <span class="box-title">cert_form.sh</span>
            </div>
            <div class="box-content">
                <form method="POST" action="form.php<?= $cert ? '?id=' . urlencode($cert['id']) : '' ?>" enctype="multipart/form-data" class="brutal-form">
                    
                    <div class="form-group">
                        <label for="name">Nombre de la Certificación</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($cert['name'] ?? '') ?>" required placeholder="Ej: CCNA Introduction to networks (CISCO)">
                    </div>

                    <div class="form-group">
                        <label for="institution">Institución Emisora</label>
                        <input type="text" id="institution" name="institution" value="<?= htmlspecialchars($cert['institution'] ?? '') ?>" required placeholder="Ej: Cisco Networking Academy">
                    </div>

                    <div class="form-group">
                        <label for="issued_date">Fecha de Emisión (Texto o Año)</label>
                        <input type="text" id="issued_date" name="issued_date" value="<?= htmlspecialchars($cert['issued_date'] ?? '') ?>" required placeholder="Ej: 2026, Enero 2026, etc.">
                    </div>

                    <div class="form-group">
                        <label>Archivo del Certificado (PDF o Imagen)</label>
                        <?php if (!empty($cert['file_url'])): ?>
                            <div style="margin-bottom: 0.5rem;">
                                <a href="<?= htmlspecialchars(getStorageUrl($cert['file_url'])) ?>" target="_blank" style="color: var(--accent-blue); font-size: 0.85rem; text-decoration: underline;">📄 Ver documento cargado actualmente</a>
                            </div>
                        <?php endif; ?>
                        <input type="hidden" name="file_url_existing" value="<?= htmlspecialchars($cert['file_url'] ?? '') ?>">
                        <input type="file" id="cert_file" name="cert_file" accept=".pdf,image/*">
                    </div>

                    <div class="form-group">
                        <label for="display_order">Orden de Visualización (Números más pequeños se muestran primero)</label>
                        <input type="number" id="display_order" name="display_order" value="<?= htmlspecialchars($cert['display_order'] ?? '10') ?>" required min="0">
                    </div>

                    <button type="submit" class="btn-brutal-primary w-100"><?= $cert ? 'Guardar Cambios' : 'Agregar Certificación' ?></button>
                </form>
            </div>
        </div>

    </main>

</body>
</html>
