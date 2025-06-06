<?php
session_start();

// Verificar si el usuario está autenticado y es un administrador
if (!isset($_SESSION['usuario']) || strpos($_SESSION['usuario'], 'admin') === false) {
    // Página de error personalizada
    http_response_code(403);
    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <title>Acceso Denegado</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f8f8f8; margin: 0; padding: 0; }
            .error-container {
                margin: 80px auto;
                max-width: 400px;
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 2px 8px #ccc;
                padding: 32px;
                text-align: center;
            }
            .error-container h1 { color: #c00; }
            .error-container a { color: #007bff; text-decoration: none; }
        </style>
    </head>
    <body>
        <div class='error-container'>
            <h1>Acceso Denegado</h1>
            <p>No tienes permisos para acceder a esta página.</p>
            <a href='login.php'>Iniciar sesión</a>
        </div>
    </body>
    </html>";
    exit();
}

// Información del administrador
$usuario = $_SESSION['usuario'];
$correo = $_SESSION['correo'] ?? 'No disponible';

// Archivo de usuarios
$archivoUsuarios = "usuarios.txt";
$adminUsuario = $usuario;

// --- POO: Definición de clases ---
interface Registrable {
    public function toCSV();
}

abstract class Usuario implements Registrable {
    protected $usuario;
    protected $hash;
    protected $id;
    protected $nombre;
    protected $correo;
    protected $telefono;
    protected $rol;

    public function __construct($usuario, $hash, $id, $nombre, $correo, $telefono, $rol) {
        $this->usuario = $usuario;
        $this->hash = $hash;
        $this->id = $id;
        $this->nombre = $nombre;
        $this->correo = $correo;
        $this->telefono = $telefono;
        $this->rol = $rol;
    }

    public function toCSV() {
        return "{$this->usuario},{$this->hash},{$this->id},{$this->nombre},{$this->correo},{$this->telefono},{$this->rol}";
    }
}

class Gestor extends Usuario {
    public function __construct($usuario, $hash, $id, $nombre, $correo, $telefono) {
        parent::__construct($usuario, $hash, $id, $nombre, $correo, $telefono, "gestor");
    }

    public static function fromCSV($line) {
        $data = explode(",", $line);
        // usuario,hash,id,nombre,correo,telefono,rol
        if (count($data) < 7 || trim($data[6]) !== "gestor") return null;
        return new self($data[0], $data[1], $data[2], $data[3], $data[4], $data[5]);
    }
}

class Cliente extends Usuario {
    private $cp;
    private $tarjeta;
    private $gestorAsignado;

    public function __construct($usuario, $hash, $id, $nombre, $correo, $telefono, $cp, $tarjeta, $gestorAsignado) {
        parent::__construct($usuario, $hash, $id, $nombre, $correo, $telefono, "cliente");
        $this->cp = $cp;
        $this->tarjeta = $tarjeta;
        $this->gestorAsignado = $gestorAsignado;
    }

    public function toCSV() {
        return "{$this->usuario},{$this->hash},{$this->id},{$this->nombre},{$this->correo},{$this->telefono},{$this->rol},{$this->cp},{$this->tarjeta},{$this->gestorAsignado}";
    }

    public static function fromCSV($line) {
        $data = explode(",", $line);
        // usuario,hash,id,nombre,correo,telefono,rol,cp,tarjeta,gestorAsignado
        if (count($data) < 10 || trim($data[6]) !== "cliente") return null;
        return new self($data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[7], $data[8], $data[9]);
    }
}

class UsuarioFactory {
    public static function crearGestor($usuario, $hash, $id, $nombre, $correo, $telefono) {
        return new Gestor($usuario, $hash, $id, $nombre, $correo, $telefono);
    }
    public static function crearCliente($usuario, $hash, $id, $nombre, $correo, $telefono, $cp, $tarjeta, $gestorAsignado) {
        return new Cliente($usuario, $hash, $id, $nombre, $correo, $telefono, $cp, $tarjeta, $gestorAsignado);
    }
}

require_once __DIR__ . '/../../vendor/autoload.php'; // Ajusta la ruta si es necesario

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dompdf\Dompdf;

// Función para enviar correo al crear un gestor
function enviarCorreoGestor($correo, $nombre) {
    $mail = new PHPMailer(true);
    try {
        // Configuración SMTP (ajusta según tu servidor)
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'misterproxd4@gmail.com'; // Cambia por tu correo
        $mail->Password = 'gbrv xuiu bmjx uajx'; // Cambia por tu contraseña o app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('misterproxd4@gmail.com', 'Botiga Admin');
        $mail->addAddress($correo, $nombre);

        $mail->isHTML(true);
        $mail->Subject = 'Alta como gestor en la botiga';
        $mail->Body = "Hola $nombre,<br>Has sido dado de alta como gestor en la botiga.";

        $mail->send();
        echo "<p style='color: green;'>Correo enviado a $correo</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>No se pudo enviar el correo: {$mail->ErrorInfo}</p>";
    }
}

// --- Función para validar contraseñas seguras ---
function password_segura($password) {
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password);
}

