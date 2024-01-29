<?php
require 'config/database.php'; // Incluye la configuración de la base de datos
$conn = conectarDB();

require 'funciones/funciones.php';
$tokenautenticado = validarToken($conn);

// Verificar si el token es válido en la base de datos
if ($tokenautenticado) {
    date_default_timezone_set('America/Guayaquil');
    $ahora = date('Y-m-d H:i:s');
    actualizarRegistro($conn, 'usuario', ['actividadtoken' => $ahora], "token = '{$tokenautenticado}'");
    // Usa actualizarRegistro para borrar el token
    $actualizado = actualizarRegistro($conn, 'usuario', ['token' => null], "token = '{$tokenautenticado}'");

    if ($actualizado) {
        echo json_encode(["success" => "Ha cerrado sesión con éxito"]);
    } else {
        echo json_encode(["error" => "Error al actualizar el token"]);
    }
} else {
    // El token no es válido
    echo json_encode(["error" => "Token no válido"]);
    exit;
}

$conn->close();
?>
