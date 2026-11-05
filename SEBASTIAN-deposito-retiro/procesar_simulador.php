<?php

session_start();

require_once "funciones.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: simulador.php");
    exit;
}

$accion = $_POST["accion"] ?? "";


// aca se ejecuta try/catch

try {
    switch ($accion) {

        // maquina idedntifica a ciudadano
        case "identificar":

            $codigo = strtoupper(trim($_POST["codigo"] ?? ""));

            if ($codigo === "") {
                throw new Exception("Debe escribir el código del ciudadano.");
            }

            $encontrado = buscarCiudadanoPorCodigo($codigo, leerUsuarios());

            if ($encontrado === null) {
                throw new Exception("No se encontró ningún ciudadano con ese código.");
            }

            $_SESSION["ciudadano_simulador"] = $encontrado["CodigoQR"];

            mensaje("exito", "Ciudadano identificado: " . $encontrado["Nombre"] . ".");
            header("Location: simulador.php");
            exit;

        case "depositar":

            if (!isset($_SESSION["ciudadano_simulador"])) {
                throw new Exception("Primero debe identificar al ciudadano.");
            }

            $material = trim($_POST["material"] ?? "");
            $cantidad = filter_var($_POST["cantidad"] ?? null, FILTER_VALIDATE_INT);

            if ($material === "") {
                throw new Exception("Debe seleccionar un material.");
            }

            if ($cantidad === false || $cantidad === null || $cantidad <= 0) {
                throw new Exception("La cantidad debe ser un número entero mayor que cero.");
            }

            $resultado = registrarDepositoSimulador($_SESSION["ciudadano_simulador"], $material, $cantidad);

            // si el ciudadano tambien tiene la sesion abierta, se le actualiza el saldo
            if (isset($_SESSION["usuario"]) && $_SESSION["usuario"]["Correo"] === $resultado["Correo"]) {
                $_SESSION["usuario"]["Saldo"] = $resultado["Saldo"];
            }

            mensaje(
                "exito",
                "Depósito registrado correctamente. Recompensa: ₡" .
                number_format($resultado["Recompensa"], 0, ",", ".") .
                ". Nuevo saldo: ₡" . number_format($resultado["Saldo"], 0, ",", ".") . "."
            );
            header("Location: simulador.php");
            exit;

            case "salir":
            unset($_SESSION["ciudadano_simulador"]);
            header("Location: simulador.php");
            exit;

        default:
            throw new Exception("Acción no reconocida.");
    }

//manejo de errores

} catch (Exception $e) {
    mensaje("error", "Error: " . $e->getMessage());
    header("Location: simulador.php");
    exit;
}
