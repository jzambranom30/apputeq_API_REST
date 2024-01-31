<?php

// Función para validar el formato del correo electrónico y el dominio
function validarCorreo($correo):bool {
    // Expresión regular para validar el formato del correo electrónico
    $patronCorreo = '/^[a-zA-Z0-9._-]+@(uteq\.edu\.ec|msuteq\.edu\.ec)$/i';

    // Comprobamos si el correo coincide con el patrón
    if (preg_match($patronCorreo, $correo)) {
        return true;
    } else {
        return false;
    }
}

function validarToken($conn) {
    // Verificar si se recibió el token en la cabecera
    $headers = apache_request_headers();

    // Verificar si se proporciona la cabecera "Authorization"
    if (isset($headers['Authorization'])) {
        // Obtener el valor de la cabecera "Authorization"
        $authorizationHeader = $headers['Authorization'];

        // Verificar si el valor comienza con "Bearer "
        if (strpos($authorizationHeader, 'Bearer ') === 0) {
            // Eliminar el prefijo "Bearer " para obtener solo el token
            $token = substr($authorizationHeader, 7);
        } else {
            // Si no comienza con "Bearer ", asignar el valor completo
           $token = $authorizationHeader;
        }

    } else {
        // Si la cabecera "Authorization" no está presente, establecer $token como null
        $token = null;
    }
    
    // Verificar si el token es válido en la base de datos
    if ($token !== null) {
        $sql = "SELECT * FROM usuario WHERE token = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        // Verificar si la consulta devolvió filas (token válido)
        if ($result->num_rows > 0) {
            return $token; // El token es válido
        } else {
            return false; // El token no es válido
        }
    }

    return false; // Si no se proporcionó un token
}

function crearRegistro($conn, $tabla, $datos):bool {
    $campos = implode(", ", array_keys($datos));
    $valores = "'" . implode("', '", array_values($datos)) . "'";

    $sql = "INSERT INTO {$tabla} ({$campos}) VALUES ({$valores})";

    if ($conn->query($sql) === TRUE) {
        return true; // Registro creado con éxito
    } else {
        return false; // Error al crear el registro
    }
}

function leerRegistro($conn, $tabla, $condicion) {
    $sql = "SELECT * FROM {$tabla} WHERE {$condicion}";
    $resultado = $conn->query($sql);

    if ($resultado === false) {
        // Manejo del error de consulta SQL
        error_log("Error en la consulta SQL: " . $conn->error);
        return null;
    }

    if ($resultado->num_rows > 0) {
        $registros = [];
        while($fila = $resultado->fetch_assoc()) {
            $registros[] = $fila;
        }
        return $registros;
    } else {
        return false; // No se encontraron registros
    }
}

function actualizarRegistro($conn, $tabla, $datos, $condicion) {
    if(empty($datos)) {
        return false; // No hay datos para actualizar
    }

    $campos = [];
    $valores = [];
    foreach ($datos as $clave => $valor) {
        $campos[] = "{$clave} = ?";
        $valores[] = $valor;
    }

    $campos_sql = join(", ", $campos);
    $sql = "UPDATE {$tabla} SET {$campos_sql} WHERE {$condicion}";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        error_log("Error preparando consulta: " . $conn->error);
        return false;
    }

    $stmt->bind_param(str_repeat('s', count($datos)), ...$valores);

    if ($stmt->execute()) {
        return true;
        echo ("Consulta SQL: " . $sql);
    } else {
        error_log("Error al ejecutar la actualización: " . $stmt->error);
        return false;
    }
}

function generarTokenAzure($endpoint, $accessKey) {
    // Paso 1: Crear una nueva identidad
    $urlIdentity = "{$endpoint}/identities";
    $headers = [
        'Authorization: "Bearer ' . $accessKey . '"',
        'Content-Type: application/json'
    ];

    $ch = curl_init($urlIdentity);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "{}");

    $responseIdentity = curl_exec($ch);
    $statusCodeIdentity = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($statusCodeIdentity != 201) {
        curl_close($ch);
        return false; // Falla al crear la identidad
    }

    $dataIdentity = json_decode($responseIdentity);
    $identityId = $dataIdentity->id;

    // Paso 2: Generar un token para la identidad creada
    $urlToken = "{$endpoint}/identities/{$identityId}/:issueAccessToken";
    $body = json_encode(["scopes" => ["chat", "voip"]]);

    curl_setopt($ch, CURLOPT_URL, $urlToken);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_POST, true);

    $responseToken = curl_exec($ch);
    $statusCodeToken = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($statusCodeToken == 200) {
        // Retorna el JSON completo de la respuesta incluyendo identity, token, y expiresOn
        return $responseToken;
    } else {
        return false; // Falla al generar el token
    }
}



?>