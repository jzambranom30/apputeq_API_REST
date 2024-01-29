<?php
require "config/database.php"; // Incluye tu script de conexión a la base de datos
$conn = conectarDB();

require "funciones/funciones.php";

// Recibir datos del formulario
$cedula = isset($_POST['cedula']) ? mysqli_real_escape_string($conn, filter_var($_POST['cedula'], FILTER_SANITIZE_STRING)) : null;
$nombre = isset($_POST['nombre']) ? mysqli_real_escape_string($conn, filter_var($_POST['nombre'], FILTER_SANITIZE_STRING)) : null;
$apellido = isset($_POST['apellido']) ? mysqli_real_escape_string($conn, filter_var($_POST['apellido'], FILTER_SANITIZE_STRING)) : null;
$edad = isset($_POST['edad']) ? mysqli_real_escape_string($conn, filter_var($_POST['edad'], FILTER_SANITIZE_NUMBER_INT)) : null;
$departamento = isset($_POST['departamento']) ? mysqli_real_escape_string($conn, filter_var($_POST['departamento'], FILTER_SANITIZE_STRING)) : null;
$cargo = isset($_POST['cargo']) ? mysqli_real_escape_string($conn, filter_var($_POST['cargo'], FILTER_SANITIZE_STRING)) : null;
$tipousuario = isset($_POST['tipousuario']) ? mysqli_real_escape_string($conn, filter_var($_POST['tipousuario'], FILTER_SANITIZE_STRING)) : null;
$email = isset($_POST['email']) ? mysqli_real_escape_string($conn, filter_var($_POST['email'], FILTER_SANITIZE_EMAIL)) : null;
$password = isset($_POST['password']) ? mysqli_real_escape_string($conn, $_POST['password']) : null;

// Validar datos aquí (asegurarse de que no estén vacíos, etc.)
$error = [];

if(!$email) {
    $error['email'] = "El email es obligatorio";
} elseif(!(validarCorreo($email))) {
    $error['email'] = "Correo inválido (solo se admiten con los dominios '@uteq.edu.ec' o '@msuteq.edu.ec')";
}

if(!$cedula) {
    $error['cedula'] = "La cédula es obligatoria";
}
if(!$nombre) {
    $error['nombre'] = "El nombre es obligatorio";
}
if(!$apellido) {
    $error['apellido'] = "El apellido es obligatorio";
}
if(!$edad) {
    $error['edad'] = "La edad es obligatoria";
}
if(!$departamento) {
    $error['departamento'] = "El departamento o facultad es obligatorio";
}
if(!$cargo) {
    $error['cargo'] = "El cargo o carrera es obligatorio";
}
if(!$tipousuario) {
    $error['tipousuario'] = "El tipo de usuario es obligatorio";
}
if(!$password) {
    $error['password'] = "La contraseña es obligatoria";
}

if(!empty($error)) {
    echo json_encode(["" => $error]);
    exit();
}

$registro = leerRegistro($conn, 'usuario', "correo = '{$email}'");
if ($registro) {
    echo json_encode(["email" => "El correo electrónico ya está en uso"]);
    exit;
}

$persona = [
    'cedula' => $cedula,
    'nombre' => $nombre,
    'apellido' => $apellido,
    'edad' => $edad,
    'departamento' => $departamento,
    'cargo' => $cargo,
    'tipousuario' => $tipousuario
];

$result = crearRegistro($conn, 'persona', $persona);

if ($result) {
    $personaId = $conn->insert_id; // Esto obtiene el ID autogenerado

    // Hashear la contraseña
    $passwordHashed = password_hash($password, PASSWORD_BCRYPT);

    $usuario = [
        'correo' => $email,
        'clave' => $passwordHashed,
        'personaId' => $personaId
    ];

    $result = crearRegistro($conn, 'usuario', $usuario);

    if ($result) {
        echo json_encode(["success" => "Usuario registrado con éxito"]);
    } else {
        echo json_encode(["error" => "Error al registrar el usuario"]);
    }
} else {
    echo json_encode(["error" => "Error al registrar los datos de la persona"]);
}

$conn->close();
?>
