<?php
// admin/upload_helper.php

/**
 * Procesa la subida de un archivo. Intenta subirlo directamente al Bucket de Supabase Storage.
 * Si no está configurada la clave o falla, realiza un fallback a almacenamiento local en /uploads/.
 *
 * @param array $file El elemento de $_FILES (ej: $_FILES['mi_archivo'])
 * @param array $allowedTypes Extensiones permitidas (ej: ['jpg', 'png', 'pdf'])
 * @param string $subfolder Subcarpeta opcional (ej: 'avatars', 'projects')
 * @return array ['success' => bool, 'url' => string, 'message' => string]
 */
function uploadFile($file, $allowedTypes = [], $subfolder = '') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Error o archivo no subido.'];
    }

    $filename = basename($file['name']);
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (!empty($allowedTypes) && !in_array($ext, $allowedTypes)) {
        return ['success' => false, 'message' => 'Extensión .' . $ext . ' no permitida. Permitidos: ' . implode(', ', $allowedTypes)];
    }

    // Nombre único para evitar colisiones
    $uniqueName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $pathInBucket = ($subfolder ? trim($subfolder, '/') . '/' : '') . $uniqueName;

    // Intentar leer credenciales de Supabase para subida directa en la nube
    $env = [];
    $envPath = __DIR__ . '/../.env';
    if (file_exists($envPath)) {
        $env = parse_ini_file($envPath);
    }
    
    $supabaseUrl = $env['SUPABASE_URL'] ?? getenv('SUPABASE_URL') ?? $_ENV['SUPABASE_URL'] ?? '';
    $supabaseKey = $env['SUPABASE_KEY'] ?? getenv('SUPABASE_KEY') ?? $_ENV['SUPABASE_KEY'] ?? '';
    $bucketName = 'portfolio_images';

    if (!empty($supabaseUrl) && !empty($supabaseKey)) {
        // endpoint oficial de Supabase Storage para subir objetos
        $uploadUrl = rtrim($supabaseUrl, '/') . '/storage/v1/object/' . $bucketName . '/' . $pathInBucket;
        $fileData = file_get_contents($file['tmp_name']);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uploadUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fileData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $supabaseKey,
            'apikey: ' . $supabaseKey,
            'Content-Type: ' . $file['type']
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 || $httpCode === 201) {
            // Retorna la ruta relativa para que getStorageUrl la resuelva dinámicamente con la URL pública del bucket
            return ['success' => true, 'url' => $pathInBucket];
        } else {
            error_log("Fallo subida Supabase (Código: $httpCode): " . $response);
        }
    }

    // --- FALLBACK: SUBIDA AL SERVIDOR LOCAL ---
    $uploadDir = __DIR__ . '/../uploads/' . ($subfolder ? trim($subfolder, '/') . '/' : '');
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $targetFilePath = $uploadDir . $uniqueName;

    if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
        $host = $_SERVER['HTTP_HOST'];
        $urlPath = "/Portafolio/uploads/" . ($subfolder ? trim($subfolder, '/') . '/' : '') . $uniqueName;
        $fileUrl = $protocol . "://" . $host . $urlPath;
        
        return ['success' => true, 'url' => $fileUrl];
    }

    return ['success' => false, 'message' => 'Error al mover el archivo subido en el servidor local.'];
}
?>
