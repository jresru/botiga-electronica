<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio</title>
    <style>
        .user-bar {
            position: fixed;
            top: 0; right: 0;
            background: #f0f0f0;
            padding: 8px 20px;
            font-size: 15px;
            border-bottom-left-radius: 8px;
            z-index: 1000;
        }
        body { margin-top: 40px; }
    </style>
</head>
<body>
<?php if (isset($_SESSION['usuario'])): ?>
    <div class="user-bar">
        Usuario: <strong><?= htmlspecialchars($_SESSION['usuario']) ?></strong>
        <a href="logout.php" style="margin-left:15px;">Cerrar sesión</a>
    </div>
<?php endif; ?>

<h1>Bienvenido al Sistema de Gestión de Productos</h1>

<?php
if (isset($_SESSION['usuario']) && isset($_SESSION['tipo'])) {
    echo "<p>Hola, <strong>{$_SESSION['usuario']}</strong>. Eres un usuario del tipo {$_SESSION['tipo']}.</p>";
    echo "<a href='logout.php'>Cerrar sesión</a>";
    echo "<br><a href='home.php'>Ir al Dashboard</a>";
    // Redirigimos según el tipo de usuario
    if ($_SESSION['tipo'] === 'admin') {
        header('Location: dashboard_admin.php');
        exit();
    } elseif ($_SESSION['tipo'] === 'cliente') {
        header('Location: dashboard_cliente.php');
        exit();
    } elseif ($_SESSION['tipo'] === 'gestor') {
        header('Location: dashboard_gestor.php');
        exit();
    }
} else {
    echo "<a href='login.php'>Iniciar sesión</a>";
    echo "<br><a href='info.php'>Información del Sistema</a>";
}
?>
</body>
</html>
