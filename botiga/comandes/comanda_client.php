<?php
session_start();

if (!isset($_SESSION['usuario']) || !isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'gestor') {
    echo "Acceso solo para gestores.";
    exit();
}

$gestor = $_SESSION['usuario'];
$usuariosFile = __DIR__ . '/../apl/usuarios.txt';
$comandesArchivo = __DIR__ . '/comandas.txt';

// Obtener clientes asignados a este gestor
$clientesAsignados = [];
if (file_exists($usuariosFile)) {
    $usuarios = file($usuariosFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($usuarios as $linea) {
        $campos = explode(',', $linea);
        if (isset($campos[6]) && trim($campos[6]) === 'cliente' && isset($campos[9]) && trim($campos[9]) === $gestor) {
            $clientesAsignados[] = trim($campos[0]);
        }
    }
}

// Interfaz para serialización
interface Registrable {
    public function toCSV();
    public static function fromCSV($line);
}

// Clase Comanda (Pedido)
class Comanda implements Registrable {
    public $nombreProducto;
    public $idProducto;
    public $precioProducto;
    public $ivaProducto;
    public $disponibilidad;
    public $usuario;

    public function __construct($nombreProducto, $idProducto, $precioProducto, $ivaProducto, $disponibilidad, $usuario) {
        $this->nombreProducto = $nombreProducto;
        $this->idProducto = $idProducto;
        $this->precioProducto = $precioProducto;
        $this->ivaProducto = $ivaProducto;
        $this->disponibilidad = $disponibilidad;
        $this->usuario = $usuario;
    }

    public function toCSV() {
        return "{$this->nombreProducto},{$this->idProducto},{$this->precioProducto},{$this->ivaProducto},{$this->disponibilidad},{$this->usuario}";
    }

    public static function fromCSV($line) {
        $data = explode(",", $line);
        if (count($data) !== 6) return null;
        return new static($data[0], $data[1], $data[2], $data[3], $data[4], $data[5]);
    }
}

// Procesar acciones del gestor
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['accion'])
    && isset($_POST['usuario'])
    && in_array(trim($_POST['usuario']), $clientesAsignados)
) {
    $usuarioCliente = trim($_POST['usuario']);
    $idProducto = trim($_POST['idProducto']);
    $accion = $_POST['accion'];
    $motivo = isset($_POST['motivo']) ? $_POST['motivo'] : '';
    $clienteCorreo = '';
    // Buscar correo del cliente
    foreach ($usuarios as $linea) {
        $campos = explode(',', $linea);
        if (isset($campos[0]) && $campos[0] === $usuarioCliente && isset($campos[4])) {
            $clienteCorreo = $campos[4];
            break;
        }
    }
    // Leer todas las comandas
    $contenido = file($comandesArchivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $nuevasComandas = [];
    foreach ($contenido as $linea) {
        $comanda = Comanda::fromCSV($linea);
        if ($comanda && $comanda->idProducto == $idProducto && $comanda->usuario == $usuarioCliente) {
            // Acción sobre esta comanda
            if ($accion === 'rechazar') {
                // Enviar email de rechazo
                require_once __DIR__ . '/../../vendor/autoload.php';
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'misterproxd4@gmail.com';
                    $mail->Password = 'gbrv xuiu bmjx uajx';
                    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    $mail->setFrom('misterproxd4@gmail.com', 'Botiga');
                    $mail->addAddress($clienteCorreo, $usuarioCliente);
                    $mail->isHTML(true);
                    $mail->Subject = "comanda rebutjada";
                    $mail->Body = "Tu comanda ha estat rebutjada.<br>Motiu: " . htmlspecialchars($motivo);
                    $mail->send();
                } catch (Exception $e) {}
                // No añadir la comanda (se elimina)
                continue;
            } elseif ($accion === 'tramitar') {
                // Enviar email de tramitación
                require_once __DIR__ . '/../../vendor/autoload.php';
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'misterproxd4@gmail.com';
                    $mail->Password = 'gbrv xuiu bmjx uajx';
                    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    $mail->setFrom('misterproxd4@gmail.com', 'Botiga');
                    $mail->addAddress($clienteCorreo, $usuarioCliente);
                    $mail->isHTML(true);
                    $mail->Subject = "tramitant la comanda";
                    $mail->Body = "La teva comanda està sent tramitada.";
                    $mail->send();
                } catch (Exception $e) {}
                // Añadir la comanda igual (sin cambios)
                $nuevasComandas[] = $comanda->toCSV();
            } elseif ($accion === 'finalizar') {
                // Enviar email de finalización
                require_once __DIR__ . '/../../vendor/autoload.php';
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'misterproxd4@gmail.com';
                    $mail->Password = 'gbrv xuiu bmjx uajx';
                    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    $mail->setFrom('misterproxd4@gmail.com', 'Botiga');
                    $mail->addAddress($clienteCorreo, $usuarioCliente);
                    $mail->isHTML(true);
                    $mail->Subject = "comanda enviada";
                    $mail->Body = "La teva comanda ha estat enviada i pagada.";
                    $mail->send();
                } catch (Exception $e) {}
                // No añadir la comanda (se elimina)
                continue;
            }
        } else if ($comanda) {
            $nuevasComandas[] = $comanda->toCSV();
        }
    }
    file_put_contents($comandesArchivo, implode(PHP_EOL, $nuevasComandas) . PHP_EOL);
    echo "<p style='color: green;'>Acción realizada correctamente.</p>";
}

