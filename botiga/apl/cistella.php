<?php
session_start();
if (!isset($_SESSION['usuario']) || !isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'cliente') {
    header('Location: login.php');
    exit();
}

// Ruta del archivo de productos
$archivoProductos = 'productos.txt';

// Verificar si existe el archivo de productos
if (!file_exists($archivoProductos)) {
    die("Error: El archivo de productos no existe.");
}

// Leer productos
$productos = file($archivoProductos, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// Procesar la selección de productos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $seleccionados = $_POST['productos'] ?? [];
    $cantidades = $_POST['cantidades'] ?? [];
    $cistella = [];

    foreach ($seleccionados as $index) {
        $datosProducto = explode(',', $productos[$index]);
        $idProducto = $datosProducto[0];
        $nombre = $datosProducto[1];
        $precio = $datosProducto[2];
        $iva = $datosProducto[3];
        $cantidad = isset($cantidades[$index]) ? intval($cantidades[$index]) : 0;

        if ($cantidad > 0) {
            $cistella[] = [
                'id' => $idProducto,
                'nombre' => $nombre,
                'precio' => $precio,
                'iva' => $iva,
                'cantidad' => $cantidad
            ];
        }
    }

    // Guardar cistella en un archivo con el nombre del usuario
    $archivoCistella = "cistelles/{$_SESSION['usuario']}.txt";
    file_put_contents($archivoCistella, json_encode($cistella));

    $mensaje = "Cistella guardada correctamente.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de la Cistella</title>
</head>
<body>
    <h1>Gestión de la Cistella</h1>

    <?php if (isset($mensaje)): ?>
        <p style="color: green;"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

    <form method="POST">
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Precio (€)</th>
                    <th>IVA (%)</th>
                    <th>Disponible</th>
                    <th>Cantidad</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $index => $producto): 
                    list($id, $nombre, $precio, $iva, $disponible) = explode(',', $producto);
                ?>
                    <tr>
                        <td><?= htmlspecialchars($id) ?></td>
                        <td><?= htmlspecialchars($nombre) ?></td>
                        <td><?= htmlspecialchars($precio) ?></td>
                        <td><?= htmlspecialchars($iva) ?></td>
                        <td>
                            <?php if ($disponible === '1'): ?>
                                <input type="checkbox" name="productos[]" value="<?= $index ?>">
                            <?php else: ?>
                                No disponible
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($disponible === '1'): ?>
                                <input type="number" name="cantidades[<?= $index ?>]" min="1" value="0">
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit">Guardar Cistella</button>
    </form>

    <a href="/PROYECTO/Projecto/botiga/apl/dashboard_cliente.php">Volver al área personal</a>
</body>
</html>
