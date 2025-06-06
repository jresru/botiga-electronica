<?php
session_start();

// Verificar si el usuario está autenticado y es un gestor
if (!isset($_SESSION['usuario']) || !isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'gestor') {
    header('Location: login.php');
    exit();
}

// Información del gestor
$usuario = $_SESSION['usuario'];
$correo = $_SESSION['correo'] ?? 'No disponible';

echo "<h1>Bienvenido, gestor " . htmlspecialchars($usuario) . "</h1>"; //htmlspecialchars es para que no lo lea con la finalidad de ejecutar el codigo
echo "<p>Tu correo es: $correo</p>";



// Función para leer el archivo de productos
function fLlegeixFitxer($rutaFitxer) {
    if (!file_exists($rutaFitxer)) {
        return [];
    }

    $contingut = file_get_contents($rutaFitxer);
    if ($contingut === false) {
        return [];
    }

    return explode("\n", trim($contingut)); //trim elimina los pesacios por delante y por detras
}

// Ruta del archivo de productos
$file_path = 'C:/xampp/htdocs/PROYECTO/Projecto/botiga/productes/productes.txt';

// Obtener la lista de productos existentes
$productes = fLlegeixFitxer($file_path);

// Interfaz para serialización
interface Registrable {
    public function toCSV();
    public static function fromCSV($line);
}

// Clase base Producto
class Producto implements Registrable {
    public $nombre;
    public $id;
    public $precio;
    public $iva;
    public $disponibilidad;

    public function __construct($nombre, $id, $precio, $iva, $disponibilidad) {
        $this->nombre = $nombre;
        $this->id = $id;
        $this->precio = $precio;
        $this->iva = $iva;
        $this->disponibilidad = $disponibilidad;
    }

    public function toCSV() {
        return "{$this->nombre},{$this->id},{$this->precio},{$this->iva},{$this->disponibilidad}";
    }

    public static function fromCSV($line) {
        $data = explode(",", $line);
        if (count($data) < 5) return null;
        return new static($data[0], $data[1], $data[2], $data[3], $data[4]);
    }
}

// Ejemplo de subclase (puedes ampliarla)
class ProductoFisico extends Producto {
    public $peso;

    public function __construct($nombre, $id, $precio, $iva, $disponibilidad, $peso = null) {
        parent::__construct($nombre, $id, $precio, $iva, $disponibilidad);
        $this->peso = $peso;
    }

    public function toCSV() {
        return parent::toCSV() . ($this->peso !== null ? ",{$this->peso}" : "");
    }

    public static function fromCSV($line) {
        $data = explode(",", $line);
        if (count($data) < 5) return null;
        $peso = isset($data[5]) ? $data[5] : null;
        return new static($data[0], $data[1], $data[2], $data[3], $data[4], $peso);
    }
}

// Agregar un nuevo producto al archivo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['_method']) || $_POST['_method'] === 'POST')) {
    if (isset($_POST['nomProducte']) && isset($_POST['idProducte']) && isset($_POST['preuProducte']) && isset($_POST['ivaProducte']) && isset($_POST['disponibilitat'])) {
        $producto = new Producto(
            $_POST['nomProducte'],
            $_POST['idProducte'],
            $_POST['preuProducte'],
            $_POST['ivaProducte'],
            $_POST['disponibilitat']
        );
        // Añadir salto de línea antes del producto si el archivo no está vacío
        $prependNewline = '';
        if (file_exists($file_path) && filesize($file_path) > 0) {
            $prependNewline = "\n";
        }
        file_put_contents($file_path, $prependNewline . $producto->toCSV(), FILE_APPEND);
        header("Location: dashboard_gestor.php");
        exit();
    }
}

// Esborrament de productes (DELETE spoofing)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'DELETE') {
    if (isset($_POST['idBorrar'])) {
        $idBorrar = $_POST['idBorrar'];
        $productes = file($file_path, FILE_IGNORE_NEW_LINES);
        $novosProductes = [];
        foreach ($productes as $producte) {
            $p = Producto::fromCSV($producte);
            if ($p && $p->id != $idBorrar) {
                $novosProductes[] = $p->toCSV();
            }
        }
        file_put_contents($file_path, implode("\n", $novosProductes));
        header("Location: dashboard_gestor.php");
        exit();
    }
}

