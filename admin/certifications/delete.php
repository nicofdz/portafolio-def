<?php
// admin/certifications/delete.php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config/db.php';

$id = $_GET['id'] ?? '';

if (!empty($id)) {
    try {
        $stmt = $pdo->prepare("DELETE FROM certifications WHERE id = :id");
        $stmt->execute([':id' => $id]);
        header('Location: index.php?success=' . urlencode('Certificación eliminada con éxito.'));
        exit;
    } catch (PDOException $e) {
        header('Location: index.php?error=' . urlencode('Error al eliminar la certificación: ' . $e->getMessage()));
        exit;
    }
} else {
    header('Location: index.php?error=' . urlencode('ID de certificación no provisto.'));
    exit;
}
?>
