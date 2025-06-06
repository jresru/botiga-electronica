<?php
session_start();

// Verificar si el usuario está autenticado y es un cliente
if (!isset($_SESSION['usuario']) || !isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'cliente') {
    header('Location: login.php');
    exit();
}

$usuario = $_SESSION['usuario'];
$cestaArchivo = "C:/xampp/htdocs/PROYECTO/Projecto/botiga/cistelles/{$usuario}_cesta.txt";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['producto'])) {
        // Obtener el producto seleccionado y eliminarlo del archivo
        $productos = file($cestaArchivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $productoSeleccionado = $_POST['producto'];

        // Filtrar los productos y eliminar el seleccionado
        $productos = array_filter($productos, function($producto) use ($productoSeleccionado) {
            return $producto !== $productoSeleccionado;
        });

        // Guardar los productos actualizados en el archivo
        file_put_contents($cestaArchivo, implode(PHP_EOL, $productos) . PHP_EOL);

        echo "<p>Producto eliminado correctamente.</p>";
        echo "<a href='dashboard_cliente.php'>Volver al Dashboard</a>";
        exit();
    }
}

?>

<h2>Borrar producto de tu Cistella</h2>

<?php
if (file_exists($cestaArchivo)) {
    $productos = file($cestaArchivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if (count($productos) > 0) {
        echo "<form method='POST'>";
        foreach ($productos as $producto) {
            echo "<input type='radio' name='producto' value='" . htmlspecialchars($producto) . "'> " . htmlspecialchars($producto) . "<br>";
        }
        echo "<input type='submit' value='Borrar'>";
        echo "</form>";
    } else {
        echo "<p>No tienes productos en tu cistella.</p>";
    }
} else {
    echo "<p>No existe tu archivo de cistella.</p>";
}

echo "<a href='dashboard_cliente.php'>Volver al Dashboard</a>";
?>
