<?php 
session_start();

class Auth {
    public static function login($username, $password, $usuariosFile = 'usuarios.txt') {
        // Cambia la ruta a la absoluta/correcta
        $usuariosFile = __DIR__ . '/usuarios.txt';
        if (!file_exists($usuariosFile)) {
            die("Error: El archivo usuarios.txt no existe.");
        }
        $usuarios = file($usuariosFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($usuarios as $usuario) {
            $campos = explode(',', $usuario);
            if (count($campos) >= 2) {
                $user = $campos[0];
                $passHash = $campos[1];
                $rol = (isset($campos[6]) ? trim($campos[6]) : '');
                if ($username === $user && password_verify($password, $passHash)) {
                    $_SESSION['usuario'] = $username;
                    $_SESSION['correo'] = (isset($campos[4]) ? $campos[4] : "$username@ejemplo.com");
                    // Solo usar el campo de rol del archivo
                    if ($rol === 'admin' || $rol === 'gestor' || $rol === 'cliente') {
                        $_SESSION['tipo'] = $rol;
                    } else {
                        $_SESSION['tipo'] = '';
                    }
                    return true;
                }
            }
        }
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    if (Auth::login($username, $password)) {
        if ($_SESSION['tipo'] === 'admin') {
            header('Location: dashboard_admin.php');
            exit();
        } elseif ($_SESSION['tipo'] === 'gestor') {
            header('Location: dashboard_gestor.php');
            exit();
        } elseif ($_SESSION['tipo'] === 'cliente') {
            header('Location: dashboard_cliente.php');
            exit();
        } else {
            echo "<p>El usuario no tiene un rol válido.</p>";
        }
    } else {
        echo "<p>Usuario o contraseña incorrectos.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
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
<!-- Formulario de inicio de sesión -->
<h2>Iniciar Sesión</h2>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio</title>
    <script>
        // Abrir la URL en una nueva pestaña al cargar la página
        window.onload = function() {
            ('http://localhost/PROYECTO/Projecto/botiga/apl/generar_hashes.php', '_blank');
        };
    </script>
</head>
<form method="POST">
    <label>Usuario: <input type="text" name="username" required></label><br>
    <label>Contraseña: <input type="password" name="password" required></label><br>
    <input type="submit" value="Iniciar sesión">
</form>
<a href='/PROYECTO/Projecto'>INICI</a>;
<!DOCTYPE html>
<html lang="ca">


</html>

