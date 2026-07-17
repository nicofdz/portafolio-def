<?php
// api/tracker_status.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/tracker.php';

echo json_encode([
    'active_users' => (int)($activeUsers ?? 1),
    'total_views' => (int)($totalViews ?? 0)
]);
exit;
?>
