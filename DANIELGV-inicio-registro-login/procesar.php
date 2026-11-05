<?php

session_start();

require_once "funciones.php";



if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}


$accion = $_POST["accion"] ?? "";


//AQUI EJECUTAMOS LA ACCION DE TRY/CATCH PARA LOS ERROREES.

try {
    switch ($accion) {

        
        case "registrar":
            
            $usuario = validarRegistro($_POST);

            
            $usuarios = leerUsuarios();

            
            $correos = array_column($usuarios, "Correo");

            
            if (array_search($usuario["Correo"], $correos) !== false) {
                throw new Exception("Ya existe una cuenta registrada con ese correo.");
            }

            
            $usuario["CodigoQR"] = generarCodigoUsuario($usuario["Correo"]);

            
            array_push($usuarios, $usuario);
            guardarUsuarios($usuarios);

            mensaje("exito", "Cuenta creada correctamente. Ya puede iniciar sesión.");
            header("Location: login.php");
            exit;

        // PARA INICIAR SESION
        case "iniciar_sesion":
            
            $datos = validarLogin($_POST);

            
            $usuarios = leerUsuarios();
            $encontrado = buscarUsuarioPorCorreo($datos["Correo"], $usuarios);

            
            if ($encontrado === null || $encontrado["Contraseña"] !== $datos["Contraseña"]) {
                throw new Exception("Correo o contraseña incorrectos.");
            }

            
            $_SESSION["usuario"] = array(
                "Nombre"   => $encontrado["Nombre"],
                "Correo"   => $encontrado["Correo"],
                "CodigoQR" => $encontrado["CodigoQR"],
                "Saldo"    => $encontrado["Saldo"]
            );

            mensaje("exito", "Bienvenido de nuevo, " . $encontrado["Nombre"] . ".");

            // si es la cuenta administradora, lo mandamos directo al panel de Claudio
            if ($encontrado["Correo"] === "carlos.prueba@example.com") {
                header("Location: ../CLAUDIO-panel-admin/panel_admin.php");
                exit;
            }

            header("Location: index.php");
            exit;

        // ESTE ES PARA CERRAR SESIÓN
        case "cerrar_sesion":
            session_destroy();
            header("Location: index.php");
            exit;

        // ESTE SI ES UNA ACCION QUE ES RARA
        default:
            throw new Exception("Acción no reconocida.");
    }

//AQUI HICIMOS EL MANEJO DE LOS ERRORES

} catch (Exception $e) {
    mensaje("error", "Error: " . $e->getMessage());

    
    if ($accion === "registrar") {
        header("Location: registro.php");
    } else {

    //AQUI REDIRECCIONA AL FINAL
        header("Location: login.php");
    }
    exit;
}
