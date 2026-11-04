
<?php
// DEPOSITOS Y RECOMPENSAS

require_once __DIR__ . "/../DANIELGV-inicio-registro-login/funciones.php";
require_once __DIR__ . "/maquinas.php";
date_default_timezone_set("America/Costa_Rica");

// Rutas de archivos
define("RUTA_DEPOSITOS", DIR_DATOS . "/depositos.txt");
define("RUTA_MATERIALES", __DIR__ . "/datos/materiales.txt");
define("RUTA_RETIROS", DIR_DATOS . "/retiros.txt");

// Leer materiales y recompensas
function leerMateriales()
{
    $materiales = array();
    if (!file_exists(RUTA_MATERIALES)) {
        return $materiales;
    }

    $archivo = fopen(RUTA_MATERIALES, "r");
    while (!feof($archivo)) {
        $linea = trim(fgets($archivo));
        if ($linea === "") {
            continue;
        }

        $partes = explode("|", $linea);
        if (count($partes) === 2 && is_numeric($partes[1]) && $partes[1] >= 0) {
            array_push($materiales, array(
                "Nombre" => $partes[0],
                "Valor" => (int)$partes[1]
            ));
        }
    }
    fclose($archivo);
    return $materiales;
}

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

// Registrar depósito y actualizar saldo
function registrarDeposito($correo, $codigoQR, $maquina, $material, $cantidad)
{
    if (!is_int($cantidad) || $cantidad <= 0) {
        throw new Exception("La cantidad debe ser mayor que cero.");
    }

    $maquinaValida = false;
    foreach (obtenerMaquinas() as $m) {
        if ($m["Ubicacion"] === $maquina && $m["Estado"] === "Activa") {
            $maquinaValida = true;
        }
    }
    if (!$maquinaValida) {
        throw new Exception("Debe seleccionar una máquina activa.");
    }

    $valor = null;
    foreach (leerMateriales() as $m) {
        if ($m["Nombre"] === $material) {
            $valor = $m["Valor"];
        }
    }
    if ($valor === null) {
        throw new Exception("El material seleccionado no es válido.");
    }

    // Buscar usuario por correo y código
    $usuarios = leerUsuarios();
    $indice = -1;
    for ($i = 0; $i < count($usuarios); $i++) {
        if ($usuarios[$i]["Correo"] === $correo && $usuarios[$i]["CodigoQR"] === $codigoQR) {
            $indice = $i;
            break;
        }
    }
    if ($indice === -1) {
        throw new Exception("No se encontró el usuario.");
    }

    // Calcular recompensa
    $recompensa = $cantidad * $valor;
    $usuarios[$indice]["Saldo"] = $usuarios[$indice]["Saldo"] + $recompensa;

    if (!is_dir(DIR_DATOS)) {
        mkdir(DIR_DATOS, 0777, true);
    }

    // Guardar depósito y saldo
    $fecha = date("Y-m-d H:i:s");
    $registro = $fecha . "|" . $correo . "|" . $codigoQR . "|" .
        $maquina . "|" . $material . "|" . $cantidad . "|" .
        $valor . "|" . $recompensa . "\n";

    $resultado = file_put_contents(RUTA_DEPOSITOS, $registro, FILE_APPEND | LOCK_EX);
    if ($resultado === false) {
        throw new Exception("No se pudo guardar el depósito.");
    }

    guardarUsuarios($usuarios);
    return array(
        "Recompensa" => $recompensa,
        "Saldo" => $usuarios[$indice]["Saldo"]
    );
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

// Registrar solicitud de retiro ficticia
function solicitarRetiro($correo, $metodo, $destino, $monto)
{
    if ($metodo !== "SINPE Móvil" && $metodo !== "Transferencia bancaria") {
        throw new Exception("Debe seleccionar un método de retiro.");
    }

    $destino = trim($destino);
    if ($destino === "" || strlen($destino) > 40 ||
        strpos($destino, "|") !== false || strpos($destino, "\n") !== false) {
        throw new Exception("Ingrese un destino válido.");
    }

    // Validar teléfono, IBAN y monto
    if ($metodo === "SINPE Móvil" && !preg_match('/^[0-9]{8}$/', $destino)) {
        throw new Exception("Digite un número de 8 dígitos.");
    }
    if ($metodo === "Transferencia bancaria" && !preg_match('/^CR[0-9]{20}$/i', $destino)) {
        throw new Exception("Digite un IBAN de Costa Rica válido.");
    }
    if (!is_int($monto) || $monto <= 0) {
        throw new Exception("El monto debe ser mayor que cero.");
    }

    $usuario = obtenerUsuarioActual($correo);
    if ($usuario === null) {
        throw new Exception("Usuario no encontrado.");
    }

    $disponible = saldoDisponibleParaRetiro($correo, $usuario["Saldo"]);
    if ($monto > $disponible) {
        throw new Exception("El saldo disponible es insuficiente.");
    }

    // Guardar solicitud pendiente
    if (!is_dir(DIR_DATOS)) {
        mkdir(DIR_DATOS, 0777, true);
    }

    $registro = date("Y-m-d H:i:s") . "|" . $correo . "|" .
        $metodo . "|" . $destino . "|" . $monto . "|Pendiente\n";

    $resultado = file_put_contents(RUTA_RETIROS, $registro, FILE_APPEND | LOCK_EX);
    if ($resultado === false) {
        throw new Exception("No se pudo guardar la solicitud.");
    }
}
