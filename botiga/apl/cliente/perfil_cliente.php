<?php
session_start();

// Verificamos que el usuario esté autenticado
if (!isset($_SESSION['usuario'])) {
    header('Location: C:\xampp\htdocs\PROYECTO\Projecto\botiga\apl\login.php');
    exit();
}

$datos_personales = [
    'Nom' => 'Joan Garcia',
    'Email' => 'joan.garcia@example.com',
    'Telèfon' => '123456789',
    'Adreça' => 'Carrer Gran, 123, Barcelona',
];
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Perfil del Client</title>
</head>
<body>
    <h1>Les teves dades personals</h1>
    <ul>
        <?php foreach ($datos_personales as $clau => $valor): ?>
            <li><strong><?= $clau ?>:</strong> <?= htmlspecialchars($valor) ?></li>
        <?php endforeach; ?>
    </ul>
    <a href="C:\xampp\htdocs\PROYECTO\Projecto\botiga\apl\logout.php">Tancar Sessió</a>
</body>
</html>