// Procesar el formulario de actualización, creación de gestores, creación de clientes o eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_manager'])) {
        // Crear gestor usando POO y factoría
        $nuevoGestorUsuario = trim($_POST['manager_username']);
        $nuevoGestorId = trim($_POST['manager_id']);
        $nuevoGestorContrasena = $_POST['manager_password'];
        // Validar contraseña segura
        if (!password_segura($nuevoGestorContrasena)) {
            echo "<p style='color: red;'>La contraseña debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas, un número y un símbolo.</p>";
        } else if (!empty($nuevoGestorUsuario) && !empty($nuevoGestorId) && !empty($nuevoGestorContrasena) && !empty($nuevoGestorNombre) && !empty($nuevoGestorCorreo) && !empty($nuevoGestorTelefono)) {
            $nuevoGestorHash = password_hash($nuevoGestorContrasena, PASSWORD_DEFAULT);
            $gestor = UsuarioFactory::crearGestor($nuevoGestorUsuario, $nuevoGestorHash, $nuevoGestorId, $nuevoGestorNombre, $nuevoGestorCorreo, $nuevoGestorTelefono);
            if (file_exists($archivoUsuarios)) {
                file_put_contents($archivoUsuarios, $gestor->toCSV() . PHP_EOL, FILE_APPEND);
                header('Location: dashboard_admin.php?gestor=ok');
                exit();
            } else {
                echo "<p style='color: red;'>El archivo de usuarios no existe.</p>";
            }
        } else {
            echo "<p style='color: red;'>Todos los campos para crear un gestor son obligatorios.</p>";
        }
    } elseif (isset($_POST['create_client'])) {
        // Crear cliente usando POO y factoría
        $nuevoClienteUsuario = trim($_POST['client_username']);
        $nuevoClienteId = rand(1000,9999); // O usa un campo de formulario si lo tienes
        $nuevoClienteContrasena = $_POST['client_password'];
        // Validar contraseña segura
        if (!password_segura($nuevoClienteContrasena)) {
            echo "<p style='color: red;'>La contraseña debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas, un número y un símbolo.</p>";
        } else if (!empty($nuevoClienteUsuario) && !empty($nuevoClienteContrasena) && !empty($nuevoClienteNombre) && !empty($nuevoClienteCorreo) && !empty($nuevoClienteTelefono) && !empty($nuevoClienteCP) && !empty($nuevoClienteTarjeta) && !empty($nuevoClienteGestor)) {
            $nuevoClienteHash = password_hash($nuevoClienteContrasena, PASSWORD_DEFAULT);
            $cliente = UsuarioFactory::crearCliente($nuevoClienteUsuario, $nuevoClienteHash, $nuevoClienteId, $nuevoClienteNombre, $nuevoClienteCorreo, $nuevoClienteTelefono, $nuevoClienteCP, $nuevoClienteTarjeta, $nuevoClienteGestor);
            if (file_exists($archivoUsuarios)) {
                file_put_contents($archivoUsuarios, $cliente->toCSV() . PHP_EOL, FILE_APPEND);
                header('Location: dashboard_admin.php?cliente=ok');
                exit();
            } else {
                echo "<p style='color: red;'>El archivo de usuarios no existe.</p>";
            }
        } else {
            echo "<p style='color: red;'>Todos los campos para crear un cliente son obligatorios.</p>";
        }
    } elseif (isset($_POST['delete_client'])) {
        // Eliminar cliente
        $usuarioEliminar = trim($_POST['client_username']);
        if (!empty($usuarioEliminar)) {
            if (file_exists($archivoUsuarios)) {
                $usuarios = file($archivoUsuarios, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                $usuariosActualizados = [];
                $encontrado = false;
                foreach ($usuarios as $linea) {
                    $campos = explode(',', $linea);
                    if (isset($campos[0]) && $campos[0] === $usuarioEliminar && isset($campos[6]) && trim($campos[6]) === 'cliente') {
                        $encontrado = true;
                        // Opcional: eliminar archivos de cistella y comanda del cliente
                        $cistella = __DIR__ . "/../../cistelles/{$usuarioEliminar}.txt";
                        $comanda = __DIR__ . "/../../comandes/{$usuarioEliminar}_comanda.txt";
                        if (file_exists($cistella)) unlink($cistella);
                        if (file_exists($comanda)) unlink($comanda);
                        continue; // No añadir este usuario
                    }
                    $usuariosActualizados[] = $linea;
                }
                file_put_contents($archivoUsuarios, implode(PHP_EOL, $usuariosActualizados) . PHP_EOL);
                header('Location: dashboard_admin.php?eliminado=' . ($encontrado ? 'ok' : 'no'));
                exit();
            } else {
                echo "<p style='color: red;'>El archivo de usuarios no existe.</p>";
            }
        } else {
            echo "<p style='color: red;'>Debes indicar el usuario del cliente a eliminar.</p>";
        }
    } else {
        // Actualizar datos del administrador...
        // Este código ya está en tu script original
    }

    if (isset($_POST['exportar_gestores_pdf'])) {
        if (file_exists($archivoUsuarios)) {
            $usuarios = file($archivoUsuarios, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $gestores = [];
            foreach ($usuarios as $usuarioLinea) {
                $campos = explode(',', $usuarioLinea);
                if (isset($campos[6]) && trim($campos[6]) === 'gestor') {
                    $gestores[] = $campos;
                }
            }
            // Ordenar por nombre (campo 3)
            usort($gestores, function($a, $b) {
                return strcmp($a[3], $b[3]);
            });
            $html = "<h1>Lista de Gestores</h1><table border='1' cellpadding='5'><tr><th>Usuario</th><th>ID</th><th>Nombre</th><th>Correo</th><th>Teléfono</th></tr>";
            foreach ($gestores as $campos) {
                $html .= "<tr>";
                $html .= "<td>" . htmlspecialchars($campos[0]) . "</td>";
                $html .= "<td>" . htmlspecialchars($campos[2]) . "</td>";
                $html .= "<td>" . htmlspecialchars($campos[3]) . "</td>";
                $html .= "<td>" . htmlspecialchars($campos[4]) . "</td>";
                $html .= "<td>" . htmlspecialchars($campos[5]) . "</td>";
                $html .= "</tr>";
            }
            $html .= "</table>";

            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream("gestores.pdf");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
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

<?php
// Mostrar información y formularios
echo "<h1>Bienvenido, administrador " . htmlspecialchars($usuario) . "</h1>";
echo "<p>Tu correo es: $correo</p>";

// Formulario de actualización
echo "<h2>Actualizar datos del administrador</h2>";
echo "<form method='POST'>
        <label for='username'>Nuevo usuario:</label>
        <input type='text' id='username' name='username' value='" . htmlspecialchars($adminUsuario) . "' required><br><br>
        
        <label for='password'>Nueva contraseña:</label>
        <input type='password' id='password' name='password' required><br><br>
        
        <label for='email'>Nuevo correo electrónico:</label>
        <input type='email' id='email' name='email' value='" . htmlspecialchars($correo) . "' required><br><br>
        
        <button type='submit'>Actualizar</button>
    </form>";

// Formulario de creación de gestores
echo "<h2>Registrar Gestor</h2>";
echo "<form method='POST'>
        <label>Nombre de Usuario: <input type='text' name='manager_username' required></label><br>
        <label>ID usuario: <input type='text' name='manager_id' required></label><br>
        <label>Contraseña: <input type='password' name='manager_password' required></label><br>
        <label>Nombre Completo: <input type='text' name='manager_name' required></label><br>
        <label>Correo Electrónico: <input type='email' name='manager_email' required></label><br>
        <label>Teléfono: <input type='text' name='manager_phone' required></label><br>
        <input type='submit' name='create_manager' value='Registrar Gestor'>
    </form>";

// Formulario de creación de clientes
echo "<h2>Registrar Cliente</h2>";
echo "<form method='POST'>
        <label>Nombre de Usuario: <input type='text' name='client_username' required></label><br>
        <label>Contraseña: <input type='password' name='client_password' required></label><br>
        <label>Nombre Completo: <input type='text' name='client_name' required></label><br>
        <label>Correo Electrónico: <input type='email' name='client_email' required></label><br>
        <label>Teléfono: <input type='text' name='client_phone' required></label><br>
        <label>Código Postal: <input type='text' name='client_cp' required></label><br>
        <label>Tarjeta de Crédito: <input type='text' name='client_credit_card' required></label><br>
        <label>Gestor Asignado: <input type='text' name='client_manager' required></label><br>
        <input type='submit' name='create_client' value='Registrar Cliente'>
    </form>";

// Formulario de eliminación de clientes
echo "<h2>Eliminar cliente</h2>";
echo "<form method='POST'>
        <input type='hidden' name='delete_client' value='1'>
        <label for='client_username'>Usuario del cliente:</label>
        <input type='text' id='client_username' name='client_username' required><br><br>
        <button type='submit'>Eliminar Cliente</button>
    </form>";

// Botón para exportar gestores a PDF
echo "<form method='POST' style='margin-top:20px;'><button type='submit' name='exportar_gestores_pdf'>Exportar Gestores a PDF</button></form>";

// Mostrar mensajes de éxito tras redirección
if (isset($_GET['cliente']) && $_GET['cliente'] === 'ok') {
    echo "<p style='color: green;'>Cliente creado correctamente.</p>";
}
if (isset($_GET['gestor']) && $_GET['gestor'] === 'ok') {
    echo "<p style='color: green;'>Gestor creado correctamente.</p>";
}
if (isset($_GET['eliminado'])) {
    if ($_GET['eliminado'] === 'ok') {
        echo "<p style='color: green;'>Cliente eliminado correctamente.</p>";
    } else {
        echo "<p style='color: red;'>No se encontró el cliente a eliminar.</p>";
    }
}

// Mostrar lista de gestores en pantalla (ordenados por nombre)
function mostrarGestores($archivoUsuarios) {
    if (file_exists($archivoUsuarios)) {
        $usuarios = file($archivoUsuarios, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $gestores = [];
        foreach ($usuarios as $usuarioLinea) {
            $campos = explode(',', $usuarioLinea);
            if (isset($campos[6]) && trim($campos[6]) === 'gestor') {
                $gestores[] = $campos;
            }
        }
        // Ordenar por nombre (campo 3)
        usort($gestores, function($a, $b) {
            return strcmp($a[3], $b[3]);
        });
        if (count($gestores) > 0) {
            echo "<h2>Lista de Gestores</h2>";
            echo "<table border='1' cellpadding='5'><tr><th>Usuario</th><th>ID</th><th>Nombre</th><th>Correo</th><th>Teléfono</th></tr>";
            foreach ($gestores as $campos) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($campos[0]) . "</td>";
                echo "<td>" . htmlspecialchars($campos[2]) . "</td>";
                echo "<td>" . htmlspecialchars($campos[3]) . "</td>";
                echo "<td>" . htmlspecialchars($campos[4]) . "</td>";
                echo "<td>" . htmlspecialchars($campos[5]) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No hay gestores registrados.</p>";
        }
    } else {
        echo "<p>No se encuentra el archivo de usuarios.</p>";
    }
}

// Mostrar lista de clientes en pantalla (ordenados por nombre de usuario)
function mostrarClientes($archivoUsuarios) {
    if (file_exists($archivoUsuarios)) {
        $usuarios = file($archivoUsuarios, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $clientes = [];
        foreach ($usuarios as $usuarioLinea) {
            $campos = explode(',', $usuarioLinea);
            if (isset($campos[6]) && trim($campos[6]) === 'cliente') {
                $clientes[] = $campos;
            }
        }
        // Ordenar por nombre de usuario (campo 0)
        usort($clientes, function($a, $b) {
            return strcmp($a[0], $b[0]);
        });
        if (count($clientes) > 0) {
            echo "<h2>Lista de Clientes</h2>";
            echo "<table border='1' cellpadding='5'><tr>
                <th>Usuario</th>
                <th>ID</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Rol</th>
                <th>Código Postal</th>
                <th>Tarjeta</th>
                <th>Gestor Asignado</th>
                <th>Correo Gestor</th>
            </tr>";
            foreach ($clientes as $campos) {
                // Buscar gestor asignado y su correo
                $gestorAsignado = isset($campos[9]) ? trim($campos[9]) : '';
                $correoGestor = '';
                if ($gestorAsignado !== '') {
                    foreach ($usuarios as $lineaGestor) {
                        $cGestor = explode(',', $lineaGestor);
                        if (isset($cGestor[0]) && $cGestor[0] === $gestorAsignado && isset($cGestor[4])) {
                            $correoGestor = $cGestor[4];
                            break;
                        }
                    }
                }
                echo "<tr>";
                echo "<td>" . htmlspecialchars($campos[0]) . "</td>";
                echo "<td>" . htmlspecialchars($campos[2]) . "</td>";
                echo "<td>" . htmlspecialchars($campos[3]) . "</td>";
                echo "<td>" . htmlspecialchars($campos[4]) . "</td>";
                echo "<td>" . htmlspecialchars($campos[5]) . "</td>";
                echo "<td>" . htmlspecialchars($campos[6]) . "</td>";
                echo "<td>" . (isset($campos[7]) ? htmlspecialchars($campos[7]) : '') . "</td>";
                echo "<td>" . (isset($campos[8]) ? htmlspecialchars($campos[8]) : '') . "</td>";
                echo "<td>" . htmlspecialchars($gestorAsignado) . "</td>";
                echo "<td>" . htmlspecialchars($correoGestor) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No hay clientes registrados.</p>";
        }
    } else {
        echo "<p>No se encuentra el archivo de usuarios.</p>";
    }
}

// Mostrar lista de gestores en pantalla
mostrarGestores($archivoUsuarios);

// Mostrar lista de clientes en pantalla
mostrarClientes($archivoUsuarios);

echo "<a href='logout.php'>Cerrar sesión</a>";
?>
