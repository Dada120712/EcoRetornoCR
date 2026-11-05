<?php

require_once __DIR__ . "/../DANIELGV-inicio-registro-login/funciones.php";
date_default_timezone_set("America/Costa_Rica");


// aca van las constantes con las rutas, depositos y retiros 

define("RUTA_MATERIALES_P3", __DIR__ . "/datos/materiales.txt");
define("RUTA_DEPOSITOS_P3", DIR_DATOS . "/depositos.txt");
define("RUTA_RETIROS_P3", DIR_DATOS . "/retiros.txt");


// lee los materiales con lo que paga cada uno por unidad

function leerMaterialesSimulador()
{
    $materiales = array();

    if (!file_exists(RUTA_MATERIALES_P3)) {
        return $materiales;
    }

    $archivo = fopen(RUTA_MATERIALES_P3, "r");

    while (!feof($archivo)) {
        $linea = trim(fgets($archivo));

        if ($linea === "") {
            continue;
        }

        $partes = explode("|", $linea);

        if (count($partes) === 2 && is_numeric($partes[1]) && $partes[1] >= 0) {
            array_push($materiales, array(
                "Nombre" => $partes[0],
                "Valor"  => (int)$partes[1]
            ));
        }
    }

    fclose($archivo);

    return $materiales;
}


// aca la maquina identifica al ciudadano con su codigo 

function buscarCiudadanoPorCodigo($codigo, $usuarios)
{
    foreach ($usuarios as $u) {
        if ($u["CodigoQR"] === $codigo) {
            return $u;
        }
    }

    return null;
}


// registra el deposito, calcula la recompensa y se la suma al saldo del usuario

function registrarDepositoSimulador($codigo, $material, $cantidad)
{
    if (!is_int($cantidad) || $cantidad <= 0) {
        throw new Exception("La cantidad debe ser un número entero mayor que cero.");
    }

    // busca el valor del material que eligieron
    $valor = null;
    foreach (leerMaterialesSimulador() as $m) {
        if ($m["Nombre"] === $material) {
            $valor = $m["Valor"];
        }
    }

    if ($valor === null) {
        throw new Exception("El material seleccionado no es válido.");
    }

    // busca al ciudadano por el codigo
    $usuarios = leerUsuarios();
    $indice = -1;

    for ($i = 0; $i < count($usuarios); $i++) {
        if ($usuarios[$i]["CodigoQR"] === $codigo) {
            $indice = $i;
            break;
        }
    }

    if ($indice === -1) {
        throw new Exception("No se encontró ningún ciudadano con ese código.");
    }

    // la recompensa es el valor del material por la cantidad
    $recompensa = $cantidad * $valor;
    $usuarios[$indice]["Saldo"] = $usuarios[$indice]["Saldo"] + $recompensa;

    if (!is_dir(DIR_DATOS)) {
        mkdir(DIR_DATOS, 0777, true);
    }

    $registro = date("Y-m-d H:i:s") . "|" . $usuarios[$indice]["Correo"] . "|" . $codigo . "|"
        . "Simulador de máquina" . "|" . $material . "|" . $cantidad . "|"
        . $valor . "|" . $recompensa . "\n";

    $resultado = file_put_contents(RUTA_DEPOSITOS_P3, $registro, FILE_APPEND | LOCK_EX);

    if ($resultado === false) {
        throw new Exception("No se pudo guardar el depósito.");
    }

    guardarUsuarios($usuarios);

    return array(
        "Recompensa" => $recompensa,
        "Saldo"      => $usuarios[$indice]["Saldo"],
        "Correo"     => $usuarios[$indice]["Correo"]
    );
}


// Leenm todas las solicitudes de retiro del txt

function leerSolicitudes()
{
    $solicitudes = array();

    if (!file_exists(RUTA_RETIROS_P3)) {
        return $solicitudes;
    }

    $archivo = fopen(RUTA_RETIROS_P3, "r");

    while (!feof($archivo)) {
        $linea = trim(fgets($archivo));

        if ($linea === "") {
            continue;
        }

        $partes = explode("|", $linea);

        if (count($partes) === 6) {
            array_push($solicitudes, array(
                "Fecha"   => $partes[0],
                "Correo"  => $partes[1],
                "Metodo"  => $partes[2],
                "Destino" => $partes[3],
                "Monto"   => (int)$partes[4],
                "Estado"  => $partes[5]
            ));
        }
    }

    fclose($archivo);

    return $solicitudes;
}


// historial mas nuevas primero

function solicitudesDeUsuario($correo)
{
    $lista = array();

    foreach (leerSolicitudes() as $s) {
        if ($s["Correo"] === $correo) {
            array_push($lista, $s);
        }
    }

    return array_reverse($lista);
}


// saldo disponible es el saldo menos lo que ya esta pendiente de retiro

function saldoDisponible($correo, $saldo)
{
    $pendiente = 0;

    foreach (solicitudesDeUsuario($correo) as $s) {
        if ($s["Estado"] === "Pendiente") {
            $pendiente = $pendiente + $s["Monto"];
        }
    }

    return max(0, $saldo - $pendiente);
}


// Valida y guarda la solicitud de retiro (ficticia)

function registrarSolicitudRetiro($correo, $metodo, $destino, $monto)
{
    if ($metodo !== "SINPE Móvil" && $metodo !== "Transferencia bancaria") {
        throw new Exception("Debe seleccionar un método de retiro.");
    }

    $destino = trim($destino);

    if ($destino === "" || strlen($destino) > 40 || strpos($destino, "|") !== false) {
        throw new Exception("Ingrese un destino válido.");
    }

    if ($metodo === "SINPE Móvil" && !preg_match('/^[0-9]{8}$/', $destino)) {
        throw new Exception("Para SINPE Móvil digite un número de 8 dígitos.");
    }

    if ($metodo === "Transferencia bancaria" && !preg_match('/^CR[0-9]{20}$/i', $destino)) {
        throw new Exception("Digite un IBAN de Costa Rica válido.");
    }

    if (!is_int($monto) || $monto <= 0) {
        throw new Exception("El monto debe ser un número entero mayor que cero.");
    }

    $usuario = buscarUsuarioPorCorreo($correo, leerUsuarios());

    if ($usuario === null) {
        throw new Exception("No se encontró el usuario.");
    }

    $disponible = saldoDisponible($correo, $usuario["Saldo"]);

    if ($monto > $disponible) {
        throw new Exception("El saldo disponible es insuficiente.");
    }

    if (!is_dir(DIR_DATOS)) {
        mkdir(DIR_DATOS, 0777, true);
    }

    // queda guardada como pendiente
    $registro = date("Y-m-d H:i:s") . "|" . $correo . "|" . $metodo . "|"
        . strtoupper($destino) . "|" . $monto . "|Pendiente\n";

    $resultado = file_put_contents(RUTA_RETIROS_P3, $registro, FILE_APPEND | LOCK_EX);

    if ($resultado === false) {
        throw new Exception("No se pudo guardar la solicitud.");
    }
}
