
<?php

// Iniciar sesión si todavía no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cuenta administradora de prueba
$correoAdmin = "carlos.prueba@example.com";

// Si no hay sesión, enviar al login
if (
    !isset($_SESSION["usuario"]) ||
    !is_array($_SESSION["usuario"]) ||
    !isset($_SESSION["usuario"]["Correo"])
) {
    header("Location: ../DANIELGV-inicio-registro-login/login.php");
    exit;
}

// Verificar que sea el administrador autorizado
if ($_SESSION["usuario"]["Correo"] !== $correoAdmin) {
    http_response_code(403);
    exit("Acceso denegado. Solo el administrador puede entrar.");
}