// Mostrar comandas de los clientes asignados
if (!file_exists($comandesArchivo)) {
    echo "No hay comandas registradas en el sistema.";
    exit();
}
$contenido = file($comandesArchivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

echo "<h2>Comandas de tus clientes asignados</h2>";
echo "<table border='1' cellspacing='0' cellpadding='5'>";
echo "<thead><tr>
<th>Nombre Producto</th>
<th>ID Producto</th>
<th>Precio Producto</th>
<th>IVA Producto</th>
<th>Disponibilidad</th>
<th>Usuario</th>
<th>Acciones</th>
</tr></thead><tbody>";

foreach ($contenido as $linea) {
    $comanda = Comanda::fromCSV($linea);
    if ($comanda && in_array(trim($comanda->usuario), $clientesAsignados)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($comanda->nombreProducto) . "</td>";
        echo "<td>" . htmlspecialchars($comanda->idProducto) . "</td>";
        echo "<td>" . htmlspecialchars($comanda->precioProducto) . "</td>";
        echo "<td>" . htmlspecialchars($comanda->ivaProducto) . "%</td>";
        echo "<td>" . htmlspecialchars($comanda->disponibilidad) . "</td>";
        echo "<td>" . htmlspecialchars($comanda->usuario) . "</td>";
        echo "<td>
            <form method='post' style='display:inline;'>
                <input type='hidden' name='idProducto' value='" . htmlspecialchars($comanda->idProducto) . "'>
                <input type='hidden' name='usuario' value='" . htmlspecialchars(trim($comanda->usuario)) . "'>
                <button type='submit' name='accion' value='tramitar'>Tramitar</button>
            </form>
            <form method='post' style='display:inline;'>
                <input type='hidden' name='idProducto' value='" . htmlspecialchars($comanda->idProducto) . "'>
                <input type='hidden' name='usuario' value='" . htmlspecialchars(trim($comanda->usuario)) . "'>
                <button type='submit' name='accion' value='finalizar'>Finalizar</button>
            </form>
            <form method='post' style='display:inline;'>
                <input type='hidden' name='idProducto' value='" . htmlspecialchars($comanda->idProducto) . "'>
                <input type='hidden' name='usuario' value='" . htmlspecialchars(trim($comanda->usuario)) . "'>
                <input type='text' name='motivo' placeholder='Motivo rechazo' required>
                <button type='submit' name='accion' value='rechazar'>Rechazar</button>
            </form>
        </td>";
        echo "</tr>";
    }
}
echo "</tbody></table>";
echo "<a href='/PROYECTO/Projecto/botiga/apl/dashboard_gestor.php'>Volver al dashboard gestor</a>";
?>
