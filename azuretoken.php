<?php
// Suponiendo que este archivo se llama generateToken.php
require 'config/database.php';
require 'config/azure.php';
require 'funciones/funciones.php';

$conn = conectarDB();
$endpoint = obtenerend();
$accessKey = obtenerkey();
$token = validarToken($conn);

if ($token) {
    $user = leerRegistro($conn, 'usuario', "token = '{$token}'");

    $identity = generarTokenAzure($endpoint, $accessKey);

    if ($identity) {

        $data = json_decode($response);

        var_dump($data);

        $identidad = $data->id;
        $azure_token = $data->token;

        $ident = actualizarRegistro($conn, 'usuario', $identidad, "token = '{$token}'");

        echo json_encode(array("token" => $azure_token));

    } else {
        echo json_encode(["error" => "No se pudo generar la identidad"]);
        exit;
    }
} else {
    echo json_encode(["error" => "No ha iniciado sesión"]);
}
?>
