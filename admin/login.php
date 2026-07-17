<?php
// admin/login.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si ya está logueado, ir al dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: /Portafolio/admin/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    $env = [];
    $envPath = __DIR__ . '/../.env';
    if (file_exists($envPath)) {
        $env = parse_ini_file($envPath);
    }
    $adminUser = $env['ADMIN_USER'] ?? getenv('ADMIN_USER') ?? $_ENV['ADMIN_USER'] ?? 'admin';
    $adminPass = $env['ADMIN_PASSWORD'] ?? getenv('ADMIN_PASSWORD') ?? $_ENV['ADMIN_PASSWORD'] ?? '';

    if (!empty($adminPass) && $user === $adminUser && $pass === $adminPass) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: /Portafolio/admin/index.php');
        exit;
    } else {
        $error = 'Usuario o contraseña incorrectos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceder al Panel - Nicolás Fernández</title>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600&family=Outfit:wght@700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="admin-login-body">

    <div class="login-wrapper">
        <div class="brutal-card login-box">
            <div class="box-header">
                <span class="dot red"></span><span class="dot yellow"></span><span class="dot green"></span>
                <span class="box-title">login_panel.sh</span>
            </div>
            <div class="box-content">
                <h2>Iniciar Sesión</h2>
                <p class="subtitle-login">Panel de Control Portafolio</p>

                <?php if (!empty($error)): ?>
                    <div class="alert-box error">
                        <strong>ERROR:</strong> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php" class="brutal-form">
                    <div class="form-group">
                        <label for="username">Usuario</label>
                        <input type="text" id="username" name="username" required autocomplete="username" placeholder="Introduce el usuario">
                    </div>
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="••••••••">
                    </div>
                    <button type="submit" class="btn-brutal-primary w-100">ACCEDER_AL_SISTEMA</button>
                </form>
                <div class="back-link">
                    <a href="../">// volver al portafolio</a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
