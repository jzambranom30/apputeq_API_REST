<?php
require 'config/database.php'; // Incluye la configuración de la base de datos
$conn = conectarDB();

require 'funciones/funciones.php'; // Asegúrate de ajustar esta ruta

// Obtén las credenciales desde la solicitud
// Primero, verifica si el input 'username' está establecido y no es null
$username = isset($_POST['username']) ? mysqli_real_escape_string($conn, filter_var($_POST['username'], FILTER_SANITIZE_EMAIL)) : null;
$password = isset($_POST['password']) ? mysqli_real_escape_string($conn, $_POST['password']) : null;

$error = [];

if(!$username) {
    $error['username'] = "El usuario es obligatorio";
}
if(!$password) {
    $error['password'] = "La contraseña es obligatoria";
}
if(!empty($error)) {
    echo json_encode($error);
    exit();
}


// Usa mostrarRegistros para buscar el usuario en la base de datos
$usuarios = leerRegistro($conn, 'usuario', "correo = '{$username}'");

if ($usuarios) {
    $idUser = $usuarios[0]["id"];
    // Verifica el hash de la contraseña
    if (password_verify($password, $usuarios[0]['clave'])) {
        // Genera un token de sesión de 64 caracteres
        $session_token = bin2hex(random_bytes(32));

        // Actualiza el token en la base de datos usando la función actualizarRegistro
        $actualizado = actualizarRegistro($conn, 'usuario', ['token' => $session_token], "id = {$idUser}");

        if ($actualizado) {
            date_default_timezone_set('America/Guayaquil');
            $ahora = date('Y-m-d H:i:s');
            actualizarRegistro($conn, 'usuario', ['actividadtoken' => $ahora], "id = '{$idUser}'");
            // Devuelve el token al cliente
            echo json_encode(array("token" => $session_token));
        }
    } else {
        echo json_encode(["password" => "Contraseña incorrecta"]);
    }
} else {
    echo json_encode(["username" => "Usuario no existe"]);
}

$conn->close();
?>
