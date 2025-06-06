<?php
session_start();

echo "<h1>¡Bienvenido a la Botiga Electrònica!</h1>";

if (isset($_SESSION['usuario'])) {
    echo "Hola, " . $_SESSION['usuario'] . ".<br>";
    echo "<a href='botiga/apl/logout.php'>Cerrar sesión</a><br>";
    echo "<a href='?area_usuario=true'>Area d'usuari</a><br>"; 
    echo "<a href='botiga/apl/info.php'>Información</a>";
} else {
    echo "<a href='botiga/apl/login.php'>Iniciar Sesión</a>";
    echo " | <a href='botiga/apl/info.php'>Información</a>";
}

if (isset($_GET['area_usuario'])) {
    if (!isset($_SESSION['usuario'])) {
        header('Location: /PROYECTO/Projecto/botiga/apl/login.php');
        exit();
    }

    $usuario = $_SESSION['usuario'];

    if (strpos($usuario, 'admin') !== false) {
        header('Location: /PROYECTO/Projecto/botiga/apl/dashboard_admin.php');
        exit();
    } elseif (strpos($usuario, 'cliente') !== false) {
        header('Location: /PROYECTO/Projecto/botiga/apl/dashboard_cliente.php');
        exit();
    } elseif (strpos($usuario, 'gestor') !== false) {
        header('Location: /PROYECTO/Projecto/botiga/apl/dashboard_gestor.php');
        exit();
    } else {
        header('Location: /PROYECTO/Projecto/botiga/apl/login.php');
        exit();
    }
}
?>
