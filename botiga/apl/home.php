<?php
session_start();

if (!isset($_SESSION['usuario']) || !isset($_SESSION['tipo'])) {
    header('Location: login.php');
    exit();
}

$tipo = $_SESSION['tipo'];

switch ($tipo) {
    case 'cliente':
        header('Location: dashboard_cliente.php');
        break;
    case 'admin':
        header('Location: dashboard_admin.php');
        break;
    case 'gestor':
        header('Location: dashboard_gestor.php');
        break;
    default:
        echo "<p>Tipo de usuario desconocido.</p>";
        break;
}
exit();
?>
