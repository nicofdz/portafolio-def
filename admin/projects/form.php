<?php
// admin/projects/form.php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../upload_helper.php';

$id = $_GET['id'] ?? '';
$project = null;
$error = '';

// Helper para convertir Postgres array {} a comas
function arrayToCsvString($postgresArray) {
    if (empty($postgresArray)) return '';
    if (is_array($postgresArray)) {
        return implode(', ', $postgresArray);
    }
    $elements = str_getcsv(trim($postgresArray, '{}'));
    return implode(', ', array_map(function($el) { return trim($el, '" '); }, $elements));
}

// Helper para convertir comas a Postgres array {}
function csvStringToPostgresArray($csvString) {
    $arr = array_filter(array_map('trim', explode(',', $csvString)));
    $elements = array_map(function($val) {
        return '"' . str_replace('"', '\\"', $val) . '"';
    }, $arr);
    return '{' . implode(',', $elements) . '}';
}

if (!empty($id)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $project = $stmt->fetch();
        if (!$project) {
            header('Location: index.php?error=' . urlencode('Proyecto no encontrado.'));
            exit;
        }
    } catch (PDOException $e) {
        $error = "Error al buscar el proyecto: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $githubUrl = $_POST['github_url'] ?? '';
    $liveUrl = $_POST['live_url'] ?? '';
    $tags = csvStringToPostgresArray($_POST['tags'] ?? '');
    $isVisible = isset($_POST['is_visible']) ? 1 : 0;
    $displayOrder = (int)($_POST['display_order'] ?? 10);
    
    // Procesar imágenes existentes a conservar
    $existingUrls = $_POST['existing_image_urls'] ?? [];
    
    // Procesar nuevas imágenes subidas
    $newUrls = [];
    if (isset($_FILES['project_images']) && !empty($_FILES['project_images']['name'][0])) {
        $filesCount = count($_FILES['project_images']['name']);
        for ($i = 0; $i < $filesCount; $i++) {
            if ($_FILES['project_images']['error'][$i] === UPLOAD_ERR_OK) {
                $tempFile = [
                    'name' => $_FILES['project_images']['name'][$i],
                    'type' => $_FILES['project_images']['type'][$i],
                    'tmp_name' => $_FILES['project_images']['tmp_name'][$i],
                    'error' => $_FILES['project_images']['error'][$i],
                    'size' => $_FILES['project_images']['size'][$i]
                ];
                $uploadRes = uploadFile($tempFile, ['jpg', 'jpeg', 'png', 'gif', 'webp'], 'projects');
                if ($uploadRes['success']) {
                    $newUrls[] = $uploadRes['url'];
                } else {
                    $error .= " Error al subir imagen " . ($i + 1) . ": " . $uploadRes['message'];
                }
            }
        }
    }

    $finalUrls = array_merge($existingUrls, $newUrls);
    
    // Formatear array de imágenes Postgres
    $elements = array_map(function($val) {
        return '"' . str_replace('"', '\\"', $val) . '"';
    }, $finalUrls);
    $imageUrls = '{' . implode(',', $elements) . '}';

    try {
        if ($project) {
            // Actualizar
            $updateStmt = $pdo->prepare("UPDATE projects SET 
                title = :title,
                description = :description,
                github_url = :github_url,
                live_url = :live_url,
                tags = :tags,
                image_urls = :image_urls,
                is_visible = :is_visible,
                display_order = :display_order
                WHERE id = :id");
            
            $updateStmt->execute([
                ':title' => $title,
                ':description' => $description,
                ':github_url' => $githubUrl,
                ':live_url' => $liveUrl,
                ':tags' => $tags,
                ':image_urls' => $imageUrls,
                ':is_visible' => $isVisible,
                ':display_order' => $displayOrder,
                ':id' => $id
            ]);
            
            header('Location: index.php?success=' . urlencode('Proyecto actualizado con éxito.'));
            exit;
        } else {
            // Insertar nuevo (generando un UUID para id, o dejar que la BD lo genere si usa auto-uuid)
            $insertStmt = $pdo->prepare("INSERT INTO projects (
                title, description, github_url, live_url, tags, image_urls, is_visible, display_order, created_at
            ) VALUES (
                :title, :description, :github_url, :live_url, :tags, :image_urls, :is_visible, :display_order, NOW()
            )");
            
            $insertStmt->execute([
                ':title' => $title,
                ':description' => $description,
                ':github_url' => $githubUrl,
                ':live_url' => $liveUrl,
                ':tags' => $tags,
                ':image_urls' => $imageUrls,
                ':is_visible' => $isVisible,
                ':display_order' => $displayOrder
            ]);

            header('Location: index.php?success=' . urlencode('Proyecto creado con éxito.'));
            exit;
        }
    } catch (PDOException $e) {
        $error = "Error al guardar el proyecto: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $project ? 'Editar' : 'Agregar' ?> Proyecto - Panel Admin</title>
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
            <h2><?= $project ? 'Editar Proyecto: ' . htmlspecialchars($project['title']) : 'Agregar Nuevo Proyecto' ?></h2>
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
                <span class="box-title">project_form.sh</span>
            </div>
            <div class="box-content">
                <form method="POST" action="form.php<?= $project ? '?id=' . urlencode($project['id']) : '' ?>" enctype="multipart/form-data" class="brutal-form">
                    
                    <div class="form-group">
                        <label for="title">Título del Proyecto</label>
                        <input type="text" id="title" name="title" value="<?= htmlspecialchars($project['title'] ?? '') ?>" required placeholder="Ej: ArteCom">
                    </div>

                    <div class="form-group">
                        <label for="description">Descripción Completa (Se verá en el modal de detalles)</label>
                        <textarea id="description" name="description" rows="8" required placeholder="Describe las funcionalidades, tecnologías y tu rol en el desarrollo del proyecto..."><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="tags">Tecnologías / Tags (Separadas por comas)</label>
                        <input type="text" id="tags" name="tags" value="<?= htmlspecialchars(arrayToCsvString($project['tags'] ?? '')) ?>" required placeholder="Ej: React, TypeScript, Next.js, CSS">
                    </div>

                    <div style="border: 2px dashed var(--border-color); padding: 1.5rem; margin: 1rem 0; display: flex; flex-direction: column; gap: 1.2rem;">
                        <h4 style="font-family: 'Outfit', sans-serif; font-size: 1.2rem;">Imágenes del Proyecto</h4>
                        
                        <?php 
                        $savedImages = !empty($project['image_urls']) ? str_getcsv(trim($project['image_urls'], '{}')) : [];
                        if (!empty($savedImages)): ?>
                            <div class="form-group">
                                <label>Imágenes actuales:</label>
                                <div style="display: flex; gap: 0.8rem; margin: 0.5rem 0;">
                                    <button type="button" onclick="toggleAllImages(true)" class="btn-brutal-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.75rem; cursor: pointer;"><i class="ph ph-check-square"></i> Conservar Todas</button>
                                    <button type="button" onclick="toggleAllImages(false)" class="btn-brutal-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.75rem; color: var(--accent-red); border-color: rgba(239, 68, 68, 0.4); cursor: pointer;"><i class="ph ph-trash"></i> Quitar Todas</button>
                                </div>
                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 1rem; margin-top: 0.5rem;">
                                    <?php foreach ($savedImages as $img): 
                                        $imgClean = trim($img, '" ');
                                        if (empty($imgClean)) continue;
                                    ?>
                                        <div class="image-card-box" style="border: 2px solid var(--border-color); padding: 0.5rem; background: #14110f; display: flex; flex-direction: column; align-items: center; gap: 0.5rem; transition: all 0.2s;">
                                            <img src="<?= htmlspecialchars(getStorageUrl($imgClean)) ?>" onclick="openAdminLightbox(this)" style="width: 100%; height: 80px; object-fit: cover; transition: all 0.2s; cursor: zoom-in;">
                                            <label style="font-size: 0.8rem; display: flex; align-items: center; gap: 0.3rem; cursor: pointer; user-select: none;">
                                                <input type="checkbox" name="existing_image_urls[]" value="<?= htmlspecialchars($imgClean) ?>" checked class="image-keep-cb"> Conservar
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="project_images">Subir nuevas imágenes (Selecciona múltiples archivos si lo deseas)</label>
                            <input type="file" id="project_images" name="project_images[]" multiple accept="image/*">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="github_url">URL del Repositorio en GitHub</label>
                        <input type="url" id="github_url" name="github_url" value="<?= htmlspecialchars($project['github_url'] ?? '') ?>" placeholder="https://github.com/tu-usuario/proyecto">
                    </div>

                    <div class="form-group">
                        <label for="live_url">URL de la Demo en Vivo (Sitio Web)</label>
                        <input type="url" id="live_url" name="live_url" value="<?= htmlspecialchars($project['live_url'] ?? '') ?>" placeholder="https://mi-proyecto.com">
                    </div>

                    <div class="form-group">
                        <label for="display_order">Orden de Visualización (Números más pequeños se muestran primero)</label>
                        <input type="number" id="display_order" name="display_order" value="<?= htmlspecialchars($project['display_order'] ?? '10') ?>" required min="0">
                    </div>

                    <div class="form-group" style="flex-direction: row; align-items: center; gap: 0.5rem; margin-top: 1rem; margin-bottom: 1.5rem;">
                        <input type="checkbox" id="is_visible" name="is_visible" value="1" <?= (!isset($project['is_visible']) || $project['is_visible']) ? 'checked' : '' ?> style="width: auto; cursor: pointer;">
                        <label for="is_visible" style="cursor: pointer; user-select: none;">Visible en el Portafolio (Se mostrará públicamente)</label>
                    </div>

                    <button type="submit" class="btn-brutal-primary w-100"><?= $project ? 'Guardar Cambios' : 'Crear Proyecto' ?></button>
                </form>
            </div>
        </div>

    </main>

    <div id="admin-image-lightbox" class="brutal-modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.85); z-index: 10000; align-items: center; justify-content: center; flex-direction: column; padding: 1rem;">
        <div style="position: relative; max-width: 90%; max-height: 80%; border: 3px solid var(--border-color); background: var(--bg-boxes); padding: 5px; box-shadow: 10px 10px 0px rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center;">
            <button id="close-admin-lightbox" class="close-btn" style="position: absolute; top: -15px; right: -15px; background: var(--accent-red); color: white; border: 2px solid var(--border-color); width: 35px; height: 35px; cursor: pointer; font-weight: bold; font-family: monospace; display: flex; align-items: center; justify-content: center;">X</button>
            <img id="admin-lightbox-img" src="" style="max-width: 100%; max-height: 70vh; display: block; object-fit: contain;">
        </div>
        <div style="margin-top: 1.5rem;">
            <button id="admin-lightbox-action-btn" type="button" class="btn-brutal-primary" style="padding: 0.8rem 1.5rem; font-size: 1rem; cursor: pointer; font-family: 'Outfit', sans-serif; font-weight: 900; letter-spacing: 0.5px; border-width: 3px; box-shadow: 4px 4px 0px rgba(0,0,0,0.4); text-transform: uppercase; transition: all 0.1s;"></button>
        </div>
    </div>

    <script>
        // Lógica de visualización/eliminación desde Lightbox del Admin
        let activeLightboxCb = null;
        const adminLightbox = document.getElementById('admin-image-lightbox');
        const adminLightboxImg = document.getElementById('admin-lightbox-img');
        const adminLightboxActionBtn = document.getElementById('admin-lightbox-action-btn');
        const closeAdminLightboxBtn = document.getElementById('close-admin-lightbox');

        function openAdminLightbox(imgElement) {
            const card = imgElement.closest('.image-card-box');
            if (!card) return;

            const cb = card.querySelector('.image-keep-cb');
            if (!cb) return;

            activeLightboxCb = cb;
            adminLightboxImg.src = imgElement.src;
            updateLightboxButtonState();
            
            adminLightbox.style.display = 'flex';
        }

        function updateLightboxButtonState() {
            if (!activeLightboxCb) return;

            if (activeLightboxCb.checked) {
                adminLightboxActionBtn.textContent = "🗑️ Quitar del Proyecto";
                adminLightboxActionBtn.style.background = "var(--accent-red)";
                adminLightboxActionBtn.style.color = "white";
                adminLightboxActionBtn.style.transform = "none";
                adminLightboxActionBtn.style.boxShadow = "4px 4px 0px rgba(0,0,0,0.4)";
            } else {
                adminLightboxActionBtn.textContent = "✔️ Conservar en el Proyecto";
                adminLightboxActionBtn.style.background = "var(--accent-blue)";
                adminLightboxActionBtn.style.color = "white";
                adminLightboxActionBtn.style.transform = "none";
                adminLightboxActionBtn.style.boxShadow = "4px 4px 0px rgba(0,0,0,0.4)";
            }
        }

        adminLightboxActionBtn.addEventListener('click', () => {
            if (!activeLightboxCb) return;

            // Alternar estado de la casilla
            activeLightboxCb.checked = !activeLightboxCb.checked;
            updateImageCardState(activeLightboxCb);
            updateLightboxButtonState();
        });

        function closeAdminLightbox() {
            adminLightbox.style.display = 'none';
            activeLightboxCb = null;
        }

        closeAdminLightboxBtn.addEventListener('click', closeAdminLightbox);
        adminLightbox.addEventListener('click', (e) => {
            if (e.target === adminLightbox) {
                closeAdminLightbox();
            }
        });

        // Lógica de casillas y estados visuales existentes
        function toggleAllImages(checked) {
            const checkboxes = document.querySelectorAll('.image-keep-cb');
            checkboxes.forEach(cb => {
                cb.checked = checked;
                updateImageCardState(cb);
            });
        }

        function updateImageCardState(cb) {
            const card = cb.closest('.image-card-box');
            if (card) {
                if (cb.checked) {
                    card.style.borderColor = 'var(--border-color)';
                    card.style.background = '#14110f';
                    card.querySelector('img').style.opacity = '1';
                    card.querySelector('img').style.filter = 'none';
                } else {
                    card.style.borderColor = 'var(--accent-red)';
                    card.style.background = 'rgba(239, 68, 68, 0.1)';
                    card.querySelector('img').style.opacity = '0.3';
                    card.querySelector('img').style.filter = 'grayscale(100%)';
                }
            }
        }

        document.querySelectorAll('.image-keep-cb').forEach(cb => {
            // Set initial state
            updateImageCardState(cb);
            // Listen for changes
            cb.addEventListener('change', () => {
                updateImageCardState(cb);
            });
        });
    </script>
</body>
</html>