// Modificar (PUT spoofing)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'PUT') {
    if (isset($_POST['accion']) && $_POST['accion'] == 'modificar' && isset($_POST['idModificar'])) {
        $idModificar = trim($_POST['idModificar']);
        $nuevoNombre = trim($_POST['nomProducteModificar']) ?? '';
        $nuevoPrecio = trim($_POST['preuProducteModificar']) ?? '';
        $nuevoIVA = trim($_POST['ivaProducteModificar']) ?? '';
        $nuevaDisponibilidad = trim($_POST['disponibilitatModificar']) ?? '';

        if (!file_exists($file_path)) {
            die("<p style='color: red;'>El archivo no existe: $file_path</p>");
        }
        $productes = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $productosActualizados = [];
        $productoModificado = false;
        foreach ($productes as $linea => $producte) {
            $p = Producto::fromCSV($producte);
            if (!$p) continue;
            if (trim($p->id) == $idModificar) {
                if (!empty($nuevoNombre)) $p->nombre = $nuevoNombre;
                if (!empty($nuevoPrecio)) $p->precio = $nuevoPrecio;
                if (!empty($nuevoIVA)) $p->iva = $nuevoIVA;
                if (!empty($nuevaDisponibilidad)) $p->disponibilidad = $nuevaDisponibilidad;
                $productoModificado = true;
            }
            $productosActualizados[] = $p->toCSV();
        }
        if ($productoModificado) {
            file_put_contents($file_path, implode("\n", $productosActualizados) . "\n");
            echo "<p style='color: green;'>Producto modificado correctamente.</p>";
        } else {
            echo "<p style='color: red;'>No se encontró un producto con el ID $idModificar.</p>";
        }
    }
}

// Visualització de productes
$productes = file($file_path, FILE_IGNORE_NEW_LINES);
usort($productes, function($a, $b) {
    $dataA = explode(",", $a);
    $dataB = explode(",", $b);
    return strcmp($dataA[0], $dataB[0]);
});

echo "<h2>Llista de productes:</h2>";
foreach ($productes as $producte) {
    $p = Producto::fromCSV($producte);
    if ($p) {
        echo "<p><strong>Nom:</strong> {$p->nombre}<br>
              <strong>ID:</strong> {$p->id}<br>
              <strong>Preu:</strong> {$p->precio}<br>
              <strong>IVA:</strong> {$p->iva}<br>
              <strong>Disponibilitat:</strong> {$p->disponibilidad}<br></p>";
    }
}

?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulari de Producte</title>
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
<h1>Gestionar Productes</h1>
<form method="POST">
    <!-- Crear  -->
    <input type="text" name="nomProducte" placeholder="Nom" ><br>
    <input type="number" name="idProducte" placeholder="ID" ><br>
    <input type="number" name="preuProducte" placeholder="Preu" step="0.01"><br>
    <input type="number" name="ivaProducte" placeholder="IVA" step="0.01"><br>
    <input type="radio" name="disponibilitat" value="si" > Disponible
    <input type="radio" name="disponibilitat" value="no" > No disponible<br><br>

    <button type="submit" name="accion" value="crear" placeholder="ID del producto a crear">Crear </button>
</form>
<br>
<!-- Eliminar (DELETE spoofing) -->
<form method="POST">
    <input type="hidden" name="_method" value="DELETE">
    <input type="number" name="idBorrar" placeholder="ID a borrar"><br>
    <button type="submit" name="accion" value="eliminar">Eliminar</button>
</form>
<br>
<!-- Modificar (PUT spoofing) -->
<form method="POST">
    <input type="hidden" name="_method" value="PUT">
    <h2>Modificar Producte</h2>
    <input type="text" name="nomProducteModificar" placeholder="Nombre del producto"><br>
    <input type="number" name="preuProducteModificar" placeholder="Precio" step="0.01"><br>
    <input type="number" name="ivaProducteModificar" placeholder="IVA" step="0.01"><br>
    <input type="radio" name="disponibilitatModificar" value="si"> Disponible
    <input type="radio" name="disponibilitatModificar" value="no"> No disponible<br><br>
    <input type="number" name="idModificar" placeholder="ID del producto a modificar" required><br><br>
    <button type="submit" name="accion" value="modificar">Modificar</button>
</form>
<a href='/PROYECTO/Projecto/index.php'>INICI</a>
<li><a href="\PROYECTO\Projecto\botiga\comandes\comanda_client.php">Gestionar comandas de gestor</a></li> 
</body>
</html>
