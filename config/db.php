<?php
// db.php

$env = [];
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $env = parse_ini_file($envPath);
}

$host = $env['DB_HOST'] ?? getenv('DB_HOST') ?? $_ENV['DB_HOST'] ?? '';
$port = $env['DB_PORT'] ?? getenv('DB_PORT') ?? $_ENV['DB_PORT'] ?? '5432';
$dbname = $env['DB_NAME'] ?? getenv('DB_NAME') ?? $_ENV['DB_NAME'] ?? '';
$user = $env['DB_USER'] ?? getenv('DB_USER') ?? $_ENV['DB_USER'] ?? '';
$password = $env['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?? $_ENV['DB_PASSWORD'] ?? '';
$supabaseUrl = $env['SUPABASE_URL'] ?? getenv('SUPABASE_URL') ?? $_ENV['SUPABASE_URL'] ?? '';

if (empty($host) || empty($dbname) || empty($user) || empty($password)) {
    die("Error: Faltan credenciales de base de datos en las variables de entorno.");
}

try {
    // El DSN limpio, sin el usuario ni la contraseña metidos a la fuerza
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    
    // Le pasamos las variables directamente al PDO (esto evita que el signo $ rompa todo)
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    //echo "¡Conectado a Supabase leyendo desde el .env con éxito!";

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// $supabaseUrl ya está definido al inicio del archivo
$bucketName = 'portfolio_images';

function getStorageUrl($path) {
    global $supabaseUrl, $bucketName;
    if (empty($path)) {
        return '';
    }
    // Si ya es una URL completa, retornarla tal cual
    if (strpos($path, 'https://') === 0 || strpos($path, 'http://') === 0) {
        return $path;
    }
    // Si no, construir la URL
    if (empty($supabaseUrl)) {
        return '';
    }
    return $supabaseUrl . '/storage/v1/object/public/' . $bucketName . '/' . ltrim($path, '/');
}
?>