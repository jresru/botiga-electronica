<?php
session_start();
if (!isset($_SESSION['usuario']) || !isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'cliente') {
    header('Location: /PROYECTO/Projecto/botiga/apl/login.php');
    exit();
}

// Obtener datos del cliente
$usuario = $_SESSION['usuario'];
$correo = $_SESSION['correo'];

// Obtener gestor asignado y su correo desde usuarios.txt
$gestorCorreo = '';
$gestorUsuario = '';
$usuariosFile = __DIR__ . '/../usuarios.txt';
if (file_exists($usuariosFile)) {
    $usuarios = file($usuariosFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($usuarios as $linea) {
        $campos = explode(',', $linea);
        // Buscar línea del cliente actual
        if (isset($campos[0]) && $campos[0] === $usuario && isset($campos[9])) {
            $gestorUsuario = trim($campos[9]);
            break;
        }
    }
    // Buscar correo del gestor asignado
    if ($gestorUsuario) {
        foreach ($usuarios as $linea) {
            $campos = explode(',', $linea);
            if (isset($campos[0]) && trim($campos[0]) === $gestorUsuario && isset($campos[4])) {
                $gestorCorreo = trim($campos[4]);
                break;
            }
        }
    }
}

// PHPMailer
require_once __DIR__ . '/../../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $motivo = trim($_POST['motivo']);
    $contenido = trim($_POST['contenido']);

    if (empty($motivo) || empty($contenido)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        $asunto = ($motivo === 'modificacion')
            ? "petició de modificacio del compte de client"
            : "petició d'esborrament del compte de client";
        $mensajeCorreo = "Usuario: $usuario<br>Correo: $correo<br>Motivo: $motivo<br>Contenido: $contenido";

        // --- Guardar en archivo ---
        $rutaArchivo = __DIR__ . '/solicitudes_modificacion.txt';
        $linea = "Usuario: $usuario\nCorreo: $correo\nMotivo: $motivo\nContenido: $contenido\n\n";
        file_put_contents($rutaArchivo, $linea, FILE_APPEND);

        if ($gestorCorreo) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'misterproxd4@gmail.com'; // Cambia por tu correo
                $mail->Password = 'gbrv xuiu bmjx uajx'; // Cambia por tu contraseña o app password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('misterproxd4@gmail.com', 'Botiga');
                $mail->addAddress($gestorCorreo, $gestorUsuario);

                $mail->isHTML(true);
                $mail->Subject = $asunto;
                $mail->Body = $mensajeCorreo;

                $mail->send();
                $mensaje = "Solicitud enviada correctamente por correo al gestor.";
            } catch (Exception $e) {
                $error = "No se pudo enviar el correo: {$mail->ErrorInfo}";
            }
        } else {
            $error = "No se pudo encontrar el correo del gestor asignado.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar o Eliminar Cuenta</title>
</head>
<body>
    <h1>Solicitar Modificación o Eliminación de Cuenta</h1>
    <p>Usuario: <?= htmlspecialchars($usuario) ?></p>
    <p>Correo: <?= htmlspecialchars($correo) ?></p>

    <?php if (isset($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php elseif (isset($mensaje)): ?>
        <p style="color: green;"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="motivo">Motivo de la solicitud:</label><br>
        <select name="motivo" id="motivo" required>
            <option value="modificacion">Modificación de datos</option>
            <option value="eliminacion">Eliminación de cuenta</option>
        </select><br><br>

        <label for="contenido">Descripción detallada:</label><br>
        <textarea name="contenido" id="contenido" rows="5" required></textarea><br><br>

        <button type="submit">Enviar Solicitud</button>
    </form>

    <a href="/PROYECTO/Projecto/botiga/apl/dashboard_cliente.php">Volver al área personal</a>
</body>
</html>
