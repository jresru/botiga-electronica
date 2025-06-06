<?php
session_start();

// Verificar si el usuario está autenticado y es un cliente
if (!isset($_SESSION['usuario']) || !isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'cliente') {
    header('Location: login.php');
    exit();
}

$usuario = $_SESSION['usuario'];
$cestaArchivo = "C:/xampp/htdocs/PROYECTO/Projecto/botiga/cistelles/{$usuario}_cesta.txt";

$file_path = 'C:/xampp/htdocs/PROYECTO/Projecto/botiga/productes/productes.txt';

// Comprobar si el archivo existe antes de leerlo
if (file_exists($file_path)) {
    // Leer las líneas del archivo y almacenarlas en el array $productos
    $productos = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
} else {
    // Si el archivo no existe, inicializar un array vacío
    $productos = [];
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['producto'])) {
        $productoSeleccionado = $_POST['producto'] . "\n";

        // Añadir el producto al archivo de la cesta del cliente
        file_put_contents($cestaArchivo, $productoSeleccionado, FILE_APPEND);

        echo "<p>Producto añadido correctamente a tu cistella.</p>";
        echo "<a href='dashboard_cliente.php'>Volver al Dashboard</a>";
        exit();
    } else {
        echo "<p>Por favor, selecciona un producto.</p>";
    }
}
?>

<h2>Añadir producto a tu Cistella</h2>

<form method="POST">
    <?php foreach ($productos as $producto): ?>
        <input type="radio" name="producto" value="<?= $producto ?>"> <?= $producto ?><br>
    <?php endforeach; ?>

    <input type="submit" value="Guardar">
</form>

<a href="dashboard_cliente.php">Volver al Dashboard</a>
