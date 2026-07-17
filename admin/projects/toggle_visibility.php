<?php
// admin/projects/toggle_visibility.php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $isVisible = isset($_POST['is_visible']) ? 1 : 0;

    if (!empty($id)) {
        try {
            $stmt = $pdo->prepare("UPDATE projects SET is_visible = :is_visible WHERE id = :id");
            $stmt->execute([
                ':is_visible' => $isVisible,
                ':id' => $id
            ]);
            header('Location: index.php?success=' . urlencode('Visibilidad del proyecto actualizada.'));
            exit;
        } catch (PDOException $e) {
            header('Location: index.php?error=' . urlencode('Error de base de datos: ' . $e->getMessage()));
            exit;
        }
    }
}
header('Location: index.php');
exit;
?>
