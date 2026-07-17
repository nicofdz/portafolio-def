<?php
// admin/logout.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION = array();
session_destroy();
$adminPath = substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], '/admin/') + 7);
header('Location: ' . $adminPath . 'login.php');
exit;
?>
