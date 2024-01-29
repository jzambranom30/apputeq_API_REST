<?php
require 'config/database.php'; // Incluye la configuración de la base de datos
$conn = conectarDB();

require 'funciones/funciones.php';
$token = validarToken($conn);

if ($token) {
    $user = leerRegistro($conn, 'usuario', "token = '{$token}'");
    $id = $user[0]['personaId'];
    
    $registrosPersona = (leerRegistro($conn, 'persona', "id = {$id}"));
    $registrosCita = leerRegistro($conn, 'cita', "personaId = {$id}");
    // Combina los datos en una sola respuesta
    $respuesta = [
        "persona" => $registrosPersona,
        "citas" => $registrosCita
    ];

    echo json_encode($respuesta);
} else {
    echo json_encode(["error" => "No ha iniciado sesión"]);
}

?>