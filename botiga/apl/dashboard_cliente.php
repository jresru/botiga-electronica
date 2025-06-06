<?php
session_start();

// Verificar si el usuario está autenticado y es un cliente
if (!isset($_SESSION['usuario']) || !isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'cliente') {
    header('Location: login.php');
    exit();
}

// Información del cliente
$usuario = $_SESSION['usuario'];
$correo = $_SESSION['correo'] ?? 'No disponible';

// Ruta al archivo de la cesta del cliente
$cestaArchivo = "C:/xampp/htdocs/PROYECTO/Projecto/botiga/cistelles/{$usuario}_cesta.txt";

// Inicializar mensaje de respuesta
$mensaje = "";
$error = "";

// Procesar acciones del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'acceptar_compra':
                // Convertir la cistella en una comanda
                $comandaArchivo = "C:/xampp/htdocs/PROYECTO/Projecto/botiga/comandes/{$usuario}_comanda.txt";
                $comandasGeneral = "C:/xampp/htdocs/PROYECTO/Projecto/botiga/comandes/comandas.txt";
                if (file_exists($cestaArchivo)) {
                    // Copiar la cesta a la comanda individual
                    copy($cestaArchivo, $comandaArchivo);
                    // Añadir cada línea de la cesta al archivo general de comandas
                    $lineas = file($cestaArchivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    foreach ($lineas as $linea) {
                        // Añade el usuario al final de la línea para identificar la comanda
                        file_put_contents($comandasGeneral, $linea . ',' . $usuario . PHP_EOL, FILE_APPEND);
                    }
                    unlink($cestaArchivo);
                    $mensaje = "Comanda creada correctament.";
                } else {
                    $error = "La cistella està buida.";
                }
                break;

            case 'esborrar_cistella':
                // Eliminar la cistella
                if (file_exists($cestaArchivo)) {
                    unlink($cestaArchivo);
                    $mensaje = "Cistella esborrada correctament.";
                } else {
                    $error = "No hi ha cap cistella per esborrar.";
                }
                break;
        }
    }
}

// Mostrar mensajes
if (!empty($mensaje)) {
    echo "<p style='color: green;'>$mensaje</p>";
}
if (!empty($error)) {
    echo "<p style='color: red;'>$error</p>";
}

echo "<h1>Bienvenido, cliente " . htmlspecialchars($usuario) . "</h1>";
echo "<p>Tu correo es: $correo</p>";

// Mostrar la sección de tu cesta
echo "<h2>Tu Cistella</h2>";

$contenido = [];

// Verificar si el archivo existe antes de leerlo
if (file_exists($cestaArchivo)) {
    $contenido = file($cestaArchivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
} else {
    echo "<p>No hay productos en tu cistella.</p>";
}

// Variables para los totales
$totalConIVA = 0;
$totalSinIVA = 0;

// Genera la tabla HTML si hay contenido
if (!empty($contenido)) {
    echo "<table border='1' cellspacing='0' cellpadding='5'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>Nom Producte</th>";
    echo "<th>ID Producte</th>";
    echo "<th>Preu Producte (€)</th>";
    echo "<th>IVA (%)</th>";
    echo "<th>Disponibilitat</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";

    // Procesa cada línea del archivo
    foreach ($contenido as $linea) {
        $datos = explode(",", $linea); // Ajusta el delimitador si no es una coma
        if (count($datos) === 5) { // Asegúrate de que haya exactamente 5 campos
            $nomProducte = htmlspecialchars($datos[0]);
            $idProducte = htmlspecialchars($datos[1]);
            $preuProducte = (float)$datos[2];
            $ivaProducte = (float)$datos[3];
            $disponibilitat = htmlspecialchars($datos[4]);

            // Calcula los totales
            $precioConIVA = $preuProducte + ($preuProducte * $ivaProducte / 100);
            $totalConIVA += $precioConIVA;
            $totalSinIVA += $preuProducte;

            // Agrega la fila a la tabla
            echo "<tr>";
            echo "<td>$nomProducte</td>";
            echo "<td>$idProducte</td>";
            echo "<td>" . number_format($preuProducte, 2) . "</td>";
            echo "<td>" . number_format($ivaProducte, 2) . "</td>";
            echo "<td>$disponibilitat</td>";
            echo "</tr>";
        }
    }

    echo "</tbody>";
    echo "</table>";
}

// Mostrar totales
echo "<br><strong>Totales:</strong><br>";
echo "Total sin IVA: " . number_format($totalSinIVA, 2) . " €<br>";
echo "Total con IVA: " . number_format($totalConIVA, 2) . " €<br><br>";

// Opciones de gestión
?>
<h3>Opciones de gestión</h3>
<form method="POST">
    <button type="submit" name="accion" value="acceptar_compra">Acceptar la compra</button>
    <button type="submit" name="accion" value="esborrar_cistella">Esborrar la cistella</button>
</form>
<?php

// Enlaces adicionales
echo "<a href='añadir_producto.php'>Añadir producto</a><br>";
echo "<a href='borrar_producto.php'>Borrar producto</a><br>";
echo "<a href='logout.php'>Cerrar sesión</a><br>";
echo "<a href='/PROYECTO/Projecto'>INICI</a>";

?>
<h2>Opciones</h2>
<ul>
    <li><a href="/PROYECTO/Projecto/botiga/apl/cliente/modificar_cuenta.php">Solicitar modificación o eliminación de cuenta</a></li>
    <li><a href="/PROYECTO/Projecto/botiga/apl/cliente/solicitar_motivo_rechazo.php">Solicitar motivo de rechazo de una comanda</a></li>
    <li><a href="logout.php">Cerrar sesión</a></li>
</ul>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Cliente</title>
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
    <div class="user-bar">
        Usuario: <strong><?= htmlspecialchars($usuario) ?></strong>
        <a href="logout.php" style="margin-left:15px;">Cerrar sesión</a>
    </div>
</body>
</html>
