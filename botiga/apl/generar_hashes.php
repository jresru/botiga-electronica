<?php
// Usuarios y contraseñas
$usuarios = [
    'admin' => 'FjeClot2425',
    'gestor1' => 'gestor',
    'cliente1' => 'cliente',
];

// Aquí se almacenan los nuevos gestores
// Si deseas cargar todos los datos previos guardados, se podría agregar aquí una lógica de lectura y escritura en el archivo.

$archivoUsuarios = 'usuarios.txt'; // Puedes cambiar el nombre a algo más adecuado si es necesario.

if (file_exists($archivoUsuarios)) {
    $gestoresGuardados = file($archivoUsuarios, FILE_IGNORE_NEW_LINES);
} else {
    $gestoresGuardados = [];
}

// Leer todos los gestores guardados y agregar uno nuevo
if (!empty($gestoresGuardados)) {
    foreach ($gestoresGuardados as $gestor) {
        echo $gestor . "<br>";
    }
}



// Crear el archivo usuarios.txt
$file = fopen('usuarios.txt', 'w');
foreach ($usuarios as $username => $password) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    fwrite($file, "$username,$hash\n");
}
fclose($file);

echo "Archivo usuarios.txt generado correctamente.";
?>
