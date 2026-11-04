
<?php
// PROCESAR LOS DEPOSITOS

session_start();
require_once "funciones_persona2.php";

// Verificar que los datos vengan del formulario
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: deposito.php");
    exit;
}

// Verificar que el ciudadano haya iniciado sesión
if (!isset($_SESSION["usuario"])) {
    header("Location: ../DANIELGV-inicio-registro-login/login.php");
    exit;
}

try {
    // Obtener y validar los datos del formulario
    $maquina = trim($_POST["maquina"] ?? "");
    $material = trim($_POST["material"] ?? "");
    $cantidad = filter_var($_POST["cantidad"] ?? null, FILTER_VALIDATE_INT);

    if ($maquina === "" || $material === "") {
        throw new Exception("Debe completar todos los campos.");
    }

    if ($cantidad === false || $cantidad <= 0) {
        throw new Exception("La cantidad debe ser un número entero mayor que cero.");
    }

    // Identificar al usuario
    $correo = $_SESSION["usuario"]["Correo"];
    $usuario = obtenerUsuarioActual($correo);

    if ($usuario === null) {
        throw new Exception("No se encontró el usuario.");
    }

    // Registrar depósito y calcular recompensa
    $resultado = registrarDeposito(
        $correo,
        $usuario["CodigoQR"],
        $maquina,
        $material,
        $cantidad
    );

    // Actualizar saldo de la sesión
    $_SESSION["usuario"]["Saldo"] = $resultado["Saldo"];

    // Mostrar mensaje de éxito
    mensaje(
        "exito",
        "Depósito registrado correctamente. Ganaste ₡" .
        number_format($resultado["Recompensa"], 0, ",", ".") .
        " por reciclar."
    );

    header("Location: panel.php");
    exit;

} catch (Exception $e) {
    mensaje("error", "Error: " . $e->getMessage());
    header("Location: deposito.php");
    exit;
}