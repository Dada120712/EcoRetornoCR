<?php

session_start();

require_once "funciones_persona3.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: retiro.php");
    exit;
}

if (!isset($_SESSION["usuario"])) {
    header("Location: ../DANIELGV-inicio-registro-login/login.php");
    exit;
}

$accion = $_POST["accion"] ?? "";


// aca se ejecuta try7catch

try {
    switch ($accion) {

        case "solicitar":

            $metodo  = trim($_POST["metodo"] ?? "");
            $destino = trim($_POST["destino"] ?? "");
            $monto   = filter_var($_POST["monto"] ?? null, FILTER_VALIDATE_INT);

            if ($metodo === "" || $destino === "") {
                throw new Exception("Debe completar todos los campos.");
            }

            if ($monto === false || $monto === null || $monto <= 0) {
                throw new Exception("El monto debe ser un número entero mayor que cero.");
            }

            registrarSolicitudRetiro($_SESSION["usuario"]["Correo"], $metodo, $destino, $monto);

            mensaje(
                "exito",
                "Solicitud registrada por ₡" . number_format($monto, 0, ",", ".") .
                ". Quedó en estado Pendiente."
            );
            header("Location: solicitudes.php");
            exit;

        default:
            throw new Exception("Acción no reconocida.");
    }

// aca manejo de errores 
} catch (Exception $e) {
    mensaje("error", "Error: " . $e->getMessage());
    header("Location: retiro.php");
    exit;
}
