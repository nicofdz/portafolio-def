<?php
// admin/upload_helper.php

/**
 * Procesa la subida de un archivo. Intenta subirlo directamente al Bucket de Supabase Storage.
 * Si es una imagen (jpg, png, webp, gif), la optimiza y convierte a WebP a calidad 75 con un ancho máximo de 1200px.
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

    $tempPath = $file['tmp_name'];
    $mimeType = $file['type'];
    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
    $wasOptimized = false;

    // --- OPTIMIZACIÓN Y CONVERSIÓN A WEBP (Si es imagen y GD está disponible) ---
    if ($isImage && function_exists('imagecreatefromstring') && function_exists('imagewebp')) {
        $imgData = file_get_contents($file['tmp_name']);
        $srcImage = @imagecreatefromstring($imgData);
        
        if ($srcImage !== false) {
            $width = imagesx($srcImage);
            $height = imagesy($srcImage);
            
            // Redimensionar si el ancho es mayor a 1200px para evitar imágenes gigantescas
            $maxWidth = 1200;
            if ($width > $maxWidth) {
                $newWidth = $maxWidth;
                $newHeight = (int)floor($height * ($maxWidth / $width));
                
                $dstImage = imagecreatetruecolor($newWidth, $newHeight);
                
                // Conservar la transparencia de PNGs/WebPs
                imagealphablending($dstImage, false);
                imagesavealpha($dstImage, true);
                
                imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($srcImage);
                $srcImage = $dstImage;
            }
            
            // Crear archivo temporal WebP
            $optimizedTempFile = tempnam(sys_get_temp_dir(), 'opt_') . '.webp';
            if (imagewebp($srcImage, $optimizedTempFile, 75)) {
                $tempPath = $optimizedTempFile;
                $ext = 'webp';
                $mimeType = 'image/webp';
                $wasOptimized = true;
            }
            imagedestroy($srcImage);
        }
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
    $uploadSuccess = false;
    $returnedUrl = '';

    if (!empty($supabaseUrl) && !empty($supabaseKey)) {
        // endpoint oficial de Supabase Storage para subir objetos
        $uploadUrl = rtrim($supabaseUrl, '/') . '/storage/v1/object/' . $bucketName . '/' . $pathInBucket;
        $fileData = file_get_contents($tempPath);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uploadUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fileData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $supabaseKey,
            'apikey: ' . $supabaseKey,
            'Content-Type: ' . $mimeType
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 || $httpCode === 201) {
            $uploadSuccess = true;
            $returnedUrl = $pathInBucket;
        } else {
            error_log("Fallo subida Supabase (Código: $httpCode): " . $response);
        }
    }

    // --- FALLBACK: SUBIDA AL SERVIDOR LOCAL ---
    if (!$uploadSuccess) {
        $uploadDir = __DIR__ . '/../uploads/' . ($subfolder ? trim($subfolder, '/') . '/' : '');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $targetFilePath = $uploadDir . $uniqueName;

        $moveRes = false;
        if ($wasOptimized) {
            $moveRes = rename($tempPath, $targetFilePath);
        } else {
            $moveRes = move_uploaded_file($file['tmp_name'], $targetFilePath);
        }

        if ($moveRes) {
            $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
            $host = $_SERVER['HTTP_HOST'];
            
            $scriptName = $_SERVER['SCRIPT_NAME'];
            $projectBasePath = substr($scriptName, 0, strpos($scriptName, '/admin/')); // ej: "/Portafolio" o ""
            $urlPath = $projectBasePath . "/uploads/" . ($subfolder ? trim($subfolder, '/') . '/' : '') . $uniqueName;
            $fileUrl = $protocol . "://" . $host . $urlPath;
            
            $uploadSuccess = true;
            $returnedUrl = $fileUrl;
        }
    }

    // Limpiar archivo temporal si se creó uno optimizado y se subió a Supabase
    if ($wasOptimized && file_exists($tempPath)) {
        @unlink($tempPath);
    }

    if ($uploadSuccess) {
        return ['success' => true, 'url' => $returnedUrl];
    }

    return ['success' => false, 'message' => 'Error al guardar el archivo en Supabase o en el servidor local.'];
}
?>
