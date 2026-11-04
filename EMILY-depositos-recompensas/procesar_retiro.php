
<?php
// PROCESAR SOLICITUDES DE RETIRO

session_start();
require_once "funciones_persona2.php";

// Verificar que los datos vengan del formulario
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: retiro.php");
    exit;
}

// Verificar que el ciudadano haya iniciado sesión
if (!isset($_SESSION["usuario"])) {
    header("Location: ../DANIELGV-inicio-registro-login/login.php");
    exit;
}

try {
    // Obtener y validar los datos
    $metodo = trim($_POST["metodo"] ?? "");
    $destino = trim($_POST["destino"] ?? "");
    $monto = filter_var($_POST["monto"] ?? null, FILTER_VALIDATE_INT);

    if ($metodo === "" || $destino === "") {
        throw new Exception("Debe completar todos los campos.");
    }

    if ($monto === false || $monto === null || $monto <= 0) {
        throw new Exception("El monto debe ser un número entero mayor que cero.");
    }

    // Identificar al ciudadano
    $correo = $_SESSION["usuario"]["Correo"];
    $usuario = obtenerUsuarioActual($correo);

    if ($usuario === null) {
        throw new Exception("No se encontró el usuario.");
    }

    // Registrar la solicitud y verificar el saldo
    solicitarRetiro($correo, $metodo, $destino, $monto);

    // Mostrar mensaje de éxito
    mensaje(
        "exito",
        "Solicitud de retiro registrada correctamente por ₡" .
        number_format($monto, 0, ",", ".") .
        ". Su solicitud está pendiente."
    );

    header("Location: panel.php");
    exit;

} catch (Exception $e) {
    mensaje("error", "Error: " . $e->getMessage());
    header("Location: retiro.php");
    exit;
}

