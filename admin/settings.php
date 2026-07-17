<?php
// admin/settings.php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/upload_helper.php';

$success = '';
$error = '';

// Helper para convertir string Postgres array {} a texto separado por comas
function arrayToCsvString($postgresArray) {
    if (empty($postgresArray)) return '';
    if (is_array($postgresArray)) {
        return implode(', ', $postgresArray);
    }
    $elements = str_getcsv(trim($postgresArray, '{}'));
    return implode(', ', array_map(function($el) { return trim($el, '" '); }, $elements));
}

// Helper para convertir texto separado por comas a formato array Postgres {}
function csvStringToPostgresArray($csvString) {
    $arr = array_filter(array_map('trim', explode(',', $csvString)));
    $elements = array_map(function($val) {
        return '"' . str_replace('"', '\\"', $val) . '"';
    }, $arr);
    return '{' . implode(',', $elements) . '}';
}

try {
    $stmt = $pdo->query("SELECT * FROM portfolio_settings LIMIT 1");
    $settings = $stmt->fetch();
} catch (PDOException $e) {
    $error = "Error al leer los ajustes: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $heroTitle = $_POST['hero_title'] ?? '';
    $heroSubtitle = $_POST['hero_subtitle'] ?? '';
    $aboutText = $_POST['about_text'] ?? '';
    $email = $_POST['email'] ?? '';
    $githubUrl = $_POST['github_url'] ?? '';
    $linkedinUrl = $_POST['linkedin_url'] ?? '';
    
    // Procesar Imagen de Perfil / Avatar
    $profileImageUrl = $_POST['profile_image_url_existing'] ?? '';
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $uploadRes = uploadFile($_FILES['profile_image'], ['jpg', 'jpeg', 'png', 'gif', 'webp'], 'avatars');
        if ($uploadRes['success']) {
            $profileImageUrl = $uploadRes['url'];
        } else {
            $error .= " Error avatar: " . $uploadRes['message'];
        }
    }

    // Procesar CV PDF
    $cvUrl = $_POST['cv_url_existing'] ?? '';
    if (isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] === UPLOAD_ERR_OK) {
        $uploadRes = uploadFile($_FILES['cv_file'], ['pdf'], 'cv');
        if ($uploadRes['success']) {
            $cvUrl = $uploadRes['url'];
        } else {
            $error .= " Error CV: " . $uploadRes['message'];
        }
    }
    
    // Arrays de tecnologías
    $skillsFrontend = csvStringToPostgresArray($_POST['skills_frontend'] ?? '');
    $skillsBackend = csvStringToPostgresArray($_POST['skills_backend'] ?? '');
    $skillsTools = csvStringToPostgresArray($_POST['skills_tools'] ?? '');

    try {
        if ($settings) {
            // Actualizar fila existente
            $updateStmt = $pdo->prepare("UPDATE portfolio_settings SET 
                hero_title = :hero_title,
                hero_subtitle = :hero_subtitle,
                about_text = :about_text,
                cv_url = :cv_url,
                email = :email,
                github_url = :github_url,
                linkedin_url = :linkedin_url,
                profile_image_url = :profile_image_url,
                skills_frontend = :skills_frontend,
                skills_backend = :skills_backend,
                skills_tools = :skills_tools,
                updated_at = NOW()
                WHERE id = :id");
            
            $updateStmt->execute([
                ':hero_title' => $heroTitle,
                ':hero_subtitle' => $heroSubtitle,
                ':about_text' => $aboutText,
                ':cv_url' => $cvUrl,
                ':email' => $email,
                ':github_url' => $githubUrl,
                ':linkedin_url' => $linkedinUrl,
                ':profile_image_url' => $profileImageUrl,
                ':skills_frontend' => $skillsFrontend,
                ':skills_backend' => $skillsBackend,
                ':skills_tools' => $skillsTools,
                ':id' => $settings['id']
            ]);
        } else {
            // Si no existe, crear la fila única
            $insertStmt = $pdo->prepare("INSERT INTO portfolio_settings (
                hero_title, hero_subtitle, about_text, cv_url, email, github_url, linkedin_url, profile_image_url, skills_frontend, skills_backend, skills_tools, is_singleton
            ) VALUES (
                :hero_title, :hero_subtitle, :about_text, :cv_url, :email, :github_url, :linkedin_url, :profile_image_url, :skills_frontend, :skills_backend, :skills_tools, true
            )");

            $insertStmt->execute([
                ':hero_title' => $heroTitle,
                ':hero_subtitle' => $heroSubtitle,
                ':about_text' => $aboutText,
                ':cv_url' => $cvUrl,
                ':email' => $email,
                ':github_url' => $githubUrl,
                ':linkedin_url' => $linkedinUrl,
                ':profile_image_url' => $profileImageUrl,
                ':skills_frontend' => $skillsFrontend,
                ':skills_backend' => $skillsBackend,
                ':skills_tools' => $skillsTools
            ]);
        }
        
        $success = "Ajustes guardados con éxito.";
        
        // Recargar datos actualizados
        $stmt = $pdo->query("SELECT * FROM portfolio_settings LIMIT 1");
        $settings = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Error al guardar los ajustes: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajustes de Portafolio - Panel Admin</title>
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
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="settings.php" class="active">Ajustes</a></li>
                    <li><a href="projects/index.php">Proyectos</a></li>
                    <li><a href="certifications/index.php">Certificaciones</a></li>
                </ul>
            </nav>
            <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
        </div>
    </header>

    <main class="container">
        <div class="admin-section-header">
            <h2>Ajustes Generales del Portafolio</h2>
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

        <div class="brutal-card" style="margin-bottom: 3rem;">
            <div class="box-header">
                <span class="dot red"></span><span class="dot yellow"></span><span class="dot green"></span>
                <span class="box-title">edit_settings_form.sh</span>
            </div>
            <div class="box-content">
                <form method="POST" action="settings.php" enctype="multipart/form-data" class="brutal-form">
                    
                    <div class="form-group">
                        <label for="hero_title">Título Principal (Hero Title)</label>
                        <input type="text" id="hero_title" name="hero_title" value="<?= htmlspecialchars($settings['hero_title'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="hero_subtitle">Subtítulo (Hero Subtitle)</label>
                        <input type="text" id="hero_subtitle" name="hero_subtitle" value="<?= htmlspecialchars($settings['hero_subtitle'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Imagen de Perfil (Avatar)</label>
                        <?php if (!empty($settings['profile_image_url'])): ?>
                            <div style="margin-bottom: 0.5rem; display: flex; align-items: center; gap: 1rem;">
                                <img src="<?= htmlspecialchars(getStorageUrl($settings['profile_image_url'])) ?>" alt="Avatar" style="width: 60px; height: 60px; object-fit: cover; border: 2px solid var(--border-color);">
                                <span style="font-size: 0.8rem; color: var(--text-muted);">Imagen actual cargada. Sube una nueva para reemplazarla.</span>
                            </div>
                        <?php endif; ?>
                        <input type="hidden" name="profile_image_url_existing" value="<?= htmlspecialchars($settings['profile_image_url'] ?? '') ?>">
                        <input type="file" id="profile_image" name="profile_image" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label>Archivo del CV (PDF)</label>
                        <?php if (!empty($settings['cv_url'])): ?>
                            <div style="margin-bottom: 0.5rem;">
                                <a href="<?= htmlspecialchars(getStorageUrl($settings['cv_url'])) ?>" target="_blank" style="color: var(--accent-blue); font-size: 0.85rem; text-decoration: underline;">📄 Ver CV actual subido</a>
                            </div>
                        <?php endif; ?>
                        <input type="hidden" name="cv_url_existing" value="<?= htmlspecialchars($settings['cv_url'] ?? '') ?>">
                        <input type="file" id="cv_file" name="cv_file" accept=".pdf">
                    </div>

                    <div class="form-group">
                        <label for="about_text">Texto "Acerca de Mí"</label>
                        <textarea id="about_text" name="about_text" rows="8" required><?= htmlspecialchars($settings['about_text'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="email">Correo Electrónico de Contacto</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($settings['email'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="github_url">URL de GitHub</label>
                        <input type="url" id="github_url" name="github_url" value="<?= htmlspecialchars($settings['github_url'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="linkedin_url">URL de LinkedIn</label>
                        <input type="url" id="linkedin_url" name="linkedin_url" value="<?= htmlspecialchars($settings['linkedin_url'] ?? '') ?>">
                    </div>

                    <div style="border: 2px dashed var(--border-color); padding: 1.5rem; margin: 1rem 0; display: flex; flex-direction: column; gap: 1.2rem;">
                        <h4 style="font-family: 'Outfit', sans-serif; font-size: 1.2rem;">Habilidades (Separadas por comas)</h4>
                        
                        <div class="form-group">
                            <label for="skills_frontend">Frontend</label>
                            <input type="text" id="skills_frontend" name="skills_frontend" value="<?= htmlspecialchars(arrayToCsvString($settings['skills_frontend'] ?? '')) ?>" placeholder="HTML, CSS, JavaScript, React">
                        </div>

                        <div class="form-group">
                            <label for="skills_backend">Backend</label>
                            <input type="text" id="skills_backend" name="skills_backend" value="<?= htmlspecialchars(arrayToCsvString($settings['skills_backend'] ?? '')) ?>" placeholder="PHP, Laravel, MySQL, Postgres">
                        </div>

                        <div class="form-group">
                            <label for="skills_tools">Herramientas & Herramientas TI</label>
                            <input type="text" id="skills_tools" name="skills_tools" value="<?= htmlspecialchars(arrayToCsvString($settings['skills_tools'] ?? '')) ?>" placeholder="Git, VS Code, Figma, Linux">
                        </div>
                    </div>

                    <button type="submit" class="btn-brutal-primary w-100">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </main>

</body>
</html>
