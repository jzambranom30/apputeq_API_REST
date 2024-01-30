<?php
require 'config/database.php'; // Incluye la configuración de la base de datos
$conn = conectarDB();

require 'funciones/funciones.php';
$token = validarToken($conn);

if($token) {
    $user = leerRegistro($conn, 'usuario', "token = '{$token}'");
    $id = $user[0]['personaId'];

    // Recibir datos del formulario
    $fecha = isset($_POST['fecha']) ? mysqli_real_escape_string($conn, $_POST['fecha']) : null;
    $hora = isset($_POST['hora']) ? mysqli_real_escape_string($conn, $_POST['hora']) : null;

    // Validar datos aquí (asegurarse de que no estén vacíos, etc.)
    $error = [];

    if(!$fecha) {
        $error['fecha'] = "La fecha es obligatoria";
    }
    if(!$hora) {
        $error['hora'] = "La hora es obligatoria";
    }

    if(!empty($error)) {
        echo json_encode(["error" => $error]);
        exit();
    }

    $fechaexiste = leerRegistro($conn, 'cita', "fecha = '{$fecha}'");
    $horaexiste = leerRegistro($conn, 'cita', "hora = '{$hora}'");

    if (($fechaexiste && !$horaexiste) || (!$fechaexiste && $horaexiste)) {
        $cita = [
            'fecha' => $fecha,
            'hora' => $hora,
            'personaId' => $id
        ];
    
        $result = crearRegistro($conn, 'cita', $cita);
    
        if ($result) {
            echo json_encode(["success" => "Su cita ha sido registrado con éxito"]);
        } else {
            echo json_encode(["error" => "Error al registrar cita"]);
        }
    } else {
        echo json_encode(["error" => "Fecha y hora ocupadas, seleccione una fecha u horario diferente"]);
    }

} else {
    echo json_encode(["error" => "No ha iniciado sesión"]);
}
$conn->close();
?>
