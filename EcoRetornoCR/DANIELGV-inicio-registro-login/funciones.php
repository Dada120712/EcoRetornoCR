<?php

//1. En esta va el manejador de errores que es persoalizado

set_error_handler(function ($nivel, $mensaje, $archivo, $linea) {
    throw new ErrorException($mensaje, 0, $nivel, $archivo, $linea);
});


// 2. Aqui van las contantes con las rutas


define("DIR_DATOS", __DIR__ . "/datos");
define("RUTA_USUARIOS", DIR_DATOS . "/usuarios.txt");


//3.Aqui validamos el correo

function validarCorreo($correo)
{
    return filter_var($correo, FILTER_VALIDATE_EMAIL) !== false;
}


//4.En esta parte LO QUE HICIMOS FUE GUARDAR UN MENSAJE EN LA SESIÓN

function mensaje($tipo, $texto)
{
    $_SESSION["mensaje"] = array("tipo" => $tipo, "texto" => $texto);
}


//5. en esta función lo que hacemos el codigo qr, para cada persona o usuario.


function generarCodigoUsuario($correo)
{
   
    return "ECR-" . strtoupper(substr(md5($correo . uniqid()), 0, 8));
}


//6.En este punto lo que se hace es validar toda la info de cada persona que vaya a registrarse

function validarRegistro($datos)
{
    $nombre     = trim($datos["nombre"] ?? "");
    $correo     = trim($datos["correo"] ?? "");
    $telefono   = trim($datos["telefono"] ?? "");
    $contraseña = trim($datos["contraseña"] ?? "");

    //el nombre tiene que tener x lo menos 6 caracteres, si no es un error
    if (strlen($nombre) < 6) {
        throw new Exception("El nombre no puede tener menos de 6 caracteres!!");
    }

    // en este el correo tiene que tener un formato valido y real, con el @
    if (!validarCorreo($correo)) {
        throw new Exception("El formato de correo no es valido");
    }

    //en el telefono, solo puede tener numeros y guiones, y que este entre 8 y 15 caracteres.
    if (!preg_match('/^[0-9\-\s]{8,15}$/', $telefono)) {
        throw new Exception("El teléfono debe tener entre 8 y 15 dígitos.");
    }

    // la contraseña por su parte tiene que ser fuerte entonces mínimo 10carac.
    if (strlen($contraseña) < 10) {
        throw new Exception("La contraseña debe tener al menos 10 caracteres., ya que debe de ser una contraseña fuerte.");
    }

    // Función de la flecha.
    $formatear = fn($t) => mb_convert_case($t, MB_CASE_TITLE, "UTF-8");

    
    return array(
        "Nombre"     => $formatear($nombre),
        "Correo"     => strtolower($correo),
        "Telefono"   => $telefono,
        "Contraseña" => $contraseña,
        "Saldo"      => 0
    );
}


//7. aqui hacemos lo mismo que el de registrase, pero para los datos de incio de sesión.

function validarLogin($datos)
{
    $correo     = trim($datos["correo"] ?? "");
    $contraseña = trim($datos["contraseña"] ?? "");

    if ($correo === "" || $contraseña === "") {
        throw new Exception("Debe completar correo y contraseña.");
    }

    return array("Correo" => strtolower($correo), "Contraseña" => $contraseña);
}


//8. en esta es la creación de los archivos txt, con el modelo w, que nos esneño el profe en la clase.
function guardarUsuarios($usuarios)
{
    if (!is_dir(DIR_DATOS)) {
        mkdir(DIR_DATOS, 0777, true);
    }

    $archivo = fopen(RUTA_USUARIOS, "w");

    foreach ($usuarios as $u) {
        fwrite($archivo, $u["Nombre"] . "|" . $u["Correo"] . "|" . $u["Telefono"] . "|"
            . $u["Contraseña"] . "|" . $u["CodigoQR"] . "|" . $u["Saldo"] . "\n");
    }

    fclose($archivo);
}


//9. ya en este es el archivo de los usuario en txt, para poder leer.

function leerUsuarios()
{
    $usuarios = array();

    if (!file_exists(RUTA_USUARIOS)) {
        return $usuarios;
    }

    $archivo = fopen(RUTA_USUARIOS, "r");

    while (!feof($archivo)) {
        $linea = trim(fgets($archivo));

        if ($linea === "") {
            continue;
        }

        $partes = explode("|", $linea);

        
        if (count($partes) === 6) {
            array_push($usuarios, array(
                "Nombre"     => $partes[0],
                "Correo"     => $partes[1],
                "Telefono"   => $partes[2],
                "Contraseña" => $partes[3],
                "CodigoQR"   => $partes[4],
                "Saldo"      => (int)$partes[5]
            ));
        }
    }

    fclose($archivo);

    return $usuarios;
}


//10. buscamos al usuario x correo, porque se supone que se crea la cuenta con la contraseña
function buscarUsuarioPorCorreo($correo, $usuarios)
{
    foreach ($usuarios as $u) {
        if ($u["Correo"] === $correo) {
            return $u;
        }
    }

    return null;
}
