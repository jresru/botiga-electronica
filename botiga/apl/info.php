<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Información</title>
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
<h2>Información del funcionamiento de la tienda</h2>
<p>Esta tienda permite:</p>
<ul>
    <li>Iniciar sesión como administrador, gestor o cliente.</li>
    <li>Gestionar productos y pedidos.</li>
    <li>Exportar datos en PDF.</li>
    <li>Comunicación por correo electrónico con PHPMailer.</li>
</ul>
<a href="login.php">Volver al inicio de sesión</a><br>
<a href='/PROYECTO/Projecto'>INICI</a>;
</body>
</html>
