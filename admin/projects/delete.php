<?php
// admin/projects/delete.php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config/db.php';

$id = $_GET['id'] ?? '';

if (!empty($id)) {
    try {
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = :id");
        $stmt->execute([':id' => $id]);
        header('Location: index.php?success=' . urlencode('Proyecto eliminado con éxito.'));
        exit;
    } catch (PDOException $e) {
        header('Location: index.php?error=' . urlencode('Error al eliminar el proyecto: ' . $e->getMessage()));
        exit;
    }
} else {
    header('Location: index.php?error=' . urlencode('ID de proyecto no provisto.'));
    exit;
}
?>
