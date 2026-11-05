
<?php
// DEPOSITOS Y RECOMPENSAS

require_once __DIR__ . "/../DANIELGV-inicio-registro-login/funciones.php";
require_once __DIR__ . "/maquinas.php";
date_default_timezone_set("America/Costa_Rica");

// Rutas de archivos
define("RUTA_DEPOSITOS", DIR_DATOS . "/depositos.txt");
define("RUTA_RETIROS", DIR_DATOS . "/retiros.txt");

// Leer depósitos
function leerDepositos()
{
    $depositos = array();
    if (!file_exists(RUTA_DEPOSITOS)) {
        return $depositos;
    }

    $archivo = fopen(RUTA_DEPOSITOS, "r");
    while (!feof($archivo)) {
        $linea = trim(fgets($archivo));
        if ($linea === "") {
            continue;
        }

        $partes = explode("|", $linea);
        if (count($partes) === 8) {
            array_push($depositos, array(
                "Fecha" => $partes[0],
                "Correo" => $partes[1],
                "CodigoQR" => $partes[2],
                "Maquina" => $partes[3],
                "Material" => $partes[4],
                "Cantidad" => (int)$partes[5],
                "Valor" => (int)$partes[6],
                "Recompensa" => (int)$partes[7]
            ));
        }
    }
    fclose($archivo);
    return $depositos;
}

// Historial del ciudadano
function historialUsuario($correo)
{
    $historial = array();
    foreach (leerDepositos() as $deposito) {
        if ($deposito["Correo"] === $correo) {
            array_push($historial, $deposito);
        }
    }
    return array_reverse($historial);
}

function obtenerUsuarioActual($correo)
{
    $usuarios = leerUsuarios();
    return buscarUsuarioPorCorreo($correo, $usuarios);
}

// Leer solicitudes de retiro
function leerRetiros()
{
    $retiros = array();
    if (!file_exists(RUTA_RETIROS)) {
        return $retiros;
    }

    $archivo = fopen(RUTA_RETIROS, "r");
    while (!feof($archivo)) {
        $linea = trim(fgets($archivo));
        if ($linea === "") {
            continue;
        }

        $partes = explode("|", $linea);
        if (count($partes) === 6) {
            array_push($retiros, array(
                "Fecha" => $partes[0],
                "Correo" => $partes[1],
                "Metodo" => $partes[2],
                "Destino" => $partes[3],
                "Monto" => (int)$partes[4],
                "Estado" => $partes[5]
            ));
        }
    }
    fclose($archivo);
    return $retiros;
}

// Consultar retiros del ciudadano
function retirosUsuario($correo)
{
    $lista = array();
    foreach (leerRetiros() as $retiro) {
        if ($retiro["Correo"] === $correo) {
            array_push($lista, $retiro);
        }
    }
    return array_reverse($lista);
}

// Calcular saldo disponible
function saldoDisponibleParaRetiro($correo, $saldo)
{
    $pendiente = 0;
    foreach (retirosUsuario($correo) as $retiro) {
        if ($retiro["Estado"] === "Pendiente") {
            $pendiente = $pendiente + $retiro["Monto"];
        }
    }
    return max(0, $saldo - $pendiente);
}
