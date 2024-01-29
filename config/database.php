<?php

function conectarDB () :mysqli {
    $server = 'localhost';
    $user = 'root';
    $pass = 'root';
    $database = 'apputeq';

    $db = mysqli_connect($server, $user, $pass, $database);
    
    if(!$db){
        echo "Error no se pudo conectar";
        exit;
    }
    return $db;
}